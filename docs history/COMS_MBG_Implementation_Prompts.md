# COMS MBG — Implementation Prompts
> File ini berisi 3 prompt siap pakai untuk pengerjaan bertahap.  
> Gunakan satu per satu secara berurutan. Bagian 2 dan 3 bergantung pada bagian sebelumnya.

---

## ⚙️ Stack & Konvensi Global

- **Backend**: Laravel 11, Sanctum SPA, MySQL
- **Frontend**: (sesuaikan dengan stack FE tim)
- **Auth**: role_type di tabel `users` (`super_admin` | `sppg_user`)
- **RBAC**: permission slug diambil dari tabel `permissions` → `role_permission` → `roles`
- **Soft delete**: semua tabel utama menggunakan `deleted_at`
- **Naming**: semua API route prefix `/api/` — Superadmin di `/api/super-admin/`, SPPG user di `/api/admin-sppg/`

---

---

# 🔐 TAHAP 1 — UPDATE AUTH & LOGIN FLOW

## Konteks

Ini adalah tahap pertama dan fondasi dari seluruh sistem. Kerjakan ini sebelum Tahap 2 dan 3.

Tujuan utama:
1. Memperbarui alur login agar membedakan Superadmin vs SPPG User
2. Mengimplementasikan mekanisme aktivasi SPPG otomatis saat Admin SPPG pertama kali login
3. Memperbarui alur registrasi SPPG baru oleh Superadmin (section Ahli Gizi & Admin Logistik kini opsional)
4. Menambahkan template email notifikasi akun

---

## Prompt Tahap 1

```
Kamu adalah senior Laravel developer. Kerjakan task-task berikut untuk sistem COMS MBG (Catering Operations Management System — Makan Bergizi).

### STACK
- Laravel 11, Sanctum SPA, MySQL
- Tabel utama: users, s_p_p_g_s (sppgs), employees, roles, permissions, role_permission

### STRUKTUR TABEL YANG RELEVAN

**users**
- id, name, email, password, role_type (enum: super_admin | sppg_user), sppg_id (FK nullable), is_active (boolean), created_at, updated_at

**s_p_p_g_s**
- id, name, address, city, district, province, latitude, longitude, capacity, status (enum: inactive | active | deleted), pemilik_id (FK → users), deleted_at, created_at, updated_at

**employees**
- id, user_id (FK), sppg_id (FK), position (varchar), created_at, updated_at

**roles**
- id, sppg_id (FK), name (varchar), created_at, updated_at

**permissions**
- id, slug (varchar unique), module (varchar), feature (varchar), action (varchar)

**role_permission**
- role_id (FK), permission_id (FK)

---

### TASK P0 — WAJIB DIKERJAKAN

**1. Update AuthController@login**

Implementasikan login flow berikut secara berurutan:

| Step | Kondisi | Aksi |
|------|---------|------|
| 1 | User submit email + password | Cek akun ada di database |
| 2 | Akun tidak ditemukan | Return 401: "Akun tidak ditemukan" |
| 3 | Akun ada, is_active = false | Return 403: "Akun tidak aktif" |
| 4 | role_type = super_admin | Buat Sanctum token, return user data + role_type |
| 5 | role_type = sppg_user, sppg.status = inactive, bukan pemilik_id | Return 403: "Menunggu aktivasi SPPG" |
| 6 | role_type = sppg_user, sppg.status = inactive, adalah pemilik_id | UPDATE sppg.status = 'active', izinkan login, return token + permissions |
| 7 | role_type = sppg_user, sppg.status = active | Ambil role + permissions dari DB. Return token + permissions[] |

Response login berhasil (sppg_user) harus menyertakan:
```json
{
  "token": "...",
  "user": { "id": 1, "name": "...", "email": "...", "role_type": "sppg_user" },
  "sppg_status": "active",
  "permissions": ["dashboard.read", "stock.read", "stock.create", ...]
}
```

Response login berhasil (super_admin) tidak perlu permissions[] karena FE render menu statis.

**2. Update GET /api/auth/user**

Sertakan `permissions[]` dalam response agar FE bisa re-hydrate state setelah page refresh tanpa harus login ulang.

**3. Update POST /api/super-admin/sppg — Registrasi SPPG Baru**

Form registrasi dibagi 5 section. Section 4 (Ahli Gizi) dan Section 5 (Admin Logistik) kini **OPSIONAL**.

Proses dalam satu DB Transaction:
- Buat 1 entri sppg (status: inactive, pemilik_id = id Admin SPPG yang baru dibuat)
- Buat akun Admin SPPG di tabel users (role_type: sppg_user, is_active: true)
- Buat entri employee untuk Admin SPPG + assign default role "Admin SPPG"
- Jika section 4 diisi: buat akun Ahli Gizi + employee + assign default role "Ahli Gizi"
- Jika section 5 diisi: buat akun Admin Logistik + employee + assign default role "Admin Logistik"
- Password Ahli Gizi & Admin Logistik = password Admin SPPG yang di-hash ulang
- Insert data sekolah mitra ke tabel partners dengan sppg_id
- Kirim email ke semua akun yang berhasil dibuat (gunakan queue)

Validasi wajib:
- Section 1 (Data SPPG): nama, alamat, kecamatan, kota, provinsi, latitude, longitude, kapasitas — WAJIB
- Section 2 (Mitra): minimal 1 sekolah mitra — WAJIB
- Section 3 (Admin SPPG): nama, email, password — WAJIB
- Section 4 & 5: opsional (boleh kosong)

**4. Seed Default Role & Permission**

Seed 3 default role per SPPG baru dengan permission masing-masing:

| Permission Slug | Admin SPPG | Ahli Gizi | Admin Logistik |
|----------------|------------|-----------|----------------|
| dashboard.read | ✅ | ✅ | ✅ |
| employee.create/read/update/delete | ✅ | — | — |
| role.create/read/update/delete | ✅ | — | — |
| ingredients.create/read/update/delete | ✅ | ✅ | — |
| recipes.create/read/update/delete | ✅ | ✅ | — |
| menus.create/read/update/delete | ✅ | ✅ | — |
| stock.read | ✅ | ✅ | ✅ |
| stock.create/update/delete | ✅ | — | ✅ |
| stock.approve | ✅ | — | ✅ |
| distribution.create/read/update/delete | ✅ | — | ✅ |
| finance.create/read/update/delete | ✅ | — | — |
| report.read | ✅ | ✅ | ✅ |
| partner.create/read/update/delete | ✅ | — | — |

Juga tambahkan 5 permission slug baru ke tabel permissions jika belum ada:
`stock.read`, `stock.create`, `stock.update`, `stock.delete`, `stock.approve`

**5. Template Email — Laravel Mailable**

Buat class `AccountCreatedMail` dengan konten:
- Nama penerima
- Nama SPPG
- Username (email login)
- Password (plain text — karena ini setup awal)
- Link login sistem
- Peringatan: "Segera ganti password Anda setelah login pertama kali."

Desain HTML sederhana, tidak perlu custom branding kompleks untuk MVP.

**6. Update GET /api/admin-sppg/dashboard**

Tambah dua field baru dalam response:
```json
{
  "staff_completeness": {
    "ahli_gizi_registered": true,
    "admin_logistik_registered": false,
    "is_complete": false
  },
  "stock_alerts": []
}
```

`is_complete` = false jika salah satu belum terdaftar → FE menampilkan banner alert di dashboard Admin SPPG.

---

### OUTPUT YANG DIHARAPKAN
- File: `app/Http/Controllers/AuthController.php` (update)
- File: `app/Http/Controllers/SuperAdmin/SppgController.php` (update method store)
- File: `app/Http/Controllers/AdminSppg/DashboardController.php` (update)
- File: `app/Mail/AccountCreatedMail.php` (baru)
- File: `database/seeders/DefaultRolePermissionSeeder.php` (baru)
- File: `database/migrations/xxxx_add_stock_permissions.php` (baru)

Berikan juga test case untuk login flow (unit test atau feature test).
```

---

---

# 📦 TAHAP 2 — FITUR MANAJEMEN STOK

## Konteks

Kerjakan setelah Tahap 1 selesai. Fitur ini adalah modul baru yang disisipkan antara Master Bahan Baku dan Master Resep.

**Penting:** Superadmin tidak memiliki akses ke modul stok sama sekali. Semua endpoint stok hanya untuk `sppg_user` dengan permission yang sesuai.

---

## Prompt Tahap 2

```
Kamu adalah senior Laravel developer. Kerjakan implementasi modul Manajemen Stok untuk sistem COMS MBG.

Asumsikan Tahap 1 (Update Auth) sudah selesai. Tabel users, roles, permissions sudah memiliki 5 permission stok.

### TABEL BARU — WAJIB DIBUAT MIGRATION

**stock_items**
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK auto | |
| sppg_id | FK → s_p_p_g_s | Scope isolasi data per SPPG |
| ingredient_id | FK → ingredients | Referensi ke master bahan baku |
| batch_number | varchar(100) | e.g. BATCH-20260601-001 |
| quantity | decimal(10,3) | Jumlah stok saat ini |
| unit | enum(kg,liter,gram,ml,pcs) | |
| price_per_unit | decimal(12,2) | Harga per satuan |
| purchase_date | date | |
| expiry_date | date | |
| supplier | varchar(255) | |
| storage_type | enum(dry,chilled,frozen) | |
| storage_location | varchar(255) nullable | |
| sku | varchar(100) nullable | |
| notes | text nullable | |
| status | enum(pending,available,low,empty,expired) | |
| approved_by | FK → users nullable | |
| approved_at | datetime nullable | |
| proof_document | varchar(500) nullable | Path file nota/invoice |
| created_by | FK → users | |
| deleted_at | timestamp nullable | Soft delete |
| timestamps | | |

**stock_minimum**
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK auto | |
| sppg_id | FK → s_p_p_g_s | |
| ingredient_id | FK → ingredients | |
| minimum_quantity | decimal(10,3) | |
| unit | enum(kg,liter,gram,ml,pcs) | |
| timestamps | | |

UNIQUE constraint: (sppg_id, ingredient_id)

**stock_transactions**
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK auto | |
| sppg_id | FK → s_p_p_g_s | |
| stock_item_id | FK → stock_items | Batch yang terpengaruh |
| ingredient_id | FK → ingredients | Denormalisasi untuk query cepat |
| transaction_type | enum(in,out,adjustment,expired_disposal) | |
| quantity | decimal(10,3) | Jumlah yang masuk/keluar |
| quantity_before | decimal(10,3) | Snapshot sebelum transaksi |
| quantity_after | decimal(10,3) | Snapshot sesudah transaksi |
| reference_type | varchar(100) nullable | menu_publish / manual / purchase / adjustment |
| reference_id | bigint nullable | ID menu/order yang memicu |
| notes | text nullable | |
| created_by | FK → users | |
| created_at | timestamp | Log immutable — tidak ada updated_at |

---

### TASK P1 — CRUD STOK + APPROVAL

**Buat StockController dengan endpoint:**

| Method | Endpoint | Permission | Fungsi |
|--------|---------|-----------|--------|
| GET | /api/admin-sppg/stocks | stock.read | Ringkasan stok agregat per bahan baku |
| GET | /api/admin-sppg/stocks/{ingredient_id} | stock.read | Detail + daftar batch per bahan |
| POST | /api/admin-sppg/stocks | stock.create | Ajukan penambahan stok (status: pending) |
| PUT | /api/admin-sppg/stocks/{id} | stock.update | Edit batch (hanya sebelum diapprove) |
| DELETE | /api/admin-sppg/stocks/{id} | stock.delete | Soft delete batch |
| GET | /api/admin-sppg/stocks/pending | stock.approve | Daftar pengajuan pending |
| POST | /api/admin-sppg/stocks/{id}/approve | stock.approve | Approve → status: available, generate batch_number |
| POST | /api/admin-sppg/stocks/{id}/reject | stock.approve | Reject → pengajuan ditolak |
| GET | /api/admin-sppg/stocks/transactions | stock.read | Semua riwayat transaksi SPPG |
| GET | /api/admin-sppg/stocks/{id}/transactions | stock.read | Riwayat transaksi per batch |
| PUT | /api/admin-sppg/stocks/minimum/{ingredient_id} | stock.update | Set/update stok minimum per bahan |

**Logika approve:**
1. Update status stock_item → 'available' (atau 'low' jika quantity < minimum)
2. Generate batch_number: `BATCH-{YYYYMMDD}-{3digit_sequence}`
3. Catat di stock_transactions (transaction_type: 'in', quantity_before: 0, quantity_after: quantity)
4. Set approved_by = user yang approve, approved_at = now()

**Logika ringkasan agregat (GET /stocks):**
```json
[
  {
    "ingredient_id": 1,
    "ingredient_name": "Telur",
    "total_quantity": 130,
    "unit": "kg",
    "minimum_quantity": 20,
    "status": "available",
    "batch_count": 2
  }
]
```
Status ditentukan: jika total = 0 → 'empty', jika total < minimum → 'low', jika ada expired → tampilkan warning, else 'available'.

---

### TASK P2 — INTEGRASI MENU + STOK

**Update MenuController@publish (PATCH /api/admin-sppg/nutrition/menus/{id}/publish)**

Tambahkan validasi stok HARD BLOCK sebelum mengubah status menu menjadi 'published':

1. Ambil semua menu_items untuk menu ini → dapatkan recipe_id per hari
2. Dari setiap recipe, ambil recipe_ingredients (ingredient_id + weight_used dalam gram)
3. Kalikan dengan jumlah porsi (SUM partners.jumlah_porsi untuk SPPG ini)
4. Bandingkan kebutuhan total dengan stok available (exclude expired, exclude pending)
5. Jika semua cukup: lanjutkan publish, potong stok FIFO (dari batch dengan purchase_date terlama)
6. Jika ada yang kurang: return HTTP 422 dengan detail:
```json
{
  "message": "Stok tidak mencukupi untuk publish menu.",
  "shortages": [
    {
      "ingredient_id": 3,
      "ingredient_name": "Bawang Merah",
      "needed": 5.5,
      "available": 3.2,
      "unit": "kg",
      "shortage": 2.3
    }
  ]
}
```

Setiap pemotongan stok dicatat di stock_transactions (transaction_type: 'out', reference_type: 'menu_publish', reference_id: menu_id).

**Buat endpoint simulasi (non-blocking):**
GET /api/admin-sppg/stocks/check-menu/{menu_id}

Sama seperti logika di atas tapi TIDAK melakukan pemotongan stok. Hanya return hasil simulasi untuk ditampilkan sebagai alert saat menyusun menu.

---

### CATATAN PENTING
- Semua endpoint stok scoped ke sppg_id dari user yang sedang login — jangan izinkan akses lintas SPPG
- Superadmin tidak boleh mengakses endpoint ini sama sekali (middleware check role_type)
- stock_transactions bersifat immutable — tidak boleh ada UPDATE/DELETE

### OUTPUT YANG DIHARAPKAN
- Migration: `create_stock_items_table`, `create_stock_minimum_table`, `create_stock_transactions_table`
- Controller: `app/Http/Controllers/AdminSppg/StockController.php`
- Update: `app/Http/Controllers/AdminSppg/MenuController.php` (method publish)
- Service (opsional): `app/Services/StockService.php` (untuk logika FIFO + kalkulasi)
```

---

---

# 🗺️ TAHAP 3 — MODUL SUPERADMIN

## Konteks

Kerjakan setelah Tahap 1 dan Tahap 2 selesai. Modul ini membangun seluruh antarmuka dan backend untuk akun Superadmin.

Superadmin adalah akun tunggal yang dibuat via seeder. Tidak ada registrasi. Tidak ada akses ke data operasional SPPG (stok, resep, menu, distribusi).

---

## Prompt Tahap 3

```
Kamu adalah senior Laravel developer. Kerjakan implementasi modul Superadmin untuk sistem COMS MBG.

Asumsikan Tahap 1 (Auth) dan Tahap 2 (Stok) sudah selesai.

Semua route Superadmin diawali dengan `/api/super-admin/` dan dilindungi middleware yang memastikan `role_type = super_admin`.

---

### TABEL BARU — WAJIB DIBUAT MIGRATION

**sppg_drafts**
| Kolom | Tipe | Default | Keterangan |
|-------|------|---------|-----------|
| id | bigint PK auto | | |
| submission_number | varchar(50) | null | Auto-generate: DRAFT-YYYYMMDD-NNN |
| submitted_by | FK → users nullable | null | NULL jika future public, user_id Superadmin jika internal |
| source | enum(internal,public) | internal | |
| form1_data | json nullable | null | Data SPPG + mitra |
| form2_data | json nullable | null | Data akun Admin SPPG (password hashed) |
| form3_data | json nullable | null | Data Ahli Gizi + Admin Logistik |
| latitude | decimal(10,8) nullable | null | Koordinat yang diinput |
| longitude | decimal(11,8) nullable | null | |
| confirmed_latitude | decimal(10,8) nullable | null | Koordinat setelah konfirmasi di map |
| confirmed_longitude | decimal(11,8) nullable | null | |
| point_status | enum(green,yellow,red) nullable | null | Hasil validasi titik terakhir |
| map_confirmed | boolean | false | True jika sudah dikonfirmasi di Map Rekomendasi |
| status | enum(draft,submitted,registered) | draft | |
| submitted_at | datetime nullable | null | |
| deleted_at | timestamp nullable | null | Soft delete |
| timestamps | | | |

**sppg_draft_partners** (relasi 1-N dari sppg_drafts)
| Kolom | Tipe | Wajib | Keterangan |
|-------|------|-------|-----------|
| id | bigint PK auto | | |
| draft_id | FK → sppg_drafts | ✅ | |
| school_name | varchar(255) | ✅ | |
| npsn | varchar(20) nullable | | |
| level | enum(SD,SMP,SMA,SMK) | ✅ | |
| school_status | enum(negeri,swasta) | ✅ | |
| address | text | ✅ | |
| city | varchar(100) | ✅ | |
| district | varchar(100) | ✅ | |
| latitude | decimal(10,8) | ✅ | |
| longitude | decimal(11,8) | ✅ | |
| jumlah_porsi | integer | ✅ | |
| data_source | enum(database,openstreetmap) | ✅ | |
| timestamps | | | |

---

### TASK P0 — DASHBOARD & DAFTAR SPPG

**1. DashboardController (GET /api/super-admin/dashboard)**

Return:
```json
{
  "total_sppg": 45,
  "total_sppg_active": 38,
  "total_sppg_inactive": 7,
  "total_partners": 312,
  "total_daily_portions": 87500
}
```

Query:
- total_sppg: COUNT(sppgs) WHERE status != 'deleted' AND deleted_at IS NULL
- total_sppg_active: COUNT WHERE status = 'active'
- total_sppg_inactive: COUNT WHERE status = 'inactive'
- total_partners: COUNT(partners) WHERE sppg_id IS NOT NULL AND deleted_at IS NULL
- total_daily_portions: SUM(partners.jumlah_porsi) WHERE sppg_id IS NOT NULL

**2. SuperAdminSppgController**

Endpoint:
| Method | Endpoint | Keterangan |
|--------|---------|-----------|
| GET | /api/super-admin/sppg | List semua SPPG, filter ?city=&district= |
| GET | /api/super-admin/sppg/{id} | Detail SPPG |
| GET | /api/super-admin/sppg/{id}/partners | Daftar mitra + kalkulasi jarak Haversine |
| GET | /api/super-admin/sppg/{id}/menus | Daftar menu per periode (read-only) |
| PUT | /api/super-admin/sppg/{id} | Edit data SPPG |
| PATCH | /api/super-admin/sppg/{id}/deactivate | Nonaktifkan SPPG |
| PATCH | /api/super-admin/sppg/{id}/activate | Aktifkan kembali SPPG |
| DELETE | /api/super-admin/sppg/{id} | Soft delete SPPG |

**Logika deactivate:**
1. UPDATE sppgs SET status = 'inactive' WHERE id = X
2. UPDATE users SET is_active = false WHERE sppg_id = X

**Logika activate:**
1. UPDATE sppgs SET status = 'active' WHERE id = X
2. UPDATE users SET is_active = true WHERE sppg_id = X

**Logika delete (soft delete):**
1. sppgs: UPDATE deleted_at = now()
2. UPDATE users SET is_active = false WHERE sppg_id = X
3. UPDATE partners SET sppg_id = NULL WHERE sppg_id = X

**Kalkulasi jarak mitra (GET /{id}/partners):**

Gunakan Haversine formula untuk menghitung jarak (km) antara koordinat SPPG dan setiap mitra. Response:
```json
{
  "partner_id": 1,
  "school_name": "SDN Cikutra 1",
  "distance_km": 3.2,
  "estimated_minutes": null,
  "distance_status": "safe"
}
```
`distance_status`: "safe" jika ≤5km, "review" jika >5km.
`estimated_minutes` diisi dari OSRM jika tersedia, null jika tidak.

---

### TASK P1 — PENGAJUAN SPPG

**SppgSubmissionController**

| Method | Endpoint | Keterangan |
|--------|---------|-----------|
| GET | /api/super-admin/sppg-submissions | List draft + SPPG terdaftar |
| POST | /api/super-admin/sppg-submissions | Buat/update draft (auto-save) |
| GET | /api/super-admin/sppg-submissions/{id} | Detail draft |
| PUT | /api/super-admin/sppg-submissions/{id} | Update draft |
| DELETE | /api/super-admin/sppg-submissions/{id} | Hapus draft |
| POST | /api/super-admin/sppg-submissions/{id}/submit | Submit pengajuan → buat SPPG resmi |

**Logika submit (dalam satu DB Transaction):**
1. Validasi form1_data (SPPG + min 1 mitra) dan form2_data (Admin SPPG) tidak kosong
2. Buat entri sppgs (status: inactive, data dari form1_data)
3. Buat akun Admin SPPG dari form2_data
4. Buat employee Admin SPPG + assign default role
5. Jika form3_data berisi data Ahli Gizi: buat akun + employee + role
6. Jika form3_data berisi data Admin Logistik: buat akun + employee + role
7. Insert data mitra dari sppg_draft_partners ke tabel partners
8. Kirim email ke semua akun yang dibuat
9. Update sppg_drafts: status = 'registered', submitted_at = now()

**Aturan auto-save:**
- POST /api/super-admin/sppg-submissions dengan body apapun = simpan/update draft
- Jika ada draft dengan status 'draft' dan submitted_by = user saat ini yang belum disubmit, return draft tersebut
- Jika tidak ada, buat baru

---

### TASK P2 — MAP REKOMENDASI

**MapController**

| Method | Endpoint | Keterangan |
|--------|---------|-----------|
| GET | /api/super-admin/map/data | Semua layer sekaligus |
| GET | /api/super-admin/map/sppg-layers | SPPG aktif + mitra |
| GET | /api/super-admin/map/submission-layers | Pengajuan pending yang punya lat/lng |
| GET | /api/super-admin/map/recommendations | Rekomendasi mandiri sistem (K-Means) |
| GET | /api/super-admin/map/schools | Semua titik sekolah |
| POST | /api/super-admin/map/geocode | Nominatim: teks alamat → lat/lng |
| POST | /api/super-admin/map/route-check | OSRM: lat/lng A ke B → menit |
| POST | /api/super-admin/map/validate-point | Validasi titik → green/yellow/red + detail |
| POST | /api/super-admin/map/suggest-shift | Hitung A.1 dari titik A + mitra |
| POST | /api/super-admin/map/confirm-point/{submission_id} | Simpan koordinat ke draft |

**Logika validate-point (POST /map/validate-point)**

Input: `{ lat, lng, draft_partners: [{lat, lng, sppg_id?}] }`

Proses:
1. Untuk setiap mitra yang diajukan, cek apakah sudah terhubung ke SPPG existing
2. Jika mitra sudah ke SPPG existing: cek jarak SPPG existing ke mitra (Haversine)
   - Jika ≤5km DAN ≤30min → tidak bisa takeover
   - Jika >5km ATAU >30min → cek apakah SPPG baru lebih dekat → bisa takeover (KUNING)
3. Cek apakah centroid titik pengajuan berada dalam radius 5km dari SPPG existing manapun
   - Jika ya → MERAH (kecuali kapasitas SPPG existing sudah penuh → KUNING)
4. Return status + detail konflik

**Logika suggest-shift (POST /map/suggest-shift)**

Input: `{ lat, lng, draft_partners: [{lat, lng}] }`

Proses:
1. Dari titik A (lat/lng), filter mitra yang bisa dijangkau (radius 5km Haversine)
2. Hitung centroid dari mitra-mitra tersebut → ini adalah A.1
3. Jika jarak A ke A.1 > 500 meter: return A.1
4. Jika ≤ 500 meter: return null (tidak perlu geser)

**Logika rekomendasi mandiri sistem K-Means (GET /map/recommendations)**

1. Ambil semua sekolah dari tabel partners dimana sppg_id IS NULL (belum terlayani)
2. Tambahkan sekolah kandidat takeover (sppg_id NOT NULL tapi jarak ke SPPG existing > 5km)
3. Implementasikan K-Means sederhana dalam PHP:
   - K = jumlah cluster (hitung dari: total_sekolah_belum_terlayani / 200, minimum 1)
   - Inisialisasi centroid secara acak dari titik sekolah
   - Iterasi maksimal 20 kali atau sampai centroid tidak bergerak
4. Untuk setiap centroid, hitung jumlah sekolah dalam radius 5km
5. Jika ≥ 3 sekolah → centroid ini menjadi titik Rekomendasi Sistem
6. Return array titik rekomendasi dengan daftar sekolah yang akan dilayani

**Geocoding (POST /map/geocode)**

Proxy ke Nominatim:
```
GET https://nominatim.openstreetmap.org/search?q={alamat}&format=json&limit=5&countrycodes=id
```
Return top 5 hasil. Tambahkan `User-Agent` header (wajib untuk Nominatim).

**Routing (POST /map/route-check)**

Proxy ke OSRM:
```
GET http://router.project-osrm.org/route/v1/driving/{lng_a},{lat_a};{lng_b},{lat_b}?overview=false
```
Return `duration_seconds` dan `distance_meters`. Konversi ke menit untuk response.

Untuk produksi, ganti base URL ke self-hosted OSRM via environment variable `OSRM_BASE_URL`.

---

### CATATAN PENTING
- Semua route /api/super-admin/* dilindungi middleware role_type = super_admin
- Superadmin tidak boleh mengakses /api/admin-sppg/* sama sekali
- Haversine formula: implementasikan sebagai helper PHP murni, tidak perlu library
- OSRM: gunakan env variable OSRM_BASE_URL (default: http://router.project-osrm.org)
- Untuk development, public OSRM sudah cukup. Untuk produksi, dokumentasikan cara setup self-hosted OSRM

### OUTPUT YANG DIHARAPKAN
- Migration: `create_sppg_drafts_table`, `create_sppg_draft_partners_table`
- Controller: `SuperAdmin/DashboardController.php`
- Controller: `SuperAdmin/SppgController.php`
- Controller: `SuperAdmin/SppgSubmissionController.php`
- Controller: `SuperAdmin/MapController.php`
- Service: `SuperAdmin/MapService.php` (Haversine, OSRM, K-Means, validasi titik)
- Route file: `routes/api_superadmin.php`
```

---

---

## 📋 Urutan Pengerjaan yang Disarankan

```
Tahap 1 (Auth)
    ↓ selesai
Tahap 2 (Stok)
    ↓ selesai
Tahap 3 (Superadmin)
```

Tidak ada dependensi lintas Tahap 2 ↔ Tahap 3, tapi keduanya membutuhkan Tahap 1 selesai terlebih dahulu (khususnya: tabel permissions dengan 5 permission stok, dan alur auth yang sudah benar).

---

*File ini dibuat bersamaan dengan PRD_COMS_MBG_Bagian1_Auth.docx, PRD_COMS_MBG_Bagian2_Stok.docx, dan PRD_COMS_MBG_Bagian3_Superadmin.docx.*  
*Versi: 1.1 — 3 Juni 2026*
