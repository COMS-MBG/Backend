# 🔍 Laporan Analisis Kesiapan API Backend — COMS MBG
> **Tanggal Analisa:** 18 Mei 2026  
> **Sumber Data:** `api_integration_map.md` (harapan FE) vs `routes/*.php` (realita BE)  
> **Kesimpulan Cepat:** API **belum siap penuh** — ada **4 kategori masalah kritis** yang harus diselesaikan sebelum integrasi FE dapat berjalan.

---

## 📊 Scorecard Kesiapan Per Modul

| Modul | Harapan FE | Status BE | Kesiapan |
|-------|-----------|-----------|----------|
| 🔐 Auth | 4 endpoint | ✅ Semua ada, path tepat | ✅ **SIAP** |
| 🤝 Partner / Sekolah | 7 endpoint | ✅ Semua ada, path tepat | ✅ **SIAP** |
| 🥗 Master Bahan | 4 endpoint | ⚠️ Ada, tapi prefix salah | ❌ **BELUM SIAP** |
| 📖 Master Resep | 5 endpoint | ⚠️ Ada, tapi prefix salah | ❌ **BELUM SIAP** |
| 📅 Menu Planning | 4 endpoint | ⚠️ Ada, tapi prefix salah | ❌ **BELUM SIAP** |
| 👤 Karyawan (HR) | 6 endpoint | ⚠️ Ada sebagian, 1 endpoint hilang | ❌ **BELUM SIAP** |
| 🔑 Hak Akses / Role | 3 endpoint | ⚠️ Ada sebagian, 1 sub-route hilang | ⚠️ **PERLU VERIFIKASI** |
| 🚚 Distribusi Jadwal | 4 endpoint | ❌ Route file tidak terdaftar di api.php | ❌ **KRITIS** |
| 🗺️ Peta Spasial | 4 endpoint | ⚠️ Path mismatch + 1 endpoint hilang | ❌ **BELUM SIAP** |
| 📊 Laporan Distribusi | 3 endpoint | ⚠️ Hanya 1 dari 3 yang ada | ❌ **BELUM SIAP** |
| 💰 Laporan Keuangan | 3 endpoint | ⚠️ 2 dari 3 ada, stats tidak ada | ⚠️ **HAMPIR SIAP** |
| 🏠 Dashboard | 2 endpoint | ⚠️ Hanya 1 dari 2 yang ada | ❌ **BELUM SIAP** |

---

## 🔴 MASALAH KRITIS #1 — `distribution.php` Tidak Terdaftar di `api.php`

> [!CAUTION]
> Ini adalah bug paling berbahaya. File `routes/distribution.php` **sudah ada dan lengkap** (87 baris, berisi 15+ endpoint), tetapi **tidak di-require** di `routes/api.php`. Artinya seluruh modul distribusi (jadwal, riwayat, peta kurir real-time) **tidak dapat diakses sama sekali**.

**Kondisi `routes/api.php` saat ini:**
```php
require __DIR__.'/api_auth.php';
require __DIR__.'/api_superadmin.php';
require __DIR__.'/api_adminsppg.php';
require __DIR__.'/api_public.php';
// ← routes/distribution.php TIDAK ADA DI SINI
```

**Fix yang diperlukan — tambahkan 1 baris:**
```php
require __DIR__.'/api_auth.php';
require __DIR__.'/api_superadmin.php';
require __DIR__.'/api_adminsppg.php';
require __DIR__.'/api_public.php';
require __DIR__.'/distribution.php';   // ← TAMBAHKAN INI
```

**Endpoint yang akan aktif setelah fix:**

| Method | URL Aktif | Fungsi |
|--------|-----------|--------|
| GET | `/api/distribution/schedules` | List jadwal pengiriman |
| POST | `/api/distribution/schedules` | Buat jadwal baru |
| GET | `/api/distribution/schedules/{id}` | Detail jadwal |
| PUT | `/api/distribution/schedules/{id}` | Update jadwal |
| DELETE | `/api/distribution/schedules/{id}` | Hapus jadwal |
| POST | `/api/distribution/schedules/{id}/submit` | Submit tugas ke kurir |
| POST | `/api/distribution/schedules/{id}/accept` | Kurir terima tugas |
| POST | `/api/distribution/schedules/{id}/proof` | Upload bukti pengiriman |
| POST | `/api/distribution/schedules/{id}/confirm` | Konfirmasi pengiriman |
| GET | `/api/distribution/histories` | Riwayat pengiriman |
| GET | `/api/distribution/histories/analytics` | Analitik riwayat |
| GET | `/api/distribution/map/active-couriers` | Kurir aktif (live) |
| GET | `/api/distribution/map/depot` | Lokasi depot |
| POST | `/api/distribution/map/optimize-route` | Optimasi rute |

> [!WARNING]
> **Perhatian:** Base URL distribusi adalah `/api/distribution/...` (dari `distribution.php`), BUKAN `/api/admin-sppg/distributions/...` seperti yang tertulis di `api_integration_map.md`. FE harus menyesuaikan URL ini.

---

## 🔴 MASALAH KRITIS #2 — Prefix `nutrition/` Hilang di Tiga Modul Gizi

> [!CAUTION]
> Modul **Master Bahan, Master Resep, dan Menu Planning** di backend menggunakan prefix `/nutrition/` yang **tidak tercantum di `api_integration_map.md`**. Jika FE memanggil URL lama, akan dapat response `404 Not Found`.

**Perbandingan URL:**

| Modul | URL di `api_integration_map.md` (SALAH) | URL Realita di BE (BENAR) |
|-------|----------------------------------------|--------------------------|
| Master Bahan | `GET /api/admin-sppg/ingredients` | `GET /api/admin-sppg/nutrition/ingredients` |
| Bahan Dropdown | — (tidak terdokumentasi) | `GET /api/admin-sppg/nutrition/ingredients/dropdown` |
| Hitung Nutrisi | — (tidak terdokumentasi) | `POST /api/admin-sppg/nutrition/ingredients/calculate-nutrition` |
| Master Resep | `GET /api/admin-sppg/recipes` | `GET /api/admin-sppg/nutrition/recipes` |
| Resep Dropdown | — (tidak terdokumentasi) | `GET /api/admin-sppg/nutrition/recipes/dropdown` |
| Menu Planning | `GET /api/admin-sppg/menus` | `GET /api/admin-sppg/nutrition/menus` |
| Publish Menu | — (tidak terdokumentasi) | `PATCH /api/admin-sppg/nutrition/menus/{id}/publish` |
| Menu Grouped | — (tidak terdokumentasi) | `GET /api/admin-sppg/nutrition/menus/{id}/grouped` |

**Juga ditemukan 3 endpoint bonus di BE yang belum terdokumentasi di FE:**
- `POST /api/admin-sppg/nutrition/ingredients/calculate-nutrition` — untuk kalkulasi gizi otomatis
- `GET /api/admin-sppg/nutrition/recipes/dropdown` — untuk dropdown pilih resep di menu planning
- `PATCH /api/admin-sppg/nutrition/menus/{id}/publish` — untuk publish/approve menu

---

## 🟡 MASALAH SEDANG #3 — Endpoint Hilang atau Nama Berbeda

### 3a. Karyawan — `PATCH toggle-status` tidak ada

FE mengharapkan:
```
PATCH /api/admin-sppg/employees/{id}/toggle-status
```

BE yang tersedia (`api_adminsppg.php`):
```php
Route::apiResource('employees', EmployeeController::class);
Route::get('employees/{employee}/assign-role', ...);
Route::post('employees/{employee}/assign-role', ...);
// ← Tidak ada route toggle-status
```

**Yang perlu ditambahkan di BE:**
```php
Route::patch('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus']);
```

### 3b. Peta Spasial — Path Mismatch dan Endpoint Hilang

| Yang FE Harapkan | Yang Ada di BE | Status |
|-----------------|----------------|--------|
| `GET /api/admin-sppg/distribution-map` | `GET /api/admin-sppg/maps/distribution` | ⚠️ Path beda |
| `GET /api/admin-sppg/courier-tracking` | `POST /api/admin-sppg/tracking/update-location` | ⚠️ Berbeda fungsi |
| `POST /api/admin-sppg/simulation` | — | ❌ Tidak ada |
| `GET /api/admin-sppg/schools` | ✅ `apiResource('schools', ...)` | ✅ OK |
| `GET /api/super-admin/sppg` | ✅ `/api/super-admin/sppg/` | ✅ OK |

### 3c. Laporan Distribusi — Stats & Export Tidak Ada

| Yang FE Harapkan | Status di BE |
|-----------------|-------------|
| `GET /api/admin-sppg/distributions` | ✅ Ada (via apiResource) |
| `GET /api/admin-sppg/distributions/stats` | ❌ Tidak ada |
| `GET /api/admin-sppg/distributions/export` | ❌ Tidak ada |

### 3d. Laporan Keuangan — Stats Tidak Ada

| Yang FE Harapkan | Status di BE |
|-----------------|-------------|
| `GET /api/admin-sppg/financial-reports` | ✅ Ada (via apiResource) |
| `GET /api/admin-sppg/financial-reports/stats` | ❌ Tidak ada |
| `GET /api/super-admin/financial-reports` | ✅ Ada (via apiResource) |

### 3e. Dashboard Super Admin Tidak Ada

| Yang FE Harapkan | Status di BE |
|-----------------|-------------|
| `GET /api/admin-sppg/dashboard` | ✅ Ada |
| `GET /api/super-admin/dashboard` | ❌ Tidak ada (yang ada: `GET /api/public/dashboard`) |

### 3f. Hak Akses — Sub-route Permission Update Perlu Verifikasi

FE mengharapkan `PUT /api/admin-sppg/roles/{id}/permissions`. BE punya `apiResource('roles', ...)` yang menghasilkan `PUT /api/admin-sppg/roles/{id}`, tapi bukan sub-route `/permissions`. Perlu dikonfirmasi apakah `RoleController@update` memang menangani update permissions-nya sekaligus.

---

## 🟢 MODUL YANG SUDAH SIAP PENUH

### ✅ Auth
```
GET  /sanctum/csrf-cookie     → ✅
POST /api/auth/login          → ✅
GET  /api/auth/user           → ✅
POST /api/auth/logout         → ✅
```

### ✅ Partner / Sekolah
```
GET    /api/admin-sppg/partners          → ✅
GET    /api/admin-sppg/partners/summary  → ✅
GET    /api/admin-sppg/partners/{id}     → ✅
POST   /api/admin-sppg/partners          → ✅
PUT    /api/admin-sppg/partners/{id}     → ✅
DELETE /api/admin-sppg/partners/{id}     → ✅
POST   /api/admin-sppg/partners/import   → ✅
```

---

## 📋 Daftar Tindakan Perbaikan (Prioritas)

### 🔴 P0 — Lakukan Sekarang (Blokir Semua Integrasi)

| # | Tindakan | File yang Diubah |
|---|----------|-----------------|
| 1 | Tambahkan `require __DIR__.'/distribution.php'` di `api.php` | `routes/api.php` |
| 2 | Update semua URL bahan di FE: `/ingredients` → `/nutrition/ingredients` | `src/api/bahan.api.ts` |
| 3 | Update semua URL resep di FE: `/recipes` → `/nutrition/recipes` | `src/api/resep.api.ts` |
| 4 | Update semua URL menu di FE: `/menus` → `/nutrition/menus` | `src/api/menu.api.ts` |
| 5 | Update URL distribusi FE: `/admin-sppg/distributions` → `/distribution/schedules` | `src/api/distribution.api.ts` |

### 🟡 P1 — Selesaikan Sebelum Testing Integrasi

| # | Tindakan | File yang Diubah |
|---|----------|-----------------|
| 6 | Tambahkan route `PATCH employees/{employee}/toggle-status` di BE | `routes/api_adminsppg.php` |
| 7 | Tambahkan method `toggleStatus()` di `EmployeeController` | `app/Http/Controllers/API/AdminSPPG/EmployeeController.php` |
| 8 | Tambahkan route `GET distributions/stats` di BE | `routes/api_adminsppg.php` |
| 9 | Tambahkan route `GET distributions/export` di BE | `routes/api_adminsppg.php` |
| 10 | Tambahkan route `GET financial-reports/stats` di BE | `routes/api_adminsppg.php` |
| 11 | Perbaiki URL peta spasial: FE harus pakai `/maps/distribution` bukan `/distribution-map` | `src/api/spatial.api.ts` |

### 🟢 P2 — Enhancement (Boleh Belakangan)

| # | Tindakan |
|---|----------|
| 12 | Tambahkan `GET /api/super-admin/dashboard` di `api_superadmin.php` |
| 13 | Tambahkan `POST /api/admin-sppg/simulation` untuk simulasi penempatan SPPG |
| 14 | Dokumentasikan 3 endpoint bonus di FE: `calculate-nutrition`, `dropdown`, `publish` |
| 15 | Verifikasi apakah `RoleController@update` menangani permissions atau perlu sub-route `/permissions` |

---

## 🗺️ Peta URL Koreksi (Cheat Sheet untuk FE)

```
SALAH (di api_integration_map.md)          → BENAR (realita BE)
─────────────────────────────────────────────────────────────────
/api/admin-sppg/ingredients                → /api/admin-sppg/nutrition/ingredients
/api/admin-sppg/recipes                    → /api/admin-sppg/nutrition/recipes
/api/admin-sppg/menus                      → /api/admin-sppg/nutrition/menus
/api/admin-sppg/distributions (distribusi) → /api/distribution/schedules
/api/admin-sppg/courier-tracking           → /api/distribution/map/active-couriers
/api/admin-sppg/distribution-map           → /api/admin-sppg/maps/distribution
```
