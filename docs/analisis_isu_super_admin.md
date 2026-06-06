# 🔍 Analisis Isu & Flow Janggal — Modul Super Admin (COMS MBG)

> **Tanggal Analisis:** 3 Juni 2026  
> **Cakupan:** Semua file terkait `super_admin` — Controllers, Services, Models, Middleware, Migrations, Requests, Resources

---

## 📊 Ringkasan Eksekutif

| Kategori | Jumlah Isu |
|---|---|
| 🔴 **Bug Kritis** (langsung menyebabkan error/crash) | 5 |
| 🟠 **Bug Fungsional** (logika salah, data corrupt) | 4 |
| 🟡 **Celah Keamanan / Akses** | 3 |
| 🔵 **Controller Stub Kosong** (endpoint tidak berfungsi) | 3 |
| 🟤 **Inkonsistensi Arsitektur** | 5 |
| ⬜ **Peringatan Minor** | 4 |
| **TOTAL** | **24 isu** |

---

## 🔴 BUG KRITIS — Langsung menyebabkan error runtime

### [BUG-01] Typo Field `$sppg->kapasitas` di SPPGCapacityService
**File:** [`SPPGCapacityService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGCapacityService.php)  
**Baris:** 11, 17, 18, 31, 33

```php
// ❌ SALAH — kolom tidak ada di model/migration
return $sppg->schools()->count() >= $sppg->kapasitas;
$max = $sppg->kapasitas;
->filter(fn($s) => $s->schools_count >= $s->kapasitas)
```

```php
// ✅ BENAR — sesuai migration dan fillable
return $sppg->schools()->count() >= $sppg->capacity;
$max = $sppg->capacity;
->filter(fn($s) => $s->schools_count >= $s->capacity)
```

**Dampak:** Setiap call ke `getCapacityStatus()`, `isOvercapacity()`, dan `getOvercapacitySppgs()` akan **throw `undefined property` error**, menyebabkan endpoint `/api/super-admin/sppg/{id}` (show) dan `/api/super-admin/sppg/capacity-overview` crash total.

---

### [BUG-02] Filter Pencarian Menggunakan `city` tapi Route Kirim `kota`
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 23  
**File:** [`SPPGService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGService.php) baris 20–21

```php
// Controller — request parameter: 'kota'
$filters = $request->only(['status', 'kota', 'search']);

// Service — mencari key 'city' yang TIDAK ADA di $filters
if (!empty($filters['city'])) {
    $query->where('city', 'ilike', "%{$filters['city']}%");
}
```

**Dampak:** Filter pencarian berdasarkan kota **tidak pernah berfungsi** karena `$filters['city']` selalu kosong. Request `?kota=Bandung` diabaikan sepenuhnya.

---

### [BUG-03] `SPPGCapacityService` Memfilter Sekolah Berdasarkan `status = 'aktif'` padahal Enum Status Sekolah Tidak Terdefinisi
**File:** [`SPPGCapacityService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGCapacityService.php) baris 16, 31  
**File:** [`School.php` migration](file:///c:/Users/naufa/COMS_MBG/database/migrations/2026_04_28_191347_create_schools_table.php)

```php
// SPPGCapacityService.php
$current = $sppg->schools()->where('status', 'aktif')->count();

->withCount(['schools' => fn($q) => $q->where('status', 'aktif')])
```

**Masalah:** Migration `schools` tidak mendefinisikan enum/nilai valid untuk kolom `status`. Model `School.php` tidak memiliki cast untuk `status`. Nilai `'aktif'` belum tentu yang dipakai di sistem (bisa saja `'active'`). **Kapasitas selalu dilaporkan 0** karena tidak ada sekolah dengan status `'aktif'` (Bahasa Indonesia).

---

### [BUG-04] `SppgRegistrationService` Memanggil Relasi yang Tidak Eksis: `sppg`
**File:** [`SppgRegistrationService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SppgRegistrationService.php) baris 172

```php
// ❌ Relasi 'sppg' tidak ada di Model SPPG — ini bukan relasi yang valid
return $sppg->load(['owner', 'sppg' => function($q) {}]);
```

**Dampak:** Setiap kali SPPG baru berhasil didaftarkan (via `store()` atau `submit()`), response akan **crash dengan RelationNotFoundException** sehingga transaksi DB tidak di-rollback dengan bersih (data sudah tersimpan tapi response error 500 dikirim ke klien).

---

### [BUG-05] `SchoolController` Super Admin Menggunakan `sppgId()` yang Mengembalikan `null`
**File:** [`SchoolController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SchoolController.php) baris 18–20

```php
private function sppgId(): string
{
    return auth('api')->user()->sppg_id; // Super Admin tidak punya sppg_id — SELALU NULL
}
```

**Dampak:**
- `index()` memanggil `getAll($filters, 15, null)` — filter `sppg_id` jadi `null`
- `store()` memaksa `data['sppg_id'] = null` — sekolah tersimpan tanpa SPPG
- `show()` / `update()` / `destroy()` mencari sekolah dengan `sppg_id = null`

Seluruh operasi CRUD sekolah di Super Admin **menggunakan logika yang dirancang untuk Admin SPPG**, bukan Super Admin yang seharusnya bisa akses semua sekolah lintas SPPG.

---

## 🟠 BUG FUNGSIONAL — Logika salah, data bisa corrupt

### [BUG-06] Double Route untuk Registrasi SPPG — Dua Request Class Berbeda
**File:** [`api_superadmin.php`](file:///c:/Users/naufa/COMS_MBG/routes/api_superadmin.php) baris 30  
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 39

Terdapat **dua cara** mendaftarkan SPPG yang beroperasi secara paralel:
1. `POST /api/super-admin/sppg` → `SPPGController@store` dengan `RegisterSppgRequest` + `SppgRegistrationService`
2. `POST /api/super-admin/sppg-submissions/{id}/submit` → `SppgSubmissionController@submit` + `SppgRegistrationService`

Keduanya memanggil `SppgRegistrationService@register()` tapi dengan struktur data yang **berbeda**:
- Direct `store()`: data dikirim flat sebagai `{sppg: {...}, partners: [...], admin_sppg: {...}}`
- Submission `submit()`: data diambil dari JSON di kolom `form1_data`, `form2_data`, `form3_data`

**Akibat:** Tidak ada standar alur pendaftaran yang jelas. Frontend bisa bingung mana yang harus dipakai.

---

### [BUG-07] `menus()` di SPPGController Mengabaikan Parameter `$id` SPPG
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 153–159

```php
public function menus(string $id): JsonResponse
{
    // ❌ Mengambil SEMUA menu, tidak difilter per SPPG!
    $menus = Menu::latest('week_start')->get();
```

**Dampak:** Endpoint `GET /api/super-admin/sppg/{id}/menus` mengembalikan **seluruh menu dari semua SPPG**, bukan menu milik SPPG dengan `$id` yang diminta.

---

### [BUG-08] Ahli Gizi dan Admin Logistik Mendapat Password yang Salah
**File:** [`SppgRegistrationService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SppgRegistrationService.php) baris 89, 122

```php
// Ahli Gizi dibuat dengan password dari admin_sppg, bukan dari data ahli_gizi sendiri
'password' => $adminData['password'],

// Admin Logistik juga dibuat dengan password dari admin_sppg
'password' => $adminData['password'],
```

**Dampak:** Ahli Gizi dan Admin Logistik mendapatkan password yang sama dengan Admin SPPG, bukan password yang diminta di form. Email notifikasi juga mengirimkan password yang salah. Jika di masa depan `ahli_gizi.password` wajib diisi, logika ini akan mengabaikannya.

---

### [BUG-09] `destroy()` SPPG Mendetach Partner tapi Tidak Mendetach School
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 74–91

```php
// ✅ Partner di-detach (sppg_id -> null)
Partner::where('sppg_id', $id)->update(['sppg_id' => null]);

// ❌ School tidak di-detach
// SPPGService::delete() memanggil $sppg->schools()->update(['sppg_id' => null])
// tapi SPPGController::destroy() tidak memanggil SPPGService::delete()
// melainkan langsung memanggil $sppg->delete()
```

**Masalah:** `SPPGController@destroy` melakukan soft-delete langsung tanpa memanggil `SPPGService::delete()`, sehingga sekolah-sekolah yang terikat ke SPPG ini **tidak dilepas** (sppg_id tetap terisi). Terjadi data orphan.

---

## 🟡 CELAH KEAMANAN / AKSES

### [SEC-01] `RegisterSppgRequest` Menggunakan Logika Otorisasi Redundan
**File:** [`RegisterSppgRequest.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Requests/SPPG/RegisterSppgRequest.php) baris 11

```php
public function authorize(): bool
{
    return $this->user()?->role_type === 'super_admin';
}
```

Route sudah dilindungi `middleware('role:super_admin')`, yang artinya request yang sampai ke controller **sudah pasti** dari super_admin. Pengecekan ini redundan namun sebenarnya tidak berbahaya.

**Bahaya nyata:** `StoreSPPGRequest` (untuk direct store) menggunakan `hasPermission('sppg.create')` — permission ini **tidak ada** di DefaultRolePermissionSeeder untuk super_admin. Super admin bypass RBAC via `isSuperAdmin()`, tapi `hasPermission()` di user model sudah menghandle ini. Ini masih aman, tapi inkonsisten.

---

### [SEC-02] `SppgDraft::store()` Tidak Memvalidasi Kepemilikan Draft saat Update
**File:** [`SppgSubmissionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGSubmissionController.php) baris 118–155

```php
public function update(Request $request, string $id): JsonResponse
{
    // ❌ Tidak dicek apakah draft ini milik user yang sedang login
    $draft = SppgDraft::findOrFail($id);
    $draft->update($data);
```

**Dampak:** Super Admin A bisa mengupdate draft yang dibuat oleh Super Admin B. Meskipun saat ini semua Super Admin memiliki akses penuh, ini melanggar prinsip audit trail (siapa yang terakhir mengubah tidak bisa dilacak dengan benar).

---

### [SEC-03] `validatePoint` dan `suggestShift` Tidak Membatasi Jumlah Partner dalam Request
**File:** [`MapController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/MapController.php) baris 150–212

```php
$request->validate([
    'partners' => 'nullable|array',  // Tidak ada batas max!
```

**Dampak:** Klien bisa mengirim array `partners` dengan ribuan entri, memicu loop OSRM HTTP calls untuk setiap partner → **potential DoS** pada sistem OSRM dan memory exhaustion.

---

## 🔵 CONTROLLER STUB KOSONG — Endpoint terdaftar tapi tidak berfungsi

### [STUB-01] `FinancialReportController` — Semua Method Kosong
**File:** [`FinancialReportController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/FinancialReportController.php)

Route `Route::apiResource('financial-reports', FinancialReportController::class)` terdaftar dan bisa diakses, tapi **semua 5 method** (`index`, `store`, `show`, `update`, `destroy`) hanya berisi `//` comment. Request ke endpoint ini akan mengembalikan HTTP 200 dengan body **null/kosong**.

---

### [STUB-02] `MonitoringMapController` — Semua Method Kosong
**File:** [`MonitoringMapController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/MonitoringMapController.php)

Controller dibuat tapi **tidak ada route yang mendaftarkannya** di `api_superadmin.php`. Kelas ini adalah dead code.

---

### [STUB-03] `RecommendationController` — Semua Method Kosong
**File:** [`RecommendationController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/RecommendationController.php)

Sama dengan `MonitoringMapController` — controller dibuat tapi tidak ada route yang mengarah ke sini. Dead code.

---

## 🟤 INKONSISTENSI ARSITEKTUR

### [ARCH-01] Dua Entitas Berbeda untuk "Sekolah Mitra": `School` vs `Partner`
**File:** [`School.php`](file:///c:/Users/naufa/COMS_MBG/app/Models/School.php) dan [`Partner.php`](file:///c:/Users/naufa/COMS_MBG/app/Models/Partner.php)

Sistem memiliki **dua model berbeda** untuk konsep yang sangat mirip:
- `School` (tabel `schools`): Menggunakan kolom Bahasa Indonesia (`nama`, `alamat`, `kecamatan`, `kota`, `jenjang`)
- `Partner` (tabel `partners`): Menggunakan kolom Bahasa Inggris (`school_name`, `address`, `district`, `city`, `school_type`)

`SPPGService::assignSchool()` bekerja dengan `School`, sementara `SppgRegistrationService` dan `MapService` bekerja dengan `Partner`. Keduanya merepresentasikan hal yang sama (sekolah penerima MBG) namun disimpan di tabel terpisah.

**Dampak:**
- Super Admin harus paham perbedaan `School` vs `Partner` (tidak intuitif)
- Laporan kapasitas `SPPGCapacityService` menghitung dari `School`, sementara dashboard menghitung dari `Partner` → **angka kapasitas tidak konsisten**
- `SPPG::schools()` relation mengarah ke `School`, bukan `Partner`

---

### [ARCH-02] `DefaultRolePermissionSeeder` Tidak Menyertakan Role `Kurir` dan `Manajer`
**File:** [`DefaultRolePermissionSeeder.php`](file:///c:/Users/naufa/COMS_MBG/database/seeders/DefaultRolePermissionSeeder.php)

Seeder hanya membuat 3 role: `Admin SPPG`, `Ahli Gizi`, `Admin Logistik`. Namun dokumentasi sistem menyebut `Kurir` dan `Manajer` sebagai aktor dengan akses berbeda.

**Dampak:** Saat SPPG baru didaftarkan, role `kurir` dan `manajer` **tidak otomatis dibuat**. Admin SPPG harus membuat role ini manual, atau karyawan Kurir tidak bisa login dengan permission yang benar.

---

### [ARCH-03] `SPPGController@destroy` Bypass `SPPGService::delete()`
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 72–91

Controller langsung mengoperasikan `SPPG::findOrFail()` dan `$sppg->delete()` alih-alih memanggil `$this->sppgService->delete()`. Ini memecah konsistensi service layer dan menyebabkan sekolah tidak dilepas (lihat BUG-09).

---

### [ARCH-04] Status SPPG `'deleted'` Tidak Ada di Enum Migration
**File:** [`SPPGController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGController.php) baris 76  
**File:** [Migration SPPG](file:///c:/Users/naufa/COMS_MBG/database/migrations/2026_04_28_191337_create_s_p_p_g_s_table.php) baris 23

```php
// Controller — menetapkan status 'deleted'
$sppg->status = 'deleted';
$sppg->save(); // ❌ Akan ERROR di database karena enum hanya: active, inactive, pending

// Migration — enum hanya mendefinisikan 3 nilai
$table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
```

**Dampak:** Jika database menggunakan MySQL/PostgreSQL dengan tipe ENUM strict, `$sppg->save()` akan **throw database constraint exception**. Di SQLite (development), ini mungkin lolos tapi di production akan crash.

---

### [ARCH-05] `ScopeBySppg` Middleware Menggunakan `(int) $sppgId` padahal SPPG ID adalah BigInteger (bukan UUID)
**File:** [`ScopeBySppg.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Middleware/ScopeBySppg.php) baris 39

```php
if ($sppgId && !$user->ownsSppg((int) $sppgId)) {
```

**File:** [`User.php`](file:///c:/Users/naufa/COMS_MBG/app/Models/User.php) baris 121–126

```php
public function ownsSppg(int $sppgId): bool
{
    if ($this->isSuperAdmin()) return true;
    return (int) $this->sppg_id === $sppgId;
}
```

Saat ini aman karena `sppg_id` memang integer. **Tapi** parameter route yang diambil adalah `sppg` atau `sppg_id`, bukan `sppgId` — route Super Admin menggunakan `{sppgId}` (lihat `api_superadmin.php` baris 45-50). `$request->route('sppg')` dan `$request->route('sppg_id')` akan **return null** untuk rute super admin yang menggunakan `{sppgId}`, menyebabkan middleware tidak memblokir siapapun.

---

## ⬜ PERINGATAN MINOR

### [WARN-01] `getKMeansRecommendations` Menggunakan Shuffle Acak — Hasil Tidak Deterministic
**File:** [`MapService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SuperAdmin/MapService.php) baris 203

Setiap kali dipanggil, K-Means dimulai dari centroid acak. Dua request berurutan bisa memberikan rekomendasi yang berbeda sama sekali. Idealnya gunakan seed tetap atau algoritma K-Means++ untuk inisialisasi yang lebih stabil.

---

### [WARN-02] `ilike` Operator Tidak Didukung SQLite (Development)
**File:** [`SPPGService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGService.php) baris 22, 24

```php
$query->where('city', 'ilike', "%{$filters['city']}%");
$query->where('name', 'ilike', "%{$filters['search']}%");
```

`ilike` adalah operator PostgreSQL. **Di SQLite (environment development), query akan gagal**. Gunakan `LIKE` + `lower()` atau cek koneksi DB sebelum memilih operator.

---

### [WARN-03] Tidak Ada Validasi `latitude`/`longitude` SPPG Draft sebelum `submit()`
**File:** [`SppgSubmissionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SPPGSubmissionController.php) baris 187–199

Submit hanya mengecek `form1_data` dan `form2_data` tidak kosong, serta minimal 1 partner. Namun tidak memvalidasi apakah koordinat SPPG sudah dikonfirmasi (`map_confirmed = true`). SPPG bisa didaftarkan tanpa titik lokasi yang valid di peta.

---

### [WARN-04] `OSRM_BASE_URL` Menggunakan Server Publik di Production
**File:** [`MapService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SuperAdmin/MapService.php) baris 35

```php
$baseUrl = env('OSRM_BASE_URL', 'http://router.project-osrm.org');
```

Default ke server publik OSRM yang memiliki rate-limiting. Di production dengan load tinggi, ini akan gagal secara sering. Seharusnya self-hosted atau menggunakan layanan berbayar.

---

## 🗺️ Peta Dampak Isu

```
Endpoint                              | Isu yang Mempengaruhi
--------------------------------------|------------------------------------------
GET /super-admin/dashboard            | ARCH-01 (angka tidak konsisten)
GET /super-admin/sppg                 | BUG-02 (filter kota tidak bekerja), WARN-02
POST /super-admin/sppg                | BUG-06 (dual route), BUG-04 (crash saat return)
GET /super-admin/sppg/{id}            | BUG-01 (crash kapasitas), BUG-04 (crash return)
PUT /super-admin/sppg/{id}            | (relatif aman)
DELETE /super-admin/sppg/{id}         | BUG-09 (school orphan), ARCH-04 (status invalid)
POST /super-admin/sppg/{id}/deactivate| ARCH-04 (tidak ada, tapi deactivate aman)
GET /super-admin/sppg/{id}/menus      | BUG-07 (semua menu ditampilkan)
GET /super-admin/sppg/{id}/partners   | (relatif aman)
GET /super-admin/sppg/capacity-overview| BUG-01 (crash kapasitas), BUG-03 (nilai 0)
GET /super-admin/sppg/{id}/employees  | (relatif aman)
GET /super-admin/schools              | BUG-05 (filter null), ARCH-01
POST /super-admin/schools             | BUG-05 (sppg_id null)
GET /super-admin/sppg-submissions     | (relatif aman)
POST /super-admin/sppg-submissions    | (relatif aman)
POST /super-admin/sppg-submissions/{id}/submit | BUG-08 (password salah), BUG-04, WARN-03
GET /super-admin/map/data             | WARN-01, WARN-04
POST /super-admin/map/validate-point  | SEC-03 (no array size limit)
GET /super-admin/financial-reports    | STUB-01 (kosong)
```

---

## 📋 Prioritas Perbaikan

### Perbaiki Sekarang (Blocking/Critical)
1. **[BUG-01]** Ganti semua `$sppg->kapasitas` → `$sppg->capacity`
2. **[BUG-04]** Perbaiki `load(['owner', 'sppg' => ...])` → `load('owner')`
3. **[BUG-05]** Refactor `SchoolController` Super Admin agar tidak terikat sppg_id
4. **[ARCH-04]** Hapus `$sppg->status = 'deleted'` atau tambahkan `'deleted'` ke migration enum

### Segera (Sprint Berikutnya)
5. **[BUG-02]** Sesuaikan key filter `kota` ↔ `city`
6. **[BUG-03]** Sesuaikan status sekolah (`'aktif'` vs `'active'`)
7. **[BUG-07]** Filter `menus()` berdasarkan `sppg_id`
8. **[BUG-08]** Perbaiki password ahli_gizi dan admin_logistik
9. **[BUG-09]** Panggil `$sppgService->delete()` dari controller destroy
10. **[ARCH-02]** Tambahkan role `Kurir` dan `Manajer` di DefaultRolePermissionSeeder

### Perencanaan (Refactoring)
11. **[ARCH-01]** Tentukan satu entitas canonical untuk sekolah mitra
12. **[WARN-02]** Ganti `ilike` dengan `like` yang kompatibel SQLite/MySQL
13. **[WARN-03]** Tambahkan validasi `map_confirmed` sebelum submit
14. **[SEC-03]** Tambahkan `max:100` pada validasi array partners

---

*Laporan ini dihasilkan dari analisis statis kode sumber. Urutan prioritas dapat disesuaikan dengan kebutuhan tim.*
