# Alur Sistem Admin SPPG — COMS MBG
> Dokumen ini ditulis berdasarkan **kondisi kode aktual** di `app/Http/Controllers/API/AdminSPPG/`, `app/Services/`, dan `routes/api_adminsppg.php`.

---

## Daftar Isi

1. [Arsitektur & Aktor](#1-arsitektur--aktor)
2. [Autentikasi & Proteksi Rute](#2-autentikasi--proteksi-rute)
3. [Dashboard](#3-dashboard)
4. [Manajemen Karyawan & Peran](#4-manajemen-karyawan--peran)
5. [Manajemen Sekolah Mitra (Partner)](#5-manajemen-sekolah-mitra-partner)
6. [Modul Gizi: Bahan Baku (Ingredient)](#6-modul-gizi-bahan-baku-ingredient)
7. [Modul Gizi: Resep (Recipe)](#7-modul-gizi-resep-recipe)
8. [Modul Gizi: Menu Mingguan](#8-modul-gizi-menu-mingguan)
9. [Modul Stok Bahan Baku](#9-modul-stok-bahan-baku)
10. [Modul Distribusi & Pengiriman](#10-modul-distribusi--pengiriman)
11. [Pelacakan Kurir (Tracking)](#11-pelacakan-kurir-tracking)
12. [Peta Distribusi](#12-peta-distribusi)
13. [Laporan Keuangan](#13-laporan-keuangan)
14. [Alur Lintas-Modul: Publish Menu → FIFO Stok](#14-alur-lintas-modul-publish-menu--fifo-stok)
15. [Tabel Endpoint Lengkap](#15-tabel-endpoint-lengkap)

---

## 1. Arsitektur & Aktor

```
┌─────────────────────────────────────────────────────┐
│                  MODUL ADMIN SPPG                    │
│               Prefix: /api/admin-sppg               │
│         Auth: auth:sanctum (Token Sanctum)           │
└─────────────────────────────────────────────────────┘

Aktor & Hak Akses (dikontrol per permission):
┌─────────────────┬────────────────────────────────────────────────┐
│ Aktor           │ Kewenangan Utama                               │
├─────────────────┼────────────────────────────────────────────────┤
│ Pemilik SPPG    │ Semua akses, approve stok, kelola karyawan     │
│ Admin SPPG      │ Submit tugas kurir, laporan, partner           │
│ Ahli Gizi       │ CRUD bahan baku, resep, menu mingguan          │
│ Admin Logistik  │ CRUD stok, jadwal distribusi                   │
│ Kurir           │ Update lokasi GPS, lihat jadwal milik sendiri  │
└─────────────────┴────────────────────────────────────────────────┘
```

> **Catatan**: Seluruh permission dikonfigurasi melalui `roles` dan `role_permissions` yang di-seed otomatis saat SPPG didaftarkan oleh Superadmin (`DefaultRolePermissionSeeder::seedForSppg()`).

---

## 2. Autentikasi & Proteksi Rute

```
Request masuk
    │
    ▼
Middleware: auth:sanctum
    │   Token tidak valid → HTTP 401
    │
    ▼
Middleware: permission:<nama_permission>   (per controller action)
    │   Permission tidak ada  → HTTP 403
    │
    ▼
Controller Action dieksekusi
```

- Semua rute berada di bawah `Route::middleware(['auth:sanctum'])` di `routes/api_adminsppg.php`.
- Permission diproteksi menggunakan `HasMiddleware` interface di setiap controller (contoh: `permission:stock.read`).
- Role Gate khusus `role:SPPG_MANAGEMENT_ROLES` digunakan untuk modul karyawan, sekolah, dan roles.

---

## 3. Dashboard

**File**: [`DashboardController.php`](app/Http/Controllers/API/AdminSPPG/DashboardController.php)  
**Endpoint**: `GET /api/admin-sppg/dashboard`  
**Permission**: `permission:dashboard.read`

### Yang Dikembalikan:
```json
{
  "schedules": {
    "in_order": 3,
    "delivering": 2,
    "delivered": 10,
    "revision_required": 1,
    "confirmed": 8,
    "rejected": 0
  },
  "history_this_month": {
    "total_deliveries": 45,
    "total_distance_km": 312.5,
    "avg_duration_minutes": 28.4
  },
  "active_couriers": 2,
  "pending_confirmation": [
    { "id": 12, "courier_name": "Budi", "school_name": "SDN 01 Makmur", "arrived_at": "2026-06-03T..." }
  ],
  "resources": {
    "total_couriers": 4,
    "total_schools": 18
  },
  "staff_completeness": {
    "ahli_gizi_registered": true,
    "admin_logistik_registered": false,
    "is_complete": false
  }
}
```

### Logika Penting:
- **Kurir aktif**: dihitung dari `DeliverySchedule` berstatus `delivering` (distinct by `courier_id`).
- **Pending confirmation**: 5 pengiriman terakhir berstatus `delivered` yang belum dikonfirmasi.
- **Staff completeness**: cek apakah `Employee` dengan role slug `ahli-gizi` dan `admin-logistik` sudah terdaftar di SPPG berjalan.

---

## 4. Manajemen Karyawan & Peran

**File**: [`EmployeeController.php`](app/Http/Controllers/API/AdminSPPG/EmployeeController.php) | [`RoleController.php`](app/Http/Controllers/API/AdminSPPG/RoleController.php) | [`PermissionController.php`](app/Http/Controllers/API/AdminSPPG/PermissionController.php)

```
[Admin SPPG / Pemilik]
        │
        ├── GET  /employees               → List semua staf (filter: search, role_id, position)
        ├── GET  /employees/{id}          → Detail staf + role + permissions + user account
        ├── POST /employees               → Tambah staf baru (sppg_id otomatis dari token user)
        ├── PUT  /employees/{id}          → Edit data staf
        ├── DELETE /employees/{id}        → Hapus staf
        │
        ├── GET  /employees/{id}/assign-role → Tampilkan form assign role (staf + semua role tersedia)
        └── POST /employees/{id}/assign-role → Tetapkan role ke staf (update role_id di employees)

[Role & Permission Management]
        ├── GET  /roles                   → List semua role (dengan permission & jumlah staf)
        ├── GET  /roles/{id}              → Detail role beserta permissions dan employees
        ├── POST /roles                   → Buat role baru + sync permissions
        ├── PUT  /roles/{id}              → Edit role + sync permissions (overwrite)
        └── DELETE /roles/{id}           → Hapus role (BLOKIR jika masih ada staf terpakai)

        └── GET /permissions             → List semua permission (untuk form assign ke role)
```

> **Aturan penting**: Role tidak bisa dihapus jika masih ada karyawan yang menggunakan role tersebut (validasi dilakukan di `RoleController@destroy`).

---

## 5. Manajemen Sekolah Mitra (Partner)

**File**: [`PartnerController.php`](app/Http/Controllers/API/AdminSPPG/PartnerController.php)  
**Service**: [`PartnerService.php`](app/Services/Partner/PartnerService.php)

```
Endpoint:
  GET  /partners             → List mitra (filter: school_type, ownership_status, district, city, search)
  GET  /partners/{id}        → Detail mitra
  GET  /partners/summary     → Ringkasan statistik (total mitra, jenis, dll)
  POST /partners             → Tambah mitra manual (tervalidasi via StorePartnerRequest)
  PUT  /partners/{id}        → Edit data mitra
  DELETE /partners/{id}      → Hapus mitra (soft delete)
  POST /partners/import      → Import mitra massal dari file

Struktur data Partner (tabel: partners):
  - school_name, npsn, school_type (SD/SMP/SMA/SMK)
  - ownership_status (negeri/swasta)
  - address, district, city
  - latitude, longitude        ← digunakan dalam kalkulasi rute distribusi
  - portion_count              ← KRITIS: jumlah porsi = dasar kalkulasi kebutuhan stok
  - sppg_id                    ← terhubung ke SPPG induk
```

### Import Partner:
- Menerima file (format CSV/Excel) via `ImportPartnerRequest`.
- `PartnerService::importFromFile()` memvalidasi header kolom dan memproses setiap baris.
- Mengembalikan laporan: `created`, `updated`, `skipped`, dan list `errors` per baris yang gagal.

---

## 6. Modul Gizi: Bahan Baku (Ingredient)

**File**: [`IngredientController.php`](app/Http/Controllers/API/AdminSPPG/IngredientController.php)  
**Service**: [`IngredientService.php`](app/Services/SPPG/IngredientService.php)

```
Endpoint:
  GET  /nutrition/ingredients              → List bahan baku (filter: search, paginate)
  GET  /nutrition/ingredients/dropdown     → List ringkas untuk dropdown form resep
  GET  /nutrition/ingredients/{id}         → Detail bahan baku
  POST /nutrition/ingredients              → Tambah bahan baku baru
  PUT  /nutrition/ingredients/{id}         → Edit bahan baku
  DELETE /nutrition/ingredients/{id}       → Hapus bahan baku (BLOKIR jika dipakai resep)

  POST /nutrition/ingredients/calculate-nutrition
       Body: { ingredient_id, weight(gram) }
       → Preview kalkulasi nilai gizi real-time tanpa menyimpan data
         (dipanggil FE setiap user mengetik berat di form resep)
```

### Struktur Data Ingredient:
```
name, unit (kg/liter/gram/ml/pcs),
calories_per_100g, protein_per_100g, fat_per_100g, carbs_per_100g
```

> `weight_used` di `recipe_ingredients` merepresentasikan **berat dalam gram per porsi**, dikonversi ke satuan stok saat cek ketersediaan.

---

## 7. Modul Gizi: Resep (Recipe)

**File**: [`RecipeController.php`](app/Http/Controllers/API/AdminSPPG/RecipeController.php)  
**Service**: [`RecipeService.php`](app/Services/SPPG/RecipeService.php)

```
Endpoint:
  GET  /nutrition/recipes              → List resep (paginate)
  GET  /nutrition/recipes/dropdown     → List ringkas untuk form menu
  GET  /nutrition/recipes/{id}         → Detail resep + komposisi bahan baku
  POST /nutrition/recipes              → Buat resep baru + daftar bahan baku (recipe_ingredients)
  PUT  /nutrition/recipes/{id}         → Edit resep + sync bahan baku
  DELETE /nutrition/recipes/{id}       → Hapus resep

Hubungan data:
  recipes
    └── recipe_ingredients (pivot)
          ├── ingredient_id     → FK ke ingredients
          └── weight_used (gram per porsi)  ← kunci kalkulasi stok
```

---

## 8. Modul Gizi: Menu Mingguan

**File**: [`MenuController.php`](app/Http/Controllers/API/AdminSPPG/MenuController.php)  
**Service**: [`MenuService.php`](app/Services/SPPG/MenuService.php)

```
Endpoint:
  GET  /nutrition/menus                → List menu (filter: status, search, paginate)
  GET  /nutrition/menus/{id}           → Detail menu + semua menu item
  GET  /nutrition/menus/{id}/grouped   → Menu dikelompokkan per hari (untuk kalender FE)
  POST /nutrition/menus                → Buat menu mingguan baru
  PUT  /nutrition/menus/{id}           → Edit menu + replace items
  DELETE /nutrition/menus/{id}         → Hapus menu + semua items-nya

  PATCH /nutrition/menus/{id}/publish  ← ENDPOINT KRITIS (lihat seksi 14)
  POST  /nutrition/menus/refresh-statuses → Perbarui status semua menu (bisa di-CRON)
```

### Status Siklus Menu:
```
draft  →  published  →  (CRON update)  →  expired
                                       →  upcoming
```

### Struktur Body POST /menus:
```json
{
  "name": "Menu Minggu ke-23",
  "week_start": "2026-06-09",
  "week_end": "2026-06-15",
  "items": [
    { "day_of_week": 1, "menu_date": "2026-06-09", "recipe_id": 3, "meal_time": "lunch" },
    { "day_of_week": 1, "menu_date": "2026-06-09", "recipe_id": 7, "meal_time": "dinner" }
  ]
}
```

---

## 9. Modul Stok Bahan Baku

**File**: [`StockController.php`](app/Http/Controllers/API/AdminSPPG/StockController.php)  
**Service**: [`StockService.php`](app/Services/Stock/StockService.php)

### Status Lifecycle Batch Stok:
```
[Admin Logistik input] → status: pending
       │
       ├── [Pemilik/Admin Approve] → status: available / low (tergantung minimum)
       │         └── Log StockTransaction (type: IN)  + generate batch_number BATCH-YYYYMMDD-XXX
       │
       └── [Pemilik/Admin Reject] → status: rejected
```

### Endpoint Detail:

```
GET  /stocks
     → Ringkasan agregat per bahan baku
     → Tiap bahan baku: { ingredient_id, ingredient_name, total_quantity, unit,
                          minimum_quantity, status (available/low/empty),
                          has_expired, batch_count }

GET  /stocks/pending
     → Daftar batch yang menunggu approval (status: pending)
     → Load relasi: ingredient, creator (employee yang input)

GET  /stocks/{ingredient_id}
     → Detail 1 bahan baku + semua batch per ingredient di SPPG ini
     → Termasuk: minimum_quantity, list semua StockItem

GET  /stocks/transactions
     → Semua riwayat mutasi stok SPPG (paginate)
     → Load: ingredient, stockItem, creator

GET  /stocks/{id}/transactions
     → Riwayat mutasi untuk 1 batch tertentu

POST /stocks
     → Ajukan batch stok baru (status: pending)
     → Field wajib: ingredient_id, quantity, unit, price_per_unit,
                    purchase_date, expiry_date, supplier, storage_type
     → Optional: proof_document (file: jpeg/png/pdf max 2MB), sku, notes

PUT  /stocks/{id}
     → Edit batch (HANYA jika status masih pending)
     → Tidak bisa edit batch yang sudah approved/rejected

DELETE /stocks/{id}
     → Soft delete batch (HANYA jika status masih pending)

POST /stocks/{id}/approve
     → Approve batch → generate batch_number, status: available
     → Buat StockTransaction (type: IN)
     → Sync status aggregate semua batch bahan baku ini

POST /stocks/{id}/reject
     → Tolak batch → status: rejected

PUT  /stocks/minimum/{ingredient_id}
     → Set/update batas minimum stok bahan baku
     → Body: { minimum_quantity, unit }
     → Setelah update, auto-sync status semua batch aktif

GET  /stocks/check-menu/{menu_id}
     → Simulasi non-blocking: apakah stok cukup untuk 1 menu?
     → Tidak memotong stok, hanya laporan kekurangan
     → Return: { shortages: [...], sufficient: true/false }
```

### Aturan Konversi Satuan:
- Resep menyimpan berat bahan baku dalam **gram per porsi** (`weight_used`).
- Jumlah porsi = `SUM(partners.portion_count)` untuk SPPG tersebut.
- Total kebutuhan (gram) → dikonversi ke satuan stok (`kg`/`liter` = gram÷1000, lainnya tetap gram).

---

## 10. Modul Distribusi & Pengiriman

**File**: [`DistributionController.php`](app/Http/Controllers/API/AdminSPPG/DistributionController.php)  
**Service**: [`DeliveryScheduleService.php`](app/Services/Distribution/DeliveryScheduleService.php)  
**Service**: [`RouteOptimizationService.php`](app/Services/Distribution/RouteOptimizationService.php)

### Alur Distribusi:
```
1. Admin Logistik membuat Delivery Schedule
   (via rute terpisah di routes/distribution.php, bukan routes/api_adminsppg.php)

2. Admin SPPG melihat daftar jadwal aktif
   GET /distributions  → DeliverySchedule dengan status active
   GET /distributions/{id} → Detail jadwal + relasi: courier, school, assignedBy, confirmedBy, latestLocation

3. Admin SPPG submit tugas ke kurir (Reverb broadcast)
   POST /distributions/submit
        Body: { schedule_id: int }
        → DeliveryScheduleService::submitTask() dipanggil
        → Memicu broadcast Reverb ke kurir

4. Status siklus Delivery Schedule:
   in_order → delivering → delivered → confirmed
                                     → revision_required → (loop)
                                     → rejected
```

### Route Optimization (RouteOptimizationService):
```
Algoritma: Nearest-Neighbour TSP (O(n²)) → cocok untuk ≤ 30 sekolah per batch
Sumber rute: OSRM API (env: OSRM_BASE_URL, default: https://router.project-osrm.org)
Fallback: Garis lurus Haversine + asumsi kecepatan rata-rata 30 km/jam

Output optimize():
  - ordered_waypoints  → urutan sekolah yang harus dikunjungi
  - geojson (LineString) → polyline rute di peta
  - total_distance_km
  - total_duration_min
```

> **Catatan arsitektur**: `DistributionController` di AdminSPPG hanya bertugas untuk *melihat* dan *submit ke kurir*. Pembuatan/edit/hapus jadwal dikunci (`403`) dan hanya bisa dilakukan via `admin_logistik`.

---

## 11. Pelacakan Kurir (Tracking)

**File**: [`CourierTrackingController.php`](app/Http/Controllers/API/AdminSPPG/CourierTrackingController.php)  
**Service**: [`CourierLocationService.php`](app/Services/Distribution/CourierLocationService.php)

```
POST /tracking/update-location
     → Hanya kurir (role: courier) atau super_admin
     → Body: { schedule_id, latitude, longitude, speed_kmh?, heading_degrees?, accuracy_meters? }
     → Validasi: kurir yang mengirim harus merupakan kurir yang ditugaskan pada schedule tersebut
     → CourierLocationService::recordLocation() → simpan ke courier_locations

GET /tracking/active
     → Hanya admin_logistik, admin_sppg, super_admin
     → Daftar semua kurir yang sedang aktif + posisi terakhir mereka

GET /tracking/{scheduleId}/trail
     → Hanya admin_logistik, admin_sppg, super_admin, courier (hanya miliknya sendiri)
     → Seluruh jejak perjalanan kurir untuk 1 jadwal pengiriman tertentu
     → Return: { schedule_id, data: [...koordinat], total_pings }
```

### Real-Time vs REST Polling:
- **Produksi**: Gunakan **Reverb WebSocket** dengan channel `presence-distribution.map`.
- **Fallback REST**: Endpoint `tracking/active` digunakan jika WebSocket tidak tersedia.

---

## 12. Peta Distribusi

**File**: [`DistributionMapController.php`](app/Http/Controllers/API/AdminSPPG/DistributionMapController.php)

```
GET /maps/distribution
    → Hanya admin_logistik, admin_sppg, super_admin
    → Data awal peta (initial load): semua kurir aktif + posisi terakhir
    → Response menyertakan: note → "Subscribe to presence-distribution.map via Reverb"
```

> Endpoint ini dipakai sebagai **initial load** sebelum FE mulai listen WebSocket Reverb.

---

## 13. Laporan Keuangan

**File**: [`FinancialReportController.php`](app/Http/Controllers/API/AdminSPPG/FinancialReportController.php)

```
GET  /financial-reports           → List laporan (permission: finance.read)
GET  /financial-reports/{id}      → Detail laporan
POST /financial-reports           → Buat laporan baru (permission: finance.create)
PUT  /financial-reports/{id}      → Edit laporan (permission: finance.update)
DELETE /financial-reports/{id}    → Hapus laporan (permission: finance.delete)
```

> **Status implementasi**: Controller telah terdaftar dengan middleware permission yang benar, namun **logika bisnis belum diimplementasikan** (method masih kosong). Ini adalah fitur yang disiapkan untuk implementasi selanjutnya.

---

## 14. Alur Lintas-Modul: Publish Menu → FIFO Stok

Ini adalah alur paling kritis dan terintegrasi dalam sistem Admin SPPG.

```
[Ahli Gizi]
    │
    ├─ OPTIONAL: GET /stocks/check-menu/{menu_id}
    │            → Simulasi tanpa potong stok. Lihat shortages lebih dulu.
    │
    └─ PATCH /nutrition/menus/{id}/publish
              │
              ▼
         MenuController@publish()
              │  Ambil sppg_id & user_id dari token
              │
              ▼
         StockService::deductStockForMenu($sppgId, $menuId, $userId)
              │
              ├─ checkMenuRequirements() → hitung kebutuhan bahan baku
              │    ├─ Ambil semua menu items → resep → recipe_ingredients
              │    ├─ portionCount = SUM(partners.portion_count) untuk SPPG ini
              │    ├─ neededGrams[ingredientId] += weight_used × portionCount
              │    └─ Bandingkan dengan stok tersedia (status: available/low, tidak expired)
              │
              ├─ Jika ADA kekurangan:
              │    └─ throw StockShortageException(shortages)
              │         → HTTP 422 + detail bahan baku yang kurang
              │
              └─ Jika STOK CUKUP (DB Transaction):
                   ├─ Untuk setiap bahan baku:
                   │    ├─ Ambil batch terurut dari paling tua (ORDER BY purchase_date ASC) ← FIFO
                   │    ├─ Potong quantity batch satu per satu sampai kebutuhan terpenuhi
                   │    ├─ Set status batch → 'empty' jika quantity habis
                   │    ├─ Buat StockTransaction (type: 'out', reference_type: 'menu_publish')
                   │    └─ updateBatchStatuses() → sinkronisasi status agregat (available/low/empty)
                   │
                   └─ Menu::update(['status' => 'published'])
                        → Response 200: Menu berhasil dipublikasikan
```

### Contoh Output StockShortageException (HTTP 422):
```json
{
  "success": false,
  "message": "Stok tidak mencukupi untuk menu ini.",
  "shortages": [
    {
      "ingredient_id": 3,
      "ingredient_name": "Beras",
      "needed": 45.5,
      "available": 30.0,
      "unit": "kg",
      "shortage": 15.5
    }
  ]
}
```

---

## 15. Tabel Endpoint Lengkap

| Method | Path | Controller | Permission | Keterangan |
|:-------|:-----|:-----------|:-----------|:-----------|
| `GET` | `/dashboard` | DashboardController | `dashboard.read` | Statistik jadwal, kurir, staf, riwayat bulan ini |
| `GET` | `/employees` | EmployeeController | `employee.read` | List staf |
| `GET` | `/employees/{id}` | EmployeeController | `employee.read` | Detail staf |
| `POST` | `/employees` | EmployeeController | `employee.create` | Tambah staf |
| `PUT` | `/employees/{id}` | EmployeeController | `employee.update` | Edit staf |
| `DELETE` | `/employees/{id}` | EmployeeController | `employee.delete` | Hapus staf |
| `GET` | `/employees/{id}/assign-role` | EmployeeController | `employee.read` | Form assign role |
| `POST` | `/employees/{id}/assign-role` | EmployeeController | `employee.update` | Assign role ke staf |
| `GET` | `/roles` | RoleController | *(role gate)* | List role |
| `GET` | `/roles/{id}` | RoleController | *(role gate)* | Detail role |
| `POST` | `/roles` | RoleController | *(role gate)* | Buat role |
| `PUT` | `/roles/{id}` | RoleController | *(role gate)* | Edit role |
| `DELETE` | `/roles/{id}` | RoleController | *(role gate)* | Hapus role |
| `GET` | `/permissions` | PermissionController | *(role gate)* | List semua permission |
| `GET` | `/partners` | PartnerController | `partner.read` | List mitra |
| `GET` | `/partners/{id}` | PartnerController | `partner.read` | Detail mitra |
| `GET` | `/partners/summary` | PartnerController | `partner.read` | Ringkasan statistik mitra |
| `POST` | `/partners` | PartnerController | `partner.create` | Tambah mitra |
| `PUT` | `/partners/{id}` | PartnerController | `partner.update` | Edit mitra |
| `DELETE` | `/partners/{id}` | PartnerController | `partner.delete` | Hapus mitra |
| `POST` | `/partners/import` | PartnerController | `partner.create` | Import mitra dari file |
| `GET` | `/nutrition/ingredients` | IngredientController | `ingredients.read` | List bahan baku |
| `GET` | `/nutrition/ingredients/dropdown` | IngredientController | `ingredients.read` | Dropdown bahan baku |
| `GET` | `/nutrition/ingredients/{id}` | IngredientController | `ingredients.read` | Detail bahan baku |
| `POST` | `/nutrition/ingredients` | IngredientController | `ingredients.create` | Tambah bahan baku |
| `PUT` | `/nutrition/ingredients/{id}` | IngredientController | `ingredients.update` | Edit bahan baku |
| `DELETE` | `/nutrition/ingredients/{id}` | IngredientController | `ingredients.delete` | Hapus bahan baku |
| `POST` | `/nutrition/ingredients/calculate-nutrition` | IngredientController | `ingredients.read` | Preview kalkulasi gizi |
| `GET` | `/nutrition/recipes` | RecipeController | `recipes.read` | List resep |
| `GET` | `/nutrition/recipes/dropdown` | RecipeController | `recipes.read` | Dropdown resep |
| `GET` | `/nutrition/recipes/{id}` | RecipeController | `recipes.read` | Detail resep |
| `POST` | `/nutrition/recipes` | RecipeController | `recipes.create` | Buat resep |
| `PUT` | `/nutrition/recipes/{id}` | RecipeController | `recipes.update` | Edit resep |
| `DELETE` | `/nutrition/recipes/{id}` | RecipeController | `recipes.delete` | Hapus resep |
| `GET` | `/nutrition/menus` | MenuController | `menus.read` | List menu |
| `GET` | `/nutrition/menus/{id}` | MenuController | `menus.read` | Detail menu |
| `GET` | `/nutrition/menus/{id}/grouped` | MenuController | `menus.read` | Menu per hari (kalender) |
| `POST` | `/nutrition/menus` | MenuController | `menus.create` | Buat menu |
| `PUT` | `/nutrition/menus/{id}` | MenuController | `menus.update` | Edit menu |
| `DELETE` | `/nutrition/menus/{id}` | MenuController | `menus.delete` | Hapus menu |
| `PATCH` | `/nutrition/menus/{id}/publish` | MenuController | `menus.update` | **Publish menu + potong stok FIFO** |
| `POST` | `/nutrition/menus/refresh-statuses` | MenuController | `menus.update` | Refresh status semua menu |
| `GET` | `/stocks` | StockController | `stock.read` | Ringkasan stok per bahan baku |
| `GET` | `/stocks/pending` | StockController | `stock.approve` | List batch menunggu approval |
| `GET` | `/stocks/transactions` | StockController | `stock.read` | Riwayat mutasi stok (paginate) |
| `GET` | `/stocks/check-menu/{menu_id}` | StockController | `stock.read` | Simulasi kecukupan stok |
| `GET` | `/stocks/{ingredient_id}` | StockController | `stock.read` | Detail stok per bahan baku |
| `POST` | `/stocks` | StockController | `stock.create` | Ajukan batch stok baru |
| `PUT` | `/stocks/minimum/{ingredient_id}` | StockController | `stock.update` | Set batas minimum stok |
| `PUT` | `/stocks/{id}` | StockController | `stock.update` | Edit batch (hanya pending) |
| `DELETE` | `/stocks/{id}` | StockController | `stock.delete` | Hapus batch (hanya pending) |
| `POST` | `/stocks/{id}/approve` | StockController | `stock.approve` | Approve batch stok |
| `POST` | `/stocks/{id}/reject` | StockController | `stock.approve` | Tolak batch stok |
| `GET` | `/stocks/{id}/transactions` | StockController | `stock.read` | Mutasi per batch |
| `GET` | `/distributions` | DistributionController | `distribution.read` | List jadwal pengiriman |
| `GET` | `/distributions/{id}` | DistributionController | `distribution.read` | Detail jadwal |
| `POST` | `/distributions/submit` | DistributionController | `distribution.create` | Submit tugas ke kurir |
| `POST` | `/tracking/update-location` | CourierTrackingController | `distribution.update` | Kurir kirim koordinat GPS |
| `GET` | `/tracking/active` | CourierTrackingController | `distribution.read` | Semua kurir aktif + posisi |
| `GET` | `/tracking/{scheduleId}/trail` | CourierTrackingController | `distribution.read` | Jejak perjalanan 1 kurir |
| `GET` | `/maps/distribution` | DistributionMapController | `distribution.read` | Data awal peta distribusi |
| `GET` | `/financial-reports` | FinancialReportController | `finance.read` | List laporan keuangan |
| `GET` | `/financial-reports/{id}` | FinancialReportController | `finance.read` | Detail laporan |
| `POST` | `/financial-reports` | FinancialReportController | `finance.create` | Buat laporan |
| `PUT` | `/financial-reports/{id}` | FinancialReportController | `finance.update` | Edit laporan |
| `DELETE` | `/financial-reports/{id}` | FinancialReportController | `finance.delete` | Hapus laporan |

---

> *Dokumen ini terakhir diperbarui: 2026-06-03. Dihasilkan berdasarkan source code langsung.*
