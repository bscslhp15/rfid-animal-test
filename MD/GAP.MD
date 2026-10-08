# FIX PROMPT (QR ONLY): Animal Registry MVP

> Paste this file into your AI coding assistant with the project `animal-registry-supported` open.
> **Scope: QR code only.** No RFID, no NFC, no mobile app, no offline sync, no deployment work.
> Work in order: **Part A (security fixes) → Part B (finish the MVP)**. Run `php artisan test` after each item.

**Existing project:** Laravel (Breeze + Livewire/Volt), spatie/laravel-permission, simple-qrcode, DomPDF, Pest, SQLite.
**Existing features:** animals, species, tags (QR), vaccinations, health_records, scan_logs, public page `/t/{token}`, PDF record, QR download, basic dashboard.

**Goal:** a working demo that proves the client's 7 main functions:
1. Register an animal with a unique QR
2. Scan the QR with a phone camera and see the profile
3. Show owner info (authorized users only)
4. Record vaccinations and temperature
5. Handle an unregistered QR (admin creates the profile)
6. Dashboard with statistics
7. Print the full record

---

## PART A: SECURITY FIXES (must do first)

### A1. Everyone who registers becomes `staff`
- `resources/views/livewire/pages/auth/register.blade.php` calls `assignRole('staff')`; `app/Http/Middleware/EnsureRegistryStaff.php` also auto-assigns `staff` when no admin/staff exists.
- Fix: remove both. New self-registered users get no role. Create the first admin via seeder or an artisan command. Admin creates staff accounts.
- Test: a newly registered user gets 403 on `/dashboard`, `/animals`, `/animals/{id}`.

### A2. Enforce roles with policies
- Create `AnimalPolicy` (and policies for vaccination/health record). Replace the `registry.staff` middleware with permission checks.

| Role | Create/edit animal | Add health records | See owner contact | Dashboard |
|---|---|---|---|---|
| admin | yes | yes | yes | yes |
| veterinarian | no | yes | yes | yes |
| staff | create/edit | yes | yes | limited |
| owner | own contact only | view own | own | own animals |
| public (QR scan) | no | no | **no** | no |

- Seed one demo user per role. Add one negative test per role.

### A3. Protect the public scan route `/t/{token}`
- Add throttling (60/min per IP), `noindex,nofollow` meta tag, and store a hashed IP in scan logs. Ignore repeat scans from the same IP within 1 minute.
- Replace the Tailwind CDN script on the public page with compiled Vite assets.
- Test: 429 after the limit; public page never shows owner phone or address.

### A4. Edit and correct records
- Add edit/update for animals (policy-protected).
- Vaccinations and health records: add `supersedes_id`. A correction creates a new row that replaces the old one in the UI; old rows are kept as history. Never hard-delete.
- Add `audit_logs` (user, action, model, old/new values, time) for create/update/status change.

### A5. Secrets
- Do not commit or zip `.env` or `database/database.sqlite`. Generate a new `APP_KEY` per environment. `APP_DEBUG=false` outside local.

---

## PART B: FINISH THE QR MVP

### B1. Unregistered QR flow (missing main function)
- Today `/t/{token}` returns 404 for unknown tokens and logs nothing.
- Unknown/inactive token: log the scan as `not_found`.
  - Logged-in authorized user: "This QR is not registered" with a **Register this QR** button that opens the create form with the token prefilled.
  - Public user: friendly "Not registered. Contact the City Veterinary Office." page.

### B2. In-app camera scanner
- Add `/scan` using `html5-qrcode` (install via npm, bundle with Vite). It opens the phone camera, reads the QR link, and goes to `/t/{token}`. Add a "type the code" fallback.

### B3. Full profile for logged-in users
- Authorized user opening `/t/{token}` sees the full profile (owner contact, health records, quick actions). Public visitors see the limited view only.

### B4. Dashboard
- Move queries out of `dashboard.blade.php` into a Livewire component or a `DashboardStats` class.
- **Fix the Overdue/Due-soon bug:** count animals whose *latest* vaccination for that vaccine is due or overdue. Right now a renewed vaccine still counts as overdue because every historical row is counted.
- Add cards: scans this month, pending approvals. Add Chart.js charts: animals by species/category, by sex, registrations per month, scans per day. Add a recent scans table.

### B5. Animal list
- Paginate (no `->get()` on the whole table). Filters: species, breed, sex, status, owner. Search by name or tag. CSV export of the filtered result.

### B6. Registration form
- Add photo upload (validated, size-limited), category, breed (lookup table with "Other"), estimated-birthdate flag, color/markings.
- PH phone validation, owner consent checkbox (store `consent_at`).
- Owner self-registration creates `pending`; admin approves or rejects.

### B7. QR management
- Printable QR label sheet (PDF).
- Regenerate QR: deactivate the old tag (`status`, `deactivated_at`, reason) and create a new one. Change `Animal::tag()` from `hasOne` to `hasMany` plus an `activeTag()` helper.
- Stop creating tags inside `show()` (a GET request must not write data). Create tags only at registration or through an explicit action.

### B8. Data quality
- `vaccines` lookup table (admin-managed, configurable interval); `vaccinations.vaccine_id` replaces free-text `vaccine_name`.
- `recorded_by` and `administered_by` become `user_id` foreign keys.
- Scan history page: date/time, user, result, location text.

### B9. Demo data
- Seeder with 100 realistic animals (dogs, cats, cows, pigs, horses, roosters) with owners, vaccination and temperature history, and one demo user per role.

---

## DEFINITION OF DONE

- [ ] Newly registered users have no access to registry data
- [ ] Each role can do only what the matrix allows (tests prove it)
- [ ] Public scan page is throttled, `noindex`, and hides owner contact
- [ ] Records can be corrected without losing history; audit log works
- [ ] Unregistered QR can be registered from the scan page
- [ ] In-app camera scan and native phone camera scan both open the right profile
- [ ] Dashboard numbers and charts are correct
- [ ] Animal list is paginated with filters and CSV export
- [ ] QR can be downloaded, printed on labels, and regenerated
- [ ] PDF record prints correctly
- [ ] 100-animal demo seeder runs; `php artisan test` passes

## CLIENT DEMO SCRIPT

1. Log in as admin and show the dashboard with demo data.
2. Register a new dog and show the generated QR.
3. Scan the QR with a phone and show the full profile.
4. Add a vaccination and a temperature reading; show the dashboard update.
5. Open the same QR link in a private tab to show the limited public view.
6. Scan an unregistered QR and register it on the spot.
7. Mark an animal missing and scan it to show the alert.
8. Print the full PDF record.

## RULES FOR THE AI

1. Read the codebase first and confirm each finding is still accurate.
2. Work in order A1 to B9. Add Pest tests (pass and fail paths) for each item and run the suite.
3. Give a short plan (files, migrations) before editing. Migrations must keep existing data.
4. Keep the `tags` table (QR only for now), UUID animal IDs, and logic in Actions/Services.
5. Do not add RFID, NFC, mobile app, offline sync, SMS, multi-organization, or deployment work.
6. State assumptions in one line and continue; ask only if truly blocked.
7. After each part, report: what changed, tests added, remaining risks.