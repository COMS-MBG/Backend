# Alur Sistem: Autentikasi, Distribusi & API Publik — COMS MBG
> Dokumen ini melengkapi dua dokumen sebelumnya ([Admin SPPG](./admin-sppg-system-flow.md) dan [Superadmin](./superadmin-system-flow.md)).
> Ditulis berdasarkan **kondisi kode aktual**: `routes/api_auth.php`, `routes/distribution.php`, `routes/api_public.php`, `routes/channels.php`.

---

## Daftar Isi

1. [Modul Autentikasi (Auth)](#1-modul-autentikasi-auth)
2. [Modul Distribusi (Distribution)](#2-modul-distribusi-distribution)
3. [Alur Status Delivery Schedule](#3-alur-status-delivery-schedule)
4. [Modul Peta Spasial & Optimasi Rute](#4-modul-peta-spasial--optimasi-rute)
5. [Riwayat Pengiriman & Analitik](#5-riwayat-pengiriman--analitik)
6. [WebSocket Channels (Laravel Reverb)](#6-websocket-channels-laravel-reverb)
7. [API Publik (Tanpa Autentikasi)](#7-api-publik-tanpa-autentikasi)
8. [Tabel Seluruh Endpoint Sistem](#8-tabel-seluruh-endpoint-sistem)

---

## 1. Modul Autentikasi (Auth)

**Route file**: `routes/api_auth.php`  
**Prefix**: `/api/auth`  
**Middleware**: `web` (session-based) + `auth:sanctum` untuk endpoint protected

### Alur Login Lengkap:

```
Step 1 (Browser / Mobile App):
  GET /sanctum/csrf-cookie
  → Mendapat XSRF-TOKEN cookie (diperlukan Sanctum SPA)

Step 2:
  POST /api/auth/login
  Body: { email, password, remember? }

  → LoginRequest::validate()
       ├── email: required|email
       └── password: required|string

  → LoginRequest::ensureIsNotRateLimited()
       └── Rate limit: 5 percobaan per menit (berdasarkan IP+email)
       └── Jika melebihi → HTTP 429 Too Many Requests

  → LoginAction::execute($request)

       ├── Cari user by email
       │    └── Tidak ada → InvalidCredentialsException (HTTP 422)

       ├── Hash::check(password)
       │    └── Salah → InvalidCredentialsException (HTTP 422)

       ├── Cek is_active
       │    └── false → AccountDeactivatedException (HTTP 403)

       ├── Jika role_type === 'sppg_user':
       │    ├── Cek status SPPG
       │    │    ├── Jika status 'inactive' DAN user adalah pemilik_id:
       │    │    │    → Auto-activate SPPG menjadi 'active'
       │    │    └── Jika status 'inactive' DAN bukan pemilik:
       │    │         → SppgInactiveException (HTTP 403)
       │    └── Lanjut jika SPPG active

       ├── Auth::login($user, $remember)
       └── $request->session()->regenerate()  ← cegah session fixation

  → LoginController membuat Sanctum Token:
       $token = $user->createToken('auth_token')->plainTextToken

  → Response:
       {
         "success": true,
         "message": "Login berhasil",
         "token": "1|abcdefg...",
         "user": { AuthUserResource }
       }

       Jika role_type === 'sppg_user', TAMBAH:
       {
         "sppg_status": "active",
         "permissions": ["menus.read", "stock.read", "stock.create", ...]
       }

Step 3 (Frontend validasi sesi):
  GET /api/auth/user
  Header: Authorization: Bearer {token}
  → Return AuthUserResource (data user + relasi)

Step 4 (Logout):
  POST /api/auth/logout
  → Hapus token aktif
  → Invalidate session
```

### Role Type di Sistem:
| `role_type` | Deskripsi | Middleware yang berlaku |
|:-----------|:----------|:------------------------|
| `super_admin` | Superadmin sistem | `role:super_admin` |
| `sppg_user` | Semua user di bawah SPPG (admin, ahli gizi, kurir, dll) | `permission:<slug>` per controller |

### Aturan Login Khusus SPPG:
- Pemilik SPPG yang login **pertama kali** ke SPPG yang masih `inactive` → SPPG otomatis menjadi `active`.
- Staf SPPG lainnya **tidak bisa login** selama SPPG masih `inactive`.

---

## 2. Modul Distribusi (Distribution)

**Route file**: `routes/distribution.php`  
**Prefix**: `/api/distribution`  
**Middleware**: `auth:sanctum`  
**Namespace**: `App\Http\Controllers\API\Distribution`

### Aktor & Hak Akses per Action:

| Aktor | Aksi yang Diizinkan |
|:------|:--------------------|
| Admin Logistik | Buat jadwal, edit jadwal, hapus jadwal (in_order), konfirmasi pengiriman, minta revisi, lihat peta, optimasi rute |
| Admin SPPG | Submit tugas ke kurir, lihat jadwal, lihat peta |
| Kurir | Terima/tolak tugas, submit bukti, resubmit bukti, kirim GPS ping, lihat jadwal miliknya |
| Super Admin | Semua akses di atas |

---

## 3. Alur Status Delivery Schedule

Ini adalah alur inti modul distribusi — seluruh lifecycle pengiriman dari awal sampai arsip.

```
[Admin Logistik]
    │
    ├─ POST /schedules
    │  Body: { courier_id, school_id, scheduled_date, vehicle_type, notes? }
    │  → DeliveryScheduleService::createSchedule()
    │  → STATUS: in_order
    │
    │  [Opsional] GET /schedules/meta/couriers
    │  → List kurir aktif (position=kurir ATAU role slug=kurir, status=active, ada user_id)
    │  → Dipakai untuk dropdown pilih kurir di FE saat membuat jadwal
    │
    ├─ PUT /schedules/{id}        ← Hanya jika masih in_order
    │  → Edit jadwal sebelum dikirim ke kurir
    │
    └─ DELETE /schedules/{id}     ← Hanya jika status in_order (abort_unless 422 jika bukan)

[Admin SPPG]
    │
    └─ POST /schedules/{id}/submit
       → DeliveryScheduleService::submitTask()
       → STATUS: in_order → delivering
       → Broadcast ke channel courier.{courierId}: event distribution.task.submitted
       → Kurir mendapat notifikasi real-time via Reverb

[Kurir]
    │
    ├─ POST /schedules/{id}/accept
    │  → DeliveryScheduleService::acceptTask()
    │  → STATUS: delivering (konfirmasi kurir sudah terima & mulai jalan)
    │
    ├─ [Selama mengantarkan] POST /map/location/{scheduleId}
    │  → SpatialMapController::recordLocation()
    │  → Simpan GPS ping ke courier_locations
    │  → Broadcast ke presence-distribution.map: event distribution.courier.location
    │
    ├─ POST /schedules/{id}/reject
    │  Body: { rejection_reason: string, rejection_photo?: file }
    │  → DeliveryScheduleService::rejectTask()
    │  → STATUS: rejected
    │  → Kurir menolak dengan alasan + foto opsional
    │
    └─ POST /schedules/{id}/proof
       Body: { proof_photo: file }     ← file foto bukti pengiriman
       → DeliveryScheduleService::submitDeliveryProof()
       → STATUS: delivering → delivered
       → Broadcast ke presence-distribution.operations: event distribution.status.updated

[Admin Logistik] — setelah menerima notifikasi bukti

    ├─ POST /schedules/{id}/confirm
    │  Body: { notes?: string }
    │  → DeliveryScheduleService::confirmDelivery()
    │  → STATUS: delivered → confirmed
    │  → Arsipkan ke tabel delivery_histories (HAPUS dari delivery_schedules)
    │  → Return: DeliveryHistoryResource
    │
    └─ POST /schedules/{id}/revision
       Body: { notes: string (min:5, max:500) }
       → DeliveryScheduleService::requestRevision()
       → STATUS: delivered → revision_required
       → Broadcast notifikasi ke kurir

[Kurir — jika diminta revisi]
    │
    └─ POST /schedules/{id}/proof/resubmit
       Body: { proof_photo: file }
       → DeliveryScheduleService::resubmitProof()
       → STATUS: revision_required → delivered (kembali menunggu konfirmasi)
```

### Diagram Status Ringkas:
```
in_order
    │
    └──[submit]──► delivering ──[proof]──► delivered
                       │                      │
                   [reject]              [confirm] → (arsip ke histories)
                       │                      │
                   rejected          [revision]──► revision_required
                                                        │
                                               [resubmit]──► delivered
```

---

## 4. Modul Peta Spasial & Optimasi Rute

**Controller**: `SpatialMapController`  
**Service**: `CourierLocationService`, `RouteOptimizationService`

```
GET  /map/active-couriers
     → Hanya admin_logistik, admin_sppg, super_admin
     → Semua kurir yang sedang aktif + posisi GPS terakhirnya
     → Data initial load untuk live map sebelum subscribe WebSocket

GET  /map/depot
     → Koordinat depot SPPG (dari config: distribution.depot_lat/lng, distribution.depot_name)
     → Dipakai peta sebagai titik "A" (start) dalam visualisasi rute

GET  /map/trail/{scheduleId}
     → admin_logistik, admin_sppg, super_admin: bisa lihat semua trail
     → courier: hanya bisa lihat trail miliknya sendiri (abort_unless 403)
     → Return: { schedule_id, data: [...koordinat GPS], total_pings }
     → Dipakai untuk replay rute perjalanan setelah pengiriman selesai

POST /map/location/{scheduleId}            ← GPS Ping dari kurir
     Body: { latitude, longitude, speed_kmh?, heading_degrees?, accuracy_meters? }
     → Validasi: kurir yang ping harus kurir yang bertugas pada schedule tersebut
     → CourierLocationService::recordLocation() → simpan ke courier_locations
     → Broadcast Reverb: presence-distribution.map → event distribution.courier.location
     → Frekuensi: ~setiap 5 detik saat status delivering

POST /map/optimize-route                   ← Optimasi rute sebelum pengiriman
     → Hanya admin_logistik, admin_sppg, super_admin
     Body:
     {
       "origin": { "lat": -6.917, "lng": 107.619 },
       "waypoints": [
         { "lat": -6.921, "lng": 107.622, "school_id": "uuid-1", "name": "SDN 01" },
         ...  (max 30 waypoints)
       ]
     }
     → RouteOptimizationService::optimize()
          1. Nearest-Neighbour TSP (O(n²)) → urutan kunjungan optimal
          2. OSRM API → polyline jalan sesungguhnya + jarak + estimasi waktu
          Fallback: Haversine straight-line jika OSRM tidak tersedia
     → Return: {
         ordered_waypoints: [...],
         geojson: { type: "LineString", coordinates: [...] },
         total_distance_km: 12.5,
         total_duration_min: 28.3
       }
```

---

## 5. Riwayat Pengiriman & Analitik

**Controller**: `DeliveryHistoryController`  
**Route**: `/api/distribution/histories`

> Data riwayat hanya bisa **dibaca** (READ ONLY). Data masuk hanya melalui `confirmDelivery()` dari `DeliveryScheduleController`.

```
GET /histories
    Query: ?courier_id=&school_id=&date_from=2026-06-01&date_to=2026-06-30&per_page=15
    → Kurir: hanya melihat riwayat pengiriman miliknya sendiri
    → Admin: bisa filter by courier_id (kurir tidak bisa filter ini)
    → Admin: bisa filter by school_id, date_from, date_to
    → Load relasi: confirmedBy (admin yang konfirmasi)

GET /histories/{id}
    → Detail 1 riwayat
    → Load relasi: courier, school, confirmedBy, schedule

GET /histories/analytics
    → Hanya admin_logistik, admin_sppg, super_admin (abort_unless 403 jika bukan)
    Query: ?date_from=2026-06-01&date_to=2026-06-30
    Default: bulan berjalan (startOfMonth → endOfMonth)
    → Return:
    {
      "period": { "from": "2026-06-01", "to": "2026-06-30" },
      "total_deliveries": 87,
      "total_distance_km": 623.4,
      "avg_duration_minutes": 31.2,
      "deliveries_per_courier": { "Budi": 34, "Andi": 28, ... },
      "deliveries_per_school":  { "SDN 01": 12, "SDN 02": 9, ... },
      "vehicle_breakdown":      { "motor": 60, "mobil": 27 }
    }
```

---

## 6. WebSocket Channels (Laravel Reverb)

**Route file**: `routes/channels.php`

Sistem menggunakan **Laravel Reverb** sebagai WebSocket server untuk real-time notifications dan tracking.

### Konfigurasi Reverb (.env):
```
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Channels yang Terdaftar:

#### 1. `courier.{courierId}` — Private Channel
```
Tujuan: Notifikasi spesifik ke 1 kurir

Authorization:
  ✓ User adalah kurir dengan employee.id === courierId
  ✓ ATAU user memiliki role: admin_logistik / admin_sppg / super_admin

Events yang dikirim ke channel ini:
  - distribution.task.submitted  → Admin SPPG mengirim tugas ke kurir
  - distribution.status.updated  → Update status jadwal (revision_required, dll)
```

#### 2. `distribution.operations` — Presence Channel
```
Tujuan: Dashboard operasi distribusi (admin live monitor)

Authorization:
  ✓ role: admin_logistik / admin_sppg / super_admin / courier
  → Return presence data: { id, name, role }

Events yang dikirim ke channel ini:
  - distribution.status.updated  → Setiap perubahan status delivery schedule
    (admin bisa lihat semua perubahan secara real-time)
```

#### 3. `distribution.map` — Presence Channel
```
Tujuan: Live tracking kurir di peta

Authorization:
  ✓ role: admin_logistik / admin_sppg / super_admin / courier
  → Return presence data: { id, name }

Events yang dikirim ke channel ini:
  - distribution.courier.location → Setiap GPS ping dari kurir
    { courier_id, schedule_id, latitude, longitude, recorded_at }
```

### Setup Frontend (Laravel Echo + Pusher-JS):
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY,
  wsHost: import.meta.env.VITE_REVERB_HOST,
  wsPort: import.meta.env.VITE_REVERB_PORT,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
});

// Subscribe live map
window.Echo.join('distribution.map')
  .here((users) => console.log('Users online:', users))
  .listen('distribution.courier.location', (data) => {
    // Update marker kurir di peta
  });

// Subscribe notifikasi kurir
window.Echo.private(`courier.${courierId}`)
  .listen('distribution.task.submitted', (data) => {
    // Tampilkan notifikasi tugas baru
  });
```

---

## 7. API Publik (Tanpa Autentikasi)

**Route file**: `routes/api_public.php`  
**Prefix**: `/api/public`  
**Middleware**: Tidak ada (akses bebas)

| Endpoint | Controller | Status Implementasi | Keterangan |
|:---------|:-----------|:-------------------|:-----------|
| `GET /public/menus` | `PublicMenuController` | **Kosong** | Rencana: tampilkan menu aktif yang dipublikasikan |
| `GET /public/maps/sppg` | `PublicMapController` | **Kosong** | Rencana: peta lokasi SPPG untuk publik |
| `POST /public/feedback` | `FeedbackController` | **Kosong** | Rencana: kirim feedback/laporan dari publik |
| `POST /public/rating` | `RatingController` | **Kosong** | Rencana: rating layanan MBG |
| `GET /public/recommendation/generate` | `RecommendationController` | *Perlu verifikasi* | Rekomendasi SPPG baru (K-Means, dari SuperAdmin) |
| `GET /public/maps/monitoring` | `MonitoringMapController` | *Perlu verifikasi* | Peta monitoring SPPG |
| `GET /public/dashboard` | `DashboardController` | *Reuse SuperAdmin* | Dashboard publik |
| `GET/POST/PUT/DELETE /public/sppg-submissions` | `SppgSubmissionController` | *Reuse SuperAdmin* | Submission SPPG dari publik |

> **Status implementasi**: Semua controller di namespace `Public` masih berupa skeleton kosong. Ini adalah fitur yang disiapkan untuk tahap implementasi berikutnya.

---

## 8. Tabel Seluruh Endpoint Sistem

Ringkasan semua endpoint dari seluruh modul dalam satu sistem COMS MBG.

### Auth (`/api/auth`)
| Method | Path | Keterangan |
|:-------|:-----|:-----------|
| `POST` | `/auth/login` | Login + terima token Sanctum |
| `GET` | `/auth/user` | Data user aktif (protected) |
| `POST` | `/auth/logout` | Logout + hapus token |

### Distribution (`/api/distribution`)
| Method | Path | Aktor | Keterangan |
|:-------|:-----|:------|:-----------|
| `GET` | `/schedules` | Semua | List jadwal aktif (kurir: hanya miliknya) |
| `POST` | `/schedules` | Admin Logistik | Buat jadwal baru |
| `GET` | `/schedules/meta/couriers` | Admin | Daftar kurir tersedia |
| `GET` | `/schedules/{id}` | Semua | Detail jadwal |
| `PUT` | `/schedules/{id}` | Admin Logistik | Edit jadwal |
| `DELETE` | `/schedules/{id}` | Admin Logistik | Hapus jadwal (hanya in_order) |
| `POST` | `/schedules/{id}/submit` | Admin SPPG | Submit tugas ke kurir + Reverb |
| `POST` | `/schedules/{id}/accept` | Kurir | Terima tugas |
| `POST` | `/schedules/{id}/reject` | Kurir | Tolak tugas + alasan + foto |
| `POST` | `/schedules/{id}/proof` | Kurir | Upload foto bukti → status: delivered |
| `POST` | `/schedules/{id}/proof/resubmit` | Kurir | Resubmit bukti setelah revisi |
| `POST` | `/schedules/{id}/confirm` | Admin Logistik | Konfirmasi → arsip ke histories |
| `POST` | `/schedules/{id}/revision` | Admin Logistik | Minta revisi bukti dari kurir |
| `GET` | `/histories` | Semua | List riwayat (kurir: hanya miliknya) |
| `GET` | `/histories/{id}` | Semua | Detail riwayat |
| `GET` | `/histories/analytics` | Admin | Analitik periode |
| `POST` | `/map/location/{scheduleId}` | Kurir | GPS ping → simpan + broadcast |
| `GET` | `/map/active-couriers` | Admin | Semua kurir aktif + posisi |
| `GET` | `/map/depot` | Admin | Koordinat depot SPPG |
| `GET` | `/map/trail/{scheduleId}` | Admin/Kurir | Jejak rute 1 pengiriman |
| `POST` | `/map/optimize-route` | Admin | Optimasi urutan kunjungan sekolah |

### Public (`/api/public`) — Belum diimplementasikan
| Method | Path | Keterangan |
|:-------|:-----|:-----------|
| `GET` | `/public/menus` | Menu aktif (publik) |
| `GET` | `/public/maps/sppg` | Peta SPPG |
| `POST` | `/public/feedback` | Kirim feedback |
| `POST` | `/public/rating` | Kirim rating |

---

> *Dokumen ini terakhir diperbarui: 2026-06-03. Dihasilkan berdasarkan source code langsung.*
