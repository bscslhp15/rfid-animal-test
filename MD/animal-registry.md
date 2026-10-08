# BUILD PROMPT v4: Animal Identification & Health Record System (QR MVP)

> Paste this file into your AI coding assistant as the project brief.
> **Goal: build a working MVP that demonstrates the client's main functions. QR scanning only. No RFID, no deployment work yet.**

---

## 1. ROLE

You are a senior full-stack developer and data analyst. Build a clean, working MVP that can run locally and be shown to the client. Keep the structure clean so it can grow into the full system without a rewrite.

## 2. WHAT THE CLIENT NEEDS (main functions to demonstrate)

The City Veterinary Office wants a **universal animal record**. Today, owners keep a paper vaccination booklet that gets lost and is not shared between vets. The MVP must prove these functions:

1. **Register an animal** with identity, owner details, and a unique QR code
2. **Scan the QR with a phone camera** and instantly see the animal's profile
3. **Show owner info** so a stray or lost animal can be traced
4. **Record health data** (vaccinations, temperature) that stays with the animal
5. **Handle an unregistered QR** by letting an admin create the profile on the spot
6. **Show analytics** in an admin dashboard (totals, vaccinations due, recent scans)
7. **Print the full record** of an animal

## 3. MVP FEATURES

### 3.1 Users and roles
`admin`, `veterinarian`, `staff`, `owner`. Seed one demo account per role.

| Role | Edit animal identity | Add health records | See owner contact | Dashboard |
|---|---|---|---|---|
| admin | yes | yes | yes | yes |
| veterinarian | no | yes | yes | yes |
| staff | create/edit | yes | yes | limited |
| owner | own contact only | view own | own | own animals |
| public (not logged in) | no | no | **no** | no |

### 3.2 Animal registration (fillable form)
- Name, category (companion / livestock / gamefowl / wildlife), species, breed, sex, date of birth (or estimated), color/markings, photo
- Owner: full name, phone, address (barangay, city), email (optional), consent checkbox
- Admin/staff creates the record directly (`active`)
- Owner can self-register; record is `pending` until admin approves
- Species and breeds come from lookup tables with an "Other" option

### 3.3 QR code
- Generated automatically when the animal is registered
- QR content: `https://{domain}/t/{token}`. The token is random and non-sequential. Never put the database ID or personal data in the QR.
- View, download (PNG/SVG), and print a QR label (PDF)
- Admin can regenerate the QR (old one is deactivated, with reason)

### 3.4 Scan flow
- A "Scan" page opens the phone camera (`html5-qrcode`) and reads the QR
- Scanning with the phone's normal camera app also works because the QR is a link
- Result page `/t/{token}`:
  - **Found, authorized user:** full profile, owner contact, health records, quick actions (add vaccination, add temperature, edit, mark missing)
  - **Found, public:** limited profile only (name, photo, species, breed, sex, age, status, "Contact the City Veterinary Office"). No owner phone or address.
  - **Not found or inactive:** authorized user sees "Register this QR" with the token prefilled; public sees a friendly "not registered" message

### 3.5 Health records
- **Vaccinations:** vaccine (from admin-managed list), date given, next due date, batch no., administered by
- **Temperature:** value in °C, date/time, recorded by
- **Notes**
- Records are append-only. A correction creates a new entry that supersedes the old one.
- Do not hard-code vaccine schedules; the vaccine list and intervals are managed by admin.

### 3.6 Scan history
Log every scan: date/time, user (if logged in), result (found / not found), optional location text. Include a simple "Recent scans" page.

### 3.7 Missing flag
Owner or admin can mark an animal as **Missing**. A visible banner appears on the scan page and the animal appears in the dashboard.

### 3.8 Dashboard (admin)
- Cards: total animals, active, missing, pending approvals, vaccinations due in 7 / 30 days, overdue, scans this month
- Charts: animals by category/species, by sex, registrations per month, scans per day
- Animal table with filters (category, species, breed, sex, status, owner) and CSV export

### 3.9 Printable record
PDF of the full animal record: identity, owner, vaccination table, temperature history, and QR.

### 3.10 Demo data
Seeder with **100 realistic animals** (dogs, cats, cows, pigs, horses, roosters) with owners and vaccination/temperature history, so the dashboard is full from the first run.

## 4. TECH STACK (fixed)

| Layer | Choice |
|---|---|
| Backend | PHP 8.3 + **Laravel 11** |
| Database | **MySQL 8** (SQLite acceptable for local quick start) |
| UI | **Blade + Livewire 3 + Tailwind CSS**, Alpine.js, mobile-first |
| Charts | **Chart.js** |
| QR generate | **simplesoftwareio/simple-qrcode** |
| QR scan | **html5-qrcode** |
| Auth / roles | **Laravel Breeze + spatie/laravel-permission** |
| PDF | **barryvdh/laravel-dompdf** |
| Tests | **Pest** |

Put business logic in Actions/Services (not in controllers or Livewire components) so a REST API and mobile app can reuse it later.

## 5. DATABASE

```
organizations     id, name, type, address, contact, timestamps
users             id, organization_id, name, email, phone, password, status, timestamps   (+ role via spatie)
owners            id, user_id (nullable), full_name, phone, email, address, barangay, city, consent_at, timestamps
species           id, category (companion|livestock|gamefowl|wildlife), name
breeds            id, species_id, name
animals           id (uuid), name, species_id, breed_id, sex, birthdate, birthdate_estimated,
                  color_markings, photo_path, owner_id,
                  status (pending|active|missing|deceased),
                  attributes (json), registered_by, organization_id,
                  approved_by, approved_at, timestamps, soft deletes
tags              id, animal_id, identifier (unique, random), type (qr),
                  status (active|inactive), assigned_at, deactivated_at, deactivation_reason
vaccines          id, name, default_interval_days (nullable), active
vaccinations      id (uuid), animal_id, vaccine_id, given_on, next_due_on, batch_no,
                  administered_by, supersedes_id (nullable), notes, timestamps
health_records    id (uuid), animal_id, type (temperature|weight|note), value_numeric, unit,
                  details, recorded_at, recorded_by, supersedes_id (nullable)
scan_logs         id, tag_identifier, animal_id (nullable), user_id (nullable),
                  result (found|not_found), location_text, scanned_at
audit_logs        id, user_id, action, auditable_type, auditable_id, changes (json), ip, created_at
```

Design notes:
- The QR identifier lives in the **`tags`** table, not on the animal. This lets RFID/NFC tags be added later as new `type` values with no schema change.
- Animal IDs are UUIDs (for future offline sync). Single seeded organization for now.
- Index: `tags.identifier`, `animals.owner_id`, `animals.status`, `vaccinations.next_due_on`, `scan_logs.scanned_at`.
- Never hard-delete animals or health records.

## 6. BASIC SECURITY AND PRIVACY

- Policies on every model; owners only access their own animals
- Public `/t/{token}` route is rate-limited, `noindex`, and never shows owner phone/address
- Validation on all inputs; validated and size-limited image uploads
- Owner consent checkbox on registration (Data Privacy Act, RA 10173)
- Audit log for create, update, approve, and QR regeneration
- Use dummy data only

## 7. DEFINITION OF DONE

- [ ] Admin/staff registers an animal; owner self-registers and admin approves
- [ ] QR generated, downloadable, and printable
- [ ] Phone camera scan opens the correct profile (in-app scanner and native camera link)
- [ ] Authorized view shows owner and health data; public view hides private data
- [ ] Unregistered QR: authorized user can register it from the scan page
- [ ] Vaccination and temperature records can be added; "due soon / overdue" updates on the dashboard
- [ ] Missing flag works and shows on the scan page and dashboard
- [ ] Scan history is recorded and viewable
- [ ] Printable PDF record works
- [ ] Pest tests pass for registration, QR lookup, permissions, and scan logging
- [ ] App runs locally with `README.md` setup steps and demo seeder

## 8. CLIENT DEMO SCRIPT

1. Log in as admin and open the dashboard (full of demo data).
2. Register a new dog and show the generated QR and print label.
3. Scan the QR with a phone and show the full profile.
4. Add a vaccination and a temperature reading; show the dashboard update.
5. Open the same QR link in a private browser tab to show the limited public view.
6. Scan an unregistered QR and register it from the scan page.
7. Mark an animal as missing and scan it to show the alert.
8. Print the full PDF record.

## 9. OUT OF SCOPE FOR THIS MVP

RFID/NFC readers, native mobile app, offline sync, SMS/email alerts, ownership transfer workflow, feeding records, GPS tracking, multi-organization separation, government report formats, and deployment/hosting setup.

## 10. WORKING INSTRUCTIONS FOR THE AI

- Give me the folder structure and migration list first, then implement.
- State assumptions in one line and keep going; ask only if truly blocked.
- Build only what is in section 3. Stop after the MVP and wait for my approval.
- Explain any deviation from this spec and why.
