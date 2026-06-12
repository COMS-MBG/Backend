# 🏛️ COMS-MBG — Alur Lengkap SuperAdmin

> Dokumen ini menjelaskan **semua hal yang terjadi di sisi sistem** ketika SuperAdmin menggunakan aplikasi COMS-MBG — ditulis agar mudah dipahami oleh siapapun, tanpa perlu mengerti kode.

---

## 🗺️ Gambaran Besar — Apa Saja Yang Bisa Dilakukan SuperAdmin?

```mermaid
mindmap
  root((SuperAdmin))
    Dashboard
      Statistik total SPPG
      Total mitra aktif
      Total porsi harian
    Manajemen SPPG
      Lihat daftar SPPG
      Tambah SPPG baru
      Edit data SPPG
      Aktifkan / Nonaktifkan
      Hapus SPPG
      Lihat tab Mitra
      Lihat tab Menu
    Pendaftaran SPPG Baru
      Buat draft pengajuan
      Isi data SPPG
      Isi data Admin SPPG
      Isi data Ahli Gizi & Logistik
      Tambah sekolah mitra
      Konfirmasi titik di peta
      Submit & resmikan SPPG
    Peta GIS
      Lihat semua SPPG di peta
      Lihat rekomendasi lokasi baru
      Validasi titik pengajuan
      Saran geser titik ke posisi optimal
      Konfirmasi titik & dapat rekomendasi mitra otomatis
    Manajemen Sekolah
      Tambah sekolah
      Edit sekolah
      Hapus sekolah
      Assign sekolah ke SPPG
    Manajemen Karyawan
      Tambah karyawan per SPPG
      Edit data karyawan
      Hapus karyawan
    Laporan Keuangan
      Lihat laporan
      Tambah laporan
```

---

## 1️⃣ Dashboard — Halaman Utama

Saat SuperAdmin membuka aplikasi, halaman pertama yang muncul adalah **Dashboard**. Sistem secara otomatis menghitung dan menampilkan angka-angka terkini.

```mermaid
flowchart LR
    A([SuperAdmin buka Dashboard]) --> B[Sistem hitung dari database]
    B --> C[Total SPPG terdaftar\nbukan yang dihapus]
    B --> D[Total SPPG Aktif]
    B --> E[Total SPPG Tidak Aktif]
    B --> F[Total Mitra\nyang sudah terhubung ke SPPG]
    B --> G[Total Porsi Harian\njumlah semua porsi mitra]

    C --> H((Tampil ke layar))
    D --> H
    E --> H
    F --> H
    G --> H
```

> **Catatan:** Angka di dashboard bersifat **real-time** — langsung dari database saat halaman dibuka.

---

## 2️⃣ Manajemen SPPG — Pusat Kendali Dapur Produksi

### 2a. Melihat Daftar SPPG

SuperAdmin bisa mencari dan memfilter SPPG yang sudah terdaftar.

```mermaid
flowchart TD
    A([Buka halaman daftar SPPG]) --> B{Ada filter?}
    B -- Tidak --> C[Tampilkan semua SPPG\nalfabetis, 15 per halaman]
    B -- Ya --> D{Jenis filter}
    D -- Status: aktif/nonaktif --> E[Filter berdasarkan status]
    D -- Kota --> F[Filter berdasarkan kota]
    D -- Kecamatan --> G[Filter berdasarkan kecamatan]
    D -- Cari nama --> H[Filter berdasarkan nama SPPG]
    E & F & G & H --> I[Sistem hitung juga:\n• Jumlah mitra per SPPG\n• Total porsi per SPPG]
    C --> I
    I --> J[Tampil daftar SPPG\n+ ringkasan statistik keseluruhan]
```

**Statistik ringkasan** yang muncul di atas tabel:
| Angka | Keterangan |
|---|---|
| Total | Semua SPPG yang pernah didaftarkan |
| Aktif | SPPG yang sedang beroperasi |
| Tidak Aktif | SPPG yang sementara dinonaktifkan |
| Pending | SPPG yang baru didaftarkan, belum aktif |
| Overcapacity | SPPG yang jumlah sekolahnya melebihi kapasitas |

---

### 2b. Melihat Detail SPPG

Klik satu SPPG → muncul halaman detail dengan **3 tab**: Info, Mitra, Menu.

```mermaid
flowchart TD
    A([Klik SPPG tertentu]) --> B[Sistem ambil data lengkap SPPG]
    B --> C{Pilih tab}

    C -- Tab Info --> D[Tampil:\n• Nama, alamat, koordinat\n• Kapasitas\n• Status aktif/tidak\n• Info pemilik]
    D --> E[Sistem hitung kapasitas:\n• Berapa sekolah sudah masuk\n• Sisa kapasitas\n• Status: aman / penuh / overcapacity]

    C -- Tab Mitra --> F[Sistem ambil semua sekolah mitra]
    F --> G{Untuk setiap mitra}
    G --> H[Hitung jarak lurus SPPG → Mitra\npakai formula Haversine]
    G --> I[Hitung jarak jalan + waktu\npakai layanan peta OSRM]
    H & I --> J[Tandai status:\n✅ Aman jika ≤ 5 km\n⚠️ Perlu ditinjau jika > 5 km]
    J --> K[Tampil daftar mitra\n+ jarak + estimasi waktu]

    C -- Tab Menu --> L[Sistem ambil semua menu SPPG]
    L --> M[Urutkan: Dipublikasi → Terjadwal → Direncanakan → Diarsip]
    M --> N[Kelompokkan per periode minggu]
    N --> O[Tampil jadwal menu\n+ resep + info kalori per hari]
```

---

### 2c. Mengaktifkan / Menonaktifkan SPPG

```mermaid
flowchart LR
    A([Klik Nonaktifkan SPPG]) --> B[Sistem ubah status SPPG → Tidak Aktif]
    B --> C[Semua akun karyawan SPPG\ndi-nonaktifkan sekaligus]
    C --> D((Selesai — SPPG tidak bisa dipakai))

    E([Klik Aktifkan SPPG]) --> F[Sistem ubah status SPPG → Aktif]
    F --> G[Semua akun karyawan SPPG\ndiaktifkan kembali]
    G --> H((Selesai — SPPG bisa beroperasi lagi))
```

> ⚠️ **Saat SPPG dinonaktifkan, SEMUA karyawannya otomatis tidak bisa login.** Saat diaktifkan kembali, semua akun karyawan aktif juga kembali.

---

### 2d. Menghapus SPPG

```mermaid
flowchart TD
    A([Klik Hapus SPPG]) --> B[Sistem jalankan 3 aksi\ndalam satu paket tidak bisa sebagian]
    B --> C[1. SPPG ditandai terhapus]
    B --> D[2. Semua akun karyawan\ndi-nonaktifkan]
    B --> E[3. Semua sekolah mitra\ndilepas dari SPPG ini\nmitra jadi tidak bertuan]
    C & D & E --> F((Hapus selesai))
```

> ⚠️ **Menghapus SPPG tidak menghapus data sekolah mitra.** Sekolah hanya dilepas — bisa ditambahkan ke SPPG lain nantinya.

---

## 3️⃣ Pendaftaran SPPG Baru — Alur Multi-Langkah

Ini adalah fitur **paling kompleks** di SuperAdmin. Ada dua jalur:
- **Jalur Internal** — SuperAdmin sendiri yang buat draft
- **Jalur Pengajuan Masuk** — User biasa ajukan, SuperAdmin yang proses

### Gambaran Besar Alur Pendaftaran

```mermaid
flowchart TD
    START([Mulai Pendaftaran SPPG Baru]) --> JALUR{Siapa yang mengisi?}

    JALUR -- SuperAdmin sendiri --> SA[SuperAdmin buat draft\ndi halaman Submissions]
    JALUR -- Dari pengajuan user --> USER[User isi Form 1 + tambah mitra\nlalu tunggu SuperAdmin]

    SA --> FORM1
    USER --> FORM1

    FORM1[📋 FORM 1\nData SPPG\nNama, Alamat, Kapasitas] --> VALIDASI_ALAMAT

    VALIDASI_ALAMAT{Alamat valid?} -- ❌ Tidak ditemukan di peta --> ERROR1[Sistem tolak:\nMinta perbaiki alamat]
    ERROR1 --> FORM1

    VALIDASI_ALAMAT -- ✅ Valid --> SIMPAN_DRAFT[Draft tersimpan\n+ Koordinat otomatis dari alamat\nNomor: DRAFT-YYYYMMDD-001]

    SIMPAN_DRAFT --> FORM2[📋 FORM 2\nData Admin SPPG\nnama, email, no. HP]
    FORM2 --> FORM3[📋 FORM 3\nData Ahli Gizi & Admin Logistik\nopsional]
    FORM3 --> MITRA[👥 Tambah Sekolah Mitra\nminimal 1 sekolah]
    MITRA --> PETA[🗺️ Konfirmasi Titik di Peta\nSuperAdmin wajib lakukan ini]
    PETA --> SUBMIT[🚀 Submit & Resmikan SPPG]
    SUBMIT --> SELESAI([✅ SPPG Terdaftar & Aktif])
```

---

### 3a. Detail: Validasi Alamat Saat Isi Form 1

Saat pengguna mengetik alamat, sistem memvalidasinya secara otomatis ke layanan peta OpenStreetMap.

```mermaid
flowchart TD
    A[Pengguna ketik alamat:\nNama + Alamat + Kecamatan + Kota + Provinsi + Kapasitas] --> B[Sistem gabung semua jadi\nsatu kalimat pencarian]
    B --> C[Kirim ke OpenStreetMap\nuntuk dicari lokasinya]
    C --> D{Hasil pencarian?}
    D -- Tidak ditemukan --> E[❌ Tolak\nMinta format: Jl. Nama No. X, Kecamatan, Kota]
    D -- Ditemukan --> F[Hitung skor kepercayaan alamat\n0–100]
    F --> G[Simpan koordinat lat/lng\ndi dalam draft]
    G --> H[Draft dibuat dengan nomor unik:\nDRAFT-20260608-001]
    H --> I[✅ Berhasil — lanjut tambah mitra]
```

---

### 3b. Detail: Tambah Sekolah Mitra ke Draft

```mermaid
flowchart TD
    A[Input data sekolah:\nNama, NPSN, Alamat, Kecamatan, Kota, Jumlah Porsi] --> B[Validasi alamat sekolah\nke OpenStreetMap]
    B --> C{Alamat valid?}
    C -- Tidak --> D[❌ Tolak — tampilkan alternatif saran]
    C -- Ya --> E{NPSN sudah ada\ndi draft ini?}
    E -- Ya --> F[❌ Tolak — duplikat NPSN]
    E -- Tidak / Kosong --> G{Koordinat terlalu dekat\ndengan sekolah lain?\njarak < 50 meter}
    G -- Ya --> H[❌ Tolak — kemungkinan duplikat]
    G -- Tidak --> I[✅ Mitra ditambahkan ke draft\n+ koordinat tersimpan otomatis]
```

---

### 3c. Detail: Konfirmasi Titik di Peta — Langkah Kritis

Ini adalah langkah **paling penting** sebelum submit. SuperAdmin memilih/menggeser titik SPPG di peta, lalu klik "Konfirmasi".

```mermaid
flowchart TD
    A([SuperAdmin klik Konfirmasi Titik\ndi koordinat tertentu]) --> B

    B[LANGKAH 1: Validasi Titik\nApakah lokasi ini aman?] --> B1{Cek: ada SPPG aktif\nlain dalam radius 5 km?}
    B1 -- Ada & masih bisa tampung --> B2[🔴 MERAH: Konflik!\nLokasi terlalu dekat dengan SPPG lain\nyang masih memiliki kapasitas]
    B1 -- Ada tapi overcapacity --> B3[🟡 KUNING: Perhatian\nAda SPPG di dekat sini\ntapi sudah penuh]
    B1 -- Tidak ada --> B4[🟢 HIJAU: Aman\nTidak ada konflik lokasi]

    B2 & B3 & B4 --> C

    C[LANGKAH 2: Reverse Geocoding\nKoordinat → Alamat lengkap] --> C1[Sistem kirim koordinat ke OpenStreetMap]
    C1 --> C2[Dapat alamat otomatis:\nJalan, Kecamatan, Kota, Provinsi]
    C2 --> C3[Update otomatis data Form 1\nalamat SPPG diisi dari hasil ini]

    C3 --> D

    D[LANGKAH 3: Simpan ke Draft] --> D1[Simpan koordinat yang dikonfirmasi]
    D1 --> D2[Simpan status titik: hijau/kuning/merah]
    D2 --> D3[Tandai: map_confirmed = true\nSudah dikonfirmasi oleh SuperAdmin]

    D3 --> E

    E[LANGKAH 4: Cek Mitra Pengajuan yang Sudah Ada] --> E1{Untuk setiap mitra\nyang sudah ditambahkan}
    E1 --> E2[Hitung jarak & waktu tempuh\nke mitra tersebut via OSRM]
    E2 --> E3{Jarak > 5 km\natau waktu > 30 menit?}
    E3 -- Ya --> E4[⚠️ Tandai mitra: OUT OF RANGE\nSuperAdmin perlu tinjau ulang]
    E3 -- Tidak --> E5[✅ Mitra dalam jangkauan normal]
    E4 & E5 --> F

    F[LANGKAH 5: Rekomendasi Mitra Otomatis] --> F1[Cari semua sekolah\nyang BELUM punya SPPG\ndalam radius 5 km & 30 menit]
    F1 --> F2[Urutkan dari yang paling dekat]
    F2 --> F3{Data porsi tersedia?}
    F3 -- Ya --> F4[Pilih mitra sampai\ntotal porsi mencapai kapasitas SPPG]
    F3 -- Tidak --> F5[Ambil maksimal 4 sekolah terdekat]
    F4 & F5 --> F6

    F6{Sudah ada di daftar mitra\ndraft ini? cek NPSN & jarak}
    F6 -- Duplikat → skip --> F7[Abaikan]
    F6 -- Baru → tambahkan --> F8[✅ Tambahkan ke daftar mitra\ndengan label: Rekomendasi Sistem]

    F7 & F8 --> G

    G([Selesai! Laporan dikirim ke layar:\n• Status titik\n• Alamat yang diupdate\n• Jumlah mitra baru ditambahkan\n• Mitra yang out of range])
```

---

### 3d. Cek Sebelum Submit — Sistem Tolak Jika Tidak Lengkap

Saat SuperAdmin klik **Submit**, sistem menjalankan **4 pemeriksaan wajib** sebelum SPPG resmi terdaftar.

```mermaid
flowchart TD
    A([SuperAdmin klik SUBMIT]) --> G1

    G1{CEK 1: Status Draft\nApakah masih berstatus draft?}
    G1 -- Tidak / sudah didaftarkan --> ERR1[❌ Tolak: Draft ini sudah pernah didaftarkan]
    G1 -- Ya, masih draft --> G2

    G2{CEK 2: Data Wajib Lengkap?\nForm 1 & Form 2 harus terisi}
    G2 -- Belum lengkap --> ERR2[❌ Tolak: Isi data SPPG dan Admin SPPG terlebih dahulu]
    G2 -- Lengkap --> G3

    G3{CEK 3: Ada minimal\n1 sekolah mitra?}
    G3 -- Tidak ada --> ERR3[❌ Tolak: Harus ada minimal 1 mitra\nMinta SuperAdmin konfirmasi titik di peta dulu]
    G3 -- Ada --> G4

    G4{CEK 4: Titik di peta\nsudah dikonfirmasi?}
    G4 -- Belum --> ERR4[❌ Tolak: SuperAdmin harus klik Konfirmasi Titik\ndi halaman peta terlebih dahulu]
    G4 -- Sudah --> STEP1

    STEP1[STEP 1: Auto-Geocode Mitra\nJika ada mitra yang belum punya koordinat]
    STEP1 --> STEP1A{Mitra punya koordinat?}
    STEP1A -- Sudah ada --> STEP1B[Skip — tidak perlu]
    STEP1A -- Belum --> STEP1C[Cari koordinat dari alamat mitra\npakai OpenStreetMap]
    STEP1C --> STEP1D{Berhasil?}
    STEP1D -- Ya --> STEP1E[Simpan koordinat mitra]
    STEP1D -- Tidak --> STEP1F[Catat di log — lanjutkan saja]
    STEP1B & STEP1E & STEP1F --> STEP2

    STEP2{STEP 2: Masih ada mitra\ntanpa koordinat?}
    STEP2 -- Ya --> ERR5[❌ Tolak: Sebutkan nama sekolah\nyang gagal di-geocode\nMinta perbaiki alamat]
    STEP2 -- Tidak / semua OK --> STEP3

    STEP3[STEP 3: Daftarkan SPPG\nDalam satu transaksi tidak bisa sebagian]
    STEP3 --> STEP3A[Buat akun SPPG resmi\ndi database utama]
    STEP3A --> STEP3B[Buat akun Admin SPPG\ndengan username & password]
    STEP3B --> STEP3C[Buat akun Ahli Gizi\njika Form 3 diisi]
    STEP3C --> STEP3D[Buat akun Admin Logistik\njika Form 3 diisi]
    STEP3D --> STEP3E[Hubungkan semua mitra\nke SPPG yang baru]
    STEP3E --> STEP3F[Tandai draft: status = registered\ntanggal submit = sekarang]

    STEP3F --> DONE([✅ SPPG Resmi Terdaftar!\nSemua data aktif & siap digunakan])
```

---

## 4️⃣ Peta GIS — Melihat Gambaran di Peta

Halaman peta memungkinkan SuperAdmin melihat kondisi semua SPPG dan sekolah secara visual.

### 4a. Data Yang Muncul di Peta

```mermaid
flowchart LR
    A([SuperAdmin buka halaman Peta]) --> B[Sistem kumpulkan 4 lapis data]

    B --> C[🔵 LAYER 1: SPPG Aktif\nTitik biru = lokasi dapur aktif\n+ daftar mitra per SPPG]

    B --> D[🟡 LAYER 2: Draft Pengajuan\nTitik kuning = SPPG yang sedang dalam proses pendaftaran\nHanya yang sudah punya koordinat]

    B --> E[🟢 LAYER 3: Rekomendasi K-Means\nTitik rekomendasi lokasi SPPG baru\nDihitung otomatis oleh sistem]

    B --> F[⚫ LAYER 4: Data Semua Sekolah\nWarna berbeda:\n• Hijau = sudah dilayani SPPG\n• Merah = belum dilayani\n• Kuning = jarak terlalu jauh dari SPPG-nya]

    C & D & E & F --> G([Semua ditampilkan di satu peta])
```

---

### 4b. Bagaimana Rekomendasi Lokasi SPPG Baru Dihitung? (K-Means)

Sistem secara cerdas menghitung di mana sebaiknya SPPG baru dibangun, berdasarkan posisi sekolah yang belum terlayani.

```mermaid
flowchart TD
    A[Sistem cari semua sekolah\nyang BELUM punya SPPG] --> B
    B[Tambahkan sekolah yang SUDAH punya SPPG\ntapi jaraknya > 5 km dari dapur mereka\nmirip sekolah yang perlu SPPG baru] --> C

    C{Berapa banyak sekolah\nyang dikumpulkan?} --> D

    D[Tentukan jumlah kluster K:\n1 kluster per 5 sekolah\nMinimal 2, maksimal 10] --> E

    E[Tentukan titik awal K secara acak] --> F

    F[Loop hingga 20 kali / sampai stabil] --> F1[Kelompokkan setiap sekolah\nke kluster terdekat]
    F1 --> F2[Geser pusat kluster\nke rata-rata posisi anggotanya]
    F2 --> F3{Titik masih bergerak?}
    F3 -- Ya --> F1
    F3 -- Tidak / sudah 20 kali --> G

    G[Filter kluster:\nHanya tampilkan jika ada ≥ 2 sekolah\ndalam radius 5 km & 30 menit] --> H

    H([Tampilkan titik-titik rekomendasi\ndi peta — inilah lokasi ideal SPPG baru])
```

---

### 4c. Validasi Titik — Sistem Beri Sinyal Lampu Lalu Lintas

Saat SuperAdmin memilih titik baru di peta, sistem langsung mengecek apakah lokasi tersebut layak.

```mermaid
flowchart TD
    A([SuperAdmin pilih titik di peta]) --> B[Sistem cek semua SPPG aktif\nyang ada dalam radius 5 km]

    B --> C{Ada SPPG aktif\ndalam 5 km?}
    C -- Tidak ada --> D[🟢 HIJAU\nLokasi bebas konflik\nAman untuk SPPG baru]
    C -- Ada, masih bisa tampung --> E[🔴 MERAH\nKonflik!\nSPPG di dekat sini masih ada kapasitas\nTidak perlu SPPG baru di sini]
    C -- Ada, tapi sudah penuh/overcapacity --> F[🟡 KUNING\nPerhatian\nSPPG terdekat sudah overcapacity\nMungkin perlu SPPG baru]

    D & E & F --> G[Sistem juga cek:\nApakah ada sekolah mitra draft\nyang sudah terdaftar di SPPG lain?]
    G --> H{Sekolah sudah terdaftar\ndi SPPG lain & masih dekat dengan mereka?}
    H -- Ya, masih dekat → tidak bisa diambil --> I[🔴 Tambahkan ke konflik:\nSekolah ini tidak bisa di-takeover]
    H -- Sudah jauh dari SPPG lamanya --> J[🟡 Tambahkan ke perhatian:\nSekolah bisa di-takeover karena lebih dekat ke sini]
    I & J --> K([Tampilkan sinyal warna + daftar konflik])
```

---

### 4d. Saran Geser Titik ke Posisi Optimal

Fitur ini membantu SuperAdmin menemukan posisi **tengah-tengah** dari semua mitra yang ada.

```mermaid
flowchart TD
    A([SuperAdmin minta saran geser titik]) --> B[Cari semua mitra draft\nyang berada dalam radius 5 km dari titik saat ini]
    B --> C{Ada mitra\ndalam 5 km?}
    C -- Tidak ada --> D[Tidak ada saran — titik sudah optimal\natau tidak ada mitra di dekat sini]
    C -- Ada --> E[Hitung rata-rata koordinat\nsemua mitra tersebut]
    E --> F{Jarak pergeseran\n> 500 meter?}
    F -- Tidak perlu geser --> G[Tidak ada saran — perbedaan kecil\nkurang dari 500 meter]
    F -- Ya, perlu geser --> H([✅ Tampilkan koordinat baru yang disarankan\n+ berapa meter jarak pergeserannya])
```

---

### 4e. Cek Rute Antara Dua Titik

SuperAdmin bisa mengecek jarak dan waktu tempuh antara dua titik mana saja di peta.

```mermaid
flowchart LR
    A([Pilih Titik A dan Titik B di peta]) --> B[Sistem tanya ke layanan OSRM\nOpen Source Routing Machine]
    B --> C{OSRM berhasil\nmemberikan rute?}
    C -- Ya --> D[Tampil:\n• Jarak via jalan dalam km\n• Estimasi waktu dalam menit]
    C -- Gagal / timeout --> E[Tampil pesan error:\nGagal mendapat rute]
```

---

## 5️⃣ Manajemen Sekolah — Data Induk Mitra

SuperAdmin mengelola **database sekolah** yang menjadi calon mitra SPPG.

```mermaid
flowchart TD
    A([Buka halaman Sekolah]) --> B[Tampil daftar sekolah\ndengan filter:\nJenjang SD/SMP/SMA/SMK\nKota, Nama, Status SPPG]

    subgraph Tambah Sekolah
        C([Klik Tambah Sekolah]) --> D[Isi data:\nNama, NPSN, Jenjang, Status Kepemilikan\nAlamat, Kota, Kecamatan\nLatitude, Longitude, Jumlah Porsi]
        D --> E[Simpan ke database sekolah]
    end

    subgraph Assign ke SPPG
        F([Pilih sekolah → Assign ke SPPG]) --> G{Sekolah sudah di SPPG lain?}
        G -- Ya --> H[Tandai relasi lama: transferred]
        G -- Tidak --> I[Langsung assign]
        H & I --> J[Buat catatan relasi baru:\nSPPG - Sekolah sudah terhubung]
    end

    subgraph Lepas dari SPPG
        K([Lepas sekolah dari SPPG]) --> L[Sekolah dilepas\nTidak punya SPPG lagi]
        L --> M[Catatan relasi ditandai: inactive]
    end
```

> **Perbedaan Sekolah vs Mitra:** `Sekolah` adalah database induk. `Mitra (Partner)` adalah sekolah yang sudah terhubung ke SPPG tertentu. Saat sekolah di-assign, mereka menjadi mitra SPPG tersebut.

---

## 6️⃣ Manajemen Karyawan — Per SPPG

Setiap SPPG memiliki karyawan sendiri. SuperAdmin bisa mengelola karyawan di setiap SPPG.

```mermaid
flowchart TD
    A([Buka halaman SPPG → Kelola Karyawan]) --> B[Tampil daftar karyawan SPPG ini\ndengan filter: jabatan, status, nama]

    B --> C{Aksi yang dipilih}

    C -- Tambah --> D[Isi data karyawan:\nNama, jabatan, kontak, dll]
    D --> E[Simpan ke database\ndengan SPPG yang sesuai]

    C -- Edit --> F[Ubah data karyawan]
    F --> G[Simpan perubahan]

    C -- Hapus --> H[Hapus karyawan dari SPPG ini]
```

---

## 7️⃣ Laporan Keuangan

> ⚠️ **Fitur ini masih dalam pengembangan.** Endpoint sudah tersedia tapi belum ada implementasi logika bisnis. Rencananya akan mendukung: tambah laporan, lihat laporan, edit, dan hapus.

---

## 🔐 Keamanan Akses — Siapa Yang Bisa Akses Apa?

```mermaid
flowchart LR
    A([Pengguna mengakses sistem]) --> B{Login?}
    B -- Belum login --> C[❌ Ditolak\nHarus login terlebih dahulu]
    B -- Sudah login --> D{Role akun?}
    D -- Bukan SuperAdmin --> E[❌ Ditolak\nFitur SuperAdmin khusus role super_admin]
    D -- SuperAdmin --> F[✅ Akses penuh ke semua fitur SuperAdmin]

    G([Pengguna biasa / user sudah login]) --> H{Akses draft SPPG?}
    H -- Ya --> I[✅ Bisa buat draft & tambah mitra\nTidak bisa akses fitur SuperAdmin lain]
```

**Aturan akses:**
| Fitur | SuperAdmin | User Biasa (Login) | Publik |
|---|---|---|---|
| Dashboard, SPPG, Peta, Sekolah, Karyawan | ✅ | ❌ | ❌ |
| Buat draft pengajuan SPPG | ✅ | ✅ | ❌ |
| Konfirmasi titik & submit SPPG | ✅ | ❌ | ❌ |

---

## 📊 Status & Label yang Perlu Dipahami

### Status SPPG
| Status | Artinya |
|---|---|
| `active` | SPPG beroperasi normal |
| `inactive` | SPPG dinonaktifkan sementara, karyawan tidak bisa login |
| `pending` | SPPG baru didaftarkan, belum aktif |
| `deleted` | SPPG telah dihapus |

### Status Draft Pengajuan
| Status | Artinya |
|---|---|
| `draft` | Masih dalam proses pengisian |
| `registered` | Sudah di-submit, SPPG resmi terdaftar |

### Status Titik di Peta
| Warna | Artinya |
|---|---|
| 🟢 Hijau | Lokasi aman, tidak ada konflik |
| 🟡 Kuning | Ada perhatian, tapi masih bisa dilanjutkan |
| 🔴 Merah | Ada konflik serius, perlu ditinjau ulang |

### Status Sekolah di Peta
| Status | Artinya |
|---|---|
| `served` | Sudah terhubung ke SPPG dan dalam jangkauan ≤ 5 km |
| `unserved` | Belum terhubung ke SPPG manapun |
| `takeover_candidate` | Terhubung ke SPPG tapi jaraknya > 5 km — kandidat pindah SPPG |

### Status Mitra dalam Draft
| Data Source | Artinya |
|---|---|
| `manual` | Ditambahkan manual oleh pengguna |
| `system_recommendation` | Ditambahkan otomatis saat konfirmasi titik |
| `database` | Dari database sekolah yang ada |
| `out_of_range` | Mitra ini di luar jangkauan 5 km / 30 menit |

---

## 🔄 Ringkasan Seluruh Alur dalam Satu Diagram

```mermaid
flowchart TD
    LOGIN([🔐 SuperAdmin Login]) --> DASH

    DASH[📊 Dashboard\nLihat statistik ringkasan] --> PILIH{Pilih Menu}

    PILIH --> SPPG_LIST[📋 Daftar SPPG]
    SPPG_LIST --> SPPG_DETAIL[Detail SPPG\nInfo + Mitra + Menu]
    SPPG_LIST --> SPPG_TOGGLE[Aktifkan / Nonaktifkan\nOtomatis atur akun karyawan]
    SPPG_LIST --> SPPG_DELETE[Hapus SPPG\nLepas semua mitra]

    PILIH --> DAFTAR[🆕 Daftar SPPG Baru]
    DAFTAR --> DRAFT_FORM1[Isi Form 1\nData SPPG + validasi alamat]
    DRAFT_FORM1 --> DRAFT_FORM2[Isi Form 2\nData Admin SPPG]
    DRAFT_FORM2 --> DRAFT_FORM3[Isi Form 3\nAhli Gizi & Logistik - opsional]
    DRAFT_FORM3 --> DRAFT_MITRA[Tambah Sekolah Mitra]
    DRAFT_MITRA --> DRAFT_MAP[Konfirmasi Titik di Peta\nDapat rekomendasi mitra otomatis]
    DRAFT_MAP --> DRAFT_SUBMIT[Submit\n4 pemeriksaan wajib]
    DRAFT_SUBMIT --> SPPG_RESMI([✅ SPPG Resmi Aktif])

    PILIH --> PETA[🗺️ Peta GIS]
    PETA --> PETA_VIEW[Lihat 4 layer:\nSPPG aktif + Draft + Rekomendasi + Sekolah]
    PETA --> PETA_KMEANS[Rekomendasi Lokasi\nDihitung K-Means]
    PETA --> PETA_VALIDATE[Validasi Titik\n🟢🟡🔴]
    PETA --> PETA_SHIFT[Saran Geser ke Centroid]
    PETA --> PETA_ROUTE[Cek Rute A ke B]
    PETA --> PETA_CONFIRM[Konfirmasi Titik\n→ Dapat rekomendasi mitra]

    PILIH --> SEKOLAH[🏫 Manajemen Sekolah\nCRUD + Assign ke SPPG]
    PILIH --> KARYAWAN[👨‍💼 Manajemen Karyawan\nCRUD per SPPG]
    PILIH --> KEUANGAN[💰 Laporan Keuangan\nDalam pengembangan]
```

---

*Dokumen ini dibuat otomatis berdasarkan analisis kode backend COMS-MBG — branch `dev`, commit terakhir: `f8c27e3`*
