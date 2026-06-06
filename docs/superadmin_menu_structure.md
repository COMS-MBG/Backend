# Struktur Menu Rekomendasi untuk Superadmin

Sebagai pemegang kendali sistem tingkat regional (kota), menu untuk Superadmin dirancang untuk berfokus pada **pengawasan agregat (monitoring)**, **simulasi spasial (GIS)**, dan **manajemen master data / akses**, bukan operasional dapur sehari-hari.

Berikut adalah pemetaan menu yang sebaiknya ada untuk Superadmin dibandingkan dengan Admin SPPG biasa:

| Kategori Menu | Sub-Menu Rekomendasi | Deskripsi & Cakupan untuk Superadmin |
| :--- | :--- | :--- |
| **1. Dashboard** | **Dashboard Wilayah** | Ringkasan real-time se-kota Bandung (total porsi terkirim, efisiensi waktu, alarm *overcapacity* dapur). |
| **2. Peta GIS & Usulan** | **Peta Spasial Kota**<br>**Usulan SPPG Baru** | - Peta sebaran seluruh SPPG, sekolah, dan rute kurir aktif.<br>- Inbox & modul approval persetujuan lokasi SPPG baru. |
| **3. Manajemen SPPG** | **Daftar Hub SPPG**<br>**Plotting Sekolah** | - CRUD data unit SPPG (alamat, kapasitas maksimal porsi, kontak).<br>- Memetakan sekolah mitra ke SPPG terdekat (Radius Assignment). |
| **4. Laporan (Agregat)** | **Laporan Operasional**<br>**Laporan Keuangan** | - Filter per-SPPG atau multi-SPPG.<br>- Ekspor data kumulatif ke PDF/CSV untuk dinas terkait. |
| **5. Master Gizi (Standar)** | **Standardisasi Resep** | Memantau resep baku nasional/kota agar semua hub SPPG menyajikan porsi dengan kandungan gizi yang sama. |
| **6. Mitra & Sekolah** | **Sekolah Mitra**<br>**Mitra Bahan Baku** | Manajemen pusat untuk pendaftaran sekolah baru dan vendor pemasok logistik besar. |
| **7. Hak Akses & HR** | **Manajemen Karyawan**<br>**Kontrol Akses (Acl)** | - Registrasi akun manajer/ahli gizi dan penempatan lokasi kerjanya.<br>- Pengaturan hak akses (role & permissions). |

---

## Menu yang Sebaiknya DISEMBUNYIKAN / DIBATASI bagi Superadmin

Untuk mencegah penumpukan informasi (*cognitive overload*) dan menjaga fokus peran, menu berikut sebaiknya disembunyikan dari sidebar utama Superadmin:
1. **Perencanaan Menu Harian (Menu Planning)**: Ini adalah tugas Ahli Gizi di masing-masing hub SPPG. Superadmin cukup melihat laporan akhir porsi gizi.
2. **Kalkulator Gizi Operasional**: Tidak diperlukan di level pengawasan kota kecuali saat perumusan resep baku baru.
3. **Jadwal Pengiriman Kurir Detil**: Superadmin hanya memantau efisiensi agregat pengiriman (misal: persentase keterlambatan), sedangkan jadwal detail dikelola oleh Admin Logistik di hub masing-masing.
