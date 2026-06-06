# Bug Fix Guide — Modul Distribusi COMS_MBG

**Tanggal:** 2026-06-02  
**Scope:** Perbaikan kritis sebelum integrasi Frontend  
**Urutan pengerjaan:** Prioritas 1 → 2 → 3 → 4

---

## Prioritas 1 — Fix Namespace PSR-4 (Bug 1 & Bug 8)

### Masalah
File fisik ada di `app/Http/Controllers/API/` (kapital) tapi namespace di dalam file menggunakan `Api` (kecil). Tidak error di Windows, **fatal error di Linux/production**.

### File yang perlu diubah

#### `app/Http/Controllers/API/Distribution/DeliveryScheduleController.php`
```php
// SEBELUM (salah)
namespace App\Http\Controllers\Api\Distribution;

// SESUDAH (benar)
namespace App\Http\Controllers\API\Distribution;
```

#### `app/Http/Controllers/API/Distribution/DeliveryHistoryController.php`
```php
// SEBELUM (salah)
namespace App\Http\Controllers\Api\Distribution;

// SESUDAH (benar)
namespace App\Http\Controllers\API\Distribution;
```

#### `app/Http/Controllers/API/Distribution/SpatialMapController.php`
```php
// SEBELUM (salah)
namespace App\Http\Controllers\Api\Distribution;

// SESUDAH (benar)
namespace App\Http\Controllers\API\Distribution;
```

### File Service yang namespace-nya salah (Bug 8)

#### `app/Services/SPPG/DeliveryScheduleService.php`
File fisik ada di folder `SPPG/` tapi namespace-nya `Distribution`. **Pilih salah satu solusi:**

**Solusi A — Ubah namespace agar sesuai folder fisik (direkomendasikan):**
```php
// SEBELUM (salah)
namespace App\Services\Distribution;

// SESUDAH (benar — sesuai folder fisik app/Services/SPPG/)
namespace App\Services\SPPG;
```

**Solusi B — Pindahkan file ke folder yang sesuai namespace:**
```bash
mkdir -p app/Services/Distribution
mv app/Services/SPPG/DeliveryScheduleService.php app/Services/Distribution/DeliveryScheduleService.php
mv app/Services/SPPG/CourierLocationService.php app/Services/Distribution/CourierLocationService.php
```

> **Catatan:** Jika memilih Solusi B, update semua `use` statement yang mereferensikan service ini di controller dan provider.

### Update `use` statement di routes/distribution.php
```php
// SEBELUM (salah)
use App\Http\Controllers\Api\Distribution\DeliveryScheduleController;
use App\Http\Controllers\Api\Distribution\DeliveryHistoryController;
use App\Http\Controllers\Api\Distribution\SpatialMapController;

// SESUDAH (benar)
use App\Http\Controllers\API\Distribution\DeliveryScheduleController;
use App\Http\Controllers\API\Distribution\DeliveryHistoryController;
use App\Http\Controllers\API\Distribution\SpatialMapController;
```

### Verifikasi
```bash
php artisan route:list | grep distribution
# Pastikan tidak ada error "Class not found"
```

---

## Prioritas 2 — Tambahkan Auth Middleware (Bug 4)

### Masalah
`routes/distribution.php` tidak dibungkus `auth:sanctum`, sehingga:
- Semua endpoint bisa diakses tanpa login (security hole)
- `$request->user()` selalu `null` → semua endpoint yang butuh user context return **Error 500**

### Perbaikan di `routes/distribution.php`

Buka file `routes/distribution.php`, lalu bungkus **seluruh isi route** dengan middleware group:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Distribution\DeliveryScheduleController;
use App\Http\Controllers\API\Distribution\DeliveryHistoryController;
use App\Http\Controllers\API\Distribution\SpatialMapController;

Route::middleware(['auth:sanctum'])->prefix('distribution')->group(function () {

    // ── Delivery Schedule ──────────────────────────────────────────
    Route::prefix('schedules')->group(function () {
        Route::get('/',                        [DeliveryScheduleController::class, 'index']);
        Route::post('/',                       [DeliveryScheduleController::class, 'store']);
        Route::get('/{id}',                    [DeliveryScheduleController::class, 'show']);
        Route::put('/{id}',                    [DeliveryScheduleController::class, 'update']);
        Route::delete('/{id}',                 [DeliveryScheduleController::class, 'destroy']);
        Route::get('/couriers/available',      [DeliveryScheduleController::class, 'availableCouriers']);
        Route::post('/{id}/submit-task',       [DeliveryScheduleController::class, 'submitTask']);
        Route::post('/{id}/accept',            [DeliveryScheduleController::class, 'acceptTask']);
        Route::post('/{id}/reject',            [DeliveryScheduleController::class, 'rejectTask']);
        Route::post('/{id}/submit-proof',      [DeliveryScheduleController::class, 'submitProof']);
        Route::post('/{id}/resubmit-proof',    [DeliveryScheduleController::class, 'resubmitProof']);
        Route::post('/{id}/confirm-delivery',  [DeliveryScheduleController::class, 'confirmDelivery']);
        Route::post('/{id}/request-revision',  [DeliveryScheduleController::class, 'requestRevision']);
    });

    // ── Delivery History ───────────────────────────────────────────
    Route::prefix('history')->group(function () {
        Route::get('/',            [DeliveryHistoryController::class, 'index']);
        Route::get('/analytics',   [DeliveryHistoryController::class, 'analytics']);
        Route::get('/{id}',        [DeliveryHistoryController::class, 'show']);
    });

    // ── Spatial Map ────────────────────────────────────────────────
    Route::prefix('map')->group(function () {
        Route::post('/location',           [SpatialMapController::class, 'recordLocation']);
        Route::get('/couriers/active',     [SpatialMapController::class, 'activeCouriers']);
        Route::get('/trail/{scheduleId}',  [SpatialMapController::class, 'locationTrail']);
        Route::post('/optimize-route',     [SpatialMapController::class, 'optimizeRoute']);
        Route::get('/depot',               [SpatialMapController::class, 'depotLocation']);
    });

});
```

### Pastikan `routes/api.php` sudah include file ini
```php
// routes/api.php
require __DIR__.'/distribution.php';
```

> Tidak perlu wrapper tambahan di `api.php` karena middleware sudah ada di dalam `distribution.php`.

### Verifikasi — Test di Postman
```
# Tanpa token → harus return 401
GET http://your-app.test/api/distribution/schedules

# Dengan token → harus return 200
GET http://your-app.test/api/distribution/schedules
Authorization: Bearer {your_sanctum_token}
```

---

## Prioritas 3 — Hapus Route Duplikat (Bug 7)

### Masalah
`routes/api_adminsppg.php` mendaftarkan route distribusi yang mengarah ke controller kosong (`DistributionController`, `DistributionMapController`, `CourierTrackingController`). Karena Laravel register route sesuai urutan load, controller kosong ini bisa merespons duluan dan return `null`.

### Perbaikan di `routes/api_adminsppg.php`

Cari dan **hapus atau komentari** baris-baris berikut:

```php
// HAPUS atau KOMENTARI baris-baris ini:

// Route::get('/distributions', [DistributionController::class, 'index']);
// Route::post('/tracking/update-location', [CourierTrackingController::class, 'updateLocation']);
// Route::get('/maps/distribution', [DistributionMapController::class, 'index']);

// Hapus juga use statement-nya jika tidak dipakai di tempat lain:
// use App\Http\Controllers\AdminSPPG\DistributionController;
// use App\Http\Controllers\AdminSPPG\CourierTrackingController;
// use App\Http\Controllers\AdminSPPG\DistributionMapController;
```

> **Catatan:** Jangan hapus controller file-nya dulu — cukup komentari route-nya. Nanti file controller kosong itu bisa diisi atau dihapus di Prioritas 5.

### Verifikasi
```bash
php artisan route:list | grep -E "distributions|tracking/update|maps/distribution"
# Pastikan tidak ada lagi route yang mengarah ke controller kosong AdminSPPG
```

---

## Prioritas 4 — Perbaiki DistributionPolicy (Bug 3)

### Masalah
`app/Policies/DistributionPolicy.php` menggunakan `use App\Models\Distribution` yang tidak exist. Jika policy ini pernah dipanggil, akan throw **fatal error**.

### Pilih salah satu solusi:

**Solusi A — Hapus file policy (jika belum dipakai):**
```bash
rm app/Policies/DistributionPolicy.php
```
Lalu hapus registrasinya di `app/Providers/AuthServiceProvider.php`:
```php
// Hapus baris ini:
// \App\Models\Distribution::class => \App\Policies\DistributionPolicy::class,
```

**Solusi B — Ganti referensi model ke model yang ada:**
```php
<?php

namespace App\Policies;

// SEBELUM (salah — model tidak exist)
// use App\Models\Distribution;

// SESUDAH — ganti ke model yang relevan, misalnya DeliverySchedule
use App\Models\DeliverySchedule;
use App\Models\User;

class DistributionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin_logistik', 'admin_sppg', 'superadmin']);
    }

    public function view(User $user, DeliverySchedule $schedule): bool
    {
        if ($user->role === 'courier') {
            return $schedule->courier_id === $user->employee?->id;
        }
        return in_array($user->role, ['admin_logistik', 'admin_sppg', 'superadmin']);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin_logistik', 'superadmin']);
    }

    public function update(User $user, DeliverySchedule $schedule): bool
    {
        return in_array($user->role, ['admin_logistik', 'superadmin'])
            && $schedule->status === 'in_order';
    }

    public function delete(User $user, DeliverySchedule $schedule): bool
    {
        return $user->role === 'superadmin';
    }
}
```

---

## Prioritas 5 — Perbaiki Logic Bug (Bug 2 & Bug 6)

### Bug 2 — `submitTask()` tidak mengubah status

**File:** `app/Services/SPPG/DeliveryScheduleService.php` (sekitar baris 61–64)

```php
// SEBELUM (salah — status tidak berubah)
$schedule->update([
    'submitted_by' => $adminSppgId,
    'status'       => DeliverySchedule::STATUS_IN_ORDER, // ← status tetap sama
]);

// SESUDAH (benar — status berubah jadi 'submitted' setelah task dikirim ke kurir)
$schedule->update([
    'submitted_by'  => $adminSppgId,
    'submitted_at'  => now(),
    'status'        => DeliverySchedule::STATUS_SUBMITTED, // ← status baru
]);
```

Pastikan konstanta `STATUS_SUBMITTED` ada di model `DeliverySchedule`:
```php
// app/Models/DeliverySchedule.php
const STATUS_IN_ORDER  = 'in_order';
const STATUS_SUBMITTED = 'submitted';   // ← tambahkan ini
const STATUS_DELIVERING = 'delivering';
const STATUS_DELIVERED  = 'delivered';
```

Dan tambahkan migrasi jika kolom `submitted_at` belum ada:
```bash
php artisan make:migration add_submitted_at_to_delivery_schedules_table
```
```php
// Di file migrasi yang baru dibuat:
public function up(): void
{
    Schema::table('delivery_schedules', function (Blueprint $table) {
        $table->timestamp('submitted_at')->nullable()->after('submitted_by');
    });
}
```

### Bug 6 — `availableCouriers()` query tidak valid

**File:** `app/Http/Controllers/API/Distribution/DeliveryScheduleController.php`

```php
// SEBELUM (salah — kolom 'role' tidak ada di tabel employees, ->role() bukan method valid)
$couriers = Employee::query()
    ->where('role', 'courier')
    ->orWhereHas('user', fn($q) => $q->role('courier'))
    ->get();

// SESUDAH — sesuaikan dengan struktur relasi yang ada
// Asumsi: role ada di tabel users, relasi Employee->user() sudah ada
$couriers = Employee::query()
    ->whereHas('user', fn($q) => $q->where('role', 'courier'))
    ->with(['user:id,name,email', 'currentSchedule'])
    ->get();

// Atau jika role ada di tabel employees sebagai kolom 'position':
$couriers = Employee::query()
    ->where('position', 'courier')
    ->with('user:id,name,email')
    ->get();
```

> **Catatan:** Sesuaikan nama kolom dengan struktur tabel `employees` dan `users` yang sebenarnya di project kamu. Cek dengan:
> ```bash
> php artisan tinker
> >>> Schema::getColumnListing('employees')
> >>> Schema::getColumnListing('users')
> ```

---

## Prioritas 6 — Perbaiki Resource Namespace (Bug 5)

### Masalah
`DeliveryHistoryController` menggunakan:
```php
use App\Http\Resources\Distribution\DeliveryHistoryResource;
```
Tapi file fisiknya ada di `app/Http/Resources/DeliveryHistoryResource.php` (tanpa subfolder).

### Solusi — Pindahkan Resource ke subfolder yang benar
```bash
mkdir -p app/Http/Resources/Distribution
mv app/Http/Resources/DeliveryHistoryResource.php app/Http/Resources/Distribution/DeliveryHistoryResource.php
mv app/Http/Resources/DeliveryScheduleResource.php app/Http/Resources/Distribution/DeliveryScheduleResource.php
```

Lalu update namespace di dalam masing-masing file resource:
```php
// app/Http/Resources/Distribution/DeliveryHistoryResource.php
namespace App\Http\Resources\Distribution; // ← sudah benar, tinggal pastikan file-nya pindah

// app/Http/Resources/Distribution/DeliveryScheduleResource.php
namespace App\Http\Resources\Distribution; // ← sudah benar
```

---

## Checklist Akhir Sebelum Testing di Postman

Jalankan perintah ini setelah semua perbaikan selesai:

```bash
# 1. Clear semua cache
php artisan optimize:clear

# 2. Cek semua route terdaftar dengan benar
php artisan route:list --path=api/distribution

# 3. Cek tidak ada class yang bermasalah
php artisan config:cache
php artisan route:cache

# 4. Jalankan migrasi jika ada perubahan skema
php artisan migrate

# 5. Cek tidak ada error di log
tail -f storage/logs/laravel.log
```

### Urutan test endpoint di Postman

| Langkah | Method | Endpoint | Ekspektasi |
|---------|--------|----------|------------|
| 1 | GET | `/api/distribution/schedules` tanpa token | `401 Unauthenticated` |
| 2 | GET | `/api/distribution/schedules` dengan token | `200 OK` |
| 3 | GET | `/api/distribution/schedules/couriers/available` | `200` + daftar kurir |
| 4 | POST | `/api/distribution/schedules` | `201 Created` |
| 5 | GET | `/api/distribution/history` | `200 OK` |
| 6 | GET | `/api/distribution/map/depot` | `200` + koordinat depot |
| 7 | GET | `/api/distribution/map/couriers/active` | `200` + daftar kurir aktif |

---

## Ringkasan Perubahan File

| File | Aksi | Bug |
|------|------|-----|
| `Controllers/API/Distribution/*.php` | Ubah namespace `Api` → `API` | Bug 1 |
| `Services/SPPG/DeliveryScheduleService.php` | Fix namespace + fix `submitTask()` status | Bug 2, 8 |
| `Services/SPPG/CourierLocationService.php` | Fix namespace | Bug 8 |
| `routes/distribution.php` | Tambah `auth:sanctum` wrapper + fix `use` | Bug 4, 1 |
| `routes/api_adminsppg.php` | Komentari route duplikat distribusi | Bug 7 |
| `Policies/DistributionPolicy.php` | Hapus atau ganti referensi model | Bug 3 |
| `Controllers/API/Distribution/DeliveryScheduleController.php` | Fix `availableCouriers()` query | Bug 6 |
| `Http/Resources/Distribution/*.php` | Pindahkan file ke subfolder Distribution/ | Bug 5 |
| `Models/DeliverySchedule.php` | Tambah konstanta `STATUS_SUBMITTED` | Bug 2 |
