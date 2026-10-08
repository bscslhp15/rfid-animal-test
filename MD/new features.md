# FEATURE EXPANSION PROMPT: Animal Registry (QR Prototype)

> Paste this file into your AI coding assistant with the project `animal-registry-supported` open.
> **Scope:** QR only. Prototype stage, so **security hardening is deferred** (see section 12). Do not build RFID, NFC, mobile app, offline sync, SMS, or deployment work.
> Work in the order of section 11. Run `php artisan test` after each step and add Pest tests for every new feature.

**Current stack:** Laravel + Breeze + Livewire/Volt, spatie/laravel-permission, simple-qrcode, DomPDF, Pest, SQLite. Dark sidebar layout in `resources/views/livewire/layout/navigation.blade.php`.

---

## 1. WHAT TO ADD

1. **Animal type selection** that supports farm animals, poultry, and gamefowl (not only dog/cat)
2. **Feeding records and feeding monitoring** (mainly for farm animals)
3. **Ownership transfer** with history
4. **Statistics** on the dashboard: total, active, missing, upcoming vaccinations, recent scans
5. **Analytics page with charts**
6. **Reports** (filter, CSV export, print/PDF, print whole records)
7. **Notifications/alerts**: vaccinations, medical schedules, missing animals, other conditions

## 2. WHERE EACH FEATURE GOES (navigation and pages)

Dashboard and Animals pages stay, but the new features get their own **sidebar items** so those two pages do not become crowded.

| Sidebar group | Item | Route | What it contains |
|---|---|---|---|
| Overview | **Dashboard** | `/dashboard` | Stat cards + 3 panels: upcoming vaccinations, recent scans, latest alerts |
| Overview | **Analytics** | `/analytics` | All charts and metrics (new) |
| Registry | **Animals** | `/animals` | List, register, edit; profile page has tabs (see 2.1) |
| Registry | **Ownership Transfers** | `/transfers` | All transfers across animals (new) |
| Health | **Vaccinations & Schedules** | `/health` | Upcoming/overdue vaccines, medical schedules (new) |
| Farm | **Feeding** | `/feeding` | Feeding log, bulk entry, schedules, feeding summary (new) |
| Monitoring | **Alerts** | `/alerts` | Alert list with unread badge on the sidebar item (new) |
| Monitoring | **Scan History** | `/scans` | All QR scans (new) |
| Reports | **Reports** | `/reports` | Report center: filters, export, print (new) |
| Account | Profile | `/profile` | Existing |

Also add a **bell icon with unread count** in the top header that links to `/alerts`.

**Nav refactor:** the sidebar markup is duplicated for desktop and mobile. Replace it with one array of nav items (label, route, active pattern, group, optional badge) rendered by a loop, so adding items is one line.

### 2.1 Animal profile page (`/animals/{animal}`) becomes tabbed
Tabs: **Overview** (identity + QR) · **Health** (vaccinations, temperature/weight, medical schedule) · **Feeding** (feeding log for this animal + add feeding) · **Ownership** (current owner, transfer history, "Transfer ownership" button) · **Scans** (scan history for this animal).
Use Alpine.js for tab switching (no extra page loads).

---

## 3. HOUSEKEEPING FIXES (do first; these block the new features)

1. **Species are duplicated** (seeded twice in the SQLite file). Make the seeder idempotent (`firstOrCreate`) and add a unique index on `(name, category)`. Provide `php artisan migrate:fresh --seed` as the clean reset.
2. **Measurements overwrite history.** `AnimalController::persistMeasurement()` updates the latest record instead of creating a new one, so temperature/weight trends (needed for charts and alerts) are lost. Change it to always **append** a new `health_records` row.
3. **Hard deletes.** Add `SoftDeletes` to `Animal` and `Vaccination`. Do not hard-delete vaccinations when edited in the form.
4. **Overdue/due-soon count is wrong.** It counts every historical vaccination row, so a renewed vaccine still counts as overdue. Count by the **latest** vaccination per animal + vaccine name only.
5. **Move dashboard queries out of `dashboard.blade.php`** into `App\Services\DashboardStats`. The new pages reuse this logic.
6. **Paginate `/animals`** (`paginate(15)`), and add filters: category, species, status, group, search by name / pet code / owner.
7. **Vets cannot add health records** (`storeVaccination` / `storeHealthRecord` use the `update` ability, which excludes `veterinarian`). Add an `addRecords` ability to `AnimalPolicy` allowing admin, staff, and veterinarian.
8. Split `AnimalController` as features are added: `FeedingController`, `OwnershipTransferController`, `AnalyticsController`, `ReportController`, `AlertController`, `ScanLogController`, `HealthController`, `DashboardController`.

---

## 4. FEATURE: ANIMAL TYPE (farm, poultry, gamefowl)

**Problem:** the form shows one flat "Type of animal" list (Dog, Cat, Cow, Pig, Horse, Rooster).

**Solution:** a two-step selection and category-specific fields.

### 4.1 Categories and species (seed these)
| Category | Species |
|---|---|
| `companion` | Dog, Cat, Rabbit, Pet bird |
| `livestock` | Cow, Carabao, Pig, Goat, Sheep, Horse |
| `poultry` | Chicken (layer/broiler/native), Duck, Turkey, Quail |
| `gamefowl` | Gamefowl |
| `wildlife` | Other wildlife (free-text species) |

Rename the existing "Rooster" to "Gamefowl" (the male/female/age class is a field, not a species). Add `poultry` and `wildlife` to the category values. Always include "Other (specify)" per category.

### 4.2 Form behavior
- Step 1: **Category** dropdown (labels: Pet / Companion, Farm Livestock, Poultry, Gamefowl (Pang-sabong), Wildlife).
- Step 2: **Species** dropdown filtered by the chosen category (Alpine.js).
- **Breed** is a text input with a `<datalist>` of suggestions from `config/breeds.php` (per species). Free text is allowed.
- Show the **category-specific fields** below (see 4.3). Hide the others.
- The same form is used on create and edit.

### 4.3 Category-specific fields (store in `animals.attributes` JSON)
Define them in `config/animal_categories.php` (key, label, input type, options, required) so a new field never needs a migration. Validate per category.

| Category | Fields |
|---|---|
| companion | color/markings, neutered/spayed (yes/no), license no. |
| livestock | farm/herd name, ear tag no., purpose (breeding, meat, dairy, draft), pen/barn, pregnancy status (female) |
| poultry | flock/batch id, coop/house, purpose (egg, meat), date placed |
| gamefowl | age class (stag, cock, pullet, hen), bloodline/strain, leg band no., wing band no., color/plumage, gamefarm name, sire, dam |
| wildlife | species (free text), capture/rescue location, rescue date, release status |

### 4.4 New columns on `animals`
- `group_name` (nullable string, indexed): herd / flock / pen. Used for bulk feeding and filtering.
- `quantity` (unsigned int, default 1): for poultry flocks registered as a batch (1 = individual animal).
- `attributes` (json, nullable).

Show the category badge and key attributes on the list, profile, public page, and printed record.

---

## 5. FEATURE: OWNERSHIP TRANSFER

**Placement:** "Transfer ownership" button on the animal profile (**Ownership** tab) + a global `/transfers` list page.

### 5.1 Table `ownership_transfers`
`id` (uuid), `animal_id`, `from_owner_name/phone/address`, `to_owner_name/phone/address`, `transfer_type` (sale, gift, adoption, inheritance, other), `transferred_on` (date), `price` (nullable decimal), `reference_no` (nullable: bill of sale / permit no.), `notes`, `recorded_by` (user_id), `created_at`.

### 5.2 Flow
1. Click **Transfer ownership** → form with current owner prefilled (read-only) and new owner fields.
2. On save, in one **DB transaction**: create the transfer row (snapshot of the previous owner), update `animals.owner_name/phone/address` to the new owner, create an info alert "Ownership transferred".
3. The animal's **QR and history stay unchanged**.
4. Ownership tab shows a **timeline** of all owners (oldest to newest).
5. `/transfers`: table with filters (date range, type, animal, owner name), CSV export.
6. **Print transfer certificate** (PDF via DomPDF): animal details, previous owner, new owner, date, reference no., QR.
7. Include the ownership history in the full printed animal record.

---

## 6. FEATURE: FEEDING RECORDS AND MONITORING

**Placement:** per-animal **Feeding tab** (profile) + global **Feeding** page (`/feeding`). Most useful for livestock, poultry, and gamefowl; also available for companion animals.

### 6.1 Tables
`feeding_logs`: `id` (uuid), `animal_id` (fk), `feed_type` (string), `quantity` (decimal), `unit` (kg, g, cup, scoop, liter), `fed_at` (datetime), `fed_by` (user_id), `cost` (nullable decimal), `notes`.
`feeding_schedules` (optional, step 3b): `id`, `animal_id` (nullable), `group_name` (nullable), `feed_type`, `quantity`, `unit`, `times_per_day`, `active`.

Feed types come from `config/feeds.php` (commercial feed, starter, grower, layer, finisher, grass/forage, grains, supplement, vitamins, kitchen scraps, other).

### 6.2 Screens
- **Animal Feeding tab:** table of feedings, "Add feeding" form, small summary (feedings this week, total quantity this week).
- **`/feeding` page:**
  - **Quick log:** choose **one animal**, **a group** (`group_name`), or **selected animals** (checkbox list with search) → creates one `feeding_logs` row **per animal** with the same feed type and quantity per animal.
  - Filters: date range, category, species, group, feed type.
  - Summary cards: feedings today, animals fed today, animals **not fed today** (for schedule-based animals), total feed this week/month, total cost this month.
  - Feeding log table (paginated) and CSV export.
  - Section **"Not fed in last 24h"** listing livestock/poultry with no feeding record.
- **Monitoring metrics** (also shown in Analytics): feed quantity per week by feed type, feedings per day, cost per month, feed per animal vs weight change.

---

## 7. FEATURE: ALERTS / NOTIFICATIONS

Prototype delivery: **in-app only** (bell badge, `/alerts`, dashboard panel). Design the code so email/SMS channels can be added later without changing the generator.

### 7.1 Tables
`alerts`: `id`, `type`, `severity` (info, warning, critical), `animal_id` (nullable), `title`, `message`, `due_on` (nullable date), `dedupe_key` (unique), `status` (new, read, dismissed, resolved), `triggered_at`, `read_at`.
`medical_schedules`: `id` (uuid), `animal_id`, `type` (checkup, treatment, deworming, other), `title`, `scheduled_for` (date), `status` (pending, done, cancelled), `completed_at`, `notes`, `created_by`.

### 7.2 Alert types
| Type | Trigger | Severity |
|---|---|---|
| `vaccine_due_soon` | latest vaccination `next_due_on` within N days | warning |
| `vaccine_overdue` | latest vaccination `next_due_on` is past | critical |
| `medical_due` | medical schedule due within N days / overdue | warning / critical |
| `missing_animal` | animal status changed to `missing` | critical |
| `missing_animal_scanned` | a QR of a `missing` animal is scanned (include location text) | critical |
| `abnormal_temperature` | newest temperature outside the configured range for the species/category | warning |
| `no_feeding` | livestock/poultry with no feeding log in N hours | warning |
| `ownership_transferred` | transfer completed | info |

### 7.3 Rules
- Thresholds live in `config/alerts.php`: `due_soon_days` (default 7), `no_feeding_hours` (default 24), temperature ranges per species/category. **Mark the temperature ranges as placeholders for a veterinarian to confirm; do not present them as medical facts.**
- Build `App\Services\AlertGenerator` that is **idempotent** (uses `dedupe_key`, e.g. `vaccine_due:{vaccination_id}:{next_due_on}`) and **auto-resolves** alerts when the condition clears (vaccine renewed, animal found, schedule done).
- Run it via an artisan command `alerts:generate`, scheduled hourly in `routes/console.php`. Event-based alerts (`missing_animal`, `missing_animal_scanned`, `ownership_transferred`) are created immediately in the controller/action.
- Add a **"Run alert check now"** button on `/alerts` for demos.
- Alerts page: filters by type/severity/status, mark as read, dismiss, mark all read, link to the animal.

### 7.4 Health & Schedules page (`/health`)
Tabs: **Upcoming vaccinations** (next 30 days) · **Overdue** · **Medical schedules** (add, mark done) · **Recent health records**. When a schedule is marked done, offer to create a health record.

---

## 8. FEATURE: DASHBOARD STATISTICS

Rebuild `/dashboard` using `DashboardStats`.

**Stat cards:** Total animals · Active · Missing · Pending · **Upcoming vaccinations (next 7 days)** · Overdue vaccinations · Feedings today · Open alerts.

**Panels (below the cards):**
1. **Upcoming vaccinations:** next 10 (animal, vaccine, due date, days left, owner).
2. **Recent scans:** last 10 (animal, date/time, location text, scanned by). Link to `/scans`.
3. **Latest alerts:** last 5 with severity color. Link to `/alerts`.
4. Keep the existing "Recent registrations" table (limit 5).

Each card links to the matching filtered page (e.g. Missing → `/animals?status=missing`).

---

## 9. FEATURE: ANALYTICS PAGE (`/analytics`)

Use **Chart.js** installed with npm and bundled by Vite (`npm install chart.js`). No CDN. Build data in `App\Services\AnalyticsData`; pass to the view as JSON.

**Filters at top:** date range, category, species.

**Charts:**
1. Animals by category (doughnut)
2. Animals by species (bar)
3. Animals by sex (pie)
4. Animals by status (doughnut)
5. Registrations per month (line, last 12 months)
6. Age groups (bar: under 1 yr, 1-3, 3-6, 6+, unknown)
7. Vaccination coverage: % of animals with no overdue vaccine (gauge or bar), and vaccinations given per month (bar)
8. Scans per day (line, last 30 days)
9. Feeding: feed quantity per week by feed type (stacked bar) and feeding cost per month (line)
10. Ownership transfers per month (bar)
11. Weight over time for a selected animal (line) and average weight by species (bar)
12. Alerts by type (bar)

Add small KPI tiles above the charts (total animals, vaccination coverage %, scans this month, feed cost this month). Each chart shows an "No data" state.

---

## 10. FEATURE: REPORTS AND PRINTING (`/reports`)

Report center with one card per report. Each report has filters, an on-screen preview (paginated), **Export CSV**, and **Print / PDF** (DomPDF). Every PDF has a header (system name, report title, filters used, date generated, generated by).

| # | Report | Key filters |
|---|---|---|
| 1 | Animal registry list | category, species, status, group, owner, date registered |
| 2 | Vaccination status (up to date / due soon / overdue) | category, species, vaccine, date range |
| 3 | Health records summary | animal, type, date range |
| 4 | Feeding summary (per animal, per group, per feed type, totals and cost) | date range, category, group, feed type |
| 5 | Ownership transfers | date range, type |
| 6 | Missing animals | date range |
| 7 | Scan history | date range, animal, result |
| 8 | **Full animal records (batch print)** | select animals or use filters; one PDF, one animal per page (reuse `animals.print`) |

The per-animal printed record (`animals.print`) must now also include: category-specific fields, feeding summary (last 30 days), ownership history, and medical schedule.

Also add `/scans` (Scan History): paginated table with filters (date range, animal, result, user) and CSV export.

---

## 11. IMPLEMENTATION ORDER

| Step | Work | Section |
|---|---|---|
| 0 | Housekeeping fixes and nav refactor | 3, 2 |
| 1 | Animal categories, species, category-specific fields | 4 |
| 2 | Ownership transfer | 5 |
| 3 | Feeding logs (3b: schedules) | 6 |
| 4 | Medical schedules + alerts + bell badge | 7 |
| 5 | Dashboard statistics rebuild | 8 |
| 6 | Analytics page with charts | 9 |
| 7 | Reports, scan history, batch print | 10 |
| 8 | Demo seeder and tests | 13 |

Each step ends with: migrations (data-preserving), Pest tests, updated printed record where relevant, and a short summary of what changed.

---

## 12. DEFERRED (do not work on now; prototype stage)

These are known, intentionally postponed:
- The public page `/t/{token}` currently shows owner name, **phone, and address** to anyone. Keep as-is for now; a later pass will hide private owner data from public view.
- Rate limiting, `noindex`, hashed IPs on the public route
- Audit log, 2FA, Data Privacy Act controls
- Unregistered-QR flow (currently 404) and in-app camera scanner from the earlier fix list. Do them after this expansion, or sooner if the client asks.
- RFID/NFC, mobile app, offline sync, SMS/email delivery, multi-organization, deployment

---

## 13. DEMO DATA AND TESTS

**Demo seeder (100+ animals):**
- Each category represented: dogs, cats, cows, carabao, pigs, goats, chickens, ducks, gamefowl (with bloodline, bands), one or two wildlife.
- Vaccination history including some due in 7 days, some overdue, some up to date.
- Temperature and weight history (several entries per animal for charts), a few abnormal temperatures.
- 300+ feeding logs over the last 8 weeks across groups; some animals with no feeding in the last 24 hours.
- A few ownership transfers, a few missing animals, 100+ scan logs over 30 days, a few medical schedules.
- Demo users: admin, staff, veterinarian, owner.
- Run alert generator at the end of seeding so `/alerts` is populated.

**Pest tests (minimum):** category filters species and validates category fields; gamefowl registration saves attributes; measurements append (not overwrite); transfer updates owner and keeps history; transfer rolls back on failure; feeding bulk logging creates one row per selected animal; alert generator is idempotent and auto-resolves; missing animal scan creates an alert; dashboard counts use latest vaccination only; analytics data endpoint returns expected shape; each report returns CSV and PDF; batch print returns one page per animal.

---

## 14. DEFINITION OF DONE

- [ ] Registering a **pig, carabao, chicken flock, and gamefowl** works with their own fields
- [ ] Ownership transfer works, history shows on the profile, PDF certificate prints
- [ ] Feeding can be logged for one animal, a group, or selected animals; "not fed in 24h" list works
- [ ] Alerts appear for due/overdue vaccines, medical schedules, missing animals (and scanned while missing), abnormal temperature, and no feeding
- [ ] Bell badge and `/alerts` page work, including "Run alert check now"
- [ ] Dashboard shows total, active, missing, upcoming vaccinations, and recent scans
- [ ] `/analytics` renders all charts from the demo data
- [ ] `/reports` exports CSV and PDF for every report; batch print of full records works
- [ ] Sidebar contains all items in section 2 with working active states
- [ ] `php artisan migrate:fresh --seed` gives a full demo and `php artisan test` passes

## 15. CLIENT DEMO SCRIPT

1. Dashboard: totals, upcoming vaccinations, recent scans, alerts.
2. Register a **gamefowl** (bloodline, bands), then a **pig** in a pen; show the generated QR.
3. Scan the QR with a phone and open the profile.
4. Log a **bulk feeding** for a pen; show the Feeding page summary and the "not fed" list.
5. **Transfer ownership** of an animal; show the timeline and print the certificate.
6. Mark an animal missing, scan it, then open **Alerts**.
7. Open **Analytics** and walk through the charts.
8. Open **Reports**, filter vaccination status, export CSV, and batch-print full records.

## 16. RULES FOR THE AI

1. Read the codebase first and confirm sections 3 and 12 are accurate; report any that are not.
2. Give a short plan (files, migrations, routes) before each step. Migrations must preserve existing data.
3. Keep logic in Services/Actions, thin controllers, Form Requests for validation, and config files for lists (species, feeds, alert thresholds, category fields).
4. Match the existing visual style (Tailwind, dark sidebar, white cards). Keep pages mobile-friendly.
5. Do not add features outside this file. State assumptions in one line and continue.
6. After each step report: what changed, tests added, remaining issues.