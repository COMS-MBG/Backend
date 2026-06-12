# Analisa Sistem COMS-MBG
## *Dapur Masuk Bergizi — Centralized Operations Management System*

---

## 🗺️ GAMBARAN UMUM SISTEM

COMS-MBG adalah sistem manajemen operasional terpusat untuk program **Makan Bergizi** yang mengelola:
- Pendaftaran & manajemen **SPPG** (Satuan Penyelenggara Program Gizi)
- Manajemen **Mitra/Sekolah** penerima makanan
- Perencanaan **Menu & Gizi**
- **Distribusi** makanan ke sekolah via kurir
- **Monitoring GIS** real-time

### Aktor Utama
| Aktor | Role Type | Scope |
|-------|-----------|-------|
| **Super Admin** | `super_admin` | Global — semua SPPG |
| **Admin SPPG** | `sppg_user` + role `sppg_admin` | Satu SPPG |
| **Ahli Gizi** | `sppg_user` + role `nutritionist` | Satu SPPG |
| **Admin Logistik** | `sppg_user` + role `logistics_admin` | Satu SPPG |
| **Kurir** | `sppg_user` + role `courier` | Jadwal milik sendiri |

---

## 📖 PERSPEKTIF UX — ALUR PER AKTOR

---

### 🔵 ALUR 1: Pendaftaran SPPG Baru (Super Admin)

Ini adalah alur **paling kompleks** karena melibatkan GIS, validasi geocoding, dan multi-step form.

```
FASE A: Pengajuan (User Biasa / Calon Pengelola SPPG)
┌─────────────────────────────────────────────────────────────┐
│  1. User login                                              │
│  2. Isi Form 1: nama SPPG, alamat, kecamatan, kota,        │
│     provinsi, kapasitas                                     │
│  3. Sistem auto-geocoding alamat → dapat koordinat          │
│  4. Sistem buat DRAFT dengan status "draft"                 │
│  5. User tambah mitra sekolah satu per satu (min 1)        │
│     - Setiap mitra di-geocoding otomatis                    │
│     - Cek duplikat NPSN & koordinat                        │
│  6. User menunggu konfirmasi Super Admin                    │
└─────────────────────────────────────────────────────────────┘

FASE B: Review & Konfirmasi Titik (Super Admin)
┌─────────────────────────────────────────────────────────────┐
│  1. Super Admin buka Map Dashboard                          │
│  2. Lihat semua draft pengajuan di peta                    │
│  3. Klik draft → lihat titik SPPG + mitra yang diajukan    │
│  4. Sistem validasi titik: GREEN / YELLOW / RED            │
│     - GREEN: aman, tidak overlap SPPG aktif               │
│     - YELLOW: overlap tapi SPPG sekitar overcapacity      │
│     - RED: overlap dengan SPPG aktif yang masih kapasitas │
│  5. Super Admin bisa geser titik (centroid suggestion)     │
│  6. Klik "Konfirmasi Titik" → sistem:                      │
│     a. Validasi ulang status titik                         │
│     b. Reverse geocode → update alamat otomatis            │
│     c. Tandai mitra yang out of range (> 5km)              │
│     d. Tambah rekomendasi mitra dari database              │
│     e. Set draft.map_confirmed = true                      │
└─────────────────────────────────────────────────────────────┘

FASE C: Kelengkapan Data & Submit (Super Admin)
┌─────────────────────────────────────────────────────────────┐
│  1. Super Admin isi Form 2: data Admin SPPG                │
│     (nama, email, password)                                 │
│  2. Super Admin isi Form 3 (opsional):                     │
│     - Ahli Gizi (nama, email, password)                    │
│     - Admin Logistik (nama, email, password)               │
│  3. Super Admin klik "Submit" → sistem:                     │
│     a. Guard: status = draft? ✓                            │
│     b. Guard: form1 & form2 lengkap? ✓                     │
│     c. Guard: min 1 mitra? ✓                               │
│     d. Guard: map_confirmed? ✓                             │
│     e. Guard: email duplikat di DB? ✓                      │
│     f. Auto-geocode mitra yang belum punya koordinat       │
│     g. Buat SPPG (status: inactive)                        │
│     h. Seed default roles & permissions                     │
│     i. Buat akun User untuk Admin, Gizi, Logistik         │
│     j. Kirim email notifikasi ke setiap akun baru          │
│     k. Insert mitra ke tabel partners                      │
│     l. Update draft.status = registered                    │
└─────────────────────────────────────────────────────────────┘

FASE D: Aktivasi SPPG (Super Admin)
┌─────────────────────────────────────────────────────────────┐
│  1. SPPG terdaftar tapi belum aktif (status: inactive)     │
│  2. Super Admin klik "Aktifkan" → status = active          │
│  3. Semua user SPPG di-aktifkan (is_active = true)        │
└─────────────────────────────────────────────────────────────┘
```

---

### 🟢 ALUR 2: Manajemen Gizi & Menu (Admin SPPG / Ahli Gizi)

```
FASE A: Manajemen Bahan Baku (Ingredients)
┌─────────────────────────────────────────────────────────────┐
│  1. Ahli Gizi tambah bahan baku                            │
│     (nama, kalori, protein, karbohidrat, lemak per satuan) │
│  2. Bisa hitung nutrisi dari kombinasi bahan               │
└─────────────────────────────────────────────────────────────┘

FASE B: Manajemen Resep
┌─────────────────────────────────────────────────────────────┐
│  1. Ahli Gizi buat resep (nama resep)                      │
│  2. Tambah bahan ke resep dengan jumlah                    │
│  3. Sistem hitung total kalori/protein/karbohidrat/lemak   │
└─────────────────────────────────────────────────────────────┘

FASE C: Perencanaan Menu Mingguan
┌─────────────────────────────────────────────────────────────┐
│  1. Ahli Gizi buat Menu (per minggu: week_start, week_end) │
│  2. Assign resep ke hari & waktu makan                     │
│  3. Status menu: planned → scheduled → published → archived │
│  4. Publish menu → status published                         │
│  5. Super Admin bisa lihat menu SPPG dari tab "Menu"       │
└─────────────────────────────────────────────────────────────┘
```

---

### 🔴 ALUR 3: Distribusi Makanan (Multi-Aktor — Alur Paling Kritis)

```
STATUS MACHINE JADWAL PENGIRIMAN:
in_order → delivering → delivered → confirmed
    ↓              ↗
  rejected    revision_required

STEP 1: Admin Logistik Buat Jadwal [STATUS: in_order]
┌─────────────────────────────────────────────────────────────┐
│  1. Admin Logistik pilih kurir + sekolah tujuan            │
│  2. Pilih tipe kendaraan + plat nomor                      │
│  3. Set jadwal waktu pengiriman                            │
│  4. Tambah catatan pengiriman (opsional)                   │
│  5. Simpan → status = in_order                             │
└─────────────────────────────────────────────────────────────┘

STEP 2: Admin SPPG Submit Tugas ke Kurir [STATUS: in_order]
┌─────────────────────────────────────────────────────────────┐
│  1. Admin SPPG lihat jadwal yang sudah dibuat              │
│  2. Klik "Kirim Tugas" → broadcast via Laravel Reverb      │
│  3. Kurir terima notifikasi real-time di app               │
│  (Catatan: status tetap in_order setelah submit)           │
└─────────────────────────────────────────────────────────────┘

STEP 3A: Kurir Terima Tugas [STATUS: in_order → delivering]
┌─────────────────────────────────────────────────────────────┐
│  1. Kurir lihat tugas di app                               │
│  2. Kurir klik "Terima" → status = delivering              │
│  3. departed_at = sekarang                                  │
│  4. Kurir mulai kirim GPS ping ke server                   │
│     POST /distribution/map/location/{schedule}             │
└─────────────────────────────────────────────────────────────┘

STEP 3B: Kurir Tolak Tugas [STATUS: in_order → rejected]
┌─────────────────────────────────────────────────────────────┐
│  1. Kurir klik "Tolak" + isi alasan (wajib) + foto opsional│
│  2. status = rejected                                       │
│  3. Admin Logistik bisa edit & reassign jadwal             │
└─────────────────────────────────────────────────────────────┘

STEP 4: Kurir Submit Bukti Pengiriman [STATUS: delivering → delivered]
┌─────────────────────────────────────────────────────────────┐
│  1. Kurir tiba di sekolah                                  │
│  2. Foto bukti pengiriman → upload                         │
│  3. status = delivered, arrived_at = sekarang              │
│  4. Broadcast status update ke admin                       │
└─────────────────────────────────────────────────────────────┘

STEP 5A: Admin Logistik Konfirmasi [STATUS: delivered → confirmed]
┌─────────────────────────────────────────────────────────────┐
│  1. Admin Logistik review bukti foto                       │
│  2. Tambah catatan konfirmasi                              │
│  3. status = confirmed                                      │
│  4. Arsip otomatis → tabel delivery_histories              │
│  5. Snapshot: nama kurir, sekolah, jarak, waktu, foto     │
└─────────────────────────────────────────────────────────────┘

STEP 5B: Admin Logistik Minta Revisi [STATUS: delivered → revision_required]
┌─────────────────────────────────────────────────────────────┐
│  1. Admin Logistik review bukti foto → tidak valid         │
│  2. Isi catatan revisi (min 5 karakter)                    │
│  3. status = revision_required                             │
│  4. Kurir terima notifikasi (broadcast)                    │
│  5. Kurir resubmit bukti → kembali ke delivered            │
└─────────────────────────────────────────────────────────────┘
```

---

### 🟡 ALUR 4: Manajemen Stok (Admin SPPG)

```
┌─────────────────────────────────────────────────────────────┐
│  1. Admin SPPG lihat stok bahan baku saat ini              │
│  2. Buat transaksi stok (masuk/keluar)                     │
│  3. Transaksi yang besar perlu persetujuan (approval)      │
│     - Pending → Approved / Rejected                        │
│  4. Set minimum stok per bahan (alert threshold)           │
│  5. Lihat history semua transaksi                          │
└─────────────────────────────────────────────────────────────┘
```

---

### 🟤 ALUR 5: Monitoring GPS Real-Time (Admin SPPG / Admin Logistik)

```
┌─────────────────────────────────────────────────────────────┐
│  1. Admin buka Peta Distribusi                             │
│  2. Lihat kurir aktif beserta posisi real-time             │
│  3. Lihat trail perjalanan kurir per jadwal                │
│  4. Kurir kirim GPS ping secara berkala                    │
│     (POST /distribution/map/location/{schedule})           │
│  5. Admin lihat status distribusi dari peta                │
└─────────────────────────────────────────────────────────────┘
```

---

## ⚙️ PERSPEKTIF TEKNIS DETAIL

---

### 1. ARSITEKTUR SISTEM

```
┌─────────────────────────────────────────────────────────────┐
│                     FRONTEND (SPA)                         │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP / WebSocket (Reverb)
┌──────────────────────────▼──────────────────────────────────┐
│              LARAVEL API (Sanctum Cookie Auth)              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  ROUTE GROUPS                                        │   │
│  │  /api/auth/...          → AuthController             │   │
│  │  /api/admin-sppg/...    → AdminSPPG Controllers      │   │
│  │  /api/super-admin/...   → SuperAdmin Controllers     │   │
│  │  /api/distribution/...  → Distribution Controllers   │   │
│  │  /api/sppg-drafts/...   → SppgDraftController        │   │
│  │  /api/public/...        → Public Controllers         │   │
│  └────────────────┬─────────────────────────────────────┘   │
│                   │                                          │
│  ┌────────────────▼─────────────────────────────────────┐   │
│  │  SERVICE LAYER                                       │   │
│  │  SPPGService, SppgRegistrationService                │   │
│  │  DeliveryScheduleService                             │   │
│  │  MapService (GIS + K-Means + Geocoding)             │   │
│  │  AddressValidationService (Nominatim)                │   │
│  └────────────────┬─────────────────────────────────────┘   │
│                   │                                          │
│  ┌────────────────▼─────────────────────────────────────┐   │
│  │  MODEL LAYER (Eloquent + SoftDeletes)               │   │
│  │  SPPG, Partner, School, User, Employee               │   │
│  │  DeliverySchedule, DeliveryHistory                   │   │
│  │  Menu, MenuItem, Recipe, Ingredient                  │   │
│  │  StockItem, StockTransaction                         │   │
│  └────────────────┬─────────────────────────────────────┘   │
│                   │                                          │
│  ┌────────────────▼─────────────────────────────────────┐   │
│  │  DATABASE (MySQL/PostgreSQL)                         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
         │ Broadcast
┌────────▼────────┐
│  Laravel Reverb  │ (WebSocket — GPS tracking & delivery notif)
└─────────────────┘
         │ HTTP
┌────────▼────────┐
│  OSRM / Nominatim│ (External: Routing + Geocoding)
└─────────────────┘
```

---

### 2. SISTEM AUTENTIKASI & RBAC

**Auth:** Laravel Sanctum (Cookie-based SPA)
```
GET  /sanctum/csrf-cookie → XSRF token
POST /api/auth/login      → Session
GET  /api/auth/user       → Validasi session
POST /api/auth/logout     → Destroy session
```

**RBAC Hierarchy:**
```
User (role_type)
  ├── super_admin  → bypass semua permission
  └── sppg_user   → cek via Employee → Role → Permission

Employee (satu user, satu employee)
  └── Role (scoped per SPPG)
        └── Permission[] (slug: employee.read, partner.create, dst)
```

**Normalisasi Role Bilingual (ID ↔ EN):**
- `courier` = `kurir`
- `logistics_admin` = `admin_logistik` = `admin-logistik`
- `sppg_admin` = `admin_sppg` = `admin-sppg`
- `nutritionist` = `ahli_gizi`

---

### 3. STATE MACHINE DELIVERY SCHEDULE

```
in_order ──[submitTask]──────────────────────────────► in_order*
                                                         │
                              ┌──────────────[acceptTask]┘
                              │
                              ▼
                          delivering
                              │
                   [submitProof/submitDeliveryProof]
                              │
                              ▼
                          delivered
                         /          \
          [confirmDelivery]      [requestRevision]
                /                        \
          confirmed               revision_required
                                          │
                                  [resubmitProof]
                                          │
                                        delivered (kembali)
          
in_order ──[rejectTask]──► rejected (bisa diedit ulang oleh admin logistik)

* submitTask tidak mengubah status, hanya set submitted_by & broadcast
```

**Kode Status Konstantas:**
| Konstanta | Nilai String |
|-----------|-------------|
| `STATUS_IN_ORDER` | `in_order` |
| `STATUS_ACCEPTED` | `accepted` |
| `STATUS_REJECTED` | `rejected` |
| `STATUS_DELIVERING` | `delivering` |
| `STATUS_DELIVERED` | `delivered` |
| `STATUS_CONFIRMED` | `confirmed` |
| `STATUS_REVISION_REQUIRED` | `revision_required` |

> ⚠️ **Bug:** `STATUS_ACCEPTED = 'accepted'` didefinisikan tapi **tidak pernah digunakan** dalam state machine. `acceptTask()` langsung set ke `delivering`, melewati status `accepted`.

---

### 4. GIS PIPELINE

```
A. Geocoding (Alamat → Koordinat)
   AddressValidationService → Nominatim API
   ├── formatForGeocoding()  → string alamat lengkap
   └── validateAndSuggest()  → { valid, lat, lng, confidence }

B. Reverse Geocoding (Koordinat → Alamat)
   MapController::reverseGeocode() → Nominatim Reverse API
   
C. Validasi Titik SPPG
   MapService::validatePoint()
   ├── Haversine ke SPPG aktif (radius 5km)
   │   ├── Overlap tapi overcapacity → YELLOW
   │   └── Overlap dengan kapasitas → RED
   └── Cek takeover mitra per NPSN/koordinat
   
D. Rekomendasi Centroid Shift
   MapService::suggestCentroidShift()
   → hitung centroid rata-rata dari mitra yang reachable
   
E. Rekomendasi Mitra
   MapService::recommendPartnersForPoint()
   → Partner tanpa SPPG, radius 5km, durasi ≤ 30mnt
   → Sort by jarak, pilih s/d kapasitas terpenuhi

F. K-Means Rekomendasi Lokasi SPPG Baru
   MapService::getKMeansRecommendations()
   → Pooling: mitra unserved + mitra > 5km dari SPPG-nya
   → K = ceil(total/5), min 2, max 10 cluster
   → 20 iterasi konvergensi
   → Output: centroid + sekolah yang dilayani

G. Routing (OSRM)
   MapService::getRouteDurationAndDistance()
   → OSRM public API (fallback: Haversine)
   → Cache 30 hari per pasangan koordinat
```

---

### 5. SPPG REGISTRATION PIPELINE (Detail Teknis)

```
SppgDraftController::storeForm1()
  → AddressValidationService::validateAndSuggest()
  → SppgDraft::create() [status: draft, map_confirmed: false]

SppgDraftController::addPartner()
  → AddressValidationService::validateAndSuggest()
  → Cek duplikat NPSN (di draft yang SAMA)
  → Cek duplikat koordinat < 50m (di draft yang SAMA)
  → SppgDraftPartner::create()

MapController::confirmPoint()
  → MapService::validatePoint()
  → reverseGeocode() → update form1_data alamat
  → Tandai mitra out of range
  → MapService::recommendPartnersForPoint()
  → SppgDraftPartner::create() per rekomendasi baru
  → draft.map_confirmed = true

SppgSubmissionController::submit()
  → Guard 1: status = draft
  → Guard 2: form1 & form2 ada
  → Guard 3: ada mitra
  → Guard 4: map_confirmed = true
  → Guard 5: cek email duplikat (users table)
  → Auto-geocode mitra yang belum punya koordinat
  → Guard: semua mitra sudah punya koordinat
  → SppgRegistrationService::register()
     ├── SPPG::create() [status: inactive]
     ├── DefaultRolePermissionSeeder::seedForSppg()
     ├── User::create() Admin SPPG
     ├── Employee::create() Admin SPPG
     ├── Mail::queue() → AccountCreatedMail
     ├── (opsional) User + Employee Ahli Gizi + Mail
     ├── (opsional) User + Employee Admin Logistik + Mail
     └── Partner::create() per mitra
  → draft.status = registered
```

---

## 🐛 CELAH & BUG YANG DITEMUKAN

---

### 🔴 KRITIS: Git Conflict Markers Belum Diselesaikan

**Lokasi:** [`SPPGService.php` L244-L281](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGService.php#L244-L281), [`SPPGController.php` L175-L184](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php#L175-L184), [`MapController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/MapController.php) (banyak lokasi)

**Masalah:** Kode mengandung marker `<<<<<<< Updated upstream`, `=======`, dan `>>>>>>> Stashed changes`. Ini berarti ada **merge conflict yang belum diselesaikan**. Jika kode ini dijalankan, PHP akan **throw parse error** pada file tersebut karena syntax tidak valid.

**File yang terdampak:**
- `app/Services/SPPG/SPPGService.php` — method `detachSchool()`
- `app/Http/Controllers/API/SuperAdmin/SPPGController.php` — method `partners()`
- `app/Http/Controllers/API/SuperAdmin/MapController.php` — banyak method: `getMapData()`, `geocode()`, `confirmPoint()`, `resolvePartners()`, `buildSubmissionLayers()`
- `app/Services/SuperAdmin/MapService.php` — method `validatePoint()`, `getKMeansRecommendations()`, `getSchoolsLayerData()`

---

### 🔴 KRITIS: Status `accepted` Tidak Terpakai (Dead Code + Logic Bug)

**Lokasi:** [`DeliverySchedule.php` L51](file:///c:/Users/naufa/COMS_MBG/app/Models/DeliverySchedule.php#L51), [`DeliveryScheduleService.php` L82-L85](file:///c:/Users/naufa/COMS_MBG/app/Services/Distribution/DeliveryScheduleService.php#L82-L85)

**Masalah:**
```php
const STATUS_ACCEPTED = 'accepted'; // ← didefinisikan

// Tapi acceptTask() langsung set ke 'delivering':
$schedule->update(['status' => DeliverySchedule::STATUS_DELIVERING]);
// STATUS_ACCEPTED tidak pernah digunakan!
```

Scopenya juga tidak memasukkan `accepted`:
```php
public function scopeActive($query) {
    return $query->whereIn('status', [
        self::STATUS_IN_ORDER,
        self::STATUS_ACCEPTED,  // ← ada di scope tapi state ini tidak pernah dicapai
        ...
    ]);
}
```

**Dampak BPMN:** State `accepted` tidak perlu digambar dalam flow diagram karena tidak pernah digunakan secara aktual.

---

### 🟠 TINGGI: Race Condition pada `storeForm1` — Submission Number Tidak Atomic

**Lokasi:** [`SPPGSubmissionController.php` L54-L65](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGSubmissionController.php#L54-L65)

**Masalah:**
```php
$lastDraft = SppgDraft::where('submission_number', 'like', "{$prefix}%")
    ->orderBy('submission_number', 'desc')
    ->first();
$seq = 1;
if ($lastDraft && preg_match('/.../', ...)) {
    $seq = (int) $matches[1] + 1;
}
$submissionNumber = $prefix . str_pad($seq, 3, '0', STR_PAD_LEFT);
```

Ini adalah **non-atomic read-then-write** tanpa locking. Dua request bersamaan bisa mendapat nomor yang sama.

**Solusi:** Gunakan `DB::transaction()` + `lockForUpdate()` atau pindah ke auto-increment di DB level.

---

### 🟠 TINGGI: Duplikat NPSN Hanya Dicek Dalam 1 Draft

**Lokasi:** [`SppgDraftController.php` L96](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SppgDraftController.php#L96)

**Masalah:**
```php
'npsn' => 'nullable|string|max:20|unique:sppg_draft_partners,npsn',
// ↑ Ini cek GLOBAL di semua baris sppg_draft_partners!
```

Tapi kode di bawahnya (L126-L137) hanya cek dalam draft yang sama:
```php
$existingNpsn = $draft->partners()->where('npsn', ...)->exists();
```

**Masalah ganda:**
1. Rule `unique:sppg_draft_partners,npsn` akan menolak NPSN yang sama di DRAFT BERBEDA — padahal itu valid (sekolah bisa di-draft oleh banyak orang, baru final saat submit)
2. Konsistensi: ada 2 pengecekan duplikat NPSN yang saling bertentangan

---

### 🟠 TINGGI: `detachSchool()` Tidak Menutup Transaction Dengan Benar

**Lokasi:** [`SPPGService.php` L235-L281](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGService.php#L235-L281)

**Masalah:** Ada Git conflict marker di dalam method ini. Salah satu versi code tidak menutup `DB::transaction()` dengan closing brace `}` yang benar. Ini akan menyebabkan **PHP parse error** di production.

---

### 🟡 SEDANG: N+1 Query di `getPartners()` + OSRM per Mitra

**Lokasi:** [`SPPGController.php` L168-L212](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php#L168-L212)

**Masalah:** Untuk setiap mitra, kode memanggil `getRouteDurationAndDistance()` yang melakukan HTTP call ke OSRM:
```php
$data = $partners->map(function ($p) use ($sppg, $mapService) {
    $route = Cache::remember($cacheKey, ..., function () use (...) {
        return $mapService->getRouteDurationAndDistance(...); // ← HTTP per mitra!
    });
});
```

Jika ada 50 mitra tanpa cache, ini = **50 HTTP request ke OSRM**. Ini sangat lambat.

**Catatan:** Ada cache 30 hari, tapi cache cold start tetap bermasalah.

---

### 🟡 SEDANG: `SppgDraftController::addPartner()` Default Value Tidak Konsisten

**Lokasi:** [`SppgDraftController.php` L162-L163](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SppgDraftController.php#L162-L163)

**Masalah:**
```php
'level'         => $request->level ?? 'SMA',
'school_status' => $request->school_status ?? 'negeri',
```

Nilai default di-hardcode saat tambah mitra via user, tapi di `SppgSubmissionController` (SuperAdmin flow) nilai default bisa berbeda atau null. Ini bisa menyebabkan data inkonsisten antara alur user dan alur SuperAdmin.

---

### 🟡 SEDANG: `destroy()` SPPG Tidak Menghapus Jadwal Distribusi

**Lokasi:** [`SPPGController.php` L86-L98](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php#L86-L98)

**Masalah:**
```php
DB::transaction(function() use ($id) {
    $this->sppgService->delete($id);           // hapus SPPG
    User::where('sppg_id', $id)->update([...]);// nonaktifkan user
    Partner::where('sppg_id', $id)->update([...]);// lepas mitra
    // ← TIDAK menghapus/menonaktifkan DeliverySchedule yang masih aktif!
    // ← TIDAK menghapus Menu, StockItem, dll yang terkait!
});
```

Ini bisa menyebabkan **orphaned records** — jadwal pengiriman yang kurir-nya sudah nonaktif tapi schedule masih `in_order`.

---

### 🟡 SEDANG: Tidak Ada Validasi Bahwa Kurir Terima Hanya Jadwal Miliknya

**Lokasi:** [`DeliveryScheduleController.php` L147-L158](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/Distribution/DeliveryScheduleController.php#L147-L158)

**Masalah:**
```php
public function acceptTask(Request $request, DeliverySchedule $schedule): JsonResponse
{
    abort_unless($request->user()->hasAnyRole(['courier', 'super_admin']), 403);
    // ↑ Hanya cek role kurir, TIDAK cek apakah schedule.courier_id == user.employee.id
```

Semua kurir bisa accept semua jadwal selama punya role `courier`.

---

### 🟡 SEDANG: Email Duplikat Tidak Dicek untuk Update/Edit SPPG

**Lokasi:** [`SPPGSubmissionController.php` L214-L237](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGSubmissionController.php#L214-L237)

**Masalah:** Pengecekan email duplikat ada di `submit()` tapi tidak di `update()`. Jika Super Admin mengedit form2/form3 dengan email yang sudah ada di DB, tidak akan terdeteksi sampai proses submit.

---

### 🟢 MINOR: `SPPG::table` Menggunakan Nama Non-Standar

**Lokasi:** [`SPPG.php` L13](file:///c:/Users/naufa/COMS_MBG/app/Models/SPPG.php#L13)

```php
protected $table = 's_p_p_g_s';
```

Nama tabel ini sangat tidak konvensional (Laravel defaultnya akan generate `s_p_p_g_s` dari class name `SPPG`). Ini menyebabkan query SQL terlihat aneh tapi berfungsi.

---

### 🟢 MINOR: `MapService` Tidak Menggunakan `env()` dengan Cache

**Lokasi:** [`MapService.php` L16](file:///c:/Users/naufa/COMS_MBG/app/Services/SuperAdmin/MapService.php#L16)

```php
$this->osrmBase = env('OSRM_BASE_URL', 'http://router.project-osrm.org');
```

Seharusnya menggunakan `config('services.osrm.base_url')` agar compatible dengan config caching (`php artisan config:cache`). Penggunaan `env()` langsung di luar `.env` file atau service provider tidak direkomendasikan di Laravel production.

---

## 📋 RINGKASAN UNTUK BPMN

### Pool & Lane yang Perlu Digambar:

| Pool | Lane | Proses Utama |
|------|------|-------------|
| **Pendaftaran SPPG** | Pengguna/Calon Admin | Isi form, tambah mitra |
| | Super Admin | Review peta, konfirmasi titik, submit |
| | Sistem | Geocoding, validasi, seed roles, kirim email |
| **Operasi SPPG** | Admin SPPG | Kelola karyawan, sekolah, mitra |
| | Ahli Gizi | Buat resep, rencanakan menu |
| | Admin Logistik | Buat jadwal, konfirmasi pengiriman |
| **Distribusi** | Admin SPPG | Submit tugas ke kurir |
| | Kurir | Terima/tolak, kirim GPS, upload bukti |
| | Admin Logistik | Konfirmasi / minta revisi |
| | Sistem | Broadcast Reverb, arsip history |
| **GIS Monitoring** | Super Admin | Lihat peta global, rekomendasi K-Means |
| | Admin SPPG | Lihat peta distribusi aktif |

### Proses Eksternal (Service Task):
- Nominatim Geocoding API
- Nominatim Reverse Geocoding API  
- OSRM Routing API
- Laravel Reverb WebSocket

---

*Dokumen ini dibuat berdasarkan analisa kode sumber pada: 2026-06-12*
