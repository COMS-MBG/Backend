# 🔍 Analisis Admin SPPG — Verifikasi & Isu Baru (5 Juni 2026)

> **Berdasarkan:** Pemeriksaan kode aktual pada tanggal 5 Juni 2026  
> **Status:** Update dari analisis sebelumnya. Memverifikasi perbaikan yang tercatat di `perubahan_dan_analisis_flow.md`

---

## ✅ KONFIRMASI: Isu yang Sudah Diperbaiki

Semua isu yang tercatat di `perubahan_dan_analisis_flow.md` **terkonfirmasi sudah diperbaiki** dengan benar:

| Isu | Status | Verifikasi |
|-----|--------|-----------|
| BUG-A01: fallback `sppg_id = 1` | ✅ Fixed | `EmployeeController` sekarang pakai `abort_if(!$sppgId, 403)` |
| BUG-A02: index tidak scope ke SPPG | ✅ Fixed | `->where('sppg_id', $sppgId)` sudah ada |
| BUG-A03: show tanpa ownership check | ✅ Fixed | `validateOwnership()` diterapkan ke semua method |
| BUG-A04: role dibuat tanpa sppg_id | ✅ Fixed | `'sppg_id' => $sppgId` ada di create |
| BUG-A05: semua role tampil di dropdown | ✅ Fixed | Filter `where('sppg_id', $sppgId)` diterapkan |
| BUG-A06: meal_time selalu null saat update | ⚠️ **PARTIAL** | `saveMenuItems` sudah perbaiki hardcode null, tapi `meal_time` belum di `create()` — lihat isu baru |
| BUG-A07: dashboard stats global | ✅ Fixed | Semua query di-scope via `whereHas('school', ...)` |
| BUG-A08: kueri kurir salah | ✅ Fixed | Dibungkus closure, filter `sppg_id` ditambahkan |
| BUG-A09: double deduct stok saat publish ulang | ✅ Fixed | Guard `in_array($menu->status, ['published', 'archived'])` ada |
| BUG-A11: slug kurir salah `'courier'` | ✅ Fixed | Sudah jadi `'kurir'` |
| BUG-A12: quantity_before = 0 hardcoded | ✅ Fixed | Sekarang query stok aktual sebelum approval |
| BUG-A13: sekolah tidak di-scope ke SPPG | ✅ Fixed | `getSppgId()` + `validateOwnership()` diterapkan |
| SEC-A01: duplikasi middleware | ✅ Resolved | `HasMiddleware` di controller adalah satu-satunya source |
| ARCH-A03: status published di-override | ✅ Fixed | `refreshStatus()` skip jika `published` atau `archived` |
| StockService::getSummary load semua ingredient | ✅ Fixed | Hanya load ingredient yang punya stock/minimum di SPPG ini |

---

## 🔴 ISU YANG MASIH ADA (Belum Diperbaiki)

### [MASIH-01] `MenuItem::$fillable` TIDAK Menyertakan `meal_time`
**File:** [`MenuItem.php`](file:///c:/Users/naufa/COMS_MBG/app/Models/MenuItem.php) baris 18–24

```php
// Model fillable MASIH tidak ada meal_time
protected $fillable = [
    'menu_id', 'recipe_id', 'day_of_week', 'menu_date', 'order',
    // ❌ 'meal_time' tidak ada di sini!
];
```

**File:** [`MenuService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) baris 101–108

```php
// create() juga tidak memasukkan meal_time ke MenuItem::create()
foreach ($data['items'] as $item) {
    MenuItem::create([
        'menu_id'     => $menu->id,
        'recipe_id'   => $item['recipe_id'],
        'day_of_week' => $item['day_of_week'],
        'menu_date'   => $item['menu_date'],
        'order'       => $item['order'] ?? 0,
        // ❌ meal_time tidak ada!
    ]);
}
```

**Dampak:**
- `meal_time` tidak pernah tersimpan ke database, baik saat `create` maupun `update`
- Di `getMenuGroupedByDay()` baris 195–196, field ini sudah di-comment out: `// 'meal_time' => $item->meal_time`
- Dokumentasi di comment `MenuController` baris 111 menyebut `"meal_time": "lunch"` sebagai field yang diharapkan, tapi tidak pernah diproses
- `MenuRequest` juga tidak memvalidasi `meal_time` di items

**Status Fix:** Perbaikan `saveMenuItems` sudah membuang hardcode `null`, tapi field ini **tidak pernah diisi sama sekali** — karena tidak ada di `create()` maupun di `$fillable`.

---

### [MASIH-02] `DashboardController` — `stock_alerts` Selalu Array Kosong
**File:** [`DashboardController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) baris 113

```php
'stock_alerts' => [],  // ❌ Belum diimplementasikan
```

Sistem stok sudah ada, `StockService::getSummary()` sudah ada, tapi dashboard tidak menggunakannya untuk menampilkan alert stok rendah/habis.

---

### [MASIH-03] `DashboardController` — Filter `status = 'active'` pada Kurir Tidak Ada di Schema
**File:** [`DashboardController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) baris 84

```php
->where('status', 'active')  // ❌ Kolom 'status' tidak ada di tabel employees
->count(),
```

Tabel `employees` tidak memiliki kolom `status` (dikomentar di `Employee::$fillable`). Query ini berpotensi throw exception di beberapa database engine.

---

### [MASIH-04] `FinancialReportController` (Admin SPPG) — Seluruh Method Kosong
**File:** [`FinancialReportController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/FinancialReportController.php)

Semua 5 method masih `//`. Route tetap terdaftar dan bisa diakses. **Tambahan masalah**: middleware di controller menggunakan `permission:finance.read` sementara route file menggunakan `permission:report.read` — **dua permission berbeda** untuk endpoint yang sama.

---

### [MASIH-05] `DistributionController@update` dan `@destroy` — Route Terdaftar tapi Hanya Return 403
**File:** [`DistributionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) baris 114–131

Route `update` dan `destroy` masih terdaftar di `api_adminsppg.php` baris 121 (via `match(['put', 'patch'], ...)`) meskipun controller hanya mengembalikan 403. Catatan `perubahan_dan_analisis_flow.md` menyebut "route kosong tersebut dihapus" tapi **kode masih ada**. Method ini juga masih memiliki middleware `permission:distribution.update/delete` yang tidak perlu.

---

### [MASIH-06] `DistributionController@show` — Tidak Memvalidasi Kepemilikan SPPG
**File:** [`DistributionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) baris 81–89

```php
public function show(DeliverySchedule $schedule): JsonResponse
{
    // ❌ Tidak ada cek apakah schedule ini milik SPPG yang login
    $schedule->load([...]);
    return response()->json([...]);
}
```

Admin SPPG A bisa mengakses detail jadwal pengiriman milik SPPG B hanya dengan mengetahui ID-nya.

---

### [MASIH-07] `DistributionController@index` — Tidak Di-scope ke SPPG Login
**File:** [`DistributionController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) baris 44–51

```php
$query = DeliverySchedule::active()
    ->with([...])
    ->latest();

if ($request->user()->hasAnyRole(['kurir'])) {
    $query->forCourier(...);
}
```

Query tidak difilter berdasarkan `sppg_id`. Admin SPPG A akan melihat **semua jadwal distribusi dari semua SPPG**. Hanya kurir yang punya filter — dan itupun berdasarkan employee ID kurir, bukan SPPG.

---

## 🟡 ISU BARU YANG DITEMUKAN

### [BARU-01] `MenuService::update()` — Selalu Override Status dengan Tanggal, Termasuk Menu `published`
**File:** [`MenuService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) baris 124, 130

```php
// update() selalu recompute status:
$status = Menu::computeStatus($data['week_start'] ?? $menu->week_start);
$menu->update([
    // ...
    'status' => $status,  // ❌ Tidak ada guard seperti di refreshStatus()
]);
```

Berbeda dengan `refreshStatus()` yang sudah memiliki guard untuk skip jika `published`/`archived`, fungsi `update()` **tidak memiliki guard ini**. Jika Admin SPPG mengedit nama/notes menu yang sudah dipublish, status akan **dikembalikan ke `scheduled`** atau `planned` berdasarkan tanggal.

---

### [BARU-02] `MenuController@publish` — `sppg_id` Bisa `null` tanpa Error
**File:** [`MenuController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/MenuController.php) baris 206

```php
$sppgId = $request->user()->sppg_id;
// ❌ Tidak ada abort_if(!$sppgId, ...) — jika null, StockService::deductStockForMenu(null, ...) dipanggil
$stockService->deductStockForMenu($sppgId, $id, $userId);
```

Berbeda dengan `StockController` yang mengambil `sppg_id` langsung dari `$request->user()->sppg_id` tanpa validasi, jika user login tidak memiliki `sppg_id`, `StockService` akan menerima `null` sebagai `sppgId`. Ini akan menyebabkan query DB dengan kondisi `WHERE sppg_id = null` yang mengembalikan 0 stok, sehingga seolah-olah ada kekurangan stok untuk semua bahan, dan throw `StockShortageException` yang menyesatkan.

---

### [BARU-03] `RecipeService::update()` — Tidak Ada Guard jika Recipe Dipakai Menu `published`
**File:** [`RecipeService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/RecipeService.php) baris 114–142

```php
public function update(int $id, array $data): Recipe
{
    return DB::transaction(function () use ($id, $data) {
        $recipe = Recipe::findOrFail($id);
        // ❌ Tidak ada cek apakah recipe ini dipakai di menu yang sudah published!
        $recipe->recipeIngredients()->delete();
        // Hapus semua bahan lama, ganti baru, recalculate nutrisi
```

Jika resep dimodifikasi setelah menu dipublish dan stok sudah dikurangi berdasarkan resep lama, **total nutrisi dan bahan yang dikurangi dari stok menjadi tidak konsisten** dengan resep yang baru. Stok yang sudah dikurangi tidak bisa di-rollback.

---

### [BARU-04] `IngredientService::delete()` — Tidak Cek Stok yang Masih Aktif
**File:** [`IngredientService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/IngredientService.php) baris 99–113

```php
public function delete(int $id): bool
{
    $ingredient = $this->findById($id);

    // Cek hanya recipe yang memakai ingredient ini
    $usedInRecipes = $ingredient->recipeIngredients()->count();
    if ($usedInRecipes > 0) {
        throw new \Exception("...");
    }
    // ❌ Tidak ada cek apakah ada StockItem aktif untuk ingredient ini!
    return $ingredient->delete();
}
```

Jika ingredient di-delete padahal masih ada `StockItem` (batch stok) yang aktif untuk ingredient itu, **batch-batch tersebut menjadi orphan** — tidak ada foreign key constraint yang melindungi ini karena `ingredients` bisa soft-deleted.

---

### [BARU-05] `StockController@store` — Tidak Validasi bahwa `sppg_id` Valid
**File:** [`StockController.php`](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/StockController.php) baris 102

```php
$sppgId = $request->user()->sppg_id;
// ❌ Tidak ada validasi — jika null, StockItem dibuat dengan sppg_id = null
$stockItem = StockItem::create([
    'sppg_id' => $sppgId,
    // ...
]);
```

Konsisten dengan BUG-BARU-02, tidak ada guard untuk `sppg_id = null`.

---

### [BARU-06] `MenuService::getAll()` — Tidak Di-scope ke SPPG Login
**File:** [`MenuService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) baris 21–36

```php
public function getAll(array $filters = [])
{
    // ❌ Tidak ada filter sppg_id sama sekali!
    $query = Menu::with(['menuItems.recipe']);
    // ...
}
```

**Model `menus` tidak memiliki kolom `sppg_id`**, sehingga semua SPPG berbagi satu kumpulan menu yang sama. Ahli Gizi dari SPPG A bisa melihat, mengedit, bahkan mempublish menu yang dibuat oleh SPPG B. Ini adalah **desain arsitektur yang perlu diverifikasi** — apakah menu memang global atau per-SPPG?

> [!IMPORTANT]
> **Ini mungkin desain yang disengaja**, jika memang satu tim ahli gizi pusat yang membuat menu untuk semua SPPG. Tapi jika tiap SPPG seharusnya punya menu sendiri, maka tabel `menus` perlu ditambah kolom `sppg_id`.

---

### [BARU-07] `RecipeService` & `IngredientService` — Tidak Di-scope ke SPPG
**File:** [`RecipeService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/RecipeService.php)  
**File:** [`IngredientService.php`](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/IngredientService.php)

Sama dengan menu, resep dan bahan baku **tidak memiliki `sppg_id`** — bersifat global. Ahli Gizi SPPG A bisa menghapus resep yang dipakai SPPG B. Ini perlu klarifikasi: apakah resep dan bahan memang dimaksudkan sebagai master data global?

---

## 📋 Daftar Prioritas Tindakan

### 🚨 Perbaiki Segera (Breaking/Data Integrity)

| # | File | Tindakan |
|---|---|---|
| 1 | [MenuItem.php](file:///c:/Users/naufa/COMS_MBG/app/Models/MenuItem.php) | Tambahkan `'meal_time'` ke `$fillable` |
| 2 | [MenuService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) | Tambahkan `'meal_time' => $item['meal_time'] ?? null` di `create()` |
| 3 | [MenuService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/MenuService.php) | Tambahkan guard `if (in_array($menu->status, ['published', 'archived'])) return` di `update()` sebelum override status |
| 4 | [MenuController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/MenuController.php) | Tambahkan `abort_if(!$sppgId, 403, ...)` di `publish()` |
| 5 | [DistributionController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DistributionController.php) | Tambahkan scope SPPG ke `index()` dan `show()` |
| 6 | [StockController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/StockController.php) | Tambahkan validasi `sppg_id` di `store()` |
| 7 | [DashboardController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) | Hapus atau guard `->where('status', 'active')` pada kurir |

### ⚠️ Sprint Berikutnya

| # | File | Tindakan |
|---|---|---|
| 8 | [IngredientService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/IngredientService.php) | Tambahkan cek `StockItem` aktif sebelum delete ingredient |
| 9 | [RecipeService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/RecipeService.php) | Block update recipe jika dipakai di menu `published` |
| 10 | [DashboardController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/DashboardController.php) | Implementasi `stock_alerts` dari `StockService::getSummary()` |
| 11 | [FinancialReportController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/AdminSPPG/FinancialReportController.php) | Implementasi atau hapus route + perbaiki inkonsistensi permission `finance.read` vs `report.read` |

### 🔧 Perlu Klarifikasi Desain

| # | Pertanyaan |
|---|---|
| A | Apakah `menus`, `recipes`, `ingredients` dimaksudkan **global** (satu database untuk semua SPPG) atau **per-SPPG**? |
| B | Apakah kolom `status` pada `employees` akan diaktifkan kembali? (Saat ini di-comment di `$fillable`) |

---

*Laporan ini berdasarkan kode aktual pada 5 Juni 2026.*
