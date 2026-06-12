# Analisis Alur Kerja, Konflik, dan Bug Sistem Admin SPPG

Dokumen ini menyajikan analisis mendalam mengenai alur kerja (workflow) Admin SPPG pada sistem **COMS-MBG**, mengidentifikasi **Bug Kritis** dan **Konflik Logika**, serta merinci **Alur UX Detail** sebagai panduan pengujian atau analisis manual.

---

## 1. ⚙️ DAFTAR BUG & KONFLIK LOGIKA DI TEMUKAN

Berdasarkan analisis kode sumber di `app/Http/Controllers/API/AdminSPPG/` dan dependensinya, ditemukan beberapa celah keamanan, inkonsistensi data, dan bug logika yang signifikan:

### 🔴 1. Kebocoran Data Multi-Tenancy (Data Leakage)
Sistem COMS-MBG dirancang agar setiap SPPG (Satuan Penyelenggara Program Gizi) terisolasi. Namun, beberapa endpoint di bawah namespace `AdminSPPG` tidak memfilter data berdasarkan `sppg_id` pengguna yang login:

*   **A. Distribusi & Jadwal Pengiriman (`DistributionController` & `DeliveryScheduleController`)**
    *   **Masalah:** Saat mengambil list aktif (`index`) atau detail (`show`), sistem mengambil data `DeliverySchedule` secara global tanpa memfilter berdasarkan `sppg_id` dari user/sekolah/kurir yang terhubung.
    *   **Dampak:** Admin SPPG A dapat melihat jadwal pengiriman, tujuan sekolah, nama kurir, dan detail logistik dari SPPG B. Admin juga bisa memanggil `submitTask` untuk mengirim tugas kurir milik SPPG lain.
*   **B. Manajemen Mitra (`PartnerController` & `PartnerService`)**
    *   **Masalah:** Endpoint `GET /api/admin-sppg/partners` memanggil `PartnerService::getAll()` yang tidak menerima parameter `sppg_id`. Begitu pula dengan `summary`, `store`, `update`, dan `destroy`.
    *   **Dampak:** Admin SPPG A dapat melihat, mengubah, menghapus, bahkan mengimpor mitra sekolah milik SPPG B secara bebas.
*   **C. Manajemen Perencanaan Menu (`MenuController` & `MenuService`)**
    *   **Masalah:** Walaupun `index` dan `store` telah menggunakan `sppg_id`, method `show`, `showGrouped`, `update`, dan `destroy` langsung memanggil `Menu::findOrFail($id)` secara global.
    *   **Dampak:** Admin SPPG A dapat melihat layout menu detail, mengubah isi resep harian, atau menghapus rencana menu milik SPPG B jika mengetahui atau menebak ID menu tersebut.

### 🔴 2. Duplikasi & Sinkronisasi Entitas Sekolah vs Mitra (Functional Conflict)
Sistem memiliki dua tabel independen yang sama-sama merepresentasikan sekolah penerima makanan:
1.  Tabel `schools` (dibuat oleh Super Admin atau admin lokal, digunakan untuk **Jadwal Distribusi**).
2.  Tabel `partners` (mitra, terdaftar melalui registrasi/draft SPPG, digunakan untuk **Porsi Makan & Kalkulasi Nutrisi/Stok**).

*   **Konflik Logika:**
    *   Meskipun ada kode sinkronisasi berdasarkan `npsn` di `SPPGService::assignSchool()` dan `detachSchool()`, sinkronisasi ini **tidak berjalan** saat sekolah/mitra baru ditambahkan secara langsung via `SchoolController::store()` atau `PartnerController::store()`.
    *   Jika data di kedua tabel ini tidak sinkron (misalnya, jumlah siswa di `schools.student_count` berbeda dengan porsi di `partners.portion_count`, atau daftar sekolahnya berbeda), maka **kalkulasi pengurangan stok FIFO bahan baku** (yang menggunakan porsi mitra) akan bertolak belakang dengan **rencana pengiriman riil** (yang menggunakan data sekolah).

### 🟠 3. Pengubahan Tarif Pengiriman Global oleh Admin Lokal
*   **Masalah:** Di `FinancialReportController.php`, terdapat endpoint `PUT /api/admin-sppg/reports/financial/rates/{vehicleType}` yang dilindungi oleh permission `report.update`.
*   **Konflik Logika:** Tabel `shipping_rates` bersifat global (tidak memiliki `sppg_id`). Celah ini memungkinkan seorang Admin SPPG lokal mengubah tarif per-kilometer kendaraan secara global, yang akan memengaruhi perhitungan biaya pengiriman seluruh SPPG di sistem.

### 🟠 4. Status Pengiriman `accepted` Menjadi Dead-Code
*   **Masalah:** `DeliverySchedule::STATUS_ACCEPTED` (nilai `'accepted'`) didefinisikan di model dan dimasukkan dalam `scopeActive()`. Namun, pada logika `DeliveryScheduleService::acceptTask()`, status jadwal langsung diubah menjadi `delivering`:
    ```php
    $schedule->update([
        'status'      => DeliverySchedule::STATUS_DELIVERING,
        'departed_at' => now(),
    ]);
    ```
*   **Dampak:** Status `accepted` tidak pernah digunakan. Di sisi UX/UI, status ini akan membingungkan developer atau analis proses bisnis (BPMN) karena ada state mesin yang dilewati.

---

## 2. 🗺️ DETAIL ALUR UX ADMIN SPPG & CEK CACAT LOGIKA

Berikut adalah alur interaksi pengguna (UX Flow) secara mendetail untuk Admin SPPG, beserta analisis potensi kecacatan alur secara manual:

### 🔵 ALUR UX 1: Dasbor (Dashboard)
1.  **Akses Masuk:** Admin SPPG login dan diarahkan ke halaman utama Dashboard.
2.  **Tampilan:**
    *   Statistik jumlah jadwal distribusi hari ini berdasarkan status (`in_order`, `delivering`, `delivered`, `revision_required`, `confirmed`, `rejected`).
    *   Notifikasi kelengkapan staf: sistem mendeteksi apakah SPPG ini sudah mendaftarkan Ahli Gizi (`nutritionist`) dan Admin Logistik (`logistics_admin`).
    *   Alert stok kritis: menampilkan bahan baku dengan status `low`, `empty`, atau mengandung batch yang `expired`.
3.  **Potensi Cacat UX:**
    *   Jika Admin Logistik belum didaftarkan, Admin SPPG tidak bisa mendelegasikan pembuatan jadwal pengiriman, namun dashboard tetap menampilkan tab "Distribusi" yang kosong tanpa petunjuk/panduan aktivasi staf yang intuitif.

### 🟢 ALUR UX 2: Manajemen Karyawan & Pembagian Peran
1.  **Input Data:** Admin SPPG masuk ke menu Karyawan, klik "Tambah Karyawan" (mengisi Nama, NIK, Posisi Struktural, No HP, Alamat, Gaji Pokok, Tanggal Gabung).
2.  **Pemetaan Sistem (Assign Role):** Setelah data disimpan, Admin harus mengklik "Assign Role" untuk menautkan karyawan tersebut dengan akun pengguna (`User`) dan hak akses (`Role`) di sistem (misal: kurir mendapat role `courier`).
3.  **Potensi Cacat UX:**
    *   **Proses Dua Langkah yang Membingungkan:** Admin harus membuat data karyawan terlebih dahulu, baru kemudian menautkan akun user. Jika Admin lupa melakukan langkah kedua, karyawan tersebut tidak akan bisa login ke sistem meskipun statusnya aktif. Idealnya, pembuatan data karyawan struktural dan pembuatan akun sistem disatukan dalam satu form.

### 🟡 ALUR UX 3: Perencanaan Menu Mingguan (Oleh Ahli Gizi)
1.  **Pembuatan Bahan Baku & Resep:**
    *   Ahli Gizi mendaftarkan bahan baku (mengisi nilai kalori, karbohidrat, protein, lemak per 100g).
    *   Ahli Gizi membuat resep (misal: "Ayam Bakar Madu"), memasukkan bahan baku beserta berat gram yang digunakan. Sistem otomatis menjumlahkan nilai gizi resep tersebut.
2.  **Penyusunan Menu Mingguan:**
    *   Ahli Gizi membuat Rencana Menu Mingguan (menentukan tanggal mulai Senin dan akhir Kamis).
    *   Ahli Gizi menjadwalkan resep-resep tersebut pada slot waktu makan (misal: Makan Siang / Makan Malam) per hari. Status awal menu: `planned` / `scheduled`.
3.  **Publikasi Menu & Blokade Stok (Publishing):**
    *   Ahli Gizi mengklik "Publish".
    *   **Sistem Menghitung Kebutuhan:** Porsi per-sekolah dijumlahkan untuk semua sekolah mitra aktif. Jumlah gram bahan baku dikalikan total porsi tersebut.
    *   **Pengecekan FIFO & Blokade:** Jika stok di gudang (tabel `stock_items` dengan status `available` / `low`) cukup, status menu berubah menjadi `published` dan stok bahan langsung dikurangi. Jika stok kurang sedikit saja, sistem melempar error `StockShortageException` dan membatalkan publikasi menu (**Hard Block**).
4.  **Potensi Cacat UX:**
    *   **Hard Block Tanpa Solusi Alternatif:** Ketika sistem memblokir publikasi menu karena kekurangan stok, Ahli Gizi tidak diberi informasi batch mana yang kurang secara visual, sehingga sulit menentukan apakah mereka harus mengubah resep alternatif atau menunggu kiriman stok.
    *   **Ketiadaan Status Draft:** Menu yang diedit langsung memengaruhi jadwal tanpa adanya status "Menunggu Review" jika Ahli Gizi ingin meminta persetujuan Admin SPPG terlebih dahulu.

### 🔴 ALUR UX 4: Manajemen Stok Gudang
1.  **Pengajuan Stok (Logistik):** Admin Logistik menginput barang masuk (bahan baku, kuantitas, harga, tanggal kedaluwarsa, supplier, bukti nota pembelian). Status awal: `pending`.
2.  **Persetujuan (Admin SPPG):** Admin SPPG melihat daftar pengajuan stok di tab "Pending Approval". Admin mengklik "Approve" atau "Reject".
    *   Jika **Approve**: Stok masuk ke inventaris aktif, mendapatkan nomor batch otomatis (`BATCH-YYYYMMDD-XXX`), dan status bahan baku di-update menjadi `available`.
3.  **Potensi Cacat UX:**
    *   **Tidak Ada Edit setelah Approved:** Setelah stok disetujui, kuantitas tidak bisa diedit secara manual meskipun ada kesalahan pencatatan riil. Satu-satunya cara adalah menghapus transaksi atau membuat penyesuaian stok keluar baru, yang merusak historis audit log.
    *   **Race Condition Penomoran Batch:** Jika dua admin menyetujui stok secara bersamaan, penomoran batch bisa bertabrakan karena dibaca secara non-atomic dari database sebelum ditulis ulang.

### 🟣 ALUR UX 5: Siklus Distribusi (Alur Paling Rawan Cacat)
1.  **Pembuatan Jadwal:** Admin Logistik menentukan sekolah tujuan, kurir, tipe kendaraan, plat nomor, jam keberangkatan, dan membuat draft jadwal (`status: in_order`).
2.  **Pengiriman Tugas:** Admin SPPG meninjau draf tersebut dan menekan "Kirim Tugas" (status tetap `in_order` tetapi kolom `submitted_by` terisi). Reverb menyiarkan notifikasi ke handphone kurir.
3.  **Respons Kurir:**
    *   Jika **Ditolak**: Kurir menginput alasan penolakan. Status berubah menjadi `rejected`. Admin Logistik harus mengubah jadwal dan mengirim ulang.
    *   Jika **Diterima**: Kurir menekan "Terima". Status otomatis berubah menjadi `delivering` dan kurir mulai mengirim koordinat GPS secara real-time.
4.  **Bukti Pengiriman:** Kurir sampai di sekolah, mengambil foto bukti, lalu mengirimnya. Status berubah menjadi `delivered`.
5.  **Verifikasi & Arsip:** Admin Logistik memeriksa bukti foto kurir.
    *   Jika **Sesuai**: Klik "Konfirmasi". Status menjadi `confirmed` dan data dipindahkan ke snapshot tabel riwayat (`delivery_histories`).
    *   Jika **Tidak Sesuai**: Klik "Revisi". Status menjadi `revision_required`. Kurir harus mengambil ulang foto atau mengunggah bukti baru.
6.  **Potensi Cacat UX:**
    *   **Cacat Otorisasi Kurir:** Di controller, kurir manapun bisa menekan tombol "Terima" (`acceptTask`) atau "Tolak" (`rejectTask`) pada jadwal kurir lain karena sistem hanya memeriksa apakah user memiliki role `courier` tanpa memastikan `courier_id` pada jadwal adalah miliknya sendiri.
    *   **Tidak Ada Tombol Cancel/Batal:** Begitu tugas dikirim ke kurir, Admin tidak memiliki opsi untuk menarik kembali (cancel) tugas tersebut jika terjadi kesalahan rute mendadak sebelum kurir mengkliknya.
    *   **Blokade Manual Saat Revisi:** Jika status dalam `revision_required`, tidak ada batas waktu otomatis. Pengiriman bisa tersangkut selamanya jika kurir tidak merespons revisi tersebut.
