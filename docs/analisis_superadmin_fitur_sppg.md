# Analisis Fitur SuperAdmin — Kelola SPPG

> Dokumen ini memetakan alur, status implementasi, dan isu yang ditemukan untuk ketiga fitur SuperAdmin:
> **Daftar SPPG**, **Pengajuan SPPG**, dan **Map Rekomendasi**.

---

## 1. Daftar SPPG

### Alur Saat Ini

```
GET /api/super-admin/sppg             → SPPGController@index      → SPPGService::getAll()
GET /api/super-admin/sppg/{id}        → SPPGController@show       → SPPGService::findById()
GET /api/super-admin/sppg/{id}/partners → SPPGController@partners → langsung query Partner + MapService
GET /api/super-admin/sppg/{id}/menus  → SPPGController@menus      → langsung query Menu (NO sppg_id filter!)
PUT /api/super-admin/sppg/{id}        → SPPGController@update     → SPPGService::update()
DELETE /api/super-admin/sppg/{id}     → SPPGController@destroy    → SPPGService::delete()
POST /api/super-admin/sppg/{id}/deactivate → SPPGController@deactivate
POST /api/super-admin/sppg/{id}/activate   → SPPGController@activate
GET /api/super-admin/sppg/capacity-overview → SPPGController@capacityOverview
```

### Filter yang Tersedia (`SPPGService::getAll`)

| Filter | Field DB | Keterangan |
|---|---|---|
| `status` | `status` | active / inactive / pending |
| `city` / `kota` | `city` | case-insensitive LIKE |
| `district` / `kecamatan` | `district` | case-insensitive LIKE |
| `search` | `name` | case-insensitive LIKE |

### Data yang Dikembalikan `SPPGResource`

```json
{
  "id", "name", "address",
  "coordinates": { "lat", "lng" },
  "capacity", "status", "phone", "email",
  "region": { "district", "city", "province" },
  "owner": { "id", "name" },
  "total_mitra",
  "total_porsi",
  "total_penerima_manfaat",    ← ⚠️ BUG: nilai sama persis dengan total_porsi
  "schools_count",
  "schools",
  "capacity_status": { "filled", "percentage", "full" },
  "created_at", "updated_at"
}
```

### ⚠️ Isu & Bug Ditemukan

| # | Tingkat | Lokasi | Masalah |
|---|---|---|---|
| 1 | 🔴 **BUG** | `SPPGResource.php` L35 | `total_penerima_manfaat` = copy-paste dari `total_porsi`. Seharusnya dihitung terpisah (jumlah siswa penerima), bukan alias. |
| 2 | 🔴 **BUG** | `SPPGController@menus` | Query `Menu::with(...)` tidak di-filter by `sppg_id`. Semua menu di semua SPPG akan dikembalikan. |
| 3 | 🔴 **BUG** | `SPPGService@assignSchool` | Masih pakai field Indo: `tanggal_bergabung` dan `status = 'aktif'` untuk `SPPGSchool`. Perlu disesuaikan ke English (`joined_at`, `active`). |
| 4 | 🟡 **MINOR** | `SPPGService@assignSchool` | Juga masih pakai `status = 'pindah'` dan `status = 'nonaktif'` (value string Indo). |
| 5 | 🟡 **MINOR** | `SPPGController@index` | Filter masih menerima key Indo `kota` dan `kecamatan`. Tidak konsisten dengan standar sistem. Seharusnya hanya `city` dan `district`. |
| 6 | 🟡 **INCOMPLETE** | `SPPGController@show` | `capacity` dari `SPPGCapacityService` dihitung berdasarkan `schools` relasi (sekolah aktif), sedangkan `total_mitra` di resource dihitung dari `partners`. Dua hal berbeda — perlu klarifikasi mana yang benar. |
| 7 | 🟡 **MINOR** | `SPPG.php` | `pemilik_id` masih nama field Indo. Seharusnya `owner_id`. Diperbaiki di migration dan semua referensinya. |

---

## 2. Pengajuan SPPG

### Alur Saat Ini

```
Fase 1 — Draft Pendaftaran (Multi-Step Form)
────────────────────────────────────────────
POST /api/super-admin/sppg-submissions         → SppgSubmissionController@store
  ↳ Auto-save / upsert SppgDraft milik user
  ↳ Generate submission_number: DRAFT-YYYYMMDD-XXX
  ↳ Terima: form1_data, form2_data, form3_data (JSON)
  ↳ Terima: partners[] array + koordinat peta

GET  /api/super-admin/sppg-submissions         → index  (list semua draft)
GET  /api/super-admin/sppg-submissions/{id}    → show   (detail satu draft)
PUT  /api/super-admin/sppg-submissions/{id}    → update (update draft)
DELETE /api/super-admin/sppg-submissions/{id}  → destroy (hapus draft)

Fase 2 — Submit & Registrasi
─────────────────────────────
POST /api/super-admin/sppg-submissions/{id}/submit → SppgSubmissionController@submit
  ↳ Validasi: form1_data & form2_data wajib ada
  ↳ Validasi: minimal 1 partner
  ↳ Panggil SppgRegistrationService::register()
  ↳ Status draft berubah → 'registered'
```

### Yang Dilakukan `SppgRegistrationService::register()`

```
DB Transaction:
1. Buat SPPG baru (status: inactive)
2. Seed default roles & permissions untuk SPPG (DefaultRolePermissionSeeder)
3. Buat User Admin SPPG → sppg_id = SPPG baru
4. Update SPPG.pemilik_id = Admin SPPG user
5. Buat Employee record untuk Admin SPPG (position: owner)
6. Queue email AccountCreatedMail ke Admin SPPG
7. (Optional) Buat User Nutritionist → Employee record + queue email
8. (Optional) Buat User Logistics Admin → Employee record + queue email
9. Insert/link Partners ke SPPG baru
```

### Struktur Data Draft

```
SppgDraft {
  submission_number, submitted_by, source,
  form1_data (JSON),       ← Data SPPG (name, address, city, dst)
  form2_data (JSON),       ← Data Admin SPPG (name, email, password)
  form3_data (JSON),       ← Data Nutritionist & Logistics Admin (opsional)
  latitude, longitude,     ← Koordinat SPPG (raw)
  confirmed_latitude, confirmed_longitude,  ← Koordinat dikonfirmasi via map
  point_status,            ← green / yellow / red
  map_confirmed,           ← boolean
  status                   ← draft / registered
}

SppgDraftPartner {
  draft_id, school_name, npsn,
  level, school_status,
  address, city, district,
  latitude, longitude,
  jumlah_porsi,            ← ⚠️ MASIH NAMA INDO
  data_source              ← database / manual
}
```

### ⚠️ Isu & Bug Ditemukan

| # | Tingkat | Lokasi | Masalah |
|---|---|---|---|
| 1 | 🔴 **BUG** | `SppgSubmissionController@index` | Tidak ada filter scope. Semua draft dari **semua user** dikembalikan tanpa batasan. Seharusnya hanya superadmin yang bisa lihat semua, tapi sebaiknya tetap ada filter. |
| 2 | 🔴 **BUG** | `SppgSubmissionController@store` | Hanya meng-upsert draft dengan status `draft` milik user aktif — tapi tidak ada guard jika user submit lagi setelah draft sudah `registered`. |
| 3 | 🔴 **BUG** | `SppgSubmissionController@submit` L205 | Masih fallback ke key Indo: `ahli_gizi` dan `admin_logistik`. Tidak konsisten — seharusnya hanya `nutritionist` dan `logistics_admin`. |
| 4 | 🟡 **INCOMPLETE** | `SppgRegistrationService` | SPPG dibuat dengan `status: inactive`. Tidak ada endpoint/flow untuk aktivasi setelah daftar. Superadmin harus manually `POST /{id}/activate` — tidak ada notifikasi atau step otomatis. |
| 5 | 🟡 **MASALAH NAMA** | `SppgDraftPartner.php` | Field `jumlah_porsi` masih nama Indo. Harus di-rename ke `portion_count` (butuh migration). |
| 6 | 🟡 **MASALAH NAMA** | `SppgDraftPartner.php` | Field `school_status` vs `ownership_status`, dan `level` vs `school_type` — tidak konsisten dengan model `Partner` final. |
| 7 | 🟡 **TIDAK ADA VALIDASI** | `SppgSubmissionController@store` & `@update` | Tidak ada `FormRequest`. Semua field diterima mentah dengan `$request->only(...)`. Tidak ada type-check, max length, required rules. |
| 8 | 🟡 **MINOR** | `SppgRegistrationService` L56 | `$sppg->pemilik_id` masih nama Indo (harusnya `owner_id`). |
| 9 | 🟡 **SECURITY** | `SppgRegistrationService` L77-78 | Password Admin SPPG dikirim ke email dalam bentuk plaintext. Tidak ada enkripsi tambahan. Perlu dievaluasi. |

---

## 3. Map Rekomendasi

### Alur Saat Ini

```
GET  /api/super-admin/map/data              → MapController@getMapData
  ↳ Gabungkan: sppg_layers + submission_layers + recommendations

GET  /api/super-admin/map/sppg-layers       → MapController@getSppgLayers
  ↳ Query SPPG (status: active) with partners → toArray()

GET  /api/super-admin/map/submission-layers → MapController@getSubmissionLayers
  ↳ Query SppgDraft (whereNotNull lat/lng) with partners → toArray()

GET  /api/super-admin/map/recommendations   → MapController@getRecommendations
  ↳ MapService::getKMeansRecommendations()

POST /api/super-admin/map/geocode           → MapController@geocode
  ↳ Proxy Nominatim OpenStreetMap API

POST /api/super-admin/map/route-check       → MapController@routeCheck
  ↳ MapService::getRouteDurationAndDistance() via OSRM

POST /api/super-admin/map/validate-point    → MapController@validatePoint
  ↳ MapService::validatePoint() → green / yellow / red

POST /api/super-admin/map/suggest-shift     → MapController@suggestShift
  ↳ MapService::suggestCentroidShift() → centroid baru

POST /api/super-admin/map/confirm-point/{submission_id} → MapController@confirmPoint
  ↳ Re-validasi titik → save confirmed_lat/lng + point_status ke SppgDraft
```

### Logika K-Means (`MapService::getKMeansRecommendations`)

```
Input:
  → Partner tanpa sppg_id (unserved)
  → Partner dengan sppg_id tapi jarak ke SPPG > 5km (takeover candidate)

Algoritma:
  → K = max(1, floor(count(points) / 200))
  → Init K centroids secara random (shuffle + slice)
  → Iterasi max 20x:
      - Assign setiap point ke centroid terdekat
      - Recalculate centroid (mean lat/lng per cluster)
      - Stop jika tidak ada pergerakan > 0.0001 derajat
  → Filter output: hanya centroid yang punya ≥ 3 sekolah dalam radius 5km

Output per rekomendasi:
  → latitude, longitude, school_count, schools[]
```

### Logika Validasi Titik (`MapService::validatePoint`)

```
Status Poin:
  🟢 green  → tidak ada konflik
  🟡 yellow → ada konflik tapi bisa (SPPG nearby sudah overcapacity, atau mitra bisa takeover)
  🔴 red    → ada konflik serius (SPPG nearby masih punya kapasitas, atau mitra tidak bisa ditakeover)

Aturan:
  1. Jika ada SPPG aktif dalam radius ≤5km:
     - SPPG overcapacity → yellow
     - SPPG masih ada kapasitas → red
  2. Untuk setiap draft partner:
     - Jika partner sudah dilayani SPPG lain ≤5km DAN durasi ≤30 menit → red (tidak bisa takeover)
     - Jika partner bisa takeover (SPPG baru lebih dekat) → yellow
```

### ⚠️ Isu & Bug Ditemukan

| # | Tingkat | Lokasi | Masalah |
|---|---|---|---|
| 1 | 🔴 **PERFORMANCE** | `MapService::getKMeansRecommendations` L176-191 | Semua Partner dan SPPG di-load ke memory. Jika data besar (ribuan partner), ini sangat boros. Perlu ditambah batasan atau query optimization. |
| 2 | 🔴 **PERFORMANCE** | `MapService::validatePoint` L93-98 | Untuk pengecekan takeover, semua partner dengan `sppg_id` di-load ke memory lalu di-filter via PHP (collection `first(fn)`). Harusnya pakai raw SQL haversine query. |
| 3 | 🔴 **BUG** | `MapController::getSppgLayersData` L251-254 | `toArray()` langsung pada Eloquent collection — tidak melalui `SPPGResource`. Data yang keluar tidak terformat standar (bisa expose field sensitif seperti `pemilik_id`). |
| 4 | 🔴 **BUG** | `MapController::getSubmissionLayersData` L262-266 | Sama — `toArray()` langsung tanpa Resource/DTO. Data SppgDraft raw (termasuk `form2_data` yang berisi email + password admin). **SECURITY RISK.** |
| 5 | 🟡 **INCOMPLETE** | `RecommendationController.php` | File ada tapi kosong total. Tidak terpakai (fungsi rekomendasi sudah di `MapController`). File ini junk/stale. |
| 6 | 🟡 **MINOR** | `MapService::getKMeansRecommendations` | K dihitung sebagai `count/200`. Tidak ada batas atas K. Jika ada 2000 partner → K=10 centroid, bisa lambat. |
| 7 | 🟡 **MINOR** | `MapController@geocode` | Tidak ada rate limiting. Request langsung ke Nominatim bisa kena banned jika terlalu sering. |
| 8 | 🟡 **MINOR** | `MapService::validatePoint` | K-Means init pakai `shuffle()` — non-deterministic. Setiap kali di-call, hasil rekomendasi bisa berbeda. Perlu seed random atau algoritma K-Means++ untuk konsistensi. |

---

## Ringkasan Prioritas Perbaikan

### 🔴 Kritis (Harus Diperbaiki Dulu)

| # | Fitur | Aksi |
|---|---|---|
| 1 | Daftar SPPG | Fix `total_penerima_manfaat` di `SPPGResource` — pisahkan dari `total_porsi` |
| 2 | Daftar SPPG | Fix `SPPGController@menus` — tambah filter `where('sppg_id', $id)` |
| 3 | Map Rekomendasi | Fix `getSubmissionLayersData()` — **jangan expose form2_data** (ada plaintext password di situ) |
| 4 | Map Rekomendasi | Fix `getSppgLayersData()` — gunakan `SPPGResource` bukan raw `toArray()` |
| 5 | Pengajuan SPPG | Hapus fallback key Indo `ahli_gizi` / `admin_logistik` di `submit()` |

### 🟡 Penting (Sprint Berikutnya)

| # | Fitur | Aksi |
|---|---|---|
| 6 | Semua | Rename field `pemilik_id` → `owner_id` (migration + semua referensi) |
| 7 | Pengajuan SPPG | Rename `jumlah_porsi` → `portion_count` di `SppgDraftPartner` |
| 8 | Pengajuan SPPG | Standardisasi field `level` → `school_type`, `school_status` → `ownership_status` |
| 9 | Pengajuan SPPG | Tambah `FormRequest` untuk `store` & `update` |
| 10 | Daftar SPPG | Hapus alias filter `kota`/`kecamatan` — pakai hanya `city`/`district` |
| 11 | Map Rekomendasi | Tambah resource/DTO untuk SPPG layers |
| 12 | Map Rekomendasi | Optimasi query `validatePoint` — pakai SQL haversine bukan PHP filter |

### 🟢 Nice to Have

| # | Aksi |
|---|---|
| 13 | Delete `RecommendationController.php` (stale, tidak terpakai) |
| 14 | Tambah rate limiting untuk endpoint `geocode` |
| 15 | Tambah flow aktivasi otomatis setelah SPPG berhasil didaftarkan |

---

## Struktur File Terkait

```
app/
├── Http/Controllers/API/SuperAdmin/
│   ├── SPPGController.php              ← Daftar SPPG (CRUD + activate/deactivate)
│   ├── SppgSubmissionController.php    ← Pengajuan SPPG (draft multi-step)
│   ├── MapController.php               ← Map Rekomendasi (GIS + K-Means)
│   ├── RecommendationController.php    ← ⚠️ STALE — kosong, tidak terpakai
│   └── MonitoringMapController.php     ← Public monitoring map (beda konteks)
│
├── Services/
│   ├── SPPG/
│   │   ├── SPPGService.php             ← CRUD logic SPPG
│   │   ├── SPPGCapacityService.php     ← Capacity check logic
│   │   └── SppgRegistrationService.php ← Full registration flow
│   └── SuperAdmin/
│       └── MapService.php              ← Haversine, OSRM, K-Means, validatePoint
│
├── Models/
│   ├── SPPG.php                        ← Relasi: owner, schools, partners, employees
│   ├── SppgDraft.php                   ← Draft form multi-step
│   └── SppgDraftPartner.php            ← Sekolah mitra dalam draft
│
└── Http/Resources/
    └── SPPGResource.php                ← Transform SPPG data (ada bug total_penerima_manfaat)

routes/
└── api_superadmin.php                  ← Semua route superadmin
```
