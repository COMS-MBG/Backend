# 🔍 Analisis Isu & Flow Janggal — Modul Admin SPPG (COMS MBG)

> **Tanggal Analisis:** 4 Juni 2026  
> **Cakupan:** Routes, Controllers, Services, Models, Requests — seluruh file namespace `AdminSPPG`

---

## 📊 Ringkasan Eksekutif

| Kategori | Jumlah |
|---|---|
| 🔴 **Bug Kritis** (crash / error runtime) | 6 |
| 🟠 **Bug Fungsional** (logika salah, data bisa bocor) | 7 |
| 🟡 **Celah Keamanan / Akses** | 5 |
| 🔵 **Controller Stub Kosong** | 1 |
| 🟤 **Inkonsistensi Arsitektur** | 6 |
| ⬜ **Peringatan Minor** | 4 |
| **TOTAL** | **29 isu** |

---

## 🔴 BUG KRITIS — Menyebabkan error/crash langsung

### [BUG-A01] `EmployeeController@store` — Fallback `sppg_id = 1` Sangat Berbahaya
**File:** [`EmployeeController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/EmployeeController.php) baris 47–49

```php
// ❌ Jika user tidak punya sppg_id, fallback ke integer 1
$data['sppg_id'] = $request->user()->sppg_id
    ?? $request->user()->employee?->sppg_id
    ?? 1;  // <-- HARDCODED ID! Sangat berbahaya
```

**Dampak:**
- Jika user login memiliki masalah relasi (`sppg_id = null`), karyawan akan dibuat di SPPG dengan ID `1` — bisa jadi SPPG milik entitas lain
- Data silang SPPG → pelanggaran isolasi data tenant
- Seharusnya `abort(403)` atau throw exception jika `sppg_id` tidak ditemukan

**Perbaikan:**
```php
$sppgId = $request->user()->sppg_id 
    ?? $request->user()->employee?->sppg_id;
abort_if(!$sppgId, 403, 'User tidak terikat ke SPPG manapun.');
$data['sppg_id'] = $sppgId;
```

---

### [BUG-A02] `EmployeeController@index` — Tidak Filter berdasarkan SPPG Login
**File:** [`EmployeeController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/EmployeeController.php) baris 18

```php
// ❌ Mengambil SEMUA employee dari semua SPPG tanpa filter
$query = Employee::with('role')->latest();
```

**Dampak:** Admin SPPG A dapat melihat seluruh karyawan dari SPPG B, C, dst. Ini **pelanggaran privasi data tenant** yang serius. Berbeda dengan `EmployeeService::getAll()` yang memfilter per `sppg_id`, controller ini langsung query Model tanpa scope.

**Perbaikan:**
```php
$sppgId = $request->user()->sppg_id ?? abort(403);
$query = Employee::with('role')->where('sppg_id', $sppgId)->latest();
```

---

### [BUG-A03] `EmployeeController@show` — Tidak Memverifikasi Kepemilikan SPPG
**File:** [`EmployeeController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/EmployeeController.php) baris 35–39

```php
public function show(Employee $employee)
{
    // ❌ Route model binding langsung tanpa cek sppg_id
    return response()->json($employee->load('role.permissions', 'user', 'sppg'));
}
```

**Dampak:** Admin SPPG A bisa memanggil `/api/admin-sppg/employees/{id_employee_milik_SPPG_B}` dan mendapatkan data employee SPPG lain beserta data user, role, dan permissions-nya.

---

### [BUG-A04] `RoleController@store` — Tidak Menetapkan `sppg_id` pada Role Baru
**File:** [`RoleController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/RoleController.php) baris 35–38

```php
// ❌ Role dibuat tanpa sppg_id → jadi role global!
$role = Role::create([
    'name'        => $request->name,
    'description' => $request->description,
    // sppg_id TIDAK DISET!
]);
```

**Dampak:**
- Role yang dibuat oleh Admin SPPG A akan menjadi role global (`sppg_id = null`)
- Ketika Admin SPPG B membuka daftar role, role milik SPPG A akan ikut muncul
- Karyawan SPPG B bisa di-assign ke role milik SPPG A → *cross-tenant access*

---

### [BUG-A05] `RoleController@index` / `showAssignRole` — Menampilkan Semua Role Semua SPPG
**File:** [`RoleController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/RoleController.php) baris 15  
**File:** [`EmployeeController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/EmployeeController.php) baris 83

```php
// RoleController@index — tampilkan SEMUA role dari semua SPPG
$roles = Role::with('permissions')->withCount('employees')->latest()->paginate(10);

// showAssignRole — tampilkan SEMUA role untuk dropdown assign
'roles' => Role::orderBy('name', 'asc')->get(),
```

**Dampak:** Admin SPPG A bisa melihat dan assign karyawannya dengan role yang dibuat SPPG B. Dropdown assign-role menampilkan ratusan role dari semua SPPG.

---

### [BUG-A06] `MenuService::saveMenuItems` — Field `meal_time` Selalu `null` saat Update
**File:** [`MenuService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) baris 226

```php
// create() menggunakan item['meal_time'] dengan benar, namun...
// saveMenuItems() (dipanggil di update) hardcode NULL!
private function saveMenuItems(Menu $menu, array $items): void
{
    foreach ($items as $item) {
        MenuItem::create([
            'menu_id'     => $menu->id,
            'recipe_id'   => $item['recipe_id'],
            'day_of_week' => $item['day_of_week'],
            'menu_date'   => $item['menu_date'],
            'meal_time'   => null,   // ❌ Hardcoded null!
            'order'       => $item['order'] ?? 0,
        ]);
    }
}
```

**Dampak:** Setiap kali menu diupdate (PUT), semua `meal_time` pada MenuItem **akan di-reset ke null**. Data waktu makan (breakfast/lunch/dinner) hilang setelah update.

**Perbaikan:**
```php
'meal_time' => $item['meal_time'] ?? null,
```

---

## 🟠 BUG FUNGSIONAL — Logika salah / data bisa bocor

### [BUG-A07] `DashboardController` — Statistik Tidak Di-scope ke SPPG Login
**File:** [`DashboardController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) baris 27–50

```php
// ❌ DeliverySchedule tidak difilter per sppg_id
$scheduleStats = [
    'in_order'    => DeliverySchedule::where('status', 'in_order')->count(),
    'delivering'  => DeliverySchedule::where('status', 'delivering')->count(),
    // ...
];

// ❌ DeliveryHistory tidak difilter per sppg_id
$historyThisMonth = DeliveryHistory::whereBetween('departed_at', [...]);
```

**Dampak:** Dashboard Admin SPPG menampilkan angka **global seluruh sistem**, bukan statistik SPPG-nya sendiri. Setiap Admin SPPG melihat dashboard yang sama persis.

---

### [BUG-A08] `DashboardController` — `total_couriers` Menggunakan Filter `status = 'active'` yang Tidak Ada di Schema
**File:** [`DashboardController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) baris 67–69

```php
'total_couriers' => Employee::where('position', 'kurir')
    ->orWhereHas('role', fn($q) => $q->where('slug', 'kurir'))
    ->where('status', 'active')  // ❌ 'status' bukan kolom di tabel employees!
    ->count(),
```

**Dampak:** Query ini akan mengabaikan kondisi `OR` dan menjadi **query salah** karena `where()` setelah `orWhereHas()` tidak dibungkus dalam closure. Ini akan menghasilkan hitungan kurir yang salah dan bisa throw SQL error jika kolom `status` tidak ada.

**Perbaikan:**
```php
'total_couriers' => Employee::where('sppg_id', $sppgId)
    ->where(function($q) {
        $q->where('position', 'kurir')
          ->orWhereHas('role', fn($q2) => $q2->where('slug', 'kurir'));
    })->count(),
```

---

### [BUG-A09] `MenuController@publish` — Tidak Memvalidasi Status Menu Sebelum Publish
**File:** [`MenuController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/MenuController.php) baris 210–216

```php
$menu = Menu::findOrFail($id);

// Deduct stock...
$stockService->deductStockForMenu($sppgId, $id, $userId);

// ❌ Update status tanpa cek apakah menu sudah published/archived
$menu->update(['status' => 'published']);
```

**Dampak:** Menu yang sudah `published` atau `archived` bisa di-publish ulang, yang akan **memotong stok dua kali** untuk menu yang sama. Ini adalah bug data integrity serius pada sistem stok.

**Perbaikan:** Tambahkan validasi:
```php
if (in_array($menu->status, ['published', 'archived'])) {
    return response()->json(['success' => false, 'message' => 'Menu sudah dipublikasikan sebelumnya.'], 422);
}
```

---

### [BUG-A10] `StockService::getSummary` — Memuat Semua Ingredient, Termasuk yang Tidak Dimiliki SPPG
**File:** [`StockService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/Stock/StockService.php) baris 21

```php
// ❌ Memuat SEMUA ingredient yang ada di sistem
$ingredients = Ingredient::all();
```

**Dampak:** Jika ada 500 bahan baku di sistem (dari semua SPPG), dashboard stok akan menampilkan 500 baris, di mana mungkin hanya 30 yang relevan untuk SPPG ini. Performa buruk dan UX membingungkan.

---

### [BUG-A11] `DistributionController@index` — Menggunakan `hasAnyRole(['courier'])` yang Tidak Sesuai Slug
**File:** [`DistributionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) baris 49

```php
if ($request->user()->hasAnyRole(['courier'])) {
```

Berdasarkan `DefaultRolePermissionSeeder`, slug role kurir adalah `kurir` (Bahasa Indonesia), bukan `courier`. `hasAnyRole()` membandingkan dengan slug. **Filter kurir tidak pernah aktif** karena slug tidak cocok.

---

### [BUG-A12] `StockController@approve` — Duplikat Query `quantity_before` Selalu 0
**File:** [`StockController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/StockController.php) baris 275

```php
StockTransaction::create([
    // ...
    'quantity_before' => 0.0,  // ❌ Hardcoded 0, tidak ambil stok aktual sebelumnya
    'quantity_after'  => $stockItem->quantity,
]);
```

**Dampak:** Riwayat transaksi stok approval tidak akurat — `quantity_before` seharusnya diambil dari total stok bahan sebelum penambahan batch ini, bukan hardcode 0.

---

### [BUG-A13] `SchoolController` (Admin SPPG) — Tidak Di-scope ke SPPG Login
**File:** [`SchoolController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/SchoolController.php) baris 37

```php
// ❌ Tidak difilter per sppg_id
$query = School::query()->latest();
```

Admin SPPG A bisa melihat dan memodifikasi sekolah milik SPPG B. `show()` dan `update()` menggunakan route model binding tanpa scope SPPG.

---

## 🟡 CELAH KEAMANAN / AKSES

### [SEC-A01] Duplikasi Middleware — `HasMiddleware` vs Route-Level Middleware Redundan
**File:** [`MenuController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/MenuController.php) baris 23–31  
**File:** [`routes/api_adminsppg.php`](file:///c:/Users/naufa/COMS_MBG/routes/api_adminsppg.php) baris 109–124

```php
// Di controller (HasMiddleware):
new Middleware('permission:menus.read', only: ['index', 'show', 'showGrouped']),

// Di routes (route-level middleware):
Route::post('menus/refresh-statuses', ...)->middleware('permission:menus.update');
Route::get('menus/{id}/grouped', ...)->middleware('permission:menus.read');
Route::patch('menus/{id}/publish', ...)->middleware('permission:menus.update');
```

**Masalah:** Permission check untuk `showGrouped`, `publish`, dan `refreshStatuses` didefinisikan **dua kali** — sekali di `HasMiddleware` (controller) dan sekali di route. Ini menyebabkan `CheckPermission` dieksekusi dua kali untuk setiap request. Sama juga untuk `IngredientController` dan `RecipeController`. Performa redundan + potensi konflik jika kedua check berbeda scope.

---

### [SEC-A02] `CourierTrackingController@updateLocation` — Role Check Manual Melewati Middleware
**File:** [`CourierTrackingController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/CourierTrackingController.php) baris 43–47

```php
abort_unless(
    $request->user()->hasAnyRole(['courier', 'super_admin']),
    403, 'Only couriers can update location.'
);
```

Route ini dilindungi `permission:distribution.update`, yang berarti semua user dengan permission ini bisa mengakses endpoint. Tapi di dalam controller, ada pengecekan role manual yang berbeda. Inkonsistensi antara middleware dan logika dalam controller menciptakan gap keamanan: siapa yang benar-benar bisa akses?

---

### [SEC-A03] `DistributionMapController@index` — Role Check Manual yang Tidak Konsisten dengan Middleware
**File:** [`DistributionMapController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionMapController.php) baris 34–36

```php
abort_unless(
    $request->user()->hasAnyRole(['admin_logistik', 'admin_sppg', 'super_admin']),
    403
);
```

Route sudah punya `permission:distribution.read`. Pengecekan role manual di dalam controller membuat layer keamanan tidak konsisten. Jika seseorang punya `distribution.read` permission tapi bukan role yang terdaftar di sini, mereka akan diblokir meskipun seharusnya boleh akses.

---

### [SEC-A04] `RoleController@store` — Tidak Ada Validasi bahwa Role Baru Harus Unik per SPPG
**File:** [`RoleController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/RoleController.php) baris 28–30

```php
$request->validate([
    'name' => 'required|string|max:255|unique:roles,name',
    // ❌ unique:roles,name validasi GLOBAL — nama role tidak boleh sama di seluruh sistem!
]);
```

**Masalah:** Jika SPPG A sudah punya role bernama "Manajer Gudang", SPPG B tidak bisa membuat role dengan nama yang sama meski ini sangat wajar. Validasi uniqueness seharusnya scoped ke `sppg_id`: `unique:roles,name,NULL,id,sppg_id,{$sppgId}`.

---

### [SEC-A05] `RecipeRequest` — Validasi Kalori Hard-block Range 2000-2700 Membatasi Fleksibilitas
**File:** [`RecipeRequest.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Requests/Nutrition/RecipeRequest.php) baris 78–80

```php
if ($totalCalorie < 2000 || $totalCalorie > 2700) {
    $validator->errors()->add('ingredients', 'Total kalori belum memenuhi target...');
}
```

**Masalah:** Validasi ini memblokir **semua resep** yang kalorinya di luar range 2000-2700. Resep untuk snack, breakfast, atau dessert yang inherently rendah kalori **tidak bisa dibuat sama sekali**. Ini adalah logika bisnis yang over-restrictive dan seharusnya menjadi warning/notifikasi, bukan hard block.

---

## 🔵 CONTROLLER STUB KOSONG

### [STUB-A01] `FinancialReportController` (Admin SPPG) — Semua Method Kosong
**File:** [`FinancialReportController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/FinancialReportController.php)

Route `financial-reports` terdaftar lengkap dengan CRUD dan permission middleware, tapi **semua 5 method** hanya berisi `//`. Semua request ke laporan keuangan akan return `null` response.

**Catatan Kritis:** Middleware di controller menggunakan `permission:finance.read` tapi route file di baris 179 menggunakan `permission:report.read`. **Dua permission berbeda untuk endpoint yang sama** — kemungkinan akan selalu menjadi 403 atau salah satu middleware tidak aktif.

---

## 🟤 INKONSISTENSI ARSITEKTUR

### [ARCH-A01] Dua Pattern Controller yang Berbeda — Langsung vs via Service
**File:** [`EmployeeController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/EmployeeController.php) vs [`PartnerController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/PartnerController.php)

- `EmployeeController` langsung query `Employee::with('role')->latest()` tanpa service
- `PartnerController` mendelegasi ke `PartnerService`
- `IngredientController`, `RecipeController`, `MenuController` menggunakan service
- `RoleController`, `SchoolController` langsung query model

Tidak ada standar yang konsisten. Setengah controller bypass service layer, menyulitkan testing dan maintenance.

---

### [ARCH-A02] `MenuItem` Tidak Punya Field `meal_time` di `fillable`
**File:** [`MenuItem.php`](file:///c:/Users/naufa/COMS_MBG/app/Models/MenuItem.php) baris 18–24

```php
protected $fillable = [
    'menu_id', 'recipe_id', 'day_of_week', 'menu_date', 'order',
    // ❌ 'meal_time' tidak ada di fillable!
];
```

Tapi `MenuController@publish` di baris 196 mengakses `$item->meal_time_label`, dan `MenuService::create()` baris 108 **tidak memasukkan `meal_time`** ke `create()` meski `MenuController` komentar di baris 111 menyebut `"meal_time": "lunch"`. Field `meal_time` hilang di seluruh alur create/update.

---

### [ARCH-A03] `Menu::computeStatus` — Status `'published'` Bisa Meng-override Status Manual
**File:** [`MenuService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) baris 124, 234

Setiap kali `findById()` atau `update()` dipanggil, status menu **selalu di-recompute** berdasarkan tanggal. Artinya jika Admin SPPG sudah publish menu (status = `published`) tapi hari ini belum waktunya (H-7 hingga H-13), status akan **otomatis kembali ke `scheduled`** ketika menu dibuka kembali di `findById()`.

Ini juga berarti `MenuController@publish` yang sudah men-set status ke `published` akan dikembalikan ke status berdasarkan tanggal saat user membuka detail menu berikutnya.

---

### [ARCH-A04] `DistributionController@update` dan `destroy` — Mengembalikan 403 meski Tidak Seharusnya Diakses
**File:** [`DistributionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) baris 114–131

```php
public function update(...): JsonResponse
{
    return response()->json(['success' => false, 'message' => '...'], 403);
}

public function destroy(...): JsonResponse
{
    return response()->json(['success' => false, 'message' => '...'], 403);
}
```

**Masalah Ganda:**
1. Method ini memiliki `HasMiddleware` yang mendaftarkan `permission:distribution.update` dan `permission:distribution.delete`. Jika user tidak punya permission ini, middleware akan memblokir terlebih dahulu dengan HTTP 403. Jika user punya permission tapi memanggil endpoint ini, baru masuk ke controller — yang juga return 403. **Double-check yang tidak perlu.**
2. Lebih baik hapus method ini dan jangan daftarkan route `update` dan `destroy` sama sekali jika memang tidak diizinkan.

---

### [ARCH-A05] `StockService::convertGramsToUnit` — Konversi `liter` Dianggap Sama dengan `kg`
**File:** [`StockService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/Stock/StockService.php) baris 277–281

```php
if ($unit === 'kg' || $unit === 'liter') {
    return $grams / 1000.0;
}
```

**Masalah Serius:** Konversi ini mengasumsikan `1 liter = 1000 gram` (densitas air). Ini valid untuk air dan beberapa cairan, tapi **salah untuk bahan seperti minyak, susu, santan, dll.** yang memiliki densitas berbeda. Pengurangan stok untuk bahan cair akan tidak akurat.

---

### [ARCH-A06] `MenuRequest` Membatasi `day_of_week` antara 1-4 (Hanya Senin-Kamis) Tanpa Konfigurasi
**File:** [`MenuRequest.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Requests/Nutrition/MenuRequest.php) baris 30

```php
'items.*.day_of_week' => 'required|integer|between:1,4',
// between:1,4 = hanya Senin(1) s.d Kamis(4)
```

Program MBG mungkin berjalan 5 hari (Senin-Jumat), bukan 4 hari. Pembatasan ini hardcoded dan tidak fleksibel jika jadwal berubah.

---

## ⬜ PERINGATAN MINOR

### [WARN-A01] `EmployeeService::create` — Password Default Lemah
**File:** [`EmployeeService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/Employee/EmployeeService.php) baris 54

```php
'password' => $data['password'] ?? 'sppg@123',
```

Jika Admin SPPG tidak mengisi password saat membuat akun karyawan, semua karyawan akan mendapat password default `sppg@123`. Ini password umum yang mudah ditebak.

---

### [WARN-A02] `EmployeeService::getAll` — Menggunakan `ilike` (PostgreSQL-only)
**File:** [`EmployeeService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/Employee/EmployeeService.php) baris 24

```php
$query->where('name', 'ilike', "%{$filters['search']}%");
```

`ilike` tidak didukung SQLite. Di environment development, pencarian karyawan akan throw error.

---

### [WARN-A03] `DashboardController` — `stock_alerts` Selalu Array Kosong
**File:** [`DashboardController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) baris 104

```php
'stock_alerts' => [],  // TODO: Implement stock alert summary
```

Dashboard tidak menampilkan informasi stok kritis/rendah meski sistem stok sudah ada. Frontend menerima data kosong untuk bagian ini.

---

### [WARN-A04] `SchoolController (AdminSPPG)` — Tidak Menggunakan Service Layer
**File:** [`SchoolController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/SchoolController.php)

Validasi inline di `store()` dan `update()` mengulang logika validasi yang seharusnya ada di `FormRequest`. Tidak konsisten dengan controller lain yang menggunakan dedicated request class.

---

## 🗺️ Peta Dampak Isu

```
Endpoint                                    | Isu
--------------------------------------------|----------------------------------------------
GET /admin-sppg/dashboard                   | BUG-A07 (stats global), BUG-A08 (kueri salah)
GET /admin-sppg/employees                   | BUG-A02 (semua SPPG bocor!)
POST /admin-sppg/employees                  | BUG-A01 (fallback sppg_id=1)
GET /admin-sppg/employees/{id}              | BUG-A03 (data bocor cross-SPPG)
GET /admin-sppg/roles                       | BUG-A05 (semua role bocor)
POST /admin-sppg/roles                      | BUG-A04 (sppg_id tidak di-set)
GET /admin-sppg/schools                     | BUG-A13 (semua sekolah bocor)
GET /admin-sppg/nutrition/menus/{id}        | ARCH-A03 (status di-override tanggal)
PATCH /admin-sppg/nutrition/menus/{id}/publish | BUG-A09 (double deduct stok)
PUT /admin-sppg/nutrition/menus/{id}        | BUG-A06 (meal_time hilang)
GET /admin-sppg/stocks                      | BUG-A10 (semua ingredient dimuat)
POST /admin-sppg/stocks/{id}/approve        | BUG-A12 (quantity_before selalu 0)
GET /admin-sppg/distributions               | BUG-A11 (filter kurir tidak aktif)
GET /admin-sppg/tracking/active             | SEC-A03 (double role check)
GET /admin-sppg/financial-reports           | STUB-A01 (kosong), SEC-A01 (permission salah)
POST /admin-sppg/nutrition/recipes          | SEC-A05 (hard block kalori)
```

---

## 📋 Prioritas Perbaikan

### 🚨 Perbaiki Sekarang (Data Leak & Security)
1. **[BUG-A02]** Tambahkan scope `sppg_id` ke `EmployeeController@index`
2. **[BUG-A03]** Tambahkan validasi kepemilikan di `EmployeeController@show`, `update`, `destroy`
3. **[BUG-A04]** Set `sppg_id` saat membuat role di `RoleController@store`
4. **[BUG-A05]** Filter role berdasarkan `sppg_id` di `RoleController@index` dan `showAssignRole`
5. **[BUG-A13]** Scope `SchoolController` ke SPPG login
6. **[BUG-A01]** Hapus fallback `sppg_id = 1`, ganti dengan abort

### ⚠️ Sprint Berikutnya (Logika Salah)
7. **[BUG-A06]** Perbaiki `meal_time => null` di `saveMenuItems()`
8. **[BUG-A07]** Scope statistik dashboard ke SPPG login
9. **[BUG-A08]** Perbaiki kueri `total_couriers` yang salah
10. **[BUG-A09]** Tambahkan guard terhadap re-publish menu yang sudah published
11. **[BUG-A11]** Perbaiki role slug dari `'courier'` menjadi `'kurir'`
12. **[SEC-A01]** Hapus duplikasi middleware permission di route + controller

### 🔧 Perencanaan (Arsitektur)
13. **[ARCH-A02]** Tambahkan `meal_time` ke `MenuItem::fillable` dan alur create/update
14. **[ARCH-A03]** Pisahkan status auto-compute dari status manual publish
15. **[ARCH-A04]** Hapus route `update`/`destroy` di `DistributionController` yang selalu 403
16. **[ARCH-A05]** Perbaiki konversi unit liter ke gram menggunakan densitas yang benar
17. **[SEC-A04]** Perbaiki validasi uniqueness role agar scoped per `sppg_id`
18. **[SEC-A05]** Ubah hard-block kalori menjadi warning informatif

---

*Laporan ini dihasilkan dari analisis statis kode sumber. Lanjutkan ke modul Distribution untuk analisis lengkap.*
