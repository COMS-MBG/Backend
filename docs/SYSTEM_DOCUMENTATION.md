# 📘 Dokumentasi Sistem — COMS MBG Backend

> **Stack:** Laravel 11 · Sanctum SPA Auth · Custom RBAC · MySQL
> **Dibuat:** 19 Mei 2026

---

## 1. GAMBARAN SISTEM

COMS MBG (Catering Operations Management System — Makan Bergizi) adalah backend untuk program makan bergizi gratis (MBG). Menghubungkan:

- **Super Admin** — mengawasi seluruh SPPG secara nasional
- **Admin SPPG** (Pemilik/Manajer/Ahli Gizi/Admin Logistik) — operasional 1 SPPG
- **Kurir** — menerima tugas dan melaporkan pengiriman via GPS
- **Publik** — melihat menu, lokasi SPPG, memberikan feedback

---

## 2. FITUR & MODUL

| Modul | Fungsi |
|-------|--------|
| 🔐 Auth | Login/logout Sanctum SPA Cookie + CSRF |
| 🏢 SPPG Management | CRUD SPPG, assign sekolah, capacity overview |
| 🏫 Partner/Sekolah | CRUD + import CSV + filter + statistik |
| 👤 Karyawan | CRUD + assign role sistem |
| 🔑 Role & Permission | Role per-SPPG, permission modul.aksi |
| 🥗 Master Bahan | CRUD ingredient + kalkulasi nutrisi otomatis |
| 📖 Master Resep | CRUD resep + komposisi bahan + total gizi |
| 📅 Menu Planning | Jadwal mingguan + publish/archive otomatis |
| 🚚 Jadwal Pengiriman | Workflow lengkap submit→accept→deliver→confirm |
| 📍 GPS Tracking | Ping lokasi kurir real-time, trail rute |
| 📊 Riwayat Pengiriman | Archive + analitik pengiriman selesai |
| 💰 Laporan Keuangan | CRUD financial report |
| 🌐 API Publik | Menu publik, peta SPPG, feedback, rating |

---

## 3. ENTITAS DATABASE & ATRIBUT

### `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | hashed | |
| phone | string nullable | |
| profile_picture | string nullable | |
| is_active | boolean | default true |
| role_type | string | `super_admin` atau `sppg_user` |
| sppg_id | FK → sppg nullable | null = super admin |

### `s_p_p_g_s`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | |
| address / district / city / province | string | Lokasi |
| latitude / longitude | decimal | Koordinat GPS |
| capacity | integer | Kapasitas maks sekolah |
| phone / email | string nullable | |
| status | string | `active`, `inactive` |
| pemilik_id | FK → users | |
| deleted_at | timestamp | SoftDelete |

### `employees`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| sppg_id | FK → sppg | |
| user_id | FK → users nullable | null = belum punya akun |
| role_id | FK → roles nullable | null = tanpa akses |
| name | string | |
| nik | string nullable | |
| position | enum | pemilik/manajer/ahli_gizi/admin_logistik/kurir/karyawan_operasional |
| phone / address | string nullable | |
| photo | string nullable | |
| joined_at | date nullable | |
| base_salary | decimal nullable | Hidden dari JSON response |

### `roles`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | Label tampilan |
| slug | string | Auto-generate dari name |
| description | text nullable | |
| sppg_id | FK → sppg nullable | null = role global |
| deleted_at | timestamp | SoftDelete |

### `permissions`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | "Create Employee" |
| slug | string unique | `employee.create` |
| module | string | `employee`, `nutrition`, dll |
| feature | string | `ingredients`, `recipes`, dll |
| action | string | `create/read/update/delete` |

### `role_permission` (pivot)
| role_id | permission_id |

### `schools`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | UUID PK | |
| nama / alamat | string/text | |
| latitude / longitude | decimal | |
| jumlah_siswa | integer | |
| jenjang | string | SD/SMP/SMA/SMK |
| kecamatan / kota / provinsi | string | |
| kepala_sekolah | string nullable | |
| sppg_id | FK → sppg nullable | |
| status | string | |
| deleted_at | timestamp | SoftDelete |

### `partners`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | UUID PK | |
| nama_sekolah | string | |
| npsn | string | Nomor Pokok Sekolah Nasional |
| bentuk | string | SD/SMP/SMA/SMK |
| status | string | negeri/swasta |
| alamat / kecamatan / kabupaten_kota | string | |
| latitude / longitude | decimal | |
| jumlah_porsi | integer | |
| sppg_id | FK → sppg | |
| deleted_at | timestamp | SoftDelete |

### `ingredients`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | |
| carbohydrate / protein / calorie / fat | float | Per serving (gram/kkal) |
| serving_weight | float | Gram basis kalkulasi |
| description | text nullable | |

### `recipes`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name / description | string/text | |
| target_calorie/protein/carbohydrate/fat | float | Target gizi (2000–2700 kkal) |
| total_calorie/protein/carbohydrate/fat | float | Dihitung dari bahan |
| total_weight | float | Gram total |
| deleted_at | timestamp | SoftDelete |

### `recipe_ingredients` (pivot + extra)
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| recipe_id | FK → recipes | |
| ingredient_id | FK → ingredients | |
| weight_used | float | Gram yang dipakai |
| calorie/protein/carbohydrate/fat_contribution | float | Kontribusi masing-masing |
| order | integer | Urutan tampil |

### `menus`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| name | string | |
| week_start | date | Senin minggu itu |
| week_end | date | Kamis minggu itu |
| status | string | planned/scheduled/published/archived |
| notes | text nullable | |
| deleted_at | timestamp | SoftDelete |

### `menu_items`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| menu_id | FK → menus | |
| recipe_id | FK → recipes | |
| day_of_week | integer | 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis |
| menu_date | date | |
| order | integer | Urutan dalam hari |

### `delivery_schedules`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| courier_id | FK → employees | |
| school_id | FK → schools | |
| assigned_by / submitted_by / confirmed_by | FK → users | |
| vehicle_type | enum | motorcycle/car/van/truck |
| vehicle_plate | string | |
| status | enum | in_order/accepted/rejected/delivering/delivered/confirmed/revision_required |
| scheduled_at | datetime | |
| departed_at / arrived_at | datetime nullable | |
| delivery_notes / rejection_reason | text nullable | |
| rejection_photo_path / proof_photo_path | string nullable | |
| proof_submitted_at / confirmed_at / rejected_at | datetime nullable | |
| confirmation_notes | text nullable | |
| route_snapshot | JSON | Rekaman rute |
| deleted_at | timestamp | SoftDelete |

### `courier_locations`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| delivery_schedule_id | FK | |
| courier_id | FK → employees | |
| latitude / longitude | float | GPS real-time |
| speed_kmh / heading_degrees / accuracy_meters | float nullable | |
| recorded_at | datetime | Tanpa timestamps standar |

### `delivery_histories`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| delivery_schedule_id | FK | |
| courier_id | FK → employees | |
| school_id | FK → schools | |
| courier_name / school_name / school_address | string | Snapshot data |
| vehicle_type / vehicle_plate | string | |
| departed_at / arrived_at | datetime | |
| proof_photo_path | string | |
| route_snapshot | JSON | |
| distance_km | float nullable | |
| confirmed_by | FK → users | |
| confirmed_at | datetime | |
| notes | text nullable | |

---

## 4. DIAGRAM RELASI

```
User ────────────────── SPPG
 │       (sppg_id)        │
 │                        ├── Employee ──── Role ──┐
 │                        │       │         │      │
 │                        │       └── User  └── Permission
 │                        │                  (role_permission)
 │                        ├── School
 │                        ├── Partner
 │                        └── Role (scope per SPPG)

Ingredient ──── recipe_ingredients ──── Recipe
                  (pivot+nutrisi)          │
                                      MenuItem ──── Menu

Employee(kurir) ──── DeliverySchedule ──── School
                            │
                ┌───────────┴───────────┐
        CourierLocation          DeliveryHistory
         (GPS trail)              (archive data)
```

---

## 5. SISTEM RBAC

### 5.1 Dua Lapis Otorisasi

```
Layer 1: users.role_type
  ├── 'super_admin'  → bypass SEMUA gate (akses tidak terbatas)
  └── 'sppg_user'   → masuk Layer 2

Layer 2: employee → role → permissions
  Employee.role_id → Role.id
  Role ↔ Permission (many-to-many via role_permission)
  Cek via slug: "employee.create", "nutrition.read", dll
```

### 5.2 Middleware yang Tersedia

| Alias | Kelas | Fungsi |
|-------|-------|--------|
| `role:x` | `CheckRole` | Cek role_type atau employee.role.slug |
| `permission:x` | `CheckPermission` | Cek permission slug via employee→role |
| `scope.sppg` | `ScopeBySppg` | Isolasi data per SPPG |

### 5.3 Alur Pengecekan per Request

```
Request → [auth:sanctum] → cek session cookie
        → [role:pemilik|manajer]
              isSuperAdmin()? → pass
              hasAnyRole([...])? → cek employee.role.slug → pass/403
        → [permission:employee.create]
              isSuperAdmin()? → pass
              user→employee→role→permissions.contains(slug)? → pass/403
        → Controller
```

### 5.4 Daftar Permission (36 total)

| Slug | Modul |
|------|-------|
| `dashboard.read` | Dashboard |
| `employee.create/read/update/delete` | Karyawan |
| `school.create/read/update/delete` | Sekolah |
| `sppg.create/read/update/delete` | SPPG |
| `nutrition.create/read/update/delete` | Gizi (level modul) |
| `ingredients.create/read/update/delete` | Master Bahan |
| `recipes.create/read/update/delete` | Master Resep |
| `menus.create/read/update/delete` | Menu Planning |
| `distribution.create/read/update/delete` | Distribusi |
| `finance.create/read/update/delete` | Keuangan |
| `partner.create/read/update/delete` | Partner |
| `report.create/read/update/delete` | Laporan |

---

## 6. SEMUA ENDPOINT API

### Auth
| Method | URL | Keterangan |
|--------|-----|-----------|
| GET | `/sanctum/csrf-cookie` | Dapat XSRF cookie |
| POST | `/api/auth/login` | Login |
| GET | `/api/auth/user` | Ambil user login |
| POST | `/api/auth/logout` | Logout |

### Super Admin (`role:super_admin`)
| Method | URL | Keterangan |
|--------|-----|-----------|
| CRUD | `/api/super-admin/sppg` | Kelola SPPG |
| GET | `/api/super-admin/sppg/capacity-overview` | Kapasitas SPPG |
| POST | `/api/super-admin/sppg/{id}/assign-school` | Assign sekolah |
| CRUD | `/api/super-admin/schools` | Kelola sekolah |
| CRUD | `/api/super-admin/financial-reports` | Laporan keuangan |

### Admin SPPG (role pemilik/manajer/admin-sppg)
| Method | URL | Keterangan |
|--------|-----|-----------|
| CRUD | `/api/admin-sppg/employees` | Karyawan |
| GET/POST | `/api/admin-sppg/employees/{id}/assign-role` | Assign role |
| CRUD | `/api/admin-sppg/schools` | Sekolah |
| CRUD | `/api/admin-sppg/roles` | Role |
| GET | `/api/admin-sppg/permissions` | List permission |
| CRUD | `/api/admin-sppg/partners` | Partner |
| GET | `/api/admin-sppg/partners/summary` | Statistik |
| POST | `/api/admin-sppg/partners/import` | Import CSV |

### Admin SPPG (semua level)
| Method | URL | Keterangan |
|--------|-----|-----------|
| GET | `/api/admin-sppg/dashboard` | Dashboard |
| CRUD | `/api/admin-sppg/nutrition/ingredients` | Master Bahan |
| GET | `/api/admin-sppg/nutrition/ingredients/dropdown` | Dropdown |
| POST | `/api/admin-sppg/nutrition/ingredients/calculate-nutrition` | Hitung gizi |
| CRUD | `/api/admin-sppg/nutrition/recipes` | Master Resep |
| GET | `/api/admin-sppg/nutrition/recipes/dropdown` | Dropdown |
| CRUD | `/api/admin-sppg/nutrition/menus` | Menu Planning |
| PATCH | `/api/admin-sppg/nutrition/menus/{id}/publish` | Publish menu |
| GET | `/api/admin-sppg/nutrition/menus/{id}/grouped` | Menu per hari |
| POST | `/api/admin-sppg/nutrition/menus/refresh-statuses` | Refresh status |
| CRUD | `/api/admin-sppg/distributions` | Distribusi |
| POST | `/api/admin-sppg/tracking/update-location` | Update GPS |
| CRUD | `/api/admin-sppg/financial-reports` | Keuangan |
| GET | `/api/admin-sppg/maps/distribution` | Peta distribusi |

### Distribusi (semua auth)
| Method | URL | Keterangan |
|--------|-----|-----------|
| CRUD | `/api/distribution/schedules` | Jadwal pengiriman |
| POST | `/api/distribution/schedules/{id}/submit` | Submit ke kurir |
| POST | `/api/distribution/schedules/{id}/accept` | Kurir terima |
| POST | `/api/distribution/schedules/{id}/reject` | Kurir tolak |
| POST | `/api/distribution/schedules/{id}/proof` | Upload bukti |
| POST | `/api/distribution/schedules/{id}/confirm` | Konfirmasi admin |
| POST | `/api/distribution/schedules/{id}/revision` | Minta revisi |
| GET | `/api/distribution/histories` | Riwayat |
| GET | `/api/distribution/histories/analytics` | Analitik |
| GET | `/api/distribution/map/active-couriers` | Kurir aktif |
| GET | `/api/distribution/map/depot` | Lokasi depot |
| GET | `/api/distribution/map/trail/{id}` | Trail rute |
| POST | `/api/distribution/map/location/{id}` | Ping GPS |
| POST | `/api/distribution/map/optimize-route` | Optimasi rute |

### Publik (tanpa auth)
| Method | URL | Keterangan |
|--------|-----|-----------|
| GET | `/api/public/menus` | Menu aktif |
| GET | `/api/public/maps/sppg` | Lokasi SPPG |
| GET | `/api/public/maps/monitoring` | Monitoring |
| GET | `/api/public/dashboard` | Dashboard publik |
| GET | `/api/public/recommendation/generate` | Rekomendasi SPPG |
| POST | `/api/public/feedback` | Kirim feedback |
| POST | `/api/public/rating` | Kirim rating |

---

## 7. ALUR STATUS PENGIRIMAN

```
[in_order]
  → submit task → [in_order + assigned_by]
  → kurir accept → [accepted]
  → kurir berangkat → [delivering]
  → upload bukti → [delivered]
     → admin confirm → [confirmed] → arsip ke delivery_histories
     → admin revision → [revision_required]
        → kurir resubmit proof → [delivered]
  → kurir reject → [rejected] (bisa edit ulang)
```

---

## 8. ALUR STATUS MENU

```
week_start ≥ 14 hari lagi → planned
week_start 7–13 hari lagi → scheduled
week_start 0–6 hari lagi  → published (tampil publik)
week_start sudah lewat    → archived
```
Dihitung otomatis via `Menu::computeStatus($weekStart)`.

---

## 9. KALKULASI NUTRISI RESEP

```
1. Ambil ingredient.serving_weight
2. ratio = weight_used / serving_weight
3. contribution = ingredient.nilai_gizi × ratio
4. Simpan ke recipe_ingredients (calorie/protein/carbo/fat_contribution)
5. Recalculate recipe.total_* = SUM semua kontribusi bahan
6. Validasi: 2000 ≤ total_calorie ≤ 2700 kkal
```

---

## 10. LAPISAN KEAMANAN

| No | Lapisan | Implementasi |
|----|---------|-------------|
| 1 | Session Auth | `auth:sanctum` — encrypted cookie |
| 2 | CSRF | `statefulApi()` — XSRF-TOKEN wajib di setiap POST/PUT/DELETE |
| 3 | Role Gate | `CheckRole` — cek role_type / employee.role.slug |
| 4 | Permission Gate | `CheckPermission` — cek slug via employee→role→permissions |
| 5 | SPPG Scope | `ScopeBySppg` — data isolation, user hanya akses SPPG sendiri |
| 6 | Form Request | Validasi input + authorize() per operasi |
| 7 | Mass Assignment | `$fillable` di setiap model |
| 8 | Soft Delete | Data tidak benar-benar dihapus dari DB |
| 9 | Hidden Fields | `base_salary` hidden dari JSON response |
