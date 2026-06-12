# User Guide — COMS MBG
> Dokumen ini adalah **acuan pembuatan panduan pengguna (user guide)** sistem COMS MBG.
> Fokus pada **alur & step-by-step dari sudut pandang pengguna**, bukan teknis/API.

---

## Daftar Isi

- [A. Alur Login (Semua Role)](#a-alur-login-semua-role)
- [B. Superadmin](#b-superadmin)
  - [B1. Dashboard](#b1-dashboard)
  - [B2. Mendaftarkan SPPG Baru (via Draft)](#b2-mendaftarkan-sppg-baru-via-draft)
  - [B3. Kelola SPPG yang Sudah Ada](#b3-kelola-sppg-yang-sudah-ada)
  - [B4. Kelola Staf SPPG](#b4-kelola-staf-sppg)
  - [B5. Peta GIS & Rekomendasi Lokasi](#b5-peta-gis--rekomendasi-lokasi)
- [C. Admin SPPG / Pemilik](#c-admin-sppg--pemilik)
  - [C1. Login Pertama Kali (Aktivasi SPPG)](#c1-login-pertama-kali-aktivasi-sppg)
  - [C2. Dashboard](#c2-dashboard)
  - [C3. Kelola Karyawan](#c3-kelola-karyawan)
  - [C4. Kelola Sekolah Mitra](#c4-kelola-sekolah-mitra)
  - [C5. Approve / Tolak Pengajuan Stok](#c5-approve--tolak-pengajuan-stok)
  - [C6. Monitor Distribusi](#c6-monitor-distribusi)
- [D. Ahli Gizi](#d-ahli-gizi)
  - [D1. Kelola Bahan Baku](#d1-kelola-bahan-baku)
  - [D2. Buat Resep](#d2-buat-resep)
  - [D3. Buat Menu Mingguan](#d3-buat-menu-mingguan)
  - [D4. Cek Stok & Publikasikan Menu](#d4-cek-stok--publikasikan-menu)
- [E. Admin Logistik](#e-admin-logistik)
  - [E1. Ajukan Stok Baru](#e1-ajukan-stok-baru)
  - [E2. Kelola Jadwal Pengiriman](#e2-kelola-jadwal-pengiriman)
  - [E3. Konfirmasi Bukti Pengiriman](#e3-konfirmasi-bukti-pengiriman)
  - [E4. Optimasi Rute](#e4-optimasi-rute)
- [F. Admin SPPG — Submit Tugas ke Kurir](#f-admin-sppg--submit-tugas-ke-kurir)
- [G. Kurir](#g-kurir)
  - [G1. Terima / Tolak Tugas](#g1-terima--tolak-tugas)
  - [G2. Antar & Kirim Bukti](#g2-antar--kirim-bukti)
  - [G3. Resubmit Bukti (jika diminta revisi)](#g3-resubmit-bukti-jika-diminta-revisi)
- [H. Alur Distribusi Lengkap (Gabungan Semua Aktor)](#h-alur-distribusi-lengkap-gabungan-semua-aktor)

---

## A. Alur Login (Semua Role)

**Halaman**: Halaman Login

### Step-by-step:

1. Pengguna membuka aplikasi → diarahkan ke halaman Login.
2. Masukkan **Email** dan **Password** lalu klik **Masuk**.
3. Sistem mengecek:

   | Kondisi | Hasil |
   |:--------|:------|
   | Email/password salah | Muncul pesan error: "Email atau password tidak valid." |
   | Akun tidak aktif (dinonaktifkan admin) | Muncul pesan: "Akun Anda tidak aktif. Hubungi administrator." |
   | SPPG tidak aktif + bukan pemilik | Muncul pesan: "SPPG Anda sedang tidak aktif." |
   | Lebih dari 5 kali gagal dalam 1 menit | Muncul pesan: "Terlalu banyak percobaan. Coba lagi nanti." |
   | Berhasil | Diarahkan ke dashboard sesuai role |

4. Setelah berhasil, sistem menentukan halaman tujuan berdasarkan **role**:

   | Role | Diarahkan ke |
   |:-----|:------------|
   | `super_admin` | Dashboard Superadmin |
   | `sppg_user` (Admin SPPG / Pemilik) | Dashboard Admin SPPG |
   | `sppg_user` (Ahli Gizi) | Dashboard Ahli Gizi |
   | `sppg_user` (Admin Logistik) | Dashboard Admin Logistik |
   | `sppg_user` (Kurir) | Dashboard Kurir |

> **Catatan**: Jika pemilik SPPG login untuk pertama kali ke SPPG yang baru didaftarkan (status: inactive), sistem **otomatis mengaktifkan SPPG** tersebut.

---

## B. Superadmin

### B1. Dashboard

**Halaman**: Dashboard Superadmin

Setelah login, Superadmin melihat ringkasan:
- Jumlah total SPPG (aktif / tidak aktif)
- Total sekolah mitra yang terlayani
- Total porsi harian seluruh Indonesia

Dari dashboard, Superadmin bisa navigasi ke:
- Manajemen SPPG
- Peta GIS
- Laporan Keuangan

---

### B2. Mendaftarkan SPPG Baru (via Draft)

**Halaman**: Manajemen SPPG → Daftarkan SPPG Baru

Superadmin mengisi form multi-langkah. Data tersimpan otomatis (auto-save) di setiap step.

#### Step 1 — Data SPPG
1. Klik **"Daftarkan SPPG Baru"**.
2. Isi **Form 1 — Data SPPG**:
   - Nama SPPG
   - Alamat, Kecamatan, Kota, Provinsi
   - Kapasitas (jumlah maksimum sekolah yang bisa dilayani)
   - Koordinat (bisa ketik manual atau pilih dari peta)
3. Klik **"Simpan & Lanjut"** → data tersimpan otomatis sebagai draft.

#### Step 2 — Data Admin SPPG (Pemilik)
4. Isi **Form 2 — Data Admin SPPG**:
   - Nama lengkap pemilik
   - Email (akan digunakan sebagai username login)
   - Password awal (akan dikirim via email)
5. Klik **"Simpan & Lanjut"**.

#### Step 3 — Staf Tambahan (Opsional)
6. Isi **Form 3 — Ahli Gizi & Admin Logistik** (boleh dilewati):
   - Data Ahli Gizi: nama, email
   - Data Admin Logistik: nama, email
7. Klik **"Simpan & Lanjut"**.

#### Step 4 — Tambah Sekolah Mitra
8. Tambahkan sekolah mitra yang akan dilayani SPPG ini:
   - Cari sekolah dari database → klik **"Tambah"**
   - Atau tambah manual: nama sekolah, NPSN, jenjang, status, alamat, koordinat, jumlah porsi
9. Daftar sekolah yang ditambahkan muncul di tabel.
10. Klik **"Simpan & Lanjut"**.

#### Step 5 — Validasi Titik di Peta
11. Peta menampilkan titik koordinat SPPG yang diusulkan beserta semua sekolah mitranya.
12. Sistem menampilkan status validasi titik:
    - 🟢 **Hijau**: Lokasi aman, tidak tumpang tindih dengan SPPG lain.
    - 🟡 **Kuning**: Ada SPPG lain di sekitar, tapi sudah overcapacity — boleh didirikan.
    - 🔴 **Merah**: Terlalu dekat dengan SPPG aktif yang masih ada kapasitas — tidak direkomendasikan.
13. Jika ada saran pergeseran titik (centroid sekolah mitra), sistem menampilkan tombol **"Pindahkan ke Titik Optimal"**.
14. Klik **"Konfirmasi Titik"** untuk menyimpan koordinat final.

#### Step 6 — Finalisasi
15. Review semua data yang sudah diisi.
16. Klik **"Daftarkan SPPG"** → sistem memproses:
    - Membuat record SPPG (status: inactive)
    - Membuat akun user Admin SPPG (+ Ahli Gizi / Admin Logistik jika diisi)
    - Mengirim email ke setiap akun yang dibuat berisi kredensial login
    - Memasukkan semua sekolah mitra ke sistem
17. Muncul notifikasi sukses: **"SPPG berhasil didaftarkan. Email kredensial telah dikirim."**

> **Penting**: SPPG yang baru didaftarkan berstatus **inactive** sampai pemilik login pertama kali.

---

### B3. Kelola SPPG yang Sudah Ada

**Halaman**: Manajemen SPPG → Daftar SPPG

#### Melihat Daftar SPPG:
1. Buka menu **Manajemen SPPG**.
2. Gunakan filter: Status (aktif/tidak aktif), Kota, atau Pencarian nama.
3. Klik nama SPPG untuk melihat **detail** (informasi, kapasitas, daftar mitra, daftar menu).

#### Mengaktifkan SPPG:
1. Buka detail SPPG yang berstatus tidak aktif.
2. Klik tombol **"Aktifkan SPPG"**.
3. Konfirmasi → semua akun staf SPPG ini otomatis diaktifkan.

#### Menonaktifkan SPPG:
1. Buka detail SPPG yang aktif.
2. Klik tombol **"Nonaktifkan SPPG"**.
3. Konfirmasi → semua akun staf SPPG ini tidak bisa login lagi.

#### Menghapus SPPG:
1. Buka detail SPPG.
2. Klik tombol **"Hapus SPPG"**.
3. Muncul peringatan: "Semua staf akan dinonaktifkan dan semua sekolah mitra akan dilepas. Lanjutkan?"
4. Konfirmasi → SPPG dihapus (soft delete), semua staf nonaktif, semua mitra dilepas.

#### Lihat Jarak ke Sekolah Mitra:
1. Di halaman detail SPPG, buka tab **"Sekolah Mitra"**.
2. Sistem menampilkan setiap sekolah mitra + jarak (km) dari SPPG + estimasi waktu tempuh.
3. Sekolah yang jaraknya > 5 km ditandai dengan label **"Perlu Ditinjau"**.

---

### B4. Kelola Staf SPPG

**Halaman**: Detail SPPG → Tab Karyawan

1. Buka detail SPPG → pilih tab **"Karyawan"**.
2. Gunakan filter: Jabatan, Status, atau Pencarian nama.
3. Untuk menambah staf baru: klik **"Tambah Karyawan"** → isi form → simpan.
4. Untuk edit: klik nama karyawan → klik **"Edit"** → ubah data → simpan.
5. Untuk hapus: klik nama karyawan → klik **"Hapus"** → konfirmasi.

---

### B5. Peta GIS & Rekomendasi Lokasi

**Halaman**: Menu Peta / GIS

1. Buka menu **Peta**.
2. Peta menampilkan 3 layer:
   - **Layer SPPG Aktif**: Titik-titik SPPG yang sudah beroperasi + garis koneksi ke sekolah mitra.
   - **Layer Draft**: Titik-titik kandidat SPPG yang sedang dalam proses pendaftaran.
   - **Layer Rekomendasi**: Titik-titik yang disarankan sistem (hasil K-Means) berdasarkan konsentrasi sekolah yang belum terlayani.
3. Klik sebuah titik rekomendasi untuk melihat: berapa sekolah yang bisa dilayani, daftar nama sekolah.
4. Gunakan tombol **"Cari Lokasi"** untuk geocoding alamat → sistem memindahkan peta ke koordinat tersebut.
5. Gunakan tombol **"Cek Rute"** untuk melihat estimasi waktu & jarak antara 2 titik.

---

## C. Admin SPPG / Pemilik

### C1. Login Pertama Kali (Aktivasi SPPG)

1. Buka email yang diterima dari sistem (berisi email & password awal).
2. Buka aplikasi → masuk ke halaman Login.
3. Masukkan email dan password dari email → klik **"Masuk"**.
4. Sistem otomatis mengaktifkan SPPG → Admin SPPG masuk ke Dashboard.
5. Disarankan langsung **ubah password** di halaman Profil.

---

### C2. Dashboard

**Halaman**: Dashboard Admin SPPG

Admin SPPG melihat ringkasan kondisi SPPG-nya:
- **Jadwal Hari Ini**: Berapa pengiriman sedang berjalan, sudah selesai, menunggu konfirmasi.
- **Kurir Aktif**: Berapa kurir yang saat ini sedang mengantar.
- **Perlu Tindakan**: Daftar pengiriman yang sudah tiba (status: delivered) namun belum dikonfirmasi.
- **Riwayat Bulan Ini**: Total pengiriman, total jarak, rata-rata durasi.
- **Kelengkapan Staf**: Apakah Ahli Gizi dan Admin Logistik sudah terdaftar.

Jika **Kelengkapan Staf belum lengkap**, sistem menampilkan banner peringatan: *"SPPG Anda belum memiliki Ahli Gizi / Admin Logistik. Tambahkan di menu Karyawan."*

---

### C3. Kelola Karyawan

**Halaman**: Karyawan

1. Buka menu **Karyawan**.
2. Melihat daftar semua staf SPPG + role masing-masing.
3. **Tambah karyawan baru**:
   - Klik **"Tambah Karyawan"**
   - Isi: nama, email, jabatan, tanggal bergabung
   - Klik **"Simpan"**
4. **Assign Role ke karyawan**:
   - Klik nama karyawan → klik **"Assign Role"**
   - Pilih role dari daftar yang tersedia (Ahli Gizi, Admin Logistik, Kurir, dll)
   - Klik **"Simpan"**
   > Penting: Role menentukan permission apa yang dimiliki karyawan tersebut.
5. **Edit data karyawan**: Klik nama → **"Edit"** → ubah → simpan.
6. **Hapus karyawan**: Klik nama → **"Hapus"** → konfirmasi.

**Kelola Role & Permission**:
1. Buka submenu **Role** di halaman Karyawan.
2. Lihat daftar role + berapa staf yang memakai role tersebut.
3. Klik role untuk melihat daftar permission yang dimilikinya.
4. Tambah/edit role custom jika diperlukan.
> Role tidak bisa dihapus jika masih ada karyawan yang menggunakannya.

---

### C4. Kelola Sekolah Mitra

**Halaman**: Sekolah Mitra

1. Buka menu **Sekolah Mitra**.
2. Melihat daftar sekolah mitra yang dilayani SPPG ini.
3. **Tambah mitra baru**:
   - Klik **"Tambah Mitra"**
   - Isi data: nama sekolah, NPSN, jenjang, status kepemilikan, alamat, kota, kecamatan, koordinat, **jumlah porsi**
   - Klik **"Simpan"**
   > Jumlah porsi sangat penting — ini yang dipakai sistem untuk menghitung kebutuhan stok.
4. **Import massal**: Klik **"Import"** → unggah file CSV/Excel → sistem memproses dan menampilkan laporan (berhasil/gagal per baris).
5. **Edit mitra**: Klik nama sekolah → **"Edit"** → ubah → simpan.
6. **Hapus mitra**: Klik nama → **"Hapus"** → konfirmasi.

---

### C5. Approve / Tolak Pengajuan Stok

**Halaman**: Stok → Menunggu Persetujuan

1. Buka menu **Stok** → pilih tab **"Menunggu Persetujuan"**.
2. Muncul daftar batch stok yang diajukan Admin Logistik (status: pending).
3. Klik nama batch untuk melihat detail: bahan baku, jumlah, satuan, harga/unit, tanggal beli, tanggal kadaluarsa, supplier, foto bukti pembelian.
4. **Approve**:
   - Klik **"Setujui"**
   - Sistem generate nomor batch otomatis (format: `BATCH-20260603-001`)
   - Stok masuk ke inventaris, status berubah menjadi **available**
   - Histori transaksi tercatat otomatis
5. **Tolak**:
   - Klik **"Tolak"**
   - Konfirmasi → batch ditolak, tidak masuk ke inventaris

---

### C6. Monitor Distribusi

**Halaman**: Dashboard → Perlu Tindakan / Live Map

1. Di Dashboard, lihat bagian **"Perlu Tindakan"** → daftar pengiriman yang sudah tiba tapi belum dikonfirmasi.
2. Buka menu **Peta Distribusi** → melihat posisi kurir secara real-time di peta.
3. Klik kurir di peta untuk melihat: sedang menuju sekolah mana, posisi GPS terakhir, kecepatan.

---

## D. Ahli Gizi

### D1. Kelola Bahan Baku

**Halaman**: Nutrisi → Bahan Baku

1. Buka menu **Nutrisi** → pilih **"Bahan Baku"**.
2. Melihat daftar semua bahan baku + nilai gizi.
3. **Tambah bahan baku baru**:
   - Klik **"Tambah Bahan Baku"**
   - Isi: nama, satuan (kg/liter/gram/ml/pcs)
   - Isi nilai gizi per 100g: kalori, protein, lemak, karbohidrat
   - Klik **"Simpan"**
4. **Preview kalkulasi gizi**: Di form bahan baku, ketik berat (gram) → sistem langsung menampilkan estimasi nilai gizi secara live (tanpa perlu simpan dulu).
5. **Edit / Hapus**: Klik nama bahan baku → edit atau hapus.
   > Bahan baku tidak bisa dihapus jika sudah digunakan di resep.

---

### D2. Buat Resep

**Halaman**: Nutrisi → Resep

1. Buka menu **Nutrisi** → pilih **"Resep"**.
2. Klik **"Buat Resep Baru"**.
3. Isi:
   - Nama resep
   - Klik **"Tambah Bahan"** → pilih bahan baku dari dropdown → isi berat (gram per porsi) → klik **"Tambah"**
   - Ulangi untuk semua bahan
4. Sistem menampilkan total nilai gizi resep secara otomatis berdasarkan komposisi bahan.
5. Klik **"Simpan Resep"**.

---

### D3. Buat Menu Mingguan

**Halaman**: Nutrisi → Menu

1. Buka menu **Nutrisi** → pilih **"Menu"**.
2. Klik **"Buat Menu Baru"**.
3. Isi:
   - Nama menu (contoh: "Menu Minggu ke-23")
   - Tanggal mulai & tanggal selesai (week_start / week_end)
4. Tambahkan item menu per hari:
   - Pilih hari (Senin, Selasa, dll)
   - Pilih waktu makan (makan siang / makan malam)
   - Pilih resep dari dropdown
   - Klik **"Tambah"**
   - Ulangi untuk semua hari dalam periode
5. Tampilan kalender menunjukkan distribusi menu per hari.
6. Klik **"Simpan Menu"** → menu tersimpan dengan status **draft**.

---

### D4. Cek Stok & Publikasikan Menu

**Halaman**: Nutrisi → Menu → Detail Menu

1. Buka menu **Nutrisi** → pilih **"Menu"** → klik menu yang ingin dipublikasikan.
2. **Cek kecukupan stok** (opsional tapi direkomendasikan):
   - Klik **"Cek Stok"** → sistem menghitung kebutuhan bahan baku untuk semua sekolah mitra.
   - Jika ada kekurangan, muncul tabel: bahan apa yang kurang, butuh berapa, tersedia berapa, kekurangan berapa.
   - Jika kekurangan → minta Admin Logistik untuk menambah stok terlebih dahulu.
3. Jika stok cukup, klik **"Publikasikan Menu"**.
4. Sistem memproses:
   - Cek ulang ketersediaan stok secara akurat
   - Potong stok bahan baku secara otomatis (metode FIFO: batch terlama digunakan duluan)
   - Catat semua mutasi stok
5. **Jika stok tidak cukup saat publikasi**: Muncul pesan error + rincian bahan yang kurang → menu **tidak** dipublikasikan.
6. **Jika berhasil**: Menu berubah status menjadi **published** → notifikasi sukses.

---

## E. Admin Logistik

### E1. Ajukan Stok Baru

**Halaman**: Stok → Tambah Pengajuan

1. Buka menu **Stok**.
2. Melihat ringkasan stok semua bahan baku: status (tersedia/menipis/habis/kadaluarsa), jumlah saat ini, batas minimum.
3. Klik **"Ajukan Stok Baru"**.
4. Isi form:
   - Pilih bahan baku
   - Jumlah & satuan
   - Harga per satuan
   - Tanggal pembelian
   - Tanggal kadaluarsa
   - Nama supplier
   - Jenis penyimpanan (dry / chilled / frozen)
   - Lokasi penyimpanan (opsional)
   - SKU / kode produk (opsional)
   - Catatan (opsional)
   - Unggah foto bukti pembelian (opsional, max 2MB)
5. Klik **"Ajukan"** → pengajuan masuk ke daftar **"Menunggu Persetujuan"** dan menunggu di-approve oleh Admin SPPG/Pemilik.

**Edit pengajuan** (jika belum di-approve):
1. Buka menu **Stok** → tab **"Menunggu Persetujuan"**.
2. Klik pengajuan yang ingin diedit → klik **"Edit"** → ubah → simpan.
> Pengajuan yang sudah di-approve atau ditolak tidak bisa diedit.

**Atur Batas Minimum Stok**:
1. Buka detail bahan baku → klik **"Atur Minimum"**.
2. Isi jumlah minimum & satuan → simpan.
3. Sistem otomatis mengupdate status stok (available/low/empty) berdasarkan batas baru.

---

### E2. Kelola Jadwal Pengiriman

**Halaman**: Distribusi → Jadwal Pengiriman

1. Buka menu **Distribusi** → pilih **"Jadwal Pengiriman"**.
2. Melihat daftar jadwal aktif + statusnya.
3. **Buat jadwal pengiriman baru**:
   - Klik **"Buat Jadwal"**
   - Pilih **Kurir** dari dropdown (hanya kurir aktif yang punya akun)
   - Pilih **Sekolah** tujuan
   - Pilih **Tanggal** pengiriman
   - Pilih **Jenis Kendaraan**
   - Tambah catatan (opsional)
   - Klik **"Simpan"** → jadwal dibuat dengan status **in_order**
4. **Edit jadwal** (hanya jika status masih in_order):
   - Klik nama jadwal → **"Edit"** → ubah → simpan.
5. **Hapus jadwal** (hanya jika status masih in_order):
   - Klik nama jadwal → **"Hapus"** → konfirmasi.

---

### E3. Konfirmasi Bukti Pengiriman

**Halaman**: Distribusi → Jadwal Pengiriman → Status: delivered

1. Buka menu **Distribusi** → filter status **"Terkirim (menunggu konfirmasi)"**.
2. Klik jadwal yang perlu dikonfirmasi.
3. Lihat detail: foto bukti yang diunggah kurir, waktu tiba, koordinat.
4. **Konfirmasi**:
   - Klik **"Konfirmasi Pengiriman"**
   - Isi catatan (opsional)
   - Klik **"Simpan"** → pengiriman selesai, data diarsipkan ke Riwayat Pengiriman
5. **Minta Revisi** (jika bukti tidak valid):
   - Klik **"Minta Revisi"**
   - Isi alasan revisi (wajib, min 5 karakter)
   - Klik **"Kirim"** → kurir mendapat notifikasi dan harus upload ulang

---

### E4. Optimasi Rute

**Halaman**: Distribusi → Peta → Optimasi Rute

1. Buka menu **Distribusi** → pilih **"Peta"**.
2. Klik tombol **"Optimasi Rute"**.
3. Pilih sekolah-sekolah tujuan (bisa pilih lebih dari 1, max 30).
4. Klik **"Hitung Rute Optimal"** → sistem menghitung urutan kunjungan terdekat + rute jalan sesungguhnya.
5. Peta menampilkan:
   - Urutan kunjungan yang direkomendasikan (1 → 2 → 3 → dst)
   - Garis rute di jalan (bukan garis lurus)
   - Total jarak (km) dan estimasi waktu tempuh
6. Admin Logistik bisa gunakan urutan ini sebagai acuan saat membuat jadwal pengiriman.

---

## F. Admin SPPG — Submit Tugas ke Kurir

**Halaman**: Distribusi → Jadwal Pengiriman

Setelah Admin Logistik membuat jadwal (status: in_order), Admin SPPG bertugas mengirim tugas tersebut ke kurir:

1. Buka menu **Distribusi** → filter status **"In Order"**.
2. Klik jadwal yang ingin disubmit.
3. Review detail: kurir yang ditugaskan, sekolah tujuan, tanggal.
4. Klik **"Kirim ke Kurir"**.
5. Kurir yang bersangkutan langsung mendapat notifikasi real-time di aplikasinya.
6. Status jadwal berubah menjadi **delivering**.

---

## G. Kurir

### G1. Terima / Tolak Tugas

**Halaman**: Dashboard Kurir → Notifikasi Tugas Baru

1. Kurir mendapat notifikasi: *"Ada tugas pengiriman baru ke [Nama Sekolah] pada [Tanggal]."*
2. Buka notifikasi → lihat detail tugas: sekolah tujuan, waktu, catatan dari admin.
3. **Terima tugas**:
   - Klik **"Terima Tugas"** → status berubah menjadi **delivering**
   - Kurir mulai mengantar
4. **Tolak tugas**:
   - Klik **"Tolak Tugas"**
   - Isi alasan penolakan (wajib)
   - Unggah foto pendukung (opsional, misal: kendaraan rusak)
   - Klik **"Kirim"** → Admin SPPG mendapat notifikasi

---

### G2. Antar & Kirim Bukti

**Halaman**: Dashboard Kurir → Jadwal Aktif

1. Setelah menerima tugas, buka jadwal yang sedang berjalan.
2. **Selama mengantarkan**, aplikasi mengirim posisi GPS secara otomatis ke sistem setiap ~5 detik (tracking real-time).
3. Setelah tiba di sekolah dan menyerahkan makanan, klik **"Upload Bukti Pengiriman"**.
4. Ambil foto bukti (penerimaan dari pihak sekolah / kondisi makanan / dokumen tanda terima).
5. Klik **"Kirim Bukti"** → status berubah menjadi **delivered**.
6. Admin Logistik mendapat notifikasi untuk mengkonfirmasi.

---

### G3. Resubmit Bukti (jika diminta revisi)

**Halaman**: Dashboard Kurir → Jadwal → Status: Revisi Diminta

1. Kurir mendapat notifikasi: *"Bukti pengiriman Anda diminta untuk direvisi. Alasan: [alasan dari admin]."*
2. Buka jadwal yang berstatus **Revisi Diminta**.
3. Baca catatan revisi dari admin.
4. Ambil foto bukti yang lebih baik / sesuai permintaan admin.
5. Klik **"Kirim Ulang Bukti"** → status kembali ke **delivered** (menunggu konfirmasi ulang).

---

## H. Alur Distribusi Lengkap (Gabungan Semua Aktor)

Ini adalah gambaran besar alur distribusi dari awal sampai selesai:

```
ADMIN LOGISTIK
  ↓  Buat jadwal pengiriman (in_order)
  ↓  [Opsional] Cek optimasi rute

ADMIN SPPG
  ↓  Submit tugas ke kurir → notifikasi dikirim ke kurir (delivering)

KURIR
  ↓  Terima tugas
  ↓  Mulai antar — GPS tracking berjalan otomatis (real-time di peta admin)
  ↓  Tiba di sekolah — upload foto bukti (delivered)

ADMIN LOGISTIK
  ↓  Cek foto bukti
  ├── [Jika valid] Konfirmasi → pengiriman selesai & diarsipkan ke Riwayat
  └── [Jika tidak valid] Minta Revisi → kurir dapat notifikasi

KURIR (jika diminta revisi)
  ↓  Upload ulang bukti (delivered, kembali menunggu)

ADMIN LOGISTIK
  ↓  Konfirmasi akhir → pengiriman selesai & diarsipkan
```

### Status Jadwal yang Mungkin Dilihat Pengguna:

| Status | Label di UI | Artinya |
|:-------|:-----------|:--------|
| `in_order` | Menunggu Dikirim | Jadwal dibuat, belum disubmit ke kurir |
| `delivering` | Sedang Diantar | Kurir sudah menerima & sedang mengantar |
| `delivered` | Menunggu Konfirmasi | Kurir sudah upload bukti |
| `revision_required` | Revisi Diminta | Admin minta bukti diulang |
| `confirmed` | Selesai | Admin konfirmasi, diarsipkan |
| `rejected` | Ditolak | Kurir menolak tugas |

---

> *User Guide ini merupakan acuan untuk pembuatan panduan pengguna COMS MBG. Terakhir diperbarui: 2026-06-03.*
