# 🧪 Test Results — Employee Module & Hak Akses

## Test Recording

![Full browser test recording](C:/Users/hilma/.gemini/antigravity/brain/a29cf715-4f20-474d-be35-e59924ca9b7b/artifacts/test_recording.webp)

---

## Employee CRUD Results

| Test | Endpoint | Status | Notes |
|------|----------|--------|-------|
| **Create** | `POST /employees` | ✅ PASS | Toast "Karyawan berhasil ditambahkan", table updated, modal closed |
| **Read** | `GET /employees` | ✅ PASS | Table loads with correct columns (NAMA, NIK, POSISI, ROLE, AKSI) |
| **Update** | `PUT /employees/{id}` | ✅ PASS | Name changed from "Budi (Tanpa Akun)" → "Budi Updated", toast shown |
| **Delete** | `DELETE /employees/{id}` | ✅ PASS | Confirmation modal shown, employee removed, stat cards updated |
| **Assign Role** | `POST /employees/{id}/assign-role` | ✅ PASS | Role badge updated in table, role changed successfully |

## Hak Akses Results

| Test | Description | Status | Notes |
|------|-------------|--------|-------|
| **Role Selector** | Shows real role names from API | ✅ PASS | 6 roles: Manajer, Kurir, Admin Logistik, Ahli Gizi, Pemilik, SPPG Admin |
| **Stat Cards** | TOTAL FITUR / IZIN AKTIF / ROLE DIPILIH | ✅ PASS | Shows 12 features, correct active count, role name |
| **Permission Table** | Feature labels with icons | ✅ PASS | Dashboard, Distribusi, Karyawan, Finance, etc. |
| **Toggle Permission** | Click toggle → save to backend | ✅ PASS | Optimistic update, persists via `PUT /roles/{id}` |
| **Toggle Persistence** | Switch tabs → switch back | ✅ PASS | Toggle state persists after role tab switching |

---

## Backend Fixes Applied

> [!IMPORTANT]
> Two backend bugs were discovered and fixed during testing:

1. **`EmployeeController::store()`** — `sppg_id` was not set during employee creation, causing NOT NULL violation. Fixed with fallback chain: `auth.sppg_id → auth.employee.sppg_id → 1`.

2. **`UpdateEmployeeRequest::rules()`** — `$this->route('employee')` returns the bound **model object** (not ID) when route model binding is active, causing the unique NIK rule to serialize the whole model into SQL. Fixed by extracting `->id`.

3. **`UpdateEmployeeRequest::authorize()`** — Permission slug was `employee.edit` but route middleware checks `employee.update`. Fixed to match.
