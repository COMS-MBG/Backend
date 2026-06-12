# Alur Rangkaian Sistem Admin SPPG

Dokumen ini menjelaskan seluruh rangkaian alur sistem untuk **Admin SPPG (Satuan Pelayanan Program Gizi)** berdasarkan implementasi terbaru dalam codebase COMS-MBG.

---

## 1. Ikhtisar Arsitektur Admin SPPG

Modul Admin SPPG didesain untuk melayani operasional harian pada level lokal SPPG (dapur produksi gizi). Sistem ini terbagi menjadi 5 pilar utama yang saling terintegrasi:

```mermaid
graph TD
    A[Nutrisi & Resep] -->|Digunakan oleh| B[Menu Mingguan]
    C[Stok Bahan Baku] -->|Dipotong FIFO saat| B
    B -->|Memicu Pembuatan| D[Distribusi & Kurir]
    D -->|Mencatat Biaya| E[Laporan Keuangan]
    F[Karyawan & Hak Akses] -->|Mengontrol Operasional| A & B & C & D & E
```

---

## 2. Rangkaian Alur Operasional Utama

### Flow A: Perencanaan Menu Mingguan & Pemotongan Stok FIFO

Alur ini menghubungkan perencanaan gizi oleh Ahli Gizi dengan inventori fisik bahan baku oleh Admin Logistik.

```mermaid
sequenceDiagram
    autonumber
    actor AG as Ahli Gizi
    actor AD as Admin SPPG / Pemilik
    participant M as Menu & Recipe
    participant S as Stock Service
    participant DB as Database (Stock Items)

    AG->>M: Input Rencana Menu Mingguan (Resep & Porsi)
    AG->>S: Request Simulasi Cek Stok (Check Menu)
    S->>DB: Hitung kebutuhan bahan baku vs batch stok tersedia
    S-->>AG: Tampilkan Status Stok (Cukup / Kurang)
    
    rect rgb(240, 240, 240)
        note over AG, DB: Proses Publikasi Menu (Publish)
        AD->>M: Publish Menu Mingguan
        M->>S: Validasi & Potong Stok FIFO (Real-Time)
        alt Stok Tidak Cukup
            S-->>AD: Block & Throw StockShortageException (HTTP 422)
        else Stok Cukup
            S->>DB: Kurangi kuantitas batch terlama (FIFO berdasarkan expired_at)
            S->>DB: Buat log StockTransaction (Type: OUT)
            M->>DB: Set status menu menjadi 'published'
            S-->>AD: Response Sukses (Menu Aktif)
        end
    end
```

#### Rincian Teknis Flow A:
1. **Penyusunan Resep**: Resep (`recipes`) mendefinisikan daftar bahan baku (`ingredients`) beserta berat/porsi dalam gram.
2. **Cek Ketersediaan**: Saat simulasi/publikasi, sistem mengalikan kebutuhan bahan baku per porsi dengan jumlah porsi sekolah mitra yang dilayani oleh SPPG tersebut.
3. **Aturan FIFO**: Pemotongan stok memprioritaskan batch `stock_items` yang memiliki tanggal kadaluwarsa (`expired_at`) paling awal.
4. **Log Mutasi**: Setiap pengurangan stok mencatat transaksi bertipe `OUT` pada tabel `stock_transactions` secara immutable.

---

### Flow B: Pengadaan Stok (Procurement) & Level Batas Minimal

Alur untuk menambah ketersediaan bahan baku di SPPG melalui mekanisme persetujuan (*approval*).

```mermaid
sequenceDiagram
    autonumber
    actor AL as Admin Logistik
    actor OWN as Pemilik / Admin SPPG
    participant SC as Stock Controller
    participant DB as Database

    AL->>SC: Input Batch Stok Baru (Status: Pending, Harga, Expired)
    SC->>DB: Simpan StockItem (is_approved = false)
    OWN->>SC: Lihat Daftar Pending Stock
    
    alt Disetujui (Approve)
        OWN->>SC: Approve Batch Stok
        SC->>DB: Update StockItem (is_approved = true)
        SC->>DB: Buat Log StockTransaction (Type: IN)
    else Ditolak (Reject)
        OWN->>SC: Reject Batch Stok
        SC->>DB: Hapus/Tandai StockItem sebagai Rejected
    end

    note over SC, DB: Sinkronisasi Status Stok Minimal
    DB->>SC: Trigger check StockMinimum
    alt Stok di bawah batas minimal
        SC->>DB: Set status bahan baku menjadi 'low' atau 'empty'
    end
```

#### Rincian Teknis Flow B:
1. **Keamanan Data**: Batch stok baru tidak langsung memengaruhi ketersediaan untuk menu sebelum disetujui (`is_approved = true`).
2. **Stock Minimum**: Setiap SPPG dapat mengatur batas minimal stok bahan baku secara mandiri (`stock_minimum`). Jika total kuantitas bahan gizi yang *approved* berada di bawah batas ini, sistem akan memberikan tanda peringatan.

---

### Flow C: Pengiriman Makanan (Distribusi) & Pelacakan Kurir

Alur pengantaran makanan sehat dari dapur SPPG ke sekolah mitra menggunakan perutean yang dioptimalkan.

```mermaid
sequenceDiagram
    autonumber
    actor AD as Admin SPPG
    actor KR as Kurir
    participant DC as Distribution Controller
    participant RO as Route Optimization Service
    participant TM as Tracking Map

    AD->>DC: Buat Jadwal Pengiriman Harian (Delivery Schedules)
    DC->>RO: Hitung Rute Terpendek (OSRM / Jarak Geodesik)
    RO-->>AD: Rekomendasi Urutan Pengantaran Sekolah
    AD->>DC: Assign Kurir ke Jadwal
    
    KR->>DC: Start Delivery (Status: delivering)
    
    loop Real-Time GPS Tracking
        KR->>TM: Kirim koordinat lintang/bujur (update-location)
        TM->>AD: Tampilkan posisi kurir di Peta Distribusi
    end

    KR->>DC: Tandai Pengantaran Selesai (Status: delivered)
    DC->>AD: Update status sekolah penerima & catat histori kedatangan
```

#### Rincian Teknis Flow C:
1. **Route Optimization**: Sistem menyusun urutan sekolah yang harus dikunjungi oleh kurir agar menghemat waktu tempuh dan jarak.
2. **Geofencing/Tracking**: Lokasi kurir disimpan secara berkala ke tabel `courier_locations` untuk pelaporan jejak perjalanan (*courier trail*).

---

### Flow D: Laporan Keuangan (Financial Reporting)

Alur pertanggungjawaban anggaran biaya operasional SPPG.

```mermaid
graph LR
    A[Pembelian Batch Stok] -->|Biaya Bahan Baku| C(Laporan Keuangan Mingguan/Bulanan)
    B[Biaya Operasional & Kurir] -->|Biaya Distribusi| C
    C -->|Divalidasi oleh| D[Superadmin via Ekspor Laporan]
```

---

## 3. Struktur Endpoint API Admin SPPG

Seluruh endpoint di bawah berada di bawah prefix `/api/admin-sppg` dengan proteksi token Sanctum serta verifikasi otorisasi permission:

| Modul | Method | Path | Keterangan |
| :--- | :--- | :--- | :--- |
| **Dashboard** | `GET` | `/dashboard` | Menampilkan ringkasan jadwal pengiriman, status kurir, dan biaya bulan ini. |
| **Karyawan** | `GET` / `POST` | `/employees` | CRUD data staf internal SPPG. |
| | `POST` | `/employees/{id}/assign-role` | Menetapkan hak akses peran staf. |
| **Mitra & Sekolah** | `GET` / `POST` | `/partners` | CRUD sekolah mitra yang dilayani oleh SPPG ini. |
| | `POST` | `/partners/import` | Impor data sekolah dalam format file. |
| **Gizi & Menu** | `GET` / `POST` | `/nutrition/ingredients` | CRUD data bahan baku masakan beserta info gizinya. |
| | `GET` / `POST` | `/nutrition/recipes` | CRUD resep makanan (komposisi bahan baku). |
| | `GET` / `POST` | `/nutrition/menus` | CRUD menu mingguan. |
| | `PATCH` | `/nutrition/menus/{id}/publish` | **Mempublikasikan menu + potong stok FIFO**. |
| **Stok** | `GET` / `POST` | `/stocks` | CRUD batch persediaan stok bahan baku. |
| | `GET` | `/stocks/pending` | Listing batch pengadaan yang menunggu approval. |
| | `POST` | `/stocks/{id}/approve` | Menyetujui batch stok masuk (menambah saldo stok). |
| | `GET` | `/stocks/check-menu/{menu_id}` | Simulasi kecukupan stok sebelum publikasi menu. |
| **Distribusi** | `GET` / `POST` | `/distributions` | Mengelola jadwal & penugasan pengiriman makanan. |
| | `POST` | `/tracking/update-location` | Endpoint bagi kurir untuk mengirim data GPS saat bertugas. |
| | `GET` | `/maps/distribution` | Layer peta sebaran pengiriman aktif. |
| **Laporan** | `GET` / `POST` | `/financial-reports` | CRUD pembukuan laporan keuangan bulanan. |
