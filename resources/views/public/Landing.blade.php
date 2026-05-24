<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Badan Gizi Nasional - Satuan Pelayanan Sumur Bandung. Menyediakan asupan nutrisi seimbang untuk generasi unggul.">
    <title>Beranda - Badan Gizi Nasional</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bs-primary: #0d6efd;
            --bs-font-sans-serif: 'Inter', system-ui, sans-serif;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: #1a1a2e;
        }

        /* --- Navbar --- */
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,.07);
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
        }

        .navbar-custom .nav-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            transition: color 0.2s;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: var(--bs-primary);
        }

        /* --- Hero Section --- */
        .hero-section {
            padding-top: 6rem;
            padding-bottom: 5rem;
            background: linear-gradient(135deg, #f8faff 0%, #eef3ff 100%);
            min-height: 90vh;
        }

        .hero-section h1 {
            font-size: clamp(2rem, 4vw, 3.25rem);
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.5px;
            color: #0a1931;
        }

        .hero-card {
            border-radius: 1.5rem !important;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(13,110,253,.15) !important;
        }

        .hero-card .card-img-top {
            height: 220px;
            object-fit: cover;
        }

        .nutrisi-item {
            border-radius: 0.75rem;
            background-color: #f1f5f9;
            padding: 0.6rem 0.5rem;
            text-align: center;
        }

        .nutrisi-item .value {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0a1931;
        }

        .nutrisi-item .label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        /* --- Map Section --- */
        .map-section {
            background-color: #f1f5f9;
        }

        .map-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .map-img {
            width: 100%;
            height: 420px;
            object-fit: cover;
            border-radius: 1.25rem;
            box-shadow: 0 10px 40px rgba(0,0,0,.12);
        }

        .floating-status-card {
            position: absolute;
            bottom: 1.5rem;
            left: 1.5rem;
            background: #ffffff;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            min-width: 220px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
        }

        .floating-status-card .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #0a1931;
            line-height: 1;
        }

        /* --- Testimonial Section --- */
        .testimonial-section {
            background-color: #ffffff;
        }

        .score-card {
            border-radius: 1.25rem !important;
            border: 1px solid #e9ecef !important;
            min-width: 140px;
        }

        .score-card .big-score {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0a1931;
            line-height: 1;
        }

        .testimonial-card {
            border-radius: 1.25rem !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(13,110,253,.12) !important;
        }

        .avatar-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #6610f2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* --- Footer --- */
        .footer {
            background-color: #ffffff;
            border-top: 1px solid #e9ecef;
            padding: 1.75rem 0;
        }

        .footer a {
            text-decoration: none;
            color: #6b7280;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .footer a:hover {
            color: var(--bs-primary);
        }

        /* --- Misc --- */
        .section-badge {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .section-title {
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 800;
            color: #0a1931;
        }
    </style>
</head>
<body>

    {{-- ============================================================
        1. NAVBAR
    ============================================================ --}}
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top" id="mainNavbar">
        <div class="container">
            {{-- Logo --}}
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2" href="{{ route('landing') }}">
                <i class="bi bi-shield-fill-check fs-5"></i>
                Badan Gizi Nasional
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                {{-- Navigasi Tengah --}}
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="{{ route('landing') }}" id="nav-beranda">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="nav-program-gizi">Program Gizi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#map-section" id="nav-peta-operasional">Peta Operasional</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="nav-informasi-publik">Informasi Publik</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="nav-bantuan">Bantuan</a>
                    </li>
                </ul>

                {{-- Tombol Kanan --}}
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-light rounded-3" id="btn-search" aria-label="Cari">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="#testimoni-section" class="btn btn-sm btn-primary rounded-3 px-3 fw-semibold" id="btn-lapor">
                        Lapor
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ============================================================
        2. HERO SECTION
    ============================================================ --}}
    <section class="hero-section" id="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">

                {{-- Kolom Kiri --}}
                <div class="col-lg-6 d-flex flex-column justify-content-center">
                    <span class="badge bg-success text-white rounded-pill mb-3 py-2 px-3 section-badge align-self-start" id="badge-menu-hari-ini">
                        <i class="bi bi-calendar2-check me-1"></i> Menu Gizi Hari Ini
                    </span>
                    <h1 class="mb-3">
                        {{ $hero->title ?? 'Satuan Pelayanan Sumur Bandung' }}
                    </h1>
                    <p class="text-muted mb-4 fs-6 lh-lg" style="max-width: 480px;">
                        {{ $hero->description ?? 'Menyediakan asupan nutrisi seimbang untuk generasi unggul. Setiap hidangan diproses dengan standar keamanan pangan tertinggi dan pengawasan ahli gizi profesional.' }}
                    </p>
                    <div>
                        <a href="#" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold" id="btn-cek-jadwal-menu">
                            <i class="bi bi-calendar3 me-2"></i>Cek Jadwal Menu
                        </a>
                    </div>
                </div>

                {{-- Kolom Kanan: Card Makanan --}}
                <div class="col-lg-6">
                    <div class="card border-0 shadow-lg rounded-4 hero-card">
                        {{-- Gambar Makanan --}}
                        @if($hero && $hero->image_path)
                            <img src="{{ asset('storage/' . $hero->image_path) }}" alt="Gambar Menu Unggulan" class="card-img-top" style="height:220px; object-fit:cover;">
                        @else
                            <img src="https://images.unsplash.com/photo-1512058564366-18510be2db19?w=600&q=80" alt="Nasi Ayam Bumbu Kuning" class="card-img-top" style="height:220px; object-fit:cover;">
                        @endif

                        <div class="card-body p-4">
                            {{-- Nama & Kalori --}}
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <p class="text-muted mb-0" style="font-size:0.75rem;">
                                        <i class="bi bi-geo-alt me-1"></i>8 Hidangan Utama Terpopuler
                                    </p>
                                    <h5 class="fw-bold mb-0 mt-1">
                                        @if($menus->isNotEmpty())
                                            {{ $menus->first()->name ?? 'Nasi Ayam Bumbu Kuning' }}
                                        @else
                                            Nasi Ayam Bumbu Kuning
                                        @endif
                                    </h5>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold fs-4 text-primary">450</span>
                                    <p class="text-muted mb-0" style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px;">Kcal<br>Total Energi</p>
                                </div>
                            </div>

                            {{-- Info Nutrisi --}}
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="nutrisi-item">
                                        <div class="value">32g</div>
                                        <div class="label">Protein</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="nutrisi-item">
                                        <div class="value">55g</div>
                                        <div class="label">Karbohidrat</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="nutrisi-item">
                                        <div class="value">12g</div>
                                        <div class="label">Lemak</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ============================================================
        3. JADWAL MENU GIZI MINGGUAN (PROGRAM GIZI)
    ============================================================ --}}
    <section class="nutrition-program-section py-5" id="nutrition-program-section">
        <div class="container py-5">
            
            {{-- 1. Container & Header Section --}}
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
                <div>
                    <span class="section-badge text-primary d-block mb-1">TRANSPARANSI PUBLIK</span>
                    <h2 class="section-title mt-0 mb-2">Jadwal Menu Gizi Mingguan</h2>
                    <p class="text-muted mb-0">
                        Pantau susunan menu sehat seimbang yang dirancang khusus oleh ahli gizi untuk anak sekolah.
                    </p>
                </div>
                
                <div class="btn-group shadow-sm mt-3 mt-sm-0" role="group" aria-label="Navigasi Jadwal Gizi">
                    <button type="button" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary disabled fw-semibold text-dark opacity-100 px-3">
                        Minggu, 13 - 17 April 2026
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            {{-- 2. Grid Menu Makanan --}}
            <div class="row g-4">
                
                {{-- A. Kolom Kiri (Daftar Menu Harian) --}}
                <div class="col-lg-8">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        
                        {{-- Card 1: SENIN --}}
                        <div class="col">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-2">
                                    <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 0.5px;">SENIN, 13 APR</span>
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80" alt="Nasi Uduk Ayam Goreng" class="card-img-top rounded-0" style="height: 180px; object-fit: cover;">
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-2 pb-3 px-3">
                                    <span class="fw-bold text-dark">Nasi Uduk Ayam Goreng</span>
                                    <span class="fw-bold text-primary">650 Kcal</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 2: SELASA --}}
                        <div class="col">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-2">
                                    <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 0.5px;">SELASA, 14 APR</span>
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <img src="https://images.unsplash.com/photo-1541832676-9b763b0239ab?w=600&q=80" alt="Sup Daging Sapi Kentang" class="card-img-top rounded-0" style="height: 180px; object-fit: cover;">
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-2 pb-3 px-3">
                                    <span class="fw-bold text-dark">Sup Daging Sapi &amp; Kentang</span>
                                    <span class="fw-bold text-primary">620 Kcal</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 3: RABU --}}
                        <div class="col">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-2">
                                    <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 0.5px;">RABU, 15 APR</span>
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <img src="https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=600&q=80" alt="Ayam Bakar Madu" class="card-img-top rounded-0" style="height: 180px; object-fit: cover;">
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-2 pb-3 px-3">
                                    <span class="fw-bold text-dark">Ayam Bakar Madu &amp; Lalapan</span>
                                    <span class="fw-bold text-primary">640 Kcal</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card 4: KAMIS --}}
                        <div class="col">
                            <div class="card border-0 shadow-sm rounded-4 h-100 bg-light">
                                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-2">
                                    <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 0.5px;">KAMIS, 16 APR</span>
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <img src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=600&q=80" alt="Ikan Nila Asam Manis" class="card-img-top rounded-0" style="height: 180px; object-fit: cover;">
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-2 pb-3 px-3">
                                    <span class="fw-bold text-dark">Ikan Nila Saus Asam Manis</span>
                                    <span class="fw-bold text-primary">610 Kcal</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- B. Kolom Kanan (Menu Highlight Hari Ini) --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-lg rounded-4 h-100 overflow-hidden">
                        
                        {{-- Image Container --}}
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1603048588665-791ca8aea617?w=600&q=80" alt="Tumis Sapi Brokoli" class="w-100" style="height: 280px; object-fit: cover;">
                            
                            {{-- Badge HARI INI --}}
                            <span class="position-absolute top-0 end-0 m-3 badge bg-primary px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 0.75rem; letter-spacing: 0.5px;">HARI INI</span>
                            
                            {{-- Dark Overlay & Title --}}
                            <div class="position-absolute bottom-0 start-0 end-0 p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, rgba(0,0,0,0) 100%);">
                                <h4 class="fw-bold mb-0">Tumis Sapi Brokoli &amp; Nasi Merah</h4>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3">Kandungan Gizi &amp; Nutrisi</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-egg-fried me-1"></i> 35g Protein</span>
                                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-lightning-charge me-1"></i> 680 Kcal</span>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-basket me-1"></i> 60g Karbo</span>
                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-droplet me-1"></i> 14g Lemak</span>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-block bg-success rounded-circle animate-pulse" style="width: 10px; height: 10px;"></span>
                                <span class="small fw-semibold text-success" style="font-size: 0.8rem;">Status Distribusi: Sedang Dikirim</span>
                            </div>
                            <a href="#" class="btn btn-primary btn-sm rounded-3 px-3 fw-semibold">Lihat Detail Gizi</a>
                        </div>

                    </div>
                </div>

            </div>

            {{-- 3. Alert Catatan --}}
            <div class="alert alert-light border shadow-sm rounded-3 mt-4 d-flex align-items-center gap-3 p-3" role="alert">
                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                <div class="text-secondary small">
                    <strong>Catatan Penting:</strong> Menu dapat berubah sewaktu-waktu menyesuaikan ketersediaan bahan pangan lokal segar dengan kandungan gizi setara.
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================
        4. MAP SECTION – Peta Sebaran Operasional
    ============================================================ --}}
    <section class="map-section py-5" id="map-section">
        <div class="container">

            {{-- Header --}}
            <div class="mb-4">
                <span class="section-badge text-primary">Monitor Distribusi</span>
                <h2 class="section-title mt-1">Peta Sebaran Operasional</h2>
                <p class="text-muted">
                    Visualisasi real-time distribusi makanan dari Satuan Pelayanan Pusat ke sekolah-sekolah<br>
                    penerima manfaat di wilayah Sumur Bandung.
                </p>
            </div>

            {{-- Map Wrapper --}}
            <div class="map-wrapper">
                <img
                    src="https://maps.googleapis.com/maps/api/staticmap?center=Bandung,Indonesia&zoom=13&size=1200x420&maptype=roadmap&style=feature:all|element:geometry|color:0xf5f5f5&style=feature:road|color:0xffffff&key=AIzaSyD-9tSrke72PouQMnMX-a7eZSW0jkFMBWY"
                    onerror="this.src='https://tile.openstreetmap.org/12/3293/2040.png'"
                    alt="Peta Operasional Sumur Bandung"
                    class="map-img img-fluid"
                    id="peta-operasional-img"
                >

                {{-- Floating Status Card --}}
                <div class="floating-status-card" id="floating-status-card">
                    <p class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                        Status Operasional
                    </p>
                    <div class="stat-number">25</div>
                    <p class="text-muted mt-1 mb-2" style="font-size: 0.75rem;">Sekolah Terlayani (Real-time)</p>
                    <div class="progress" style="height: 6px; border-radius: 99px;" role="progressbar" aria-label="Persentase sekolah terlayani" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-success" style="width: 65%; border-radius: 99px;"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="text-muted" style="font-size:0.7rem;">0%</small>
                        <small class="text-success fw-semibold" style="font-size:0.7rem;">65%</small>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- ============================================================
        4. TESTIMONIAL SECTION – Transparansi & Aspirasi
    ============================================================ --}}
    <section class="testimonial-section py-5" id="testimoni-section">
        <div class="container">

            {{-- Header: Judul di kiri, Score Card di kanan --}}
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-5">
                <div>
                    <h2 class="section-title mb-1">Transparansi &amp; Aspirasi</h2>
                    <p class="text-muted mb-0" style="max-width: 460px;">
                        Kami percaya bahwa masukan dari masyarakat, terutama wali murid, adalah kunci untuk terus meningkatkan kualitas pelayanan gizi nasional.
                    </p>
                </div>

                {{-- Score Card --}}
                <div class="card score-card shadow-sm text-center p-3" id="score-card">
                    <div class="big-score">4.7</div>
                    <div class="d-flex justify-content-center gap-1 mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= 4)
                                <i class="bi bi-star-fill text-warning" style="font-size:.85rem;"></i>
                            @else
                                <i class="bi bi-star-half text-warning" style="font-size:.85rem;"></i>
                            @endif
                        @endfor
                    </div>
                    <small class="text-muted mt-1" style="font-size:0.7rem;">Berdasarkan ulasan</small>
                </div>
            </div>

            {{-- Grid Testimoni --}}
            <div class="row g-4" id="testimoni-grid">
                @forelse($feedbacks as $feedback)
                    <div class="col-md-4">
                        <div class="card testimonial-card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                {{-- Avatar + Nama --}}
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($feedback->name ?? $feedback->user_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="fw-semibold mb-0" style="font-size:0.9rem;">
                                            {{ $feedback->name ?? $feedback->user_name ?? 'Pengguna Anonim' }}
                                        </p>
                                        <small class="text-muted" style="font-size:0.75rem;">
                                            {{ $feedback->role ?? 'Wali Murid' }}
                                        </small>
                                    </div>
                                </div>

                                {{-- Rating Bintang --}}
                                <div class="mb-3">
                                    @php $rating = $feedback->rating ?? 4; @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $rating)
                                            <i class="bi bi-star-fill text-warning" style="font-size:.8rem;"></i>
                                        @else
                                            <i class="bi bi-star text-secondary" style="font-size:.8rem;"></i>
                                        @endif
                                    @endfor
                                </div>

                                {{-- Isi Ulasan --}}
                                <p class="text-muted mb-0" style="font-size:0.875rem; line-height:1.7;">
                                    "{{ $feedback->message ?? $feedback->content ?? $feedback->ulasan ?? 'Ulasan dari masyarakat.' }}"
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Fallback Testimoni Dummy --}}
                    @php
                        $dummyTestimonials = [
                            ['name' => 'Ibu Sarah K.', 'role' => 'Wali Murid SDN 12 Bandung', 'rating' => 5, 'message' => 'Sangat puas dengan kualitas makannya. Anak saya sekarang lebih semangat ke sekolah dan nafsu makannya meningkat. Porsinya juga pas untuk anak SD.'],
                            ['name' => 'Bapak Ahmad R.', 'role' => 'Wali Murid SDN 7 Bandung', 'rating' => 4, 'message' => 'Menu yang diberikan sangat variasi, setiap hari berbeda jadi anak tidak bosan. Terima kasih Badan Gizi Nasional sudah hadir di wilayah kami.'],
                            ['name' => 'Ibu Maya S.', 'role' => 'Wali Murid SMPN 3 Bandung', 'rating' => 4, 'message' => 'Anak saya bilang ayam bumbu kuningnya sangat enak. Kebersihan kemasan juga sangat terjaga. Harapannya program ini terus berkelanjutan.'],
                        ];
                    @endphp
                    @foreach($dummyTestimonials as $index => $dummy)
                        <div class="col-md-4">
                            <div class="card testimonial-card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($dummy['name'], 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="fw-semibold mb-0" style="font-size:0.9rem;">{{ $dummy['name'] }}</p>
                                            <small class="text-muted" style="font-size:0.75rem;">{{ $dummy['role'] }}</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $dummy['rating'])
                                                <i class="bi bi-star-fill text-warning" style="font-size:.8rem;"></i>
                                            @else
                                                <i class="bi bi-star text-secondary" style="font-size:.8rem;"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <p class="text-muted mb-0" style="font-size:0.875rem; line-height:1.7;">
                                        "{{ $dummy['message'] }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>

            {{-- Tombol Beri Penilaian --}}
            <div class="text-center mt-5">
                <a href="#" class="btn btn-outline-primary rounded-3 px-4 py-2 fw-semibold" id="btn-beri-penilaian">
                    <i class="bi bi-pencil-square me-2"></i>Beri Penilaian
                </a>
            </div>

        </div>
    </section>

    {{-- ============================================================
        5. FOOTER
    ============================================================ --}}
    <footer class="footer" id="main-footer">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- Copyright Kiri --}}
                <div class="text-muted" style="font-size:0.85rem;">
                    <i class="bi bi-shield-fill-check text-primary me-1"></i>
                    <strong class="text-dark">Badan Gizi Nasional</strong>
                    &copy; {{ date('Y') }} Badan Gizi Nasional Republik Indonesia. Seluruh Hak Cipta Dilindungi.
                </div>

                {{-- Link Kanan --}}
                <div class="d-flex flex-wrap gap-3">
                    <a href="#" id="footer-kebijakan-privasi">Kebijakan Privasi</a>
                    <a href="#" id="footer-syarat-ketentuan">Syarat &amp; Ketentuan</a>
                    <a href="#" id="footer-kontak-kami">Kontak Kami</a>
                    <a href="#" id="footer-aksesibilitas">Aksesibilitas</a>
                </div>
            </div>
        </div>
    </footer>

    {{-- Bootstrap 5 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Smooth scroll untuk anchor link
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Navbar shadow saat scroll
        const navbar = document.getElementById('mainNavbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,.10)';
            } else {
                navbar.style.boxShadow = '0 2px 12px rgba(0,0,0,.05)';
            }
        });
    </script>
</body>
</html>