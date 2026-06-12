# 📋 DOKUMENTASI SISTEM — COMS MBG (Coffee Management System — Makan Bergizi Gratis)

> **Versi:** 1.0  
> **Framework:** Laravel 11 (PHP)  
> **Autentikasi:** Laravel Sanctum (Cookie-based SPA)  
> **Database:** SQLite (dev) / MySQL (prod)  
> **Dibuat:** 2026  

---

## 📌 Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Arsitektur Sistem](#2-arsitektur-sistem)
3. [Alur Autentikasi & Keamanan](#3-alur-autentikasi--keamanan)
4. [Sistem RBAC (Role-Based Access Control)](#4-sistem-rbac-role-based-access-control)
5. [Struktur Database & Entitas](#5-struktur-database--entitas)
6. [Alur Proses per Modul](#6-alur-proses-per-modul)
7. [Daftar Endpoint API](#7-daftar-endpoint-api)
8. [Middleware Stack](#8-middleware-stack)
9. [Diagram Relasi Antar Entitas](#9-diagram-relasi-antar-entitas)

---

## 1. Gambaran Umum Sistem

**COMS MBG** adalah sistem manajemen berbasis REST API untuk mengelola program **Makan Bergizi Gratis** di sekolah-sekolah. Sistem ini mengelola:

- **SPPG** (Satuan Pelayanan Pangan Gizi) — unit dapur/pengolahan pangan
- **Karyawan** dengan role & permission berbeda per SPPG
- **Resep & Menu** — perencanaan gizi mingguan
- **Distribusi** — penjadwalan & pelacakan pengiriman makanan ke sekolah
- **Mitra Sekolah (Partner)** — data sekolah penerima
- **Laporan Keuangan**
- **Konten Landing Page** publik

---

## 2. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                       CLIENT (SPA / Mobile)                  │
└───────────────────────────┬─────────────────────────────────┘
                            │  HTTP Requests
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   LARAVEL API SERVER                         │
│                                                             │
│  ┌──────────┐   ┌───────────────┐   ┌───────────────────┐  │
│  │  Routes  │──▶│  Middleware   │──▶│   Controllers     │  │
│  │ api.php  │   │  Stack        │   │   (per modul)     │  │
│  └──────────┘   └───────────────┘   └────────┬──────────┘  │
│                                              │              │
│                 ┌───────────────────────────┐│              │
│                 │       Services Layer       ││              │
│                 └───────────────────────────┘│              │
│                                              ▼              │
│                 ┌───────────────────────────────────────┐   │
│                 │        Eloquent ORM (Models)           │   │
│                 └───────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      DATABASE                                │
│              SQLite (dev) / MySQL (prod)                    │
└─────────────────────────────────────────────────────────────┘
```

### Struktur Direktori Utama

```
app/
├── Constants/
│   └── RoleConstant.php          # Konstanta slug role
├── Http/
│   ├── Controllers/
│   │   └── API/
│   │       ├── Auth/             # Login, Logout, User
│   │       ├── SuperAdmin/       # SPPG, School, Employee, Report
│   │       ├── AdminSPPG/        # Dashboard, Employee, School,
│   │       │                     # Ingredient, Recipe, Menu,
│   │       │                     # Distribution, Partner, Report,
│   │       │                     # Role, Permission
│   │       ├── Distribution/     # DeliverySchedule, History, Map
│   │       └── Public/           # Menu, Map, Feedback, Rating
│   ├── Middleware/
│   │   ├── CheckRole.php         # Gate role
│   │   ├── CheckPermission.php   # Gate permission
│   │   └── ScopeBySppg.php       # Scope data per SPPG
│   └── Requests/                 # Form Request Validation
├── Models/                       # 22 Eloquent Models
├── Services/                     # Business logic layer
│   ├── AI/, Employee/, GIS/
│   ├── Partner/, Public/
│   ├── Recommendation/, SPPG/, School/
└── Policies/                     # Authorization policies

routes/
├── api.php                       # Entry point (include semua)
├── api_auth.php                  # /api/auth/*
├── api_superadmin.php            # /api/super-admin/*
├── api_adminsppg.php             # /api/admin-sppg/*
├── api_public.php                # /api/public/*
└── distribution.php              # /api/distribution/*
```

---

## 3. Alur Autentikasi & Keamanan

### 3.1 Flow Login (Cookie-based Sanctum SPA)

```
Client                          Server
  │                               │
  │  1. GET /sanctum/csrf-cookie  │
  │──────────────────────────────▶│
  │◀──────────────────────────────│ Set-Cookie: XSRF-TOKEN
  │                               │
  │  2. POST /api/auth/login      │
  │     { email, password }       │
  │──────────────────────────────▶│
  │                               │  a. Validasi kredensial
  │                               │  b. Buat session (web guard)
  │◀──────────────────────────────│ Set-Cookie: session
  │     { user, role, sppg }      │
  │                               │
  │  3. GET /api/auth/user        │
  │     Cookie: session           │
  │──────────────────────────────▶│
  │◀──────────────────────────────│ { id, name, email, role_name, sppg_id }
  │                               │
  │  4. POST /api/auth/logout     │
  │──────────────────────────────▶│ Hapus session
  │◀──────────────────────────────│ { message: "Logged out" }
```

### 3.2 Lapisan Keamanan (Security Layers)

Setiap request yang masuk melewati **3 lapisan keamanan** secara berurutan:

```
HTTP Request
     │
     ▼
┌─────────────────────────────────────────┐
│  Layer 1: AUTENTIKASI                   │
│  Middleware: auth:sanctum               │
│  Cek: Apakah user sudah login?         │
│  ❌ Gagal → 401 Unauthenticated         │
└───────────────────┬─────────────────────┘
                    │ ✅ User terautentikasi
                    ▼
┌─────────────────────────────────────────┐
│  Layer 2: OTORISASI ROLE                │
│  Middleware: CheckRole (role:xxx)       │
│  Cek: Apakah user punya role yang      │
│       sesuai?                           │
│  SuperAdmin → bypass otomatis          │
│  ❌ Gagal → 403 Role tidak sesuai      │
└───────────────────┬─────────────────────┘
                    │ ✅ Role sesuai
                    ▼
┌─────────────────────────────────────────┐
│  Layer 3: OTORISASI PERMISSION          │
│  Middleware: CheckPermission            │
│  Cek: Apakah role user punya           │
│       permission spesifik?             │
│  SuperAdmin → bypass otomatis          │
│  ❌ Gagal → 403 Permission tidak cukup │
└───────────────────┬─────────────────────┘
                    │ ✅ Semua lolos
                    ▼
              Controller Action
```

### 3.3 Logika isSuperAdmin — Bypass Semua Gate

`SuperAdmin` memiliki hak akses tanpa batas:

```php
// User::isSuperAdmin()
return $this->role_type === 'super_admin';

// CheckRole::handle() → baris 31-33
if ($request->user()->isSuperAdmin()) {
    return $next($request);  // bypass semua role check
}

// CheckPermission::handle()
if ($user->isSuperAdmin()) return true;  // bypass semua permission check
```

### 3.4 Alur Pengecekan Permission

```
User
 │
 └──▶ employee (record karyawan)
       │
       └──▶ role (role yang di-assign)
             │
             └──▶ permissions (via pivot role_permission)
                   │
                   └──▶ cek slug permission
                         contoh: "ingredients.read", "distribution.update"
```

### 3.5 SPPG Scope — Isolasi Data per SPPG

Middleware `ScopeBySppg` memastikan pengguna **hanya bisa akses data SPPG miliknya**:

```
Request masuk ke /api/sppg/{sppg}/employees
          │
          ▼
ScopeBySppg::handle()
   ├── SuperAdmin? → bypass, lanjut
   └── Ambil sppg_id dari route parameter
         ├── user->ownsSppg(sppgId)?
         │     ✅ Ya → inject _sppg_id ke request → lanjut ke controller
         │     ❌ Tidak → 403 "Anda tidak punya akses ke SPPG ini"
```

---

## 4. Sistem RBAC (Role-Based Access Control)

### 4.1 Dua Jenis User

| Tipe | `role_type` | Employee Record | Deskripsi |
|------|------------|-----------------|-----------|
| Super Admin | `super_admin` | ❌ Tidak perlu | Akses penuh ke semua SPPG & fitur |
| SPPG User | `sppg_user` | ✅ Wajib ada | Terikat ke 1 SPPG, role dari `employees.role_id` |

### 4.2 Daftar Role SPPG

| Role | Slug | Deskripsi |
|------|------|-----------|
| SPPG Admin | `admin-sppg` | Akses penuh semua modul |
| Pemilik | `pemilik` | Akses penuh semua modul + keuangan |
| Ahli Gizi | `ahli-gizi` | Mengelola bahan baku, resep, menu |
| Admin Logistik | `admin-logistik` | Mengelola distribusi & pengiriman |
| Kurir | `kurir` | Melakukan pengiriman & update status |
| Manajer | `manajer` | Manajer operasional, akses baca mayoritas modul |

### 4.3 Matriks Permission per Role

| Permission | Super Admin | Admin SPPG / Pemilik | Ahli Gizi | Admin Logistik | Kurir | Manajer |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| `dashboard.read` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `employee.create/update/delete` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `employee.read` | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| `ingredients.*` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `recipes.*` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `menus.*` | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `menus.read` | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| `distribution.read` | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ |
| `distribution.create/delete` | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| `distribution.update` | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |
| `partner.read` | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ |
| `partner.create/update/delete` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `partner.update` | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| `report.read` | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| `report.create` | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| `finance.*` | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |

### 4.4 Role yang Dapat Akses SPPG Management Routes

Role yang termasuk `SPPG_MANAGEMENT_ROLES` (dapat akses employee, school, roles):
```
pemilik | manajer | admin-sppg
```

---

## 5. Struktur Database & Entitas

### 5.1 Tabel `users`

> Akun login sistem

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | Auto increment |
| `name` | VARCHAR | Nama lengkap |
| `email` | VARCHAR UNIQUE | Email login |
| `password` | VARCHAR | Bcrypt hash |
| `phone` | VARCHAR | Nomor HP |
| `profile_picture` | VARCHAR nullable | Path foto profil |
| `is_active` | BOOLEAN | Status aktif akun |
| `role_type` | VARCHAR | `super_admin` \| `sppg_user` |
| `sppg_id` | FK → sppgs | Nullable, SPPG yang dimiliki |
| `email_verified_at` | TIMESTAMP | Waktu verifikasi email |
| `remember_token` | VARCHAR | Token remember me |
| `created_at` / `updated_at` | TIMESTAMP | Timestamps |

**Relasi:**
- `hasOne` → `employees` (via `user_id`)
- `belongsTo` → `s_p_p_g_s` (via `sppg_id`)

---

### 5.2 Tabel `s_p_p_g_s`

> Unit Satuan Pelayanan Pangan Gizi — dapur/pengolah makanan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | Auto increment |
| `name` | VARCHAR | Nama SPPG |
| `address` | TEXT | Alamat lengkap |
| `district` | VARCHAR | Kecamatan |
| `city` | VARCHAR | Kota/Kabupaten |
| `province` | VARCHAR | Provinsi |
| `latitude` | FLOAT | Koordinat latitude |
| `longitude` | FLOAT | Koordinat longitude |
| `capacity` | INT | Kapasitas maks sekolah |
| `phone` | VARCHAR | Telepon |
| `email` | VARCHAR | Email |
| `status` | VARCHAR | `active` \| `inactive` |
| `pemilik_id` | FK → users | User pemilik SPPG |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | Timestamps |

**Relasi:**
- `belongsTo` → `users` (via `pemilik_id`)
- `hasMany` → `schools`
- `hasMany` → `employees`
- `hasMany` → `roles`

**Fitur Khusus:**
- `scopeNearby()` — query sekolah terdekat dengan formula Haversine
- `getIsOvercapacityAttribute()` — cek apakah melebihi kapasitas

---

### 5.3 Tabel `schools`

> Sekolah penerima program MBG yang dilayani oleh SPPG

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | UUID PK | HasUuids |
| `nama` | VARCHAR | Nama sekolah |
| `alamat` | TEXT | Alamat lengkap |
| `latitude` | FLOAT | Koordinat latitude |
| `longitude` | FLOAT | Koordinat longitude |
| `jumlah_siswa` | INT | Jumlah siswa |
| `jenjang` | VARCHAR | `SD` \| `SMP` \| `SMA` \| `SMK` |
| `kecamatan` | VARCHAR | Kecamatan |
| `kota` | VARCHAR | Kota/Kabupaten |
| `provinsi` | VARCHAR | Provinsi |
| `telepon` | VARCHAR | Nomor telepon |
| `kepala_sekolah` | VARCHAR | Nama kepala sekolah |
| `sppg_id` | FK → sppgs | SPPG yang melayani |
| `status` | VARCHAR | Status sekolah |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | Timestamps |

**Relasi:**
- `belongsTo` → `s_p_p_g_s`

**Fitur Khusus:**
- `distanceToSppg()` — hitung jarak (km) ke SPPG dengan Haversine

---

### 5.4 Tabel `s_p_p_g_schools` (Pivot)

> Relasi many-to-many antara SPPG dan School (untuk assignment)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `sppg_id` | FK → sppgs | |
| `school_id` | FK → schools | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.5 Tabel `employees`

> Karyawan SPPG — memegang role sistem

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `sppg_id` | FK → sppgs | SPPG tempat bertugas |
| `user_id` | FK → users nullable | Akun login (opsional) |
| `role_id` | FK → roles nullable | Role sistem RBAC |
| `name` | VARCHAR | Nama karyawan |
| `nik` | VARCHAR UNIQUE nullable | NIK KTP |
| `position` | VARCHAR | Jabatan struktural |
| `phone` | VARCHAR | Nomor HP |
| `address` | TEXT | Alamat |
| `photo` | VARCHAR | Path foto |
| `joined_at` | DATE | Tanggal bergabung |
| `base_salary` | DECIMAL(10,2) | Gaji pokok (hidden) |
| `created_at` / `updated_at` | TIMESTAMP | |

**Jabatan Struktural (position):**
`pemilik` | `manajer` | `ahli_gizi` | `admin_logistik` | `kurir` | `karyawan_operasional`

**Relasi:**
- `belongsTo` → `users`
- `belongsTo` → `s_p_p_g_s`
- `belongsTo` → `roles`

**Catatan Penting:** `position` = label jabatan struktural (HR), `role` = akses sistem (RBAC). Keduanya berbeda.

---

### 5.6 Tabel `roles`

> Role sistem RBAC per SPPG

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `name` | VARCHAR | Nama role |
| `slug` | VARCHAR UNIQUE | Identifier (`admin-sppg`, `kurir`, dll.) |
| `description` | TEXT | Deskripsi role |
| `sppg_id` | FK → sppgs nullable | Null = global role |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | |

**Relasi:**
- `belongsToMany` → `permissions` (via `role_permission`)
- `hasMany` → `employees`
- `belongsTo` → `s_p_p_g_s`

**Fitur:** Auto-generate slug dari nama saat create/update.

---

### 5.7 Tabel `permissions`

> Daftar permission atomik sistem

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `name` | VARCHAR | Nama human-readable |
| `slug` | VARCHAR UNIQUE | Identifier (`ingredients.read`, `distribution.create`, dll.) |
| `module` | VARCHAR | Modul utama (`nutrition`, `distribution`, dll.) |
| `feature` | VARCHAR | Fitur spesifik (`ingredients`, `recipes`, `menus`, dll.) |
| `action` | VARCHAR | Aksi (`create` \| `read` \| `update` \| `delete`) |
| `created_at` / `updated_at` | TIMESTAMP | |

**Format Slug:** `{feature}.{action}` atau `{module}.{action}`

**Daftar Modul Permission:**
| Modul | Fitur |
|-------|-------|
| `dashboard` | `dashboard` |
| `employee` | `employee` |
| `school` | `school` |
| `sppg` | `sppg` |
| `nutrition` | `nutrition`, `ingredients`, `recipes`, `menus` |
| `distribution` | `distribution` |
| `finance` | `finance` |
| `partner` | `partner` |
| `report` | `report` |

---

### 5.8 Tabel `role_permission` (Pivot)

> Relasi many-to-many antara Role dan Permission

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `role_id` | FK → roles | |
| `permission_id` | FK → permissions | |

---

### 5.9 Tabel `ingredients`

> Bahan baku makanan dengan data nilai gizi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `name` | VARCHAR | Nama bahan |
| `carbohydrate` | FLOAT | Karbohidrat (gram per serving) |
| `protein` | FLOAT | Protein (gram per serving) |
| `calorie` | FLOAT | Kalori (kcal per serving) |
| `fat` | FLOAT | Lemak (gram per serving) |
| `serving_weight` | FLOAT | Berat per serving (gram) |
| `description` | TEXT | Deskripsi bahan |
| `created_at` / `updated_at` | TIMESTAMP | |

**Relasi:**
- `hasMany` → `recipe_ingredients`
- `belongsToMany` → `recipes` (via `recipe_ingredients`)

**Fitur:** `calculateNutritionFor(float $weightGram)` — hitung nutrisi proporsional

---

### 5.10 Tabel `recipes`

> Resep makanan dengan target & realisasi nilai gizi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `name` | VARCHAR | Nama resep |
| `description` | TEXT | Deskripsi |
| `target_calorie` | FLOAT | Target kalori |
| `target_protein` | FLOAT | Target protein |
| `target_carbohydrate` | FLOAT | Target karbohidrat |
| `target_fat` | FLOAT | Target lemak |
| `total_calorie` | FLOAT | Total kalori aktual |
| `total_protein` | FLOAT | Total protein aktual |
| `total_carbohydrate` | FLOAT | Total karbohidrat aktual |
| `total_fat` | FLOAT | Total lemak aktual |
| `total_weight` | FLOAT | Total berat (gram) |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | |

**Relasi:**
- `hasMany` → `recipe_ingredients`
- `belongsToMany` → `ingredients` (via `recipe_ingredients`)
- `hasMany` → `menu_items`

---

### 5.11 Tabel `recipe_ingredients` (Pivot Detail)

> Bahan-bahan dalam sebuah resep beserta kontribusi gizinya

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `recipe_id` | FK → recipes | |
| `ingredient_id` | FK → ingredients | |
| `weight_used` | FLOAT | Berat bahan yang dipakai (gram) |
| `calorie_contribution` | FLOAT | Kontribusi kalori |
| `protein_contribution` | FLOAT | Kontribusi protein |
| `carbohydrate_contribution` | FLOAT | Kontribusi karbohidrat |
| `fat_contribution` | FLOAT | Kontribusi lemak |
| `order` | INT | Urutan tampil |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.12 Tabel `menus`

> Perencanaan menu mingguan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `name` | VARCHAR | Nama menu |
| `week_start` | DATE | Tanggal mulai minggu |
| `week_end` | DATE | Tanggal akhir minggu |
| `status` | VARCHAR | Status dinamis (lihat di bawah) |
| `notes` | TEXT | Catatan |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | |

**Status Menu (dihitung otomatis berdasarkan tanggal):**

| Status | Kondisi | Label |
|--------|---------|-------|
| `published` | H-0 hingga H-6 (minggu berjalan) | Menu Ditampilkan |
| `scheduled` | H-7 hingga H-13 | Menu Dijadwalkan |
| `planned` | H-14 ke atas | Menu Direncanakan |
| `archived` | Sudah lewat | Menu Selesai |

**Relasi:**
- `hasMany` → `menu_items`

---

### 5.13 Tabel `menu_items`

> Item resep per hari dalam sebuah menu

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `menu_id` | FK → menus | |
| `recipe_id` | FK → recipes | |
| `day_of_week` | INT | 1=Senin s.d 7=Minggu |
| `order` | INT | Urutan item dalam sehari |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.14 Tabel `partners`

> Data sekolah mitra (dari import data eksternal)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | UUID PK | HasUuids |
| `nama_sekolah` | VARCHAR | Nama sekolah |
| `npsn` | VARCHAR | Nomor Pokok Sekolah Nasional |
| `bentuk` | VARCHAR | Bentuk sekolah (SD, SMP, dll.) |
| `status` | VARCHAR | `negeri` \| `swasta` |
| `alamat` | TEXT | Alamat |
| `kecamatan` | VARCHAR | Kecamatan |
| `kabupaten_kota` | VARCHAR | Kabupaten/Kota |
| `latitude` | FLOAT | Koordinat latitude |
| `longitude` | FLOAT | Koordinat longitude |
| `jumlah_porsi` | INT | Jumlah porsi yang diterima |
| `sppg_id` | FK → sppgs | SPPG yang melayani |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | |

**Relasi:**
- `belongsTo` → `s_p_p_g_s`

---

### 5.15 Tabel `delivery_schedules`

> Jadwal pengiriman makanan dari SPPG ke sekolah

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `courier_id` | FK → employees | Kurir yang bertugas |
| `school_id` | FK → schools | Sekolah tujuan |
| `assigned_by` | FK → users | Yang memberi tugas |
| `submitted_by` | FK → users | Yang submit task ke kurir |
| `vehicle_type` | VARCHAR | `motorcycle`/`car`/`van`/`truck` |
| `vehicle_plate` | VARCHAR | Nomor plat kendaraan |
| `status` | VARCHAR | Status pengiriman (lihat di bawah) |
| `scheduled_at` | DATETIME | Waktu dijadwalkan |
| `departed_at` | DATETIME | Waktu berangkat |
| `arrived_at` | DATETIME | Waktu tiba |
| `delivery_notes` | TEXT | Catatan pengiriman |
| `rejection_reason` | TEXT | Alasan penolakan |
| `rejection_photo_path` | VARCHAR | Foto bukti penolakan |
| `rejected_at` | DATETIME | Waktu penolakan |
| `proof_photo_path` | VARCHAR | Foto bukti pengiriman |
| `proof_submitted_at` | DATETIME | Waktu submit bukti |
| `confirmed_by` | FK → users | Yang konfirmasi |
| `confirmed_at` | DATETIME | Waktu konfirmasi |
| `confirmation_notes` | TEXT | Catatan konfirmasi |
| `route_snapshot` | JSON | Snapshot rute perjalanan |
| `deleted_at` | TIMESTAMP | SoftDelete |
| `created_at` / `updated_at` | TIMESTAMP | |

**Status Pengiriman:**

| Constant | Nilai | Deskripsi |
|----------|-------|-----------|
| `STATUS_IN_ORDER` | `in_order` | Baru dibuat, menunggu |
| `STATUS_ACCEPTED` | `accepted` | Kurir menerima tugas |
| `STATUS_REJECTED` | `rejected` | Kurir menolak |
| `STATUS_DELIVERING` | `delivering` | Sedang dalam perjalanan |
| `STATUS_DELIVERED` | `delivered` | Sudah terkirim, menunggu konfirmasi |
| `STATUS_CONFIRMED` | `confirmed` | Dikonfirmasi admin logistik |
| `STATUS_REVISION_REQUIRED` | `revision_required` | Diminta revisi |

---

### 5.16 Tabel `courier_locations`

> Rekaman posisi GPS kurir secara real-time

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `delivery_schedule_id` | FK → delivery_schedules | |
| `courier_id` | FK → employees | |
| `latitude` | FLOAT | Posisi latitude |
| `longitude` | FLOAT | Posisi longitude |
| `speed_kmh` | FLOAT | Kecepatan (km/jam) |
| `heading_degrees` | FLOAT | Arah hadap (derajat) |
| `accuracy_meters` | FLOAT | Akurasi GPS (meter) |
| `recorded_at` | DATETIME | Waktu perekaman |

> **Catatan:** Tabel ini tidak memiliki `created_at`/`updated_at` untuk efisiensi high-frequency writes.

---

### 5.17 Tabel `delivery_histories`

> Rekaman permanen pengiriman yang sudah selesai (immutable log)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `delivery_schedule_id` | FK → delivery_schedules | Referensi jadwal asal |
| `courier_id` | FK → employees | |
| `school_id` | FK → schools | |
| `courier_name` | VARCHAR | Snapshot nama kurir |
| `school_name` | VARCHAR | Snapshot nama sekolah |
| `school_address` | TEXT | Snapshot alamat sekolah |
| `vehicle_type` | VARCHAR | Tipe kendaraan |
| `vehicle_plate` | VARCHAR | Nomor plat |
| `departed_at` | DATETIME | Waktu berangkat |
| `arrived_at` | DATETIME | Waktu tiba |
| `proof_photo_path` | VARCHAR | Foto bukti |
| `route_snapshot` | JSON | Rute lengkap |
| `distance_km` | FLOAT | Jarak tempuh (km) |
| `confirmed_by` | FK → users | |
| `confirmed_at` | DATETIME | |
| `notes` | TEXT | Catatan |
| `created_at` / `updated_at` | TIMESTAMP | |

**Fitur:** `getDurationMinutesAttribute()` — hitung durasi pengiriman dalam menit

---

### 5.18 Tabel `financial_reports`

> Laporan keuangan SPPG

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| *(kolom laporan keuangan)* | | Sesuai implementasi controller |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.19 Tabel `feedback`

> Umpan balik dari publik/siswa

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `content` | TEXT | Isi feedback |
| `name` | VARCHAR | Nama pengirim |
| `school_id` | FK | Sekolah terkait |
| *(kolom tambahan dari migrasi add_columns)* | | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.20 Tabel `ratings`

> Penilaian dari publik

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| *(kolom rating)* | | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.21 Tabel `recommendations`

> Rekomendasi yang di-generate sistem

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| *(kolom rekomendasi)* | | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.22 Tabel `s_p_p_g_submissions`

> Pengajuan SPPG baru dari publik

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| *(kolom submission)* | | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.23 Tabel `public_users`

> Akun pengguna publik (bukan karyawan SPPG)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| *(kolom user publik)* | | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.24 Tabel `landing_contents`

> Konten halaman landing page publik

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `section_name` | VARCHAR | Nama seksi halaman |
| `title` | VARCHAR | Judul konten |
| `description` | TEXT | Isi deskripsi |
| `image_path` | VARCHAR | Path gambar |
| `is_active` | BOOLEAN | Status aktif |
| `created_at` / `updated_at` | TIMESTAMP | |

---

### 5.25 Tabel `personal_access_tokens`

> Token Sanctum untuk API token auth (jika dipakai)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT PK | |
| `tokenable_type` | VARCHAR | Morphable type |
| `tokenable_id` | BIGINT | Morphable ID |
| `name` | VARCHAR | Nama token |
| `token` | VARCHAR | Hash token |
| `abilities` | JSON | Kemampuan token |
| `last_used_at` | TIMESTAMP | |
| `expires_at` | TIMESTAMP | |
| `created_at` / `updated_at` | TIMESTAMP | |

---

## 6. Alur Proses per Modul

### 6.1 Modul Autentikasi

```
[POST /api/auth/login]
  → Validasi email & password
  → Auth::attempt()
  → Buat session Sanctum
  → Return: { user, role_name, sppg_id, permissions }

[GET /api/auth/user]  ← Butuh: auth:sanctum
  → Ambil user dari session
  → Return: { id, name, email, role_name, sppg }

[POST /api/auth/logout]  ← Butuh: auth:sanctum
  → Invalidate session
  → Return: { message: "Berhasil logout" }
```

### 6.2 Modul SPPG (Super Admin)

```
GET    /api/super-admin/sppg              → List semua SPPG
POST   /api/super-admin/sppg              → Buat SPPG baru
GET    /api/super-admin/sppg/{id}         → Detail SPPG
PUT    /api/super-admin/sppg/{id}         → Update SPPG
DELETE /api/super-admin/sppg/{id}         → Hapus SPPG (SoftDelete)
GET    /api/super-admin/sppg/capacity-overview → Overview kapasitas
POST   /api/super-admin/sppg/{id}/assign-school  → Assign sekolah
DELETE /api/super-admin/sppg/{id}/schools/{schoolId} → Lepas sekolah
```

### 6.3 Modul Employee Management

```
[Admin SPPG mengakses]
GET    /api/admin-sppg/employees              → List karyawan SPPG sendiri
POST   /api/admin-sppg/employees              → Tambah karyawan baru
GET    /api/admin-sppg/employees/{id}         → Detail karyawan
PUT    /api/admin-sppg/employees/{id}         → Update karyawan
DELETE /api/admin-sppg/employees/{id}         → Hapus karyawan
GET    /api/admin-sppg/employees/{id}/assign-role → Form assign role
POST   /api/admin-sppg/employees/{id}/assign-role → Assign role ke karyawan

Alur Assign Akun + Role ke Karyawan:
  1. Karyawan sudah ada di tabel employees (tanpa user_id)
  2. Admin SPPG klik "Assign Role"
  3. Buat User baru (jika belum punya akun)
  4. Set employees.user_id = user.id
  5. Set employees.role_id = role.id
  6. Karyawan kini bisa login dan punya permission sesuai role
```

### 6.4 Modul Gizi (Nutrition)

```
Alur Perencanaan Menu:

1. [Ahli Gizi] Input Bahan Baku
   POST /api/admin-sppg/nutrition/ingredients
   → Simpan nama, nilai gizi per serving ke tabel ingredients

2. [Ahli Gizi] Buat Resep
   POST /api/admin-sppg/nutrition/recipes
   → Input: nama, target_calorie/protein/fat/carbo
   → Input: array bahan (ingredient_id, weight_used)
   → Sistem hitung kontribusi gizi otomatis via RecipeService
   → Simpan ke recipes + recipe_ingredients

3. [Ahli Gizi] Rencanakan Menu Mingguan
   POST /api/admin-sppg/nutrition/menus
   → Input: week_start, week_end
   → Input: menu_items per hari (day_of_week, recipe_id)
   → Status dihitung otomatis berdasarkan tanggal

4. [Ahli Gizi] Publish Menu
   PATCH /api/admin-sppg/nutrition/menus/{id}/publish
   → Update status menu
```

### 6.5 Modul Distribusi — Alur Pengiriman

```
┌────────────────────────────────────────────────────────────┐
│                  SIKLUS HIDUP PENGIRIMAN                    │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  [Admin Logistik]                                          │
│  POST /distribution/schedules                              │
│  → Buat jadwal: pilih kurir, sekolah, kendaraan           │
│  → Status: in_order                                         │
│                         │                                   │
│                         ▼                                   │
│  [Admin SPPG]                                              │
│  POST /distribution/schedules/{id}/submit                  │
│  → Submit tugas ke kurir                                   │
│  → Status: in_order (menunggu respon kurir)                │
│                         │                                   │
│           ┌─────────────┴──────────────┐                   │
│           ▼                            ▼                    │
│  [Kurir] TERIMA                [Kurir] TOLAK               │
│  POST /{id}/accept             POST /{id}/reject            │
│  → Status: accepted            → Status: rejected           │
│           │                    → Masukkan alasan+foto       │
│           ▼                                                  │
│  [Kurir] Berangkat                                          │
│  → Update GPS: POST /map/location/{schedule}               │
│  → Status: delivering (otomatis/manual)                     │
│           │                                                  │
│           ▼                                                  │
│  [Kurir] Submit Bukti                                       │
│  POST /{id}/proof                                           │
│  → Upload foto bukti pengiriman                            │
│  → Status: delivered                                        │
│           │                                                  │
│  ┌────────┴────────────────┐                               │
│  ▼                         ▼                               │
│  [Admin] KONFIRMASI  [Admin] MINTA REVISI                  │
│  POST /{id}/confirm   POST /{id}/revision                  │
│  → Status: confirmed  → Status: revision_required          │
│  → Buat record di    → Kurir re-submit                     │
│    delivery_histories POST /{id}/proof/resubmit            │
└────────────────────────────────────────────────────────────┘
```

### 6.6 Modul Peta & GIS

```
GET  /api/distribution/map/active-couriers  → Posisi kurir aktif
GET  /api/distribution/map/depot            → Lokasi depot SPPG
GET  /api/distribution/map/trail/{schedule} → Jejak perjalanan kurir
POST /api/distribution/map/location/{id}   → Kurir ping lokasi GPS
POST /api/distribution/map/optimize-route  → Optimasi rute pengiriman

GET  /api/public/maps/sppg                 → Peta lokasi semua SPPG (publik)
GET  /api/public/maps/monitoring           → Peta monitoring distribusi (publik)
GET  /api/admin-sppg/maps/distribution     → Peta distribusi (admin)
```

### 6.7 Modul Publik (Tanpa Auth)

```
GET  /api/public/menus                     → Menu aktif yang dipublikasi
GET  /api/public/maps/sppg                 → Lokasi SPPG terdekat
GET  /api/public/maps/monitoring           → Monitoring distribusi
GET  /api/public/recommendation/generate   → Generate rekomendasi SPPG
GET  /api/public/dashboard                 → Dashboard publik
POST /api/public/feedback                  → Kirim feedback
POST /api/public/rating                    → Beri rating
POST/GET/PUT/DELETE /api/public/sppg-submissions → CRUD pengajuan SPPG baru
```

---

## 7. Daftar Endpoint API

### Auth Routes — `/api/auth`

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|------|-----------|
| POST | `/api/auth/login` | ❌ Public | Login user |
| GET | `/api/auth/user` | ✅ sanctum | Data user aktif |
| POST | `/api/auth/logout` | ✅ sanctum | Logout |

---

### Super Admin Routes — `/api/super-admin`

> Middleware: `auth:sanctum` + `role:super_admin`

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/super-admin/sppg` | List SPPG |
| POST | `/super-admin/sppg` | Buat SPPG |
| GET | `/super-admin/sppg/capacity-overview` | Overview kapasitas |
| GET | `/super-admin/sppg/{id}` | Detail SPPG |
| PUT | `/super-admin/sppg/{id}` | Update SPPG |
| DELETE | `/super-admin/sppg/{id}` | Hapus SPPG |
| POST | `/super-admin/sppg/{id}/assign-school` | Assign sekolah |
| DELETE | `/super-admin/sppg/{id}/schools/{schoolId}` | Lepas sekolah |
| GET | `/super-admin/sppg/{id}/employees` | List karyawan |
| POST | `/super-admin/sppg/{id}/employees` | Tambah karyawan |
| GET | `/super-admin/sppg/{id}/employees/{id}` | Detail karyawan |
| PUT | `/super-admin/sppg/{id}/employees/{id}` | Update karyawan |
| DELETE | `/super-admin/sppg/{id}/employees/{id}` | Hapus karyawan |
| GET/POST/PUT/DELETE | `/super-admin/schools` | CRUD Sekolah |
| GET/POST/PUT/DELETE | `/super-admin/financial-reports` | CRUD Laporan Keuangan |

---

### Admin SPPG Routes — `/api/admin-sppg`

> Middleware: `auth:sanctum` + permission per endpoint

| Method | Endpoint | Permission | Deskripsi |
|--------|----------|-----------|-----------|
| GET | `/admin-sppg/dashboard` | `dashboard.read` | Dashboard SPPG |
| GET | `/admin-sppg/employees` | `employee.read` | List karyawan |
| GET | `/admin-sppg/employees/{id}` | `employee.read` | Detail karyawan |
| POST | `/admin-sppg/employees` | `employee.create` | Tambah karyawan |
| PUT/PATCH | `/admin-sppg/employees/{id}` | `employee.update` | Update karyawan |
| DELETE | `/admin-sppg/employees/{id}` | `employee.delete` | Hapus karyawan |
| GET | `/admin-sppg/employees/{id}/assign-role` | `employee.read` | Form assign role |
| POST | `/admin-sppg/employees/{id}/assign-role` | `employee.update` | Assign role |
| GET/POST/PUT/DELETE | `/admin-sppg/schools` | role: pemilik\|manajer\|admin-sppg | CRUD Sekolah |
| GET/POST/PUT/DELETE | `/admin-sppg/roles` | role: pemilik\|manajer\|admin-sppg | CRUD Role |
| GET | `/admin-sppg/permissions` | role: pemilik\|manajer\|admin-sppg | List Permission |
| GET | `/admin-sppg/partners` | `partner.read` | List mitra |
| POST | `/admin-sppg/partners` | `partner.create` | Tambah mitra |
| GET | `/admin-sppg/partners/{id}` | `partner.read` | Detail mitra |
| PUT/PATCH | `/admin-sppg/partners/{id}` | `partner.update` | Update mitra |
| DELETE | `/admin-sppg/partners/{id}` | `partner.delete` | Hapus mitra |
| GET | `/admin-sppg/partners/summary` | `partner.read` | Ringkasan mitra |
| POST | `/admin-sppg/partners/import` | `partner.create` | Import mitra (CSV) |
| GET | `/admin-sppg/nutrition/ingredients` | `ingredients.read` | List bahan |
| POST | `/admin-sppg/nutrition/ingredients` | `ingredients.create` | Tambah bahan |
| GET | `/admin-sppg/nutrition/ingredients/{id}` | `ingredients.read` | Detail bahan |
| PUT/PATCH | `/admin-sppg/nutrition/ingredients/{id}` | `ingredients.update` | Update bahan |
| DELETE | `/admin-sppg/nutrition/ingredients/{id}` | `ingredients.delete` | Hapus bahan |
| GET | `/admin-sppg/nutrition/ingredients/dropdown` | `ingredients.read` | Dropdown bahan |
| POST | `/admin-sppg/nutrition/ingredients/calculate-nutrition` | `ingredients.read` | Hitung gizi |
| GET | `/admin-sppg/nutrition/recipes` | `recipes.read` | List resep |
| POST | `/admin-sppg/nutrition/recipes` | `recipes.create` | Buat resep |
| GET | `/admin-sppg/nutrition/recipes/{id}` | `recipes.read` | Detail resep |
| PUT/PATCH | `/admin-sppg/nutrition/recipes/{id}` | `recipes.update` | Update resep |
| DELETE | `/admin-sppg/nutrition/recipes/{id}` | `recipes.delete` | Hapus resep |
| GET | `/admin-sppg/nutrition/recipes/dropdown` | `recipes.read` | Dropdown resep |
| GET | `/admin-sppg/nutrition/menus` | `menus.read` | List menu |
| POST | `/admin-sppg/nutrition/menus` | `menus.create` | Buat menu |
| GET | `/admin-sppg/nutrition/menus/{id}` | `menus.read` | Detail menu |
| GET | `/admin-sppg/nutrition/menus/{id}/grouped` | `menus.read` | Menu terkelompok per hari |
| PUT/PATCH | `/admin-sppg/nutrition/menus/{id}` | `menus.update` | Update menu |
| PATCH | `/admin-sppg/nutrition/menus/{id}/publish` | `menus.update` | Publish menu |
| DELETE | `/admin-sppg/nutrition/menus/{id}` | `menus.delete` | Hapus menu |
| POST | `/admin-sppg/nutrition/menus/refresh-statuses` | `menus.update` | Refresh status menu |
| GET | `/admin-sppg/distributions` | `distribution.read` | List distribusi |
| POST | `/admin-sppg/distributions` | `distribution.create` | Buat distribusi |
| GET | `/admin-sppg/distributions/{id}` | `distribution.read` | Detail distribusi |
| PUT/PATCH | `/admin-sppg/distributions/{id}` | `distribution.update` | Update distribusi |
| DELETE | `/admin-sppg/distributions/{id}` | `distribution.delete` | Hapus distribusi |
| POST | `/admin-sppg/tracking/update-location` | `distribution.update` | Update lokasi kurir |
| GET | `/admin-sppg/maps/distribution` | `distribution.read` | Peta distribusi |
| GET | `/admin-sppg/financial-reports` | `report.read` | List laporan keuangan |
| POST | `/admin-sppg/financial-reports` | `report.create` | Tambah laporan |
| GET | `/admin-sppg/financial-reports/{id}` | `report.read` | Detail laporan |
| PUT/PATCH | `/admin-sppg/financial-reports/{id}` | `report.update` | Update laporan |
| DELETE | `/admin-sppg/financial-reports/{id}` | `report.delete` | Hapus laporan |

---

### Distribution Routes — `/api/distribution`

> Middleware: `auth:sanctum`

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/distribution/schedules` | List jadwal pengiriman |
| POST | `/distribution/schedules` | Buat jadwal baru |
| GET | `/distribution/schedules/{id}` | Detail jadwal |
| PUT | `/distribution/schedules/{id}` | Update jadwal |
| DELETE | `/distribution/schedules/{id}` | Hapus jadwal |
| GET | `/distribution/schedules/meta/couriers` | Daftar kurir tersedia |
| POST | `/distribution/schedules/{id}/submit` | Submit tugas ke kurir |
| POST | `/distribution/schedules/{id}/accept` | Kurir terima tugas |
| POST | `/distribution/schedules/{id}/reject` | Kurir tolak tugas |
| POST | `/distribution/schedules/{id}/proof` | Submit bukti pengiriman |
| POST | `/distribution/schedules/{id}/proof/resubmit` | Re-submit bukti |
| POST | `/distribution/schedules/{id}/confirm` | Admin konfirmasi |
| POST | `/distribution/schedules/{id}/revision` | Admin minta revisi |
| GET | `/distribution/histories` | Riwayat pengiriman |
| GET | `/distribution/histories/analytics` | Analitik pengiriman |
| GET | `/distribution/histories/{id}` | Detail riwayat |
| GET | `/distribution/map/active-couriers` | Kurir aktif di peta |
| GET | `/distribution/map/depot` | Lokasi depot |
| GET | `/distribution/map/trail/{id}` | Jejak rute kurir |
| POST | `/distribution/map/location/{id}` | Ping lokasi GPS |
| POST | `/distribution/map/optimize-route` | Optimasi rute |

---

### Public Routes — `/api/public`

> Tanpa autentikasi

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/public/menus` | Menu yang dipublikasi |
| GET | `/public/maps/sppg` | Peta lokasi SPPG |
| GET | `/public/maps/monitoring` | Peta monitoring |
| GET | `/public/dashboard` | Dashboard publik |
| GET | `/public/recommendation/generate` | Generate rekomendasi |
| POST | `/public/feedback` | Kirim feedback |
| POST | `/public/rating` | Beri rating |
| GET/POST/PUT/DELETE | `/public/sppg-submissions` | Pengajuan SPPG |

---

## 8. Middleware Stack

| Middleware | Alias | Fungsi |
|-----------|-------|--------|
| `auth:sanctum` | built-in | Verifikasi autentikasi via session/token |
| `CheckRole` | `role` | Validasi role user (`role:super_admin`, `role:kurir`, dll.) |
| `CheckPermission` | `permission` | Validasi permission atomik (`permission:employee.create`) |
| `ScopeBySppg` | `scope.sppg` | Isolasi data — user hanya bisa akses SPPG miliknya |
| `web` | built-in | Cookie/session handling (digunakan di auth routes) |

### Urutan Middleware pada Setiap Request

```
auth:sanctum → role:xxx → permission:xxx.xxx → Controller
```

---

## 9. Diagram Relasi Antar Entitas

```
users
 ├──(has one)────▶ employees
 │                   ├──(belongs to)──▶ roles
 │                   │                   └──(belongs to many)──▶ permissions
 │                   └──(belongs to)──▶ s_p_p_g_s
 └──(belongs to)──▶ s_p_p_g_s
                       ├──(has many)──▶ schools
                       ├──(has many)──▶ employees
                       ├──(has many)──▶ roles
                       └──(has many)──▶ partners

Nutrition Chain:
ingredients ◀──(many to many via recipe_ingredients)──▶ recipes
recipes ◀──(has many menu_items)──▶ menus

Distribution Chain:
employees (kurir) ──▶ delivery_schedules ──▶ schools
delivery_schedules ──▶ courier_locations  (GPS trail)
delivery_schedules ──▶ delivery_histories (archive setelah confirmed)
```

---

## 🗂️ Ringkasan Tabel Database

| # | Tabel | Entitas Utama | Fitur Khusus |
|---|-------|---------------|--------------|
| 1 | `users` | Akun login | role_type, SPA auth |
| 2 | `s_p_p_g_s` | Unit SPPG | SoftDelete, GIS Haversine |
| 3 | `schools` | Sekolah | UUID, SoftDelete, GIS |
| 4 | `s_p_p_g_schools` | Pivot SPPG-School | |
| 5 | `employees` | Karyawan | role_id RBAC |
| 6 | `roles` | Role sistem | SoftDelete, auto-slug |
| 7 | `permissions` | Permission atomik | module.feature.action |
| 8 | `role_permission` | Pivot Role-Permission | |
| 9 | `ingredients` | Bahan baku | nilai gizi per serving |
| 10 | `recipes` | Resep | target vs total gizi |
| 11 | `recipe_ingredients` | Pivot Resep-Bahan | kontribusi gizi |
| 12 | `menus` | Menu mingguan | SoftDelete, status dinamis |
| 13 | `menu_items` | Item resep per hari | day_of_week |
| 14 | `partners` | Sekolah mitra | UUID, SoftDelete, GIS |
| 15 | `delivery_schedules` | Jadwal pengiriman | SoftDelete, 7 status |
| 16 | `courier_locations` | GPS real-time | no timestamps |
| 17 | `delivery_histories` | Riwayat pengiriman | immutable log |
| 18 | `financial_reports` | Laporan keuangan | |
| 19 | `feedback` | Umpan balik publik | |
| 20 | `ratings` | Rating publik | |
| 21 | `recommendations` | Rekomendasi AI | |
| 22 | `s_p_p_g_submissions` | Pengajuan SPPG | |
| 23 | `public_users` | User publik | |
| 24 | `landing_contents` | Konten landing page | scopeActive |
| 25 | `personal_access_tokens` | Token Sanctum | morphable |
| 26 | `cache` | Cache Laravel | |
| 27 | `jobs` | Queue jobs | |

---

*Dokumentasi ini dihasilkan otomatis dari kode sumber COMS-MBG.*  
*Terakhir diperbarui: 2026-05-28*
