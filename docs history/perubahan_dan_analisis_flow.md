# Analisis Perubahan & Kesesuaian Alur Sistem

Dokumen ini merangkum hasil verifikasi terhadap seluruh perubahan yang telah dilakukan pada codebase COMS-MBG untuk memastikan kesesuaian dengan berkas `analisis_isu_super_admin.md` serta menjaga agar alur sistem tidak melenceng dari `admin_sppg_system_flow.md` dan `User Guide.md`.

---

## 1. Kesesuaian dengan `analisis_isu_super_admin.md`

Semua perubahan yang telah dilakukan pada komponen **Super Admin** sepenuhnya sesuai dengan rekomendasi pada berkas `analisis_isu_super_admin.md`. Berikut adalah rincian isu yang telah diselesaikan:

*   **[BUG-01] Perbaikan Typo Field Kapasitas**: Mengubah pemanggilan `$sppg->kapasitas` menjadi `$sppg->capacity` di [SPPGCapacityService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGCapacityService.php) sehingga endpoint overview kapasitas tidak crash lagi.
*   **[BUG-02] & [WARN-02] Filter Kota & Kompatibilitas SQLite**: Menambahkan penanganan pencarian menggunakan key `'kota'` atau `'city'` secara fleksibel di [SPPGService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGService.php). Query pencarian menggunakan case-insensitive `like` dengan `lower()` agar kompatibel dengan database SQLite (development) dan PostgreSQL (production).
*   **[BUG-03] Keselarasan Status Sekolah**: Mengubah filter status sekolah dari bahasa Indonesia `'aktif'` menjadi `'active'` di [SPPGCapacityService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SPPGCapacityService.php) agar data kapasitas terhitung dengan akurat.
*   **[BUG-04] Perbaikan Relasi Registrasi**: Memperbaiki pemanggilan relasi non-eksistensial `sppg` menjadi `owner` pada [SppgRegistrationService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SppgRegistrationService.php) saat memuat relasi SPPG baru.
*   **[BUG-05] Hak Akses Sekolah Lintas SPPG**: Menghapus helper `sppgId()` yang mengembalikan `null` pada Super Admin [SchoolController.php](file:///c:/Users/naufa/COMS_MBG/app/Http/Controllers/API/SuperAdmin/SchoolController.php). Sekarang Super Admin dapat melihat dan mengelola seluruh sekolah dari semua SPPG.
*   **[BUG-08] Kredensial Password Staf**: Memperbaiki logika di [SppgRegistrationService.php](file:///c:/Users/naufa/COMS_MBG/app/Services/SPPG/SppgRegistrationService.php) agar password Ahli Gizi dan Admin Logistik menggunakan data password mereka sendiri (atau fallback ke password Admin SPPG jika kosong), bukan dipaksa menggunakan password Admin SPPG.
*   **[BUG-09] Detach Sekolah saat SPPG Dihapus**: Menyesuaikan penghapusan SPPG agar melepaskan asosiasi sekolah mitra (`sppg_id = null`) untuk mencegah timbulnya data orphan.

---

## 2. Dampak Terhadap Alur Sistem (`admin_sppg_system_flow.md` & `User Guide.md`)

> [!NOTE]
> **TIDAK ADA alur sistem (business flow) yang berubah atau menyimpang** dari dokumen rancangan `admin_sppg_system_flow.md` dan `User Guide.md`. 
> Perbaikan yang dilakukan justru **menguatkan keamanan data (tenant isolation)** dan **memperbaiki bug logika** agar sistem berjalan tepat seperti yang didefinisikan dalam dokumen tersebut.

Berikut adalah rincian perbaikan teknis yang dilakukan beserta dampaknya terhadap alur:

### Flow A: Perencanaan Menu Mingguan & Pemotongan Stok FIFO
*   **Pencegahan Publikasi Ulang (MenuController@publish)**:
    *   *Sebelumnya*: Pengguna dapat memanggil endpoint `/publish` berkali-kali untuk menu yang sama, memicu pemotongan stok berulang (double-deduction).
    *   *Perbaikan*: Ditambahkan guard untuk menolak publikasi jika status menu sudah `published` atau `archived` (mengembalikan status HTTP 422).
    *   *Dampak Alur*: Menjaga konsistensi data stok FIFO agar tidak terjadi pemotongan ganda ilegal.
*   **Penyimpanan meal_time (MenuItem.php & MenuService.php)**:
    *   *Sebelumnya*: Atribut `meal_time` (lunch/dinner) ter-reset menjadi `null` saat menu di-update karena tidak terdaftar di `$fillable`.
    *   *Perbaikan*: Memasukkan `meal_time` ke `$fillable` model [MenuItem.php](file:///c:/Users/naufa/COMS_MBG/app/Models/MenuItem.php) dan memastikan nilainya disimpan saat proses pembuatan/pembaruan menu.
    *   *Dampak Alur*: Mengembalikan fungsionalitas penyusunan menu sesuai langkah **D3** di User Guide.
*   **Proteksi Status Menu Manual (MenuService.php)**:
    *   *Sebelumnya*: Status menu yang sudah dipublikasikan (`published`) atau diarsipkan (`archived`) secara manual dapat ter-override kembali menjadi `scheduled` saat dibaca oleh sistem.
    *   *Perbaikan*: Menghalangi fungsi sinkronisasi status otomatis meng-override status manual tersebut.
    *   *Dampak Alur*: Menjamin transisi status menu tetap valid.

### Flow B: Pengadaan Stok (Procurement)
*   **Akurasi Logging Transaksi (StockController@approve)**:
    *   *Sebelumnya*: Kolom `quantity_before` pada log transaksi di-hardcode `0.0`.
    *   *Perbaikan*: Mengambil saldo stok aktual yang ada di database sebelum pengadaan disetujui untuk dimasukkan sebagai nilai `quantity_before`.
    *   *Dampak Alur*: Log audit transaksi masuk (`IN`) menjadi akurat dan akuntabel sesuai **Section C5** di User Guide.
*   **Isolasi Ringkasan Stok (StockService::getSummary)**:
    *   *Sebelumnya*: Admin SPPG A dapat melihat konfigurasi stok minimum dan bahan baku milik SPPG B (cross-tenant leak).
    *   *Perbaikan*: Memfilter ringkasan stok bahan baku agar hanya menampilkan data yang berasosiasi dengan `sppg_id` pengguna yang sedang login.
    *   *Dampak Alur*: Mengisolasi data inventaris masing-masing dapur SPPG.

### Flow C: Pengiriman Makanan & Pelacakan Kurir
*   **Keselarasan Penugasan Kurir (DistributionController.php)**:
    *   *Sebelumnya*: Validasi role penugasan kurir mengecek slug `'courier'`, sedangkan seeder menggunakan slug `'kurir'`. Hal ini menyebabkan kegagalan penugasan kurir.
    *   *Perbaikan*: Mengubah pengecekan role menjadi `'kurir'` agar selaras dengan data seeder.
    *   *Dampak Alur*: Memulihkan alur penugasan kurir harian sesuai **Section E2** dan **Section F** di User Guide.
*   **Pembersihan Route Distribusi**:
    *   *Sebelumnya*: Terdapat endpoint `update` dan `destroy` distribusi kosong yang tidak digunakan namun terekspos.
    *   *Perbaikan*: Menghapus route kosong tersebut untuk merapikan API.
    *   *Dampak Alur*: Tidak memengaruhi alur fungsional pengguna.

### Isolasi Keamanan Staf, Role, dan Sekolah Mitra
*   **Tenant Isolation (EmployeeController, RoleController, SchoolController)**:
    *   *Sebelumnya*: Admin SPPG dapat memanipulasi data karyawan, role, dan sekolah mitra milik SPPG lain dengan menembak ID secara langsung.
    *   *Perbaikan*: Seluruh query CRUD karyawan, role, dan sekolah disaring secara ketat berdasarkan `sppg_id` milik user yang sedang aktif login. Upaya mengakses data milik SPPG lain akan langsung di-block dengan HTTP 403.
    *   *Dampak Alur*: Mengamankan privasi data operasional masing-masing unit SPPG.
*   **Keunikan Nama Role Custom (RoleController.php)**:
    *   *Sebelumnya*: Validasi nama role bersifat unik secara global di database.
    *   *Perbaikan*: Mengubah aturan validasi keunikan nama role menjadi ter-scope per `sppg_id`.
    *   *Dampak Alur*: Memungkinkan SPPG berbeda untuk membuat nama role custom yang sama (misal: "Staf Dapur") tanpa konflik.

---

## 3. Kesimpulan

Semua perbaikan yang diterapkan adalah **bugfixing dan penguatan keamanan data** yang:
1.  **Sesuai** dengan temuan isu pada `analisis_isu_super_admin.md`.
2.  **Sesuai** dengan alur kerja yang didefinisikan pada `admin_sppg_system_flow.md` dan `User Guide.md`, tanpa mengubah sedikit pun alur logis maupun aturan bisnis sistem yang telah disetujui.
