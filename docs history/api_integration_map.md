# 🗺️ Dokumen Pemetaan API — COMS MBG Frontend

> **Tanggal Analisa:** 18 Mei 2026  
> **Proyek:** COMS MBG Frontend (Vue 3 + Pinia + TypeScript)  
> **Base URL API:** `http://localhost:8000/api` (diatur di `src/api/axios.ts`)  
> **Auth Method:** Sanctum SPA Cookie (CSRF-based)

---

## 📋 Ringkasan Eksekutif

| Modul | File API Tersedia | Status Integrasi | Prioritas |
|-------|-------------------|-----------------|-----------|
| 🔐 Auth | `src/api/auth.api.ts` | ✅ **SUDAH TERHUBUNG** | — |
| 🤝 Partner / Sekolah | `src/api/partner.api.ts` | ✅ **SUDAH TERHUBUNG** | — |
| 🥗 Master Bahan | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🔴 Tinggi |
| 📖 Master Resep | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🔴 Tinggi |
| 📅 Menu Planning | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🔴 Tinggi |
| 👤 Karyawan (HR) | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🔴 Tinggi |
| 🔑 Hak Akses / Role | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🔴 Tinggi |
| 🚚 Distribusi | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🟡 Menengah |
| 🗺️ Peta Spasial | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🟡 Menengah |
| 📊 Laporan Distribusi | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🟡 Menengah |
| 💰 Laporan Keuangan | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🟡 Menengah |
| 🏠 Dashboard | ❌ Belum ada | ⚠️ **PERLU DIBUAT** | 🟡 Menengah |

---

## 🏗️ Arsitektur Saat Ini

```
src/
├── api/                  ← Layer API (Axios calls) — hanya 3 file
│   ├── axios.ts          ✅ Instance Axios terkonfigurasi (CSRF, baseURL, interceptor 401)
│   ├── auth.api.ts       ✅ TERHUBUNG ke BE
│   └── partner.api.ts    ✅ TERHUBUNG ke BE
│
├── stores/               ← State management (Pinia) — 12 file
│   ├── auth.store.ts     ✅ Konsumsi auth.api.ts
│   ├── partner.store.ts  ✅ Konsumsi partner.api.ts
│   ├── bahan.store.ts    ❌ Masih pakai dummy data
│   ├── resep.store.ts    ❌ Masih pakai dummy data
│   ├── employee.store.ts ❌ Masih pakai dummy data
│   ├── menuPlanning.store.ts ❌ Masih pakai setTimeout mock
│   ├── distribution.store.ts ❌ Masih pakai hardcode data
│   ├── laporan.store.ts  ❌ Masih pakai hardcode data
│   ├── finance.store.ts  ❌ Masih pakai hardcode data
│   ├── access.store.ts   ❌ Masih ambil dari dummy file lokal
│   └── spatial.store.ts  ❌ Generate data dummy random
│
└── services/             ← Service layer
    └── partnerServices.ts ← (sudah ada, tapi sebaiknya dipindah ke api/)
```

> [!IMPORTANT]
> Pola yang benar adalah: **View → Store → `src/api/*.api.ts` → Backend**  
> Jangan pernah panggil axios langsung dari View atau Component.

---

## 📁 Detail Per Modul

### ✅ 1. Auth — SUDAH TERHUBUNG

**File:** `src/api/auth.api.ts`  
**Store:** `src/stores/auth.store.ts`

| Method | URL BE | Fungsi | Status |
|--------|--------|--------|--------|
| GET | `/sanctum/csrf-cookie` | Ambil CSRF token sebelum login | ✅ |
| POST | `/api/auth/login` | Login | ✅ |
| GET | `/api/auth/user` | Ambil data user yang login | ✅ |
| POST | `/api/auth/logout` | Logout | ✅ |

**Tidak perlu perubahan.** Auth sudah berjalan penuh.

---

### ✅ 2. Partner / Data Sekolah — SUDAH TERHUBUNG

**File:** `src/api/partner.api.ts`  
**Store:** `src/stores/partner.store.ts`  
**View:** `src/views/partner/` (belum teridentifikasi routenya)  
**Components:** `src/components/partner/`

| Method | URL BE | Fungsi | Status |
|--------|--------|--------|--------|
| GET | `/api/admin-sppg/partners` | List partner dengan filter & pagination | ✅ |
| GET | `/api/admin-sppg/partners/summary` | Statistik ringkasan | ✅ |
| GET | `/api/admin-sppg/partners/:id` | Detail satu partner | ✅ |
| POST | `/api/admin-sppg/partners` | Tambah partner | ✅ |
| PUT | `/api/admin-sppg/partners/:id` | Edit partner | ✅ |
| DELETE | `/api/admin-sppg/partners/:id` | Hapus partner | ✅ |
| POST | `/api/admin-sppg/partners/import` | Import CSV | ✅ |

**Tidak perlu perubahan.**

---

### ❌ 3. Master Bahan Baku — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/bahan.api.ts`  
**Store yang perlu diupdate:** `src/stores/bahan.store.ts`  
**View:** `src/views/gizi/MasterBahanView.vue`  
**Components:** `src/components/gizi/master-bahan/`, `src/components/master-bahan/`

**Kode yang perlu diganti di View (line 146-149):**
```typescript
// SEKARANG (dummy):
onMounted(() => {
  if (bahanStore.items.length === 0) {
    bahanStore.setItems(bahanDummy)  // ← GANTI INI
  }
})

// NANTI (API):
onMounted(() => {
  bahanStore.fetchItems()
})
```

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi | Parameter |
|--------|--------|--------|-----------|
| GET | `/api/admin-sppg/ingredients` | List semua bahan | `?search=`, `?page=`, `?per_page=` |
| POST | `/api/admin-sppg/ingredients` | Tambah bahan baru | Body: nama, satuan, kalori, protein, karbo, lemak |
| PUT | `/api/admin-sppg/ingredients/:id` | Edit bahan | Body: field yang diubah |
| DELETE | `/api/admin-sppg/ingredients/:id` | Hapus bahan | — |

**File yang harus dibuat:**
```
src/api/bahan.api.ts
```

**Perubahan di Store** (`src/stores/bahan.store.ts`, baris 70-86):
```typescript
// createIngredient — ganti addItem(newItem) dengan api call
async function createIngredient(item: Omit<BahanItem, 'id'>) {
  // TODO: await bahanApi.create(item)
}

// updateIngredient — ganti crud.updateItem dengan api call
// deleteIngredient — ganti crud.deleteItem dengan api call
```

---

### ❌ 4. Master Resep — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/resep.api.ts`  
**Store yang perlu diupdate:** `src/stores/resep.store.ts`  
**View:** `src/views/gizi/MasterResepView.vue`  
**Components:** `src/components/gizi/master-resep/`

**Kode yang perlu diganti di View (line 87-91):**
```typescript
// SEKARANG (dummy):
onMounted(() => {
  if (resepStore.items.length === 0) {
    resepStore.setItems(resepDummy)  // ← GANTI INI
  }
})

// NANTI (API):
onMounted(() => {
  resepStore.fetchItems()
})
```

**Perlu diperhatikan:** Summary Stats di View (line 94-99) masih hardcode:
```typescript
// SEKARANG (hardcode):
const summaryStats = [
  { label: 'TOTAL RESEP', value: '124', ... },   // ← GANTI DARI API
  { label: 'SESUAI STANDAR', value: '98%', ... }, // ← GANTI DARI API
  ...
]
```

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi | Parameter |
|--------|--------|--------|-----------|
| GET | `/api/admin-sppg/recipes` | List semua resep | `?search=`, `?page=`, `?per_page=` |
| GET | `/api/admin-sppg/recipes/:id` | Detail resep + bahan | — |
| POST | `/api/admin-sppg/recipes` | Buat resep baru | Body: nama, bahan[], total kalori |
| PUT | `/api/admin-sppg/recipes/:id` | Edit resep | Body: field yang diubah |
| DELETE | `/api/admin-sppg/recipes/:id` | Hapus resep | — |

**File yang harus dibuat:**
```
src/api/resep.api.ts
```

---

### ❌ 5. Menu Planning — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/menu.api.ts`  
**Store yang perlu diupdate:** `src/stores/menuPlanning.store.ts`  
**View:** `src/views/gizi/MenuPlanningView.vue`  
**Components:** `src/components/gizi/menu-planning/`

**Fungsi Store yang perlu diganti mock-nya:**

| Fungsi | Lokasi | Yang Perlu Diganti |
|--------|--------|-------------------|
| `fetchWeek(weekValue)` | `menuPlanning.store.ts` line 50-67 | `setTimeout` mock → API GET |
| `saveWeek()` | `menuPlanning.store.ts` line 69-77 | `setTimeout` mock → API PUT/POST |
| `onCopyLastWeek()` | `MenuPlanningView.vue` line 103 | `console.log` → API POST |

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi | Parameter |
|--------|--------|--------|-----------|
| GET | `/api/admin-sppg/menus` | List menu mingguan | `?week=2026-04-20` |
| GET | `/api/admin-sppg/menus/:id` | Detail satu menu | — |
| POST | `/api/admin-sppg/menus` | Buat/simpan menu minggu | Body: week, items[] |
| PUT | `/api/admin-sppg/menus/:id` | Update status menu | Body: status |

**File yang harus dibuat:**
```
src/api/menu.api.ts
```

---

### ❌ 6. Karyawan (HR) — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/employee.api.ts`  
**Store yang perlu diupdate:** `src/stores/employee.store.ts`  
**View:** `src/views/hr/EmployeeView.vue`  
**Components:** `src/components/hr/management/`

**Kode yang perlu diganti di View (line 136-141):**
```typescript
// SEKARANG (dummy):
onMounted(() => {
  if (employeeStore.items.length === 0) {
    employeeStore.setItems(employeeDummy)  // ← GANTI INI
  }
})

// NANTI (API):
onMounted(() => {
  employeeStore.fetchItems()
})
```

**Fungsi Store yang perlu dihubungkan ke API:**
- `fetchItems()` — store line 122 → body kosong, perlu diisi
- `addItem()` — store line 94 → perlu manggil API POST
- `updateItem()` — store line 99 → perlu manggil API PUT
- `deleteItem()` — store line 105 → perlu manggil API DELETE
- `toggleStatus()` — store line 111 → perlu manggil API PATCH
- `changeRole()` — store line 116 → perlu manggil API PATCH

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi | Parameter |
|--------|--------|--------|-----------|
| GET | `/api/admin-sppg/employees` | List karyawan | `?search=`, `?role=`, `?page=` |
| GET | `/api/admin-sppg/employees/:id` | Detail karyawan | — |
| POST | `/api/admin-sppg/employees` | Tambah karyawan | Body: nama, nrp, jabatan, dst |
| PUT | `/api/admin-sppg/employees/:id` | Edit karyawan | Body: field yang diubah |
| DELETE | `/api/admin-sppg/employees/:id` | Hapus karyawan | — |
| PATCH | `/api/admin-sppg/employees/:id/toggle-status` | Toggle aktif/nonaktif | — |

**File yang harus dibuat:**
```
src/api/employee.api.ts
```

---

### ❌ 7. Hak Akses / Role & Permission — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/access.api.ts`  
**Store yang perlu diupdate:** `src/stores/access.store.ts`  
**View:** `src/views/hr/EmployeeAccessView.vue`  
**Components:** `src/components/hr/access/`

**Kode yang perlu diganti di Store (line 50-63):**
```typescript
// SEKARANG (dummy lokal):
async function initialize() {
  const { featureAccessDummy, availableRoles } = await import('@/data/access.dummy')
  // ← GANTI dengan API call
}

// NANTI:
async function initialize() {
  const res = await accessApi.getFeatures()
  features.value = res.data
}
```

**Fungsi Store yang perlu dihubungkan ke API:**
- `initialize()` → API GET list roles + permissions
- `togglePermission()` → API PUT/PATCH update single permission
- `setAllPermissions()` → API PUT bulk update permissions

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/roles` | List semua role |
| GET | `/api/admin-sppg/permissions` | List semua permission |
| PUT | `/api/admin-sppg/roles/:id/permissions` | Update permission untuk role |

**File yang harus dibuat:**
```
src/api/access.api.ts
```

---

### ❌ 8. Distribusi / Jadwal Pengiriman — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/distribution.api.ts`  
**Store yang perlu diupdate:** `src/stores/distribution.store.ts`  
**View:** `src/views/distribusi/JadwalPengirimanView.vue`  
**Components:** `src/components/distribusi/`

**Kode yang perlu diganti di View (line 13-49):**
```typescript
// SEKARANG (hardcode di onMounted):
onMounted(() => {
  store.setItems([
    { id: 1, sekolah: 'SDN 012 Kebon Gedang', ...  },  // ← GANTI
    ...
  ])
})

// NANTI:
onMounted(() => {
  store.fetchItems()
})
```

**Fungsi Store yang perlu dihubungkan ke API:**
- `startDelivery(id)` — store line 77 → `setTimeout` mock harus diganti API PATCH
- `onTimeRate` — store line 46 → `static 94.2` harus dari API

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/distributions` | List distribusi harian |
| GET | `/api/admin-sppg/distributions/:id` | Detail satu distribusi |
| PATCH | `/api/admin-sppg/distributions/:id/status` | Update status pengiriman |
| GET | `/api/admin-sppg/courier-tracking` | Data tracking kurir real-time |

**File yang harus dibuat:**
```
src/api/distribution.api.ts
```

---

### ❌ 9. Peta Spasial — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/spatial.api.ts`  
**Store yang perlu diupdate:** `src/stores/spatial.store.ts`  
**View:** `src/views/distribusi/PetaSpasialView.vue`  
**Components:** `src/components/distribusi/spatial/`, `src/components/distribusi/map/`

**Fungsi Store yang perlu diganti (sangat kritis):**
- `seedData()` — store line 86-103 → Generate random 500 sekolah, **harus dari API**
- `runSimulation()` — store line 54-74 → Logic dummy, **harus dari API**
- Data SPPG Location — store line 24-26 → Hardcode 1 lokasi, **harus dari API**

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/schools` | List semua sekolah + koordinat |
| GET | `/api/super-admin/sppg` | List lokasi SPPG |
| GET | `/api/admin-sppg/distribution-map` | Data untuk peta distribusi |
| POST | `/api/admin-sppg/simulation` | Jalankan simulasi penempatan SPPG baru |

**File yang harus dibuat:**
```
src/api/spatial.api.ts
```

---

### ❌ 10. Laporan Distribusi — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/laporan.api.ts`  
**Store yang perlu diupdate:** `src/stores/laporan.store.ts`  
**View:** `src/views/laporan/LaporanView.vue`  
**Components:** `src/components/laporan/`

**Fungsi Store yang perlu diganti:**
- `fetchReports()` — store line 65-85 → Hardcode 12 item, **ganti dengan API**
- `fetchStats()` — store line 87-97 → Angka statis, **ganti dengan API**

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/distributions` | Data laporan distribusi |
| GET | `/api/admin-sppg/distributions/stats` | Statistik distribusi |
| GET | `/api/admin-sppg/distributions/export` | Export PDF/Excel |

**File yang harus dibuat:**
```
src/api/laporan.api.ts
```

---

### ❌ 11. Laporan Keuangan — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/finance.api.ts`  
**Store yang perlu diupdate:** `src/stores/finance.store.ts`  
**View:** `src/views/laporan/LaporanKeuanganView.vue`  
**Components:** `src/components/laporan-keuangan/`

**Fungsi Store yang perlu diganti:**
- `fetchReports()` — store line 99-125 → 12 data hardcode, **ganti dengan API**
- `fetchStats()` — store line 127-140 → Angka statis (Rp 250jt), **ganti dengan API**

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/financial-reports` | List transaksi keuangan |
| GET | `/api/admin-sppg/financial-reports/stats` | Statistik keuangan |
| GET | `/api/super-admin/financial-reports` | Laporan keuangan super admin |

**File yang harus dibuat:**
```
src/api/finance.api.ts
```

---

### ❌ 12. Dashboard — PERLU DIBUAT

**File yang perlu dibuat:** `src/api/dashboard.api.ts`  
**View:** `src/views/dashboard/`  
**Components:** `src/components/dashboard/`

**Components yang butuh data API:**

| Component | File | Data yang Dibutuhkan |
|-----------|------|---------------------|
| `DashboardHeader.vue` | `src/components/dashboard/` | Data user (sudah ada dari auth store) |
| `InsightMapCard.vue` | `src/components/dashboard/` | Data peta & insight distribusi |
| `LogisticsCard.vue` | `src/components/dashboard/` | Summary logistik harian |
| `MiniInfoCard.vue` | `src/components/dashboard/` | Statistik ringkasan |
| `SupplyStatusCard.vue` | `src/components/dashboard/` | Status stok & pasokan |

**Endpoint BE yang perlu dipanggil:**

| Method | URL BE | Fungsi |
|--------|--------|--------|
| GET | `/api/admin-sppg/dashboard` | Data dashboard SPPG |
| GET | `/api/super-admin/dashboard` | Data dashboard Super Admin |

**File yang harus dibuat:**
```
src/api/dashboard.api.ts
```

---

## 🗂️ Daftar File yang Harus Dibuat

Buat semua file ini di folder `src/api/`:

```
src/api/
├── axios.ts          ✅ Sudah ada
├── auth.api.ts       ✅ Sudah ada
├── partner.api.ts    ✅ Sudah ada
│
├── bahan.api.ts      ← BUAT (Prioritas 1)
├── resep.api.ts      ← BUAT (Prioritas 1)
├── employee.api.ts   ← BUAT (Prioritas 1)
├── menu.api.ts       ← BUAT (Prioritas 1)
├── access.api.ts     ← BUAT (Prioritas 1)
├── distribution.api.ts ← BUAT (Prioritas 2)
├── laporan.api.ts    ← BUAT (Prioritas 2)
├── finance.api.ts    ← BUAT (Prioritas 2)
├── spatial.api.ts    ← BUAT (Prioritas 2)
└── dashboard.api.ts  ← BUAT (Prioritas 2)
```

---

## 🔄 Daftar File Store yang Harus Diupdate

| File Store | Yang Diubah | Referensi Baris |
|------------|-------------|-----------------|
| `stores/bahan.store.ts` | `createIngredient`, `updateIngredient`, `deleteIngredient`, `fetchItems` | Line 70-86 |
| `stores/resep.store.ts` | `fetchItems` (dari `createCrudStore`) | Line 69-81 di factory |
| `stores/employee.store.ts` | `fetchItems`, `addItem`, `updateItem`, `deleteItem`, `toggleStatus` | Line 88-132 |
| `stores/menuPlanning.store.ts` | `fetchWeek`, `saveWeek` | Line 50-77 |
| `stores/distribution.store.ts` | `startDelivery`, tambah `fetchItems` | Line 77-82 |
| `stores/laporan.store.ts` | `fetchReports`, `fetchStats` | Line 65-97 |
| `stores/finance.store.ts` | `fetchReports`, `fetchStats` | Line 99-140 |
| `stores/access.store.ts` | `initialize`, `togglePermission`, `setAllPermissions` | Line 50-89 |
| `stores/spatial.store.ts` | `seedData`, `runSimulation`, data SPPG location | Line 24-103 |

---

## 🔄 Daftar View yang Harus Diupdate

| File View | Yang Diubah | Baris yang Diubah |
|-----------|-------------|-------------------|
| `views/gizi/MasterBahanView.vue` | Ganti `setItems(bahanDummy)` → `fetchItems()` | Line 146-149 |
| `views/gizi/MasterResepView.vue` | Ganti `setItems(resepDummy)` → `fetchItems()`, ganti summary stats hardcode | Line 87-99 |
| `views/gizi/MenuPlanningView.vue` | Ganti mock week data → `menuStore.fetchWeek()`, implementasi `onCopyLastWeek` | Line 53-72, 103 |
| `views/hr/EmployeeView.vue` | Ganti `setItems(employeeDummy)` → `fetchItems()`, implementasi onSubmit ke API | Line 136-194 |
| `views/distribusi/JadwalPengirimanView.vue` | Ganti hardcode `setItems([...])` → `store.fetchItems()` | Line 13-48 |
| `views/laporan/LaporanView.vue` | Pastikan `fetchReports()` & `fetchStats()` sudah panggil API | — |
| `views/laporan/LaporanKeuanganView.vue` | Pastikan `fetchReports()` & `fetchStats()` sudah panggil API | — |
| `views/distribusi/PetaSpasialView.vue` | Ganti `seedData()` dengan `fetchSchools()` dari API | — |

---

## 📌 Pola yang Harus Diikuti

Ikuti pola yang sudah ada di `partner.api.ts` sebagai referensi:

```typescript
// src/api/bahan.api.ts — contoh pola yang benar
import api from '@/api/axios'
import type { BahanItem } from '@/types/gizi'

const BASE = '/admin-sppg/ingredients'

export const bahanApi = {
  async getAll(params?: Record<string, string | number | undefined>) {
    const { data } = await api.get(BASE, { params })
    return data
  },
  async create(payload: Omit<BahanItem, 'id'>) {
    const { data } = await api.post(BASE, payload)
    return data
  },
  async update(id: number, payload: Partial<BahanItem>) {
    const { data } = await api.put(`${BASE}/${id}`, payload)
    return data
  },
  async delete(id: number) {
    const { data } = await api.delete(`${BASE}/${id}`)
    return data
  },
}
```

> [!TIP]
> Selalu import `api` dari `@/api/axios` (bukan axios langsung). Instance ini sudah dikonfigurasi dengan baseURL, CSRF token, timeout 15s, dan interceptor 401 otomatis.

> [!WARNING]
> Jangan hapus file `src/data/*.dummy.ts` sebelum semua API tersambung. Gunakan sebagai fallback saat testing.

> [!NOTE]
> File `src/services/partnerServices.ts` sebaiknya dikosongkan atau dihapus karena logic-nya sudah ada di `src/stores/partner.store.ts` + `src/api/partner.api.ts`. Jangan buat duplikasi.
