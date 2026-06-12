# Alur Sistem Superadmin — COMS MBG
> Dokumen ini ditulis berdasarkan **kondisi kode aktual** di `app/Http/Controllers/API/SuperAdmin/`, `app/Services/SuperAdmin/`, `app/Services/SPPG/SppgRegistrationService.php`, dan `routes/api_superadmin.php`.

---

## Daftar Isi

1. [Arsitektur & Posisi Superadmin](#1-arsitektur--posisi-superadmin)
2. [Autentikasi & Proteksi Rute](#2-autentikasi--proteksi-rute)
3. [Dashboard Superadmin](#3-dashboard-superadmin)
4. [Manajemen SPPG — CRUD & Status Lifecycle](#4-manajemen-sppg--crud--status-lifecycle)
5. [Manajemen Karyawan per SPPG](#5-manajemen-karyawan-per-sppg)
6. [Manajemen Sekolah (Master Data)](#6-manajemen-sekolah-master-data)
7. [Sistem Draft Pengajuan SPPG Baru](#7-sistem-draft-pengajuan-sppg-baru)
8. [Alur Submit Draft → Registrasi SPPG](#8-alur-submit-draft--registrasi-sppg)
9. [Modul Peta GIS](#9-modul-peta-gis)
10. [Alur Kerja Validasi Titik & Centroid](#10-alur-kerja-validasi-titik--centroid)
11. [K-Means Rekomendasi Pendirian SPPG](#11-k-means-rekomendasi-pendirian-sppg)
12. [Laporan Keuangan](#12-laporan-keuangan)
13. [Tabel Endpoint Lengkap](#13-tabel-endpoint-lengkap)

---

## 1. Arsitektur & Posisi Superadmin

```
┌─────────────────────────────────────────────────────────┐
│                   MODUL SUPERADMIN                       │
│              Prefix: /api/super-admin                    │
│    Auth: auth:sanctum + role:super_admin                 │
└─────────────────────────────────────────────────────────┘

Superadmin adalah aktor tunggal dengan akses penuh terhadap:
  ┌─────────────────────────────────────────────────┐
  │  ✦ Melihat & mengelola SEMUA SPPG               │
  │  ✦ Mendaftarkan SPPG baru (langsung atau draft)  │
  │  ✦ Mengaktifkan / menonaktifkan SPPG             │
  │  ✦ Menghapus SPPG beserta cascading effects      │
  │  ✦ Mengelola staf per SPPG                       │
  │  ✦ Mengelola master data sekolah                 │
  │  ✦ Akses peta GIS: semua SPPG aktif + kandidat   │
  │  ✦ Mendapat rekomendasi pendirian SPPG baru      │
  └─────────────────────────────────────────────────┘
```

---

## 2. Autentikasi & Proteksi Rute

```
Request masuk ke /api/super-admin/*
        │
        ▼
Middleware: auth:sanctum
        │   Token tidak valid / tidak ada → HTTP 401
        │
        ▼
Middleware: role:super_admin
        │   User bukan superadmin → HTTP 403
        │
        ▼
Controller Action dieksekusi
```

Semua rute Superadmin dibungkus dalam satu grup di `routes/api_superadmin.php`:
```php
Route::middleware(['auth:sanctum', 'role:super_admin'])
    ->prefix('super-admin')
    ->group(function () { ... });
```

---

## 3. Dashboard Superadmin

**File**: [`DashboardController.php`](app/Http/Controllers/API/SuperAdmin/DashboardController.php)  
**Endpoint**: `GET /api/super-admin/dashboard`

### Yang Dikembalikan:
```json
{
  "success": true,
  "data": {
    "total_sppg": 12,
    "total_sppg_active": 9,
    "total_sppg_inactive": 3,
    "total_partners": 142,
    "total_daily_portions": 18500
  }
}
```

### Logika:
| Field | Sumber Data |
|:------|:------------|
| `total_sppg` | `SPPG::where('status', '!=', 'deleted')->count()` — tidak termasuk yang di-soft delete |
| `total_sppg_active` | `SPPG::where('status', 'active')->count()` |
| `total_sppg_inactive` | `SPPG::where('status', 'inactive')->count()` |
| `total_partners` | `Partner::whereNotNull('sppg_id')->count()` — hanya mitra yang sudah terlayani |
| `total_daily_portions` | `Partner::whereNotNull('sppg_id')->sum('portion_count')` |

---

## 4. Manajemen SPPG — CRUD & Status Lifecycle

**File**: [`SPPGController.php`](app/Http/Controllers/API/SuperAdmin/SPPGController.php)  
**Services**: `SPPGService`, `SPPGCapacityService`, `MapService`

### Status Lifecycle SPPG:
```
[Pendaftaran] → status: inactive
      │
      ├── [Superadmin Activate] → POST /sppg/{id}/activate
      │         └─ status: active
      │              ├─ Semua user SPPG: is_active = true
      │              └─ SPPG beroperasi normal
      │
      ├── [Superadmin Deactivate] → POST /sppg/{id}/deactivate
      │         └─ status: inactive
      │              └─ Semua user SPPG: is_active = false (tidak bisa login)
      │
      └── [Superadmin Delete] → DELETE /sppg/{id}
                └─ status: deleted → Soft Delete
                     ├─ Semua user SPPG: is_active = false
                     └─ Semua partners: sppg_id = null (dilepas / tidak terlayani)
```

### Endpoint Detail:

```
GET /sppg
    Query: ?status=active&kota=Bandung&search=nama
    → List SPPG (paginate) + summary stats agregat
    → Response: { data: [SPPGResource...], meta: {...}, stats: {...} }

GET /sppg/capacity-overview
    → List SPPG yang overcapacity (jumlah sekolah >= kapasitas)

GET /sppg/{id}
    → Detail 1 SPPG + status kapasitas
    → Response: { data: SPPGResource, capacity: {...} }

POST /sppg
    → Daftarkan SPPG baru secara langsung (tanpa draft)
    → Memanggil SppgRegistrationService::register() (lihat seksi 8)
    → Menggunakan RegisterSppgRequest untuk validasi

PUT /sppg/{id}
    → Edit data SPPG (name, address, city, province, latitude, longitude, capacity, dll)

DELETE /sppg/{id}           ← DB Transaction
    → Set status = 'deleted' + soft delete (deleted_at)
    → Set is_active = false untuk SEMUA user ber-sppg_id ini
    → Set sppg_id = null untuk SEMUA partner terlayani SPPG ini

POST /sppg/{id}/activate    ← DB Transaction
    → Set SPPG status = 'active'
    → Set is_active = true untuk SEMUA user ber-sppg_id ini

POST /sppg/{id}/deactivate  ← DB Transaction
    → Set SPPG status = 'inactive'
    → Set is_active = false untuk SEMUA user ber-sppg_id ini

GET /sppg/{id}/partners
    → List semua sekolah mitra yang dilayani SPPG ini
    → Setiap mitra dilengkapi:
        - distance_km    (Haversine dari koordinat SPPG ke koordinat mitra)
        - estimated_minutes (via OSRM proxy, bisa null jika OSRM tidak tersedia)
        - distance_status: 'safe' jika ≤ 5 km, 'review' jika > 5 km

GET /sppg/{id}/menus
    → List semua menu yang pernah dibuat (diurutkan dari terbaru)

POST /sppg/{sppgId}/assign-school
    Body: { school_id: uuid }
    → Attach sekolah (tabel schools) ke SPPG

DELETE /sppg/{sppgId}/schools/{schoolId}
    → Detach sekolah dari SPPG
```

---

## 5. Manajemen Karyawan per SPPG

**File**: [`EmployeeController.php`](app/Http/Controllers/API/SuperAdmin/EmployeeController.php)  
**Service**: `EmployeeService`

Superadmin bisa mengelola staf di **semua SPPG** menggunakan path parameter `{sppgId}`:

```
GET  /sppg/{sppgId}/employees
     Query: ?position=kurir&status=active&search=nama
     → List staf di SPPG tertentu (paginate, filter berdasarkan position/status/nama)

GET  /sppg/{sppgId}/employees/{id}
     → Detail 1 staf (EmployeeResource: data + role + relasi)

POST /sppg/{sppgId}/employees
     → Tambah staf baru ke SPPG
     → Menggunakan StoreEmployeeRequest untuk validasi
     → sppg_id diambil dari path param (bukan dari token user)

PUT  /sppg/{sppgId}/employees/{id}
     → Edit data staf (UpdateEmployeeRequest)

DELETE /sppg/{sppgId}/employees/{id}
     → Hapus staf dari SPPG
```

> **Perbedaan dari AdminSPPG**: Di Superadmin, `sppgId` diambil dari **path parameter URL**, bukan dari token user. Artinya Superadmin bisa kelola karyawan di SPPG manapun.

---

## 6. Manajemen Sekolah (Master Data)

**File**: [`SchoolController.php`](app/Http/Controllers/API/SuperAdmin/SchoolController.php)  
**Service**: `SchoolService`

```
GET  /schools
     Query: ?jenjang=SD&search=nama
     → List sekolah yang terikat ke SPPG milik Superadmin (by sppg_id dari token)

GET  /schools/{id}
     → Detail sekolah

POST /schools
     → Tambah sekolah (sppg_id otomatis diambil dari auth user token)

PUT  /schools/{id}
     → Edit sekolah

DELETE /schools/{id}
     → Hapus sekolah
```

> **Catatan**: Controller ini mengambil `sppg_id` dari `auth('api')->user()->sppg_id`. Ini adalah master data sekolah (tabel `schools`), berbeda dari sekolah mitra `partners` yang menjadi basis kalkulasi porsi stok.

---

## 7. Sistem Draft Pengajuan SPPG Baru

**File**: [`SppgSubmissionController.php`](app/Http/Controllers/API/SuperAdmin/SPPGSubmissionController.php)  
**Tabel**: `sppg_drafts`, `sppg_draft_partners`

Sistem draft memungkinkan Superadmin mengisi form pendaftaran SPPG baru secara bertahap (auto-save) sebelum akhirnya difinalisasi.

### Struktur Draft (Tabel `sppg_drafts`):

```
sppg_drafts
  ├── submission_number    (auto: DRAFT-YYYYMMDD-XXX)
  ├── submitted_by         (FK → users, siapa yang membuat draft)
  ├── source               ('internal' untuk input Superadmin)
  ├── form1_data (JSON)    → Data SPPG (nama, alamat, koordinat, kapasitas, dll)
  ├── form2_data (JSON)    → Data Admin SPPG (nama, email, password)
  ├── form3_data (JSON)    → Data opsional: ahli_gizi & admin_logistik
  ├── latitude / longitude          → Koordinat sementara (input awal)
  ├── confirmed_latitude / confirmed_longitude  → Koordinat final (setelah validasi GIS)
  ├── point_status         → 'green' / 'yellow' / 'red'
  ├── map_confirmed        → boolean: apakah titik sudah dikonfirmasi via peta
  └── status               → 'draft' / 'registered'

sppg_draft_partners (relasi hasMany dari sppg_drafts)
  ├── draft_id, school_name, npsn
  ├── level (SD/SMP/SMA/SMK), school_status (negeri/swasta)
  ├── address, city, district
  ├── latitude, longitude
  ├── jumlah_porsi
  └── data_source ('database' atau 'openstreetmap')
```

### Endpoint Draft:

```
GET /sppg-submissions
    → List semua draft (diurutkan terbaru di atas) + relasi partners

GET /sppg-submissions/{id}
    → Detail 1 draft + partners

POST /sppg-submissions        ← AUTO-SAVE
    → Jika user sudah punya draft berstatus 'draft': UPDATE draft tersebut
    → Jika belum ada draft aktif: BUAT BARU dengan submission_number
    → Jika ada 'partners' dalam body: HAPUS semua draft partners lama, TULIS ulang yang baru
    → Field yang bisa disimpan:
        form1_data, form2_data, form3_data,
        latitude, longitude, confirmed_latitude, confirmed_longitude,
        point_status, map_confirmed, partners[]

PUT /sppg-submissions/{id}
    → Update draft spesifik berdasarkan ID (manual update)
    → Sama dengan POST, tapi tidak pakai logika auto-save (cari draft aktif user)

DELETE /sppg-submissions/{id}
    → Hapus draft (soft delete via SoftDeletes model)

POST /sppg-submissions/{id}/submit  ← FINALISASI KRITIS (lihat seksi 8)
    → Validasi draft sebelum difinalisasi
    → Convert draft → SPPG aktif
```

---

## 8. Alur Submit Draft → Registrasi SPPG

Ini adalah alur paling kritis di modul Superadmin.

```
Superadmin
    │
    ├─ [Opsional] Isi form dan auto-save via POST /sppg-submissions
    ├─ [Opsional] Validasi titik via POST /map/validate-point
    ├─ [Opsional] Konfirmasi koordinat via POST /map/confirm-point/{id}
    │
    └─ POST /sppg-submissions/{id}/submit
              │
              ▼
         SppgSubmissionController@submit()
              │
              ├─ Guard 1: draft.status === 'draft'?
              │    Tidak → HTTP 422: "Draft ini sudah didaftarkan."
              │
              ├─ Guard 2: form1_data DAN form2_data terisi?
              │    Tidak → HTTP 422: "Data SPPG dan Admin SPPG harus diisi."
              │
              ├─ Guard 3: draft.partners tidak kosong?
              │    Kosong → HTTP 422: "Minimal 1 sekolah mitra harus ada."
              │
              └─ DB::transaction()
                    │
                    ├─ Mapping data draft → format SppgRegistrationService:
                    │    - form1_data → 'sppg'
                    │    - form2_data → 'admin_sppg'
                    │    - form3_data.ahli_gizi → 'ahli_gizi' (nullable)
                    │    - form3_data.admin_logistik → 'admin_logistik' (nullable)
                    │    - partners[] → mapping field:
                    │        level          → school_type
                    │        school_status  → ownership_status
                    │        jumlah_porsi   → portion_count
                    │
                    └─ SppgRegistrationService::register($data)
                          │
                          ├─ 1. Buat SPPG (status: inactive)
                          ├─ 2. DefaultRolePermissionSeeder::seedForSppg() → buat role default
                          ├─ 3. Buat user Admin SPPG + Employee record (position: pemilik)
                          │       → Set SPPG.pemilik_id = user.id
                          │       → Queue email: AccountCreatedMail (async)
                          ├─ 4. [Jika ada] Buat user Ahli Gizi + Employee record
                          │       → Queue email: AccountCreatedMail (async)
                          ├─ 5. [Jika ada] Buat user Admin Logistik + Employee record
                          │       → Queue email: AccountCreatedMail (async)
                          └─ 6. Insert/Update semua partners ke tabel partners
                                  (jika partner punya 'id' → update sppg_id, jika baru → INSERT)

                    └─ Update draft: status = 'registered', submitted_at = now()
                    └─ Return SPPG baru
```

### Role Default yang Di-seed Otomatis (`DefaultRolePermissionSeeder::seedForSppg()`):
Setiap SPPG baru mendapatkan role-role ini secara otomatis dengan permission yang sudah dikonfigurasi:
- `admin-sppg` (slug)
- `ahli-gizi` (slug)
- `admin-logistik` (slug)
- `kurir` (slug)

---

## 9. Modul Peta GIS

**File**: [`MapController.php`](app/Http/Controllers/API/SuperAdmin/MapController.php)  
**Service**: [`MapService.php`](app/Services/SuperAdmin/MapService.php)

Modul ini menyediakan seluruh fungsionalitas geospasial untuk membantu Superadmin dalam memilih lokasi SPPG baru secara cerdas.

### Endpoint Data Layer:

```
GET /map/data
    → Gabungan semua layer: SPPG aktif + mitra, draft submissions, rekomendasi K-Means
    → Response: { sppg_layers: [...], submission_layers: [...], recommendations: [...] }

GET /map/sppg-layers
    → SPPG yang berstatus 'active' beserta semua partners-nya (koordinat + info)
    → Digunakan sebagai layer "SPPG Beroperasi" di peta

GET /map/submission-layers
    → Draft submissions yang sudah memiliki koordinat (latitude & longitude tidak null)
    → Digunakan sebagai layer "Kandidat Lokasi" di peta

GET /map/recommendations
    → Titik rekomendasi dari algoritma K-Means (lihat seksi 11)
    → Hanya titik dengan minimal 3 sekolah dalam radius 5 km yang ditampilkan
```

### Endpoint Utilitas GIS:

```
POST /map/geocode
     Body: { query: "Jl. Merdeka No. 1 Bandung" }
            atau { address: "..." }
     → Proxy ke Nominatim OpenStreetMap API
     → Header: User-Agent: COMS-MBG-SuperAdmin/1.0 (wajib oleh Nominatim)
     → Return: max 5 hasil pencarian alamat + koordinat
     → Timeout: 5 detik

POST /map/route-check
     Body: { lat_a, lon_a, lat_b, lon_b }
     → Proxy ke OSRM API (env: OSRM_BASE_URL, default: http://router.project-osrm.org)
     → Return: { duration_minutes, distance_meters }

POST /map/validate-point
     Body: { latitude, longitude, draft_id? atau partners[]? }
     → Hitung status kelayakan titik koordinat (Green/Yellow/Red)
     → Jika draft_id disertakan → ambil partners dari DB
     → Jika partners[] disertakan langsung → gunakan data tersebut
     → Delegasikan ke MapService::validatePoint() (lihat seksi 10)

POST /map/suggest-shift
     Body: { latitude, longitude, draft_id? atau partners[]? }
     → Rekomendasikan pergeseran titik ke centroid sekolah mitra
     → Delegasikan ke MapService::suggestCentroidShift()
     → Return: null jika pergeseran < 500 meter (tidak perlu digeser)
               { latitude, longitude, distance_meters } jika perlu digeser

POST /map/confirm-point/{submission_id}
     Body: { latitude, longitude }
     → Validasi titik terlebih dahulu via MapService::validatePoint()
     → Simpan ke draft: confirmed_latitude, confirmed_longitude, point_status, map_confirmed=true
     → Return: draft yang sudah diperbarui + partners
```

---

## 10. Alur Kerja Validasi Titik & Centroid

Semua logika ini ada di `MapService.php`.

### A. Validasi Titik (`validatePoint`)

```
Input: lat, lng, draftPartners[]
Output: { status: 'green'|'yellow'|'red', conflicts: [...] }

Langkah 1 — Cek jarak ke SPPG aktif yang sudah ada:
  Untuk setiap SPPG aktif di DB:
    dist = Haversine(input, sppg.koordinat)
    Jika dist ≤ 5 km:
      Jika SPPG aktif tersebut OVERCAPACITY (sekolah >= kapasitas):
        → status = 'yellow' (boleh mendirikan, SPPG lama sudah penuh)
        → tambah pesan konflik
      Jika SPPG aktif masih ada kapasitas:
        → status = 'red' (DILARANG, masih ada SPPG yang bisa melayani)
        → tambah pesan konflik

Langkah 2 — Cek aturan takeover sekolah mitra:
  Untuk setiap draft_partner:
    Cari di DB: apakah ada Partner dengan NPSN sama ATAU koordinat sangat berdekatan (< 50m)?
    Jika ada dan partner tersebut sudah dilayani SPPG lain:
      Hitung jarak: partner → sppg_existing
      Cek OSRM: durasi perjalanan partner → sppg_existing
      
      Jika jarak ≤ 5 km DAN durasi ≤ 30 menit:
        → status = 'red' (mitra tidak bisa di-takeover)
      Jika jarak > 5 km ATAU durasi > 30 menit:
        Hitung jarak baru: partner → titik_usulan
        Jika titik_usulan lebih dekat dari sppg_existing:
          → status = 'yellow' (takeover diizinkan karena lebih dekat)
```

### B. Saran Pergeseran Centroid (`suggestCentroidShift`)

```
Input: lat, lng, draftPartners[]
Output: null | { latitude, longitude, distance_meters }

1. Kumpulkan semua draft_partner yang berjarak ≤ 5 km dari titik input
2. Hitung centroid (rata-rata lat/lng) dari mitra yang terkumpul
3. Hitung jarak: titik_input → centroid

Jika jarak > 500 meter (0.5 km):
  → Sarankan geser ke titik centroid
  → Return: { latitude: centroid_lat, longitude: centroid_lng, distance_meters }

Jika jarak ≤ 500 meter:
  → Titik sudah cukup dekat dengan pusat sebaran
  → Return: null (tidak perlu digeser)
```

---

## 11. K-Means Rekomendasi Pendirian SPPG

**Service**: `MapService::getKMeansRecommendations()`

```
Algoritma K-Means murni PHP untuk menemukan lokasi optimal pendirian SPPG baru.

INPUT (dari DB):
  1. Sekolah yang belum terlayani (Partner::whereNull('sppg_id'))
  2. Sekolah yang sudah terlayani tapi jauh dari SPPG-nya (jarak > 5 km)
     → Kandidat takeover

PROSES:
  1. K = floor(total_sekolah / 200), minimum 1
     → Contoh: 800 sekolah → K = 4 cluster
  2. Inisialisasi centroid: pilih K sekolah secara acak sebagai titik awal
  3. Iterasi maksimum 20 putaran:
       a. Assign setiap sekolah ke centroid terdekat (Haversine)
       b. Hitung centroid baru (rata-rata lat/lng per cluster)
       c. Jika centroid tidak bergerak (< 0.0001 derajat) → hentikan iterasi
  4. Filter: hanya centroid yang melayani ≥ 3 sekolah dalam radius 5 km yang disertakan

OUTPUT:
  [
    {
      "latitude": -6.9175,
      "longitude": 107.6191,
      "school_count": 15,
      "schools": [{ "id", "name", "latitude", "longitude" }, ...]
    },
    ...
  ]
```

---

## 12. Laporan Keuangan

**File**: [`FinancialReportController.php`](app/Http/Controllers/API/SuperAdmin/FinancialReportController.php)

```
GET  /financial-reports           → List laporan keuangan
GET  /financial-reports/{id}      → Detail laporan
POST /financial-reports           → Buat laporan baru
PUT  /financial-reports/{id}      → Edit laporan
DELETE /financial-reports/{id}    → Hapus laporan
```

> **Status implementasi**: Middleware permission sudah terdefinisi dengan benar, namun **logika bisnis belum diimplementasikan** (body method kosong). Ini adalah fitur yang direncanakan untuk tahap implementasi berikutnya.

---

## 13. Tabel Endpoint Lengkap

| Method | Path | Controller | Keterangan |
|:-------|:-----|:-----------|:-----------|
| `GET` | `/dashboard` | DashboardController | Statistik agregat: total SPPG, mitra, porsi harian |
| `GET` | `/sppg` | SPPGController | List semua SPPG + filter + summary stats |
| `POST` | `/sppg` | SPPGController | Daftarkan SPPG baru langsung (tanpa draft) |
| `GET` | `/sppg/capacity-overview` | SPPGController | List SPPG yang overcapacity |
| `GET` | `/sppg/{id}` | SPPGController | Detail SPPG + status kapasitas |
| `PUT` | `/sppg/{id}` | SPPGController | Edit data SPPG |
| `DELETE` | `/sppg/{id}` | SPPGController | Soft delete SPPG + nonaktifkan user + lepas partner |
| `POST` | `/sppg/{id}/activate` | SPPGController | Aktifkan SPPG + aktifkan semua user |
| `POST` | `/sppg/{id}/deactivate` | SPPGController | Nonaktifkan SPPG + nonaktifkan semua user |
| `GET` | `/sppg/{id}/partners` | SPPGController | List mitra SPPG + jarak Haversine + estimasi OSRM |
| `GET` | `/sppg/{id}/menus` | SPPGController | List menu mingguan SPPG |
| `POST` | `/sppg/{sppgId}/assign-school` | SPPGController | Attach sekolah ke SPPG |
| `DELETE` | `/sppg/{sppgId}/schools/{schoolId}` | SPPGController | Detach sekolah dari SPPG |
| `GET` | `/sppg/{sppgId}/employees` | EmployeeController | List staf SPPG tertentu |
| `POST` | `/sppg/{sppgId}/employees` | EmployeeController | Tambah staf ke SPPG |
| `GET` | `/sppg/{sppgId}/employees/{id}` | EmployeeController | Detail staf |
| `PUT` | `/sppg/{sppgId}/employees/{id}` | EmployeeController | Edit staf |
| `DELETE` | `/sppg/{sppgId}/employees/{id}` | EmployeeController | Hapus staf |
| `GET` | `/schools` | SchoolController | List sekolah master data |
| `POST` | `/schools` | SchoolController | Tambah sekolah |
| `GET` | `/schools/{id}` | SchoolController | Detail sekolah |
| `PUT` | `/schools/{id}` | SchoolController | Edit sekolah |
| `DELETE` | `/schools/{id}` | SchoolController | Hapus sekolah |
| `GET` | `/sppg-submissions` | SppgSubmissionController | List semua draft pengajuan |
| `POST` | `/sppg-submissions` | SppgSubmissionController | **Auto-save draft** (create atau update draft aktif) |
| `GET` | `/sppg-submissions/{id}` | SppgSubmissionController | Detail draft |
| `PUT` | `/sppg-submissions/{id}` | SppgSubmissionController | Update manual draft spesifik |
| `DELETE` | `/sppg-submissions/{id}` | SppgSubmissionController | Hapus draft |
| `POST` | `/sppg-submissions/{id}/submit` | SppgSubmissionController | **Finalisasi: draft → SPPG aktif** |
| `GET` | `/map/data` | MapController | Semua layer GIS (SPPG + draft + rekomendasi) |
| `GET` | `/map/sppg-layers` | MapController | Layer SPPG aktif + partners |
| `GET` | `/map/submission-layers` | MapController | Layer draft dengan koordinat |
| `GET` | `/map/recommendations` | MapController | Titik rekomendasi K-Means |
| `POST` | `/map/geocode` | MapController | Proxy Nominatim → cari koordinat dari alamat |
| `POST` | `/map/route-check` | MapController | Proxy OSRM → estimasi rute & waktu tempuh |
| `POST` | `/map/validate-point` | MapController | Validasi kelayakan titik (Green/Yellow/Red) |
| `POST` | `/map/suggest-shift` | MapController | Saran pergeseran centroid (> 500m) |
| `POST` | `/map/confirm-point/{id}` | MapController | Konfirmasi koordinat final ke draft |
| `GET` | `/financial-reports` | FinancialReportController | List laporan keuangan |
| `POST` | `/financial-reports` | FinancialReportController | Buat laporan |
| `GET` | `/financial-reports/{id}` | FinancialReportController | Detail laporan |
| `PUT` | `/financial-reports/{id}` | FinancialReportController | Edit laporan |
| `DELETE` | `/financial-reports/{id}` | FinancialReportController | Hapus laporan |

---

> *Dokumen ini terakhir diperbarui: 2026-06-03. Dihasilkan berdasarkan source code langsung.*
