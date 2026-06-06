# Walkthrough – Perbaikan Modul Distribusi COMS_MBG

## Status Akhir: ✅ SELESAI

`php artisan optimize` → **config DONE · events DONE · routes DONE · views DONE**  
Total routes distribusi terdaftar: **25 routes** (semuanya di-prefix `/api/distribution/` dan dilindungi `auth:sanctum`)

---

## Bug Yang Diperbaiki

### Bug 1 – Namespace Inkonsisten (PSR-4 Violation)
**Sebelum:** Controllers `Api\Distribution` (huruf `i` kecil)  
**Sesudah:** Seragam `API\Distribution` (huruf kapital) sesuai folder fisik

File yang difix:
- [DeliveryScheduleController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/Distribution/DeliveryScheduleController.php)
- [DeliveryHistoryController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/Distribution/DeliveryHistoryController.php)
- [SpatialMapController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/Distribution/SpatialMapController.php)
- [distribution.php](file:///c:/Users/naufa/COMS_MBG/routes/distribution.php) — import `use` statements

---

### Bug 2 – `submitTask()` Status Tidak Berubah
**Sebelum:** Status di-set ulang ke `STATUS_IN_ORDER` (no-op yang redundan, tidak ada guard)  
**Sesudah:** Hanya update `submitted_by`, tambah guard `abort_unless(status === in_order)`, broadcast Reverb tetap jalan

File: [DeliveryScheduleService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/Distribution/DeliveryScheduleService.php)

---

### Bug 3 – `DistributionPolicy` Referensikan Model Tidak Ada
**Sebelum:** `use App\Models\Distribution` → class tidak exist → fatal error  
**Sesudah:** Diganti ke `DeliverySchedule` + logic authorization yang sesuai flow distribusi

File: [DistributionPolicy.php](file:///c:/Users/naufa/COMS_MBG/app/Policies/DistributionPolicy.php)

---

### Bug 4 – Routes Distribusi Tidak Dilindungi Auth (Security!)
**Sebelum:** `require __DIR__.'/distribution.php'` tanpa middleware → semua endpoint bisa diakses tanpa login  
**Sesudah:** `Route::middleware(['auth:sanctum'])->prefix('distribution')->group(base_path('routes/distribution.php'))`

File: [api.php](file:///c:/Users/naufa/COMS_MBG/routes/api.php)

---

### Bug 5 – Services dan Resources di Folder Salah
**Sebelum:** File di `app/Services/SPPG/` tapi namespace `App\Services\Distribution\` → autoloader tidak bisa load  
**Sesudah:** File baru dibuat di lokasi yang benar, namespace lama diperbaiki (PSR-4 compliant)

Files baru (aktif digunakan):
- [app/Services/Distribution/DeliveryScheduleService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/Distribution/DeliveryScheduleService.php)
- [app/Services/Distribution/CourierLocationService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/Distribution/CourierLocationService.php)
- [app/Services/Distribution/RouteOptimizationService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/Distribution/RouteOptimizationService.php)
- [app/Http/Resources/Distribution/DeliveryScheduleResource.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Resources/Distribution/DeliveryScheduleResource.php)
- [app/Http/Resources/Distribution/DeliveryHistoryResource.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Resources/Distribution/DeliveryHistoryResource.php)

File lama (diperbaiki namespace-nya, tidak dihapus):
- `app/Services/SPPG/*.php` → namespace diubah ke `App\Services\SPPG`
- `app/Http/Resources/Delivery*.php` → namespace diubah ke `App\Http\Resources`

---

### Bug 6 – Query `availableCouriers()` Tidak Valid
**Sebelum:**
```php
->where('role', 'courier')                // kolom 'role' tidak ada
->orWhereHas('user', fn($q) => $q->role('courier'))  // method tidak valid
```
**Sesudah:**
```php
->where(function ($q) {
    $q->where('position', 'kurir')
      ->orWhereHas('role', fn($rq) => $rq->where('slug', 'kurir'));
})
->where('status', 'active')
->whereNotNull('user_id')
```

File: [DeliveryScheduleController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/Distribution/DeliveryScheduleController.php)

---

### Bug 7 – Kolom School Salah (nama vs name, alamat vs address)
School model memakai kolom Indonesia (`nama`, `alamat`), tapi berbagai tempat memanggil `->name` dan `->address`  
**Sesudah:** Semua referensi diperbaiki ke `->nama` dan `->alamat`

File yang difix: `DeliveryScheduleService.php`, `CourierLocationService.php`, `DeliveryScheduleResource.php`, `CourierTaskSubmitted.php` (Event)

---

### Bug 8 – Events di Folder Salah
**Sebelum:** Event files di `app/Events/` root tapi namespace `App\Events\Distribution\`  
**Sesudah:** Events baru dibuat di `app/Events/Distribution/`, file lama diperbaiki namespace-nya

Files baru (aktif):
- [app/Events/Distribution/CourierTaskSubmitted.php](file:///c:/Users/naufa/COMS_MBG/app/Events/Distribution/CourierTaskSubmitted.php)
- [app/Events/Distribution/CourierLocationUpdated.php](file:///c:/Users/naufa/COMS_MBG/app/Events/Distribution/CourierLocationUpdated.php)
- [app/Events/Distribution/DeliveryStatusUpdated.php](file:///c:/Users/naufa/COMS_MBG/app/Events/Distribution/DeliveryStatusUpdated.php)

---

### Bug 9 – Route Terdaftar Dua Kali (Root Cause!)
**Sebelum:** `DistributionServiceProvider::boot()` memanggil `loadRoutesFrom(distribution.php)` TANPA prefix/middleware, PLUS api.php juga me-load file yang sama dengan prefix → `distribution.schedules.index` terdaftar 2x  
**Sesudah:** `loadRoutesFrom()` dihapus dari ServiceProvider. Hanya api.php yang load routes.

File: [DistributionServiceProvider.php](file:///c:/Users/naufa/COMS_MBG/app/Providers/DistributionServiceProvider.php)

---

### Bug 10 – Route Name Conflict (schools & roles)
`apiResource('schools', ...)` didaftarkan di `api_superadmin.php` DAN `api_adminsppg.php` → nama `schools.index` bentrok  
**Sesudah:** AdminSPPG schools & roles routes dibungkus `Route::name('sppg.')` sebagai prefix

File: [api_adminsppg.php](file:///c:/Users/naufa/COMS_MBG/routes/api_adminsppg.php)

---

## Controllers yang Diimplementasi (Sebelumnya Kosong)

| Controller | Sebelum | Sesudah |
|-----------|---------|---------|
| [DistributionController](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) | `//` kosong | Full CRUD + submit task ke kurir |
| [DistributionMapController](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionMapController.php) | `//` kosong | Delegate ke CourierLocationService |
| [CourierTrackingController](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/CourierTrackingController.php) | `//` kosong | updateLocation, activeCouriers, trail |
| [DashboardController](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) | `//` kosong | Statistik jadwal, riwayat, kurir aktif |
| [SchoolController](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/SchoolController.php) | `//` kosong | Full CRUD sekolah + guard hapus |

---

## Endpoint API Distribusi (Postman Ready)

### Base URL: `/api/distribution/` — Auth: Bearer Token (Sanctum)

#### Jadwal Pengiriman
| Method | Endpoint | Role | Fungsi |
|--------|----------|------|--------|
| GET | `/api/distribution/schedules` | All | List jadwal aktif |
| POST | `/api/distribution/schedules` | admin_logistik | Buat jadwal baru |
| GET | `/api/distribution/schedules/{id}` | All | Detail jadwal |
| PUT | `/api/distribution/schedules/{id}` | admin_logistik | Update jadwal (in_order only) |
| DELETE | `/api/distribution/schedules/{id}` | admin_logistik | Hapus jadwal (in_order only) |
| GET | `/api/distribution/schedules/meta/couriers` | admin_logistik | Dropdown kurir tersedia |
| POST | `/api/distribution/schedules/{id}/submit` | admin_sppg | Submit tugas ke kurir → Reverb broadcast |
| POST | `/api/distribution/schedules/{id}/accept` | courier | Kurir terima tugas |
| POST | `/api/distribution/schedules/{id}/reject` | courier | Kurir tolak + alasan + foto |
| POST | `/api/distribution/schedules/{id}/proof` | courier | Upload bukti pengiriman |
| POST | `/api/distribution/schedules/{id}/proof/resubmit` | courier | Resubmit bukti (setelah revisi) |
| POST | `/api/distribution/schedules/{id}/confirm` | admin_logistik | Konfirmasi → arsip ke riwayat |
| POST | `/api/distribution/schedules/{id}/revision` | admin_logistik | Minta revisi bukti |

#### Riwayat Pengiriman
| Method | Endpoint | Role | Fungsi |
|--------|----------|------|--------|
| GET | `/api/distribution/histories` | All | Riwayat (kurir: milik sendiri) |
| GET | `/api/distribution/histories/{id}` | All | Detail riwayat |
| GET | `/api/distribution/histories/analytics` | admin | Analitik bulanan |

#### Peta Spasial & Realtime
| Method | Endpoint | Role | Fungsi |
|--------|----------|------|--------|
| GET | `/api/distribution/map/active-couriers` | admin | Live map semua kurir aktif |
| POST | `/api/distribution/map/location/{schedule}` | courier | Kirim GPS ping |
| GET | `/api/distribution/map/trail/{schedule}` | admin/courier | Trail rute pengiriman |
| POST | `/api/distribution/map/optimize-route` | admin | Optimasi rute (OSRM + TSP) |
| GET | `/api/distribution/map/depot` | admin | Koordinat depot SPG |

#### AdminSPPG Panel
| Method | Endpoint | Fungsi |
|--------|----------|--------|
| GET | `/api/admin-sppg/distributions` | List jadwal (admin view) |
| GET | `/api/admin-sppg/distributions/{id}` | Detail jadwal |
| POST | `/api/admin-sppg/distributions/submit` | Submit tugas ke kurir |
| GET | `/api/admin-sppg/maps/distribution` | Live map kurir |
| POST | `/api/admin-sppg/tracking/update-location` | Update GPS kurir |
| GET | `/api/admin-sppg/tracking/active` | Kurir aktif |
| GET | `/api/admin-sppg/tracking/{id}/trail` | Trail kurir |
| GET/POST/PUT/DELETE | `/api/admin-sppg/schools` | CRUD sekolah |
| GET | `/api/admin-sppg/dashboard` | Dashboard statistik distribusi |

---

## Alur Status Delivery (State Machine)

```
in_order → [courier accept] → delivering → [courier submit proof] → delivered
                                                                         ↓
                                                          [admin confirm] → confirmed → (arsip ke histories)
                                                          [admin revision] → revision_required → [courier resubmit] → delivered

in_order → [courier reject] → rejected
```

## Realtime (Laravel Reverb)

| Channel | Event | Subscriber |
|---------|-------|-----------|
| `private-courier.{id}` | `distribution.task.submitted` | Kurir (task baru) |
| `presence-distribution.map` | `distribution.courier.location` | Admin (peta live) |
| `presence-distribution.operations` | `distribution.status.updated` | Admin dashboard |
| `private-courier.{id}` | `distribution.status.updated` | Kurir (status change) |
