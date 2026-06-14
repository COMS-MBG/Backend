<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPG - Satuan Pelayanan Program Gizi</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #334155;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* Navbar Customization */
        .navbar-custom {
            transition: all 0.4s ease;
            padding: 1.5rem 0;
            background: transparent;
        }

        .navbar-custom.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }

        .navbar-brand img {
            height: 50px;
            transition: all 0.3s ease;
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-main) !important;
            margin: 0 10px;
            position: relative;
            transition: color 0.3s;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent);
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 100px;
            padding-bottom: 80px;
            overflow: hidden;
        }

        .hero-blob {
            position: absolute;
            top: -10%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
        }

        .hero-title {
            font-weight: 800;
            font-size: 3.5rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }

        .hero-title span {
            color: var(--accent);
        }

        .hero-desc {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        .btn-custom {
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            background-color: var(--accent);
            color: white;
            border: none;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(37, 99, 235, 0.3);
            background-color: var(--accent-hover);
            color: white;
        }

        /* Partners Section */
        .partners-section {
            padding: 60px 0;
            background: white;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
        }
        
        .partner-logo {
            filter: grayscale(100%);
            opacity: 0.6;
            transition: all 0.4s ease;
            max-height: 60px;
            margin: 0 auto;
        }

        .partner-logo:hover {
            filter: grayscale(0%);
            opacity: 1;
            transform: scale(1.05);
        }

        /* Menu Section */
        .menu-section {
            padding: 100px 0;
        }

        .section-title {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .menu-card {
            background: var(--surface);
            border: none;
            border-radius: 24px;
            padding: 30px;
            height: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
        }

        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
        }

        .menu-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: inline-block;
            background: #eff6ff;
            width: 70px;
            height: 70px;
            line-height: 70px;
            text-align: center;
            border-radius: 20px;
        }

        /* Map Section */
        .map-section {
            padding: 100px 0;
            background: white;
        }

        .map-container {
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            background: #e2e8f0;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .map-filter {
            background: white;
            padding: 20px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }

        .form-control-custom {
            border-radius: 12px;
            padding: 12px 20px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .form-control-custom:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: white;
        }

        /* Rating Section */
        .rating-section {
            padding: 100px 0;
            background: var(--background);
        }

        .rating-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        }

        .star-rating {
            font-size: 2rem;
            color: #fbbf24;
            cursor: pointer;
            user-select: none;
        }

        .star-rating .star {
            transition: transform 0.2s;
            display: inline-block;
        }
        
        .star-rating .star:hover {
            transform: scale(1.2);
        }

        .review-item {
            padding: 20px;
            border-radius: 16px;
            background: #f8fafc;
            margin-bottom: 15px;
        }

        .review-name {
            font-weight: 600;
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom" id="mainNav">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <!-- Using MBG Logo -->
                <img src="{{ asset('images/Logo MBG.png') }}" alt="MBG Logo" onerror="this.src='https://via.placeholder.com/150x50?text=Logo+MBG'"> 
                <span class="ms-3 fw-bold text-dark fs-4">SPPG</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#hero">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#menu">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="#lokasi">Lokasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ulasan">Ulasan</a></li>
                    <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                        <button type="button" class="btn btn-custom px-4 py-2" data-bs-toggle="modal" data-bs-target="#sppgSubmissionModal" style="font-size: 0.9rem;">
                            Ajukan SPPG Baru
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="hero-section">
        <div class="hero-blob"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 pe-lg-5">
                    <h1 class="hero-title">Mewujudkan Generasi <span>Sehat Cerdas</span> melalui Gizi Seimbang</h1>
                    <p class="hero-desc">
                        Program Makan Bergizi Gratis (MBG) adalah wujud komitmen nyata dalam menyediakan akses makanan sehat, bergizi, dan seimbang bagi masyarakat. Melalui Satuan Pelayanan Program Gizi (SPPG) yang tersebar di berbagai titik, kami memastikan setiap porsi makanan dikelola dengan standar kesehatan dan higienitas terbaik. Jelajahi menu mingguan kami, temukan lokasi SPPG terdekat, dan bantu kami terus berkembang melalui ulasan Anda.
                    </p>
                    <a href="#lokasi" class="btn btn-custom">Cari Lokasi SPPG Terdekat</a>
                </div>
                <div class="col-lg-6 col-md-12 mt-5 mt-lg-0 text-center position-relative">
                    <img src="{{ asset('images/MBG.jpg') }}" class="img-fluid rounded-4 shadow-lg" alt="Healthy Food" style="border-radius: 2rem !important; max-height: 450px; object-fit: cover; width: 100%;">
                </div>
            </div>
        </div>
    </section>

    <!-- Mitra Section -->
    <section class="partners-section border-top border-bottom border-light">
        <div class="container text-center">
            <p class="text-muted mb-4 fw-semibold" style="letter-spacing: 1px;">DIDUKUNG OLEH MITRA TERPERCAYA</p>
            <div class="row justify-content-center align-items-center g-4">
                @foreach($partners as $partner)
                <div class="col-4 col-md-2">
                    <div class="fw-bold text-muted partner-logo fs-6">{{ $partner }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Menu Mingguan (Gaya Baru) -->
    <section class="menu-section py-5" id="menu" style="background-color: var(--background);">
        <div class="container py-5">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
                <div>
                    <span class="text-primary fw-bold d-block mb-1" style="font-size: 0.8rem; letter-spacing: 1.5px;">TRANSPARANSI PUBLIK</span>
                    <h2 class="section-title mt-0 mb-2">Jadwal Menu Gizi Mingguan</h2>
                    <p class="text-muted mb-0">Pantau susunan menu sehat seimbang yang dirancang khusus oleh ahli gizi untuk anak sekolah.</p>
                </div>
            </div>

            <!-- ========== SNIPPET: TABEL RINGKASAN GIZI MINGGUAN ========== -->
            <div class="card border-0 shadow-sm rounded-4 mt-5 overflow-hidden">
                <div class="card-header bg-white border-0 px-4 pt-4 pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-table text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0">Ringkasan Gizi Mingguan</h5>
                    </div>
                    <p class="text-muted small mb-0 mt-1">Detail kandungan nutrisi setiap menu harian</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="table-light">
                                    <th class="ps-4 py-3 fw-semibold text-secondary small text-uppercase" style="letter-spacing:0.5px;">Hari</th>
                                    <th class="py-3 fw-semibold text-secondary small text-uppercase" style="letter-spacing:0.5px;">Menu</th>
                                    <th class="py-3 fw-semibold text-secondary small text-uppercase text-center" style="letter-spacing:0.5px;">
                                        <i class="bi bi-lightning-charge me-1"></i>Kalori
                                    </th>
                                    <th class="py-3 fw-semibold text-secondary small text-uppercase text-center" style="letter-spacing:0.5px;">
                                        <i class="bi bi-egg-fried me-1"></i>Protein
                                    </th>
                                    <th class="py-3 fw-semibold text-secondary small text-uppercase text-center" style="letter-spacing:0.5px;">
                                        <i class="bi bi-basket me-1"></i>Karbo
                                    </th>
                                    <th class="pe-4 py-3 fw-semibold text-secondary small text-uppercase text-center" style="letter-spacing:0.5px;">
                                        <i class="bi bi-droplet me-1"></i>Lemak
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weeklyMenus as $item)
                                <tr style="{{ $item['is_today'] ? 'background-color: #eff6ff;' : '' }}">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item['is_today'])
                                                <span class="badge bg-primary rounded-pill px-2 py-1" style="font-size:0.65rem;">HARI INI</span>
                                            @endif
                                            <span class="fw-semibold">{{ $item['day_name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="fw-medium text-dark">{{ $item['menu'] }}</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold">{{ $item['calories'] }} Kcal</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="fw-semibold text-dark">{{ $item['protein'] }}g</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="fw-semibold text-dark">{{ $item['carbs'] }}g</span>
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        <span class="fw-semibold text-dark">{{ $item['fat'] }}g</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="2" class="ps-4 py-3 fw-bold text-secondary">Rata-rata Harian</td>
                                    <td class="py-3 text-center fw-bold text-success">
                                        {{ round(collect($weeklyMenus)->avg('calories')) }} Kcal
                                    </td>
                                    <td class="py-3 text-center fw-bold">
                                        {{ round(collect($weeklyMenus)->avg('protein'), 1) }}g
                                    </td>
                                    <td class="py-3 text-center fw-bold">
                                        {{ round(collect($weeklyMenus)->avg('carbs'), 1) }}g
                                    </td>
                                    <td class="pe-4 py-3 text-center fw-bold">
                                        {{ round(collect($weeklyMenus)->avg('fat'), 1) }}g
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ========== END SNIPPET: TABEL RINGKASAN GIZI MINGGUAN ========== -->

            <div class="alert alert-light border shadow-sm rounded-3 mt-4 d-flex align-items-center gap-3 p-3" role="alert">
                <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                <div class="text-secondary small">
                    <strong>Catatan Penting:</strong> Menu dapat berubah sewaktu-waktu menyesuaikan ketersediaan bahan pangan lokal segar dengan kandungan gizi setara.
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section id="lokasi" class="map-section">
        <div class="container">
            <div class="row mb-5 align-items-end">
                <div class="col-md-8">
                    <h2 class="section-title mb-0">Temukan SPPG Terdekat</h2>
                    <p class="text-muted mt-2 fs-5">Lihat lokasi Satuan Pelayanan Program Gizi di sekitar Anda.</p>
                </div>
            </div>

            <div class="map-filter">
                <form id="mapFilterForm" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Kota / Kabupaten</label>
                        <select class="form-select form-control-custom" id="filterCity">
                            <option value="">Semua Kota</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Kecamatan</label>
                        <input type="text" class="form-control form-control-custom" id="filterDistrict" placeholder="Cari kecamatan...">
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="button" class="btn btn-primary w-100" style="padding: 12px 20px; border-radius: 12px; font-weight: 600;" id="btnFilterMap">
                            <i class="bi bi-funnel me-1"></i>Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <div class="map-container">
                <!-- ========== SNIPPET: PETA SPPG ========== -->
                <div id="mapView" style="width: 100%; height: 100%;"></div>
                <!-- ========== END SNIPPET: PETA SPPG ========== -->
            </div>
        </div>
    </section>

    <!-- Rating Section -->
    <section id="ulasan" class="rating-section border-top border-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="rating-card text-center mb-5">
                        <h3 class="fw-bold mb-2">Bagaimana Pengalaman Anda?</h3>
                        <p class="text-muted mb-4">Berikan ulasan untuk membantu kami meningkatkan kualitas layanan.</p>
                        <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="bi bi-pencil-square me-2"></i>Tulis Ulasan
                        </button>
                    </div>

                    <h4 class="fw-bold mb-4">Ulasan Masyarakat</h4>
                    <div class="reviews-list" id="reviewsList">
                        @forelse($reviews as $review)
                            <div class="review-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="review-name">{{ $review->masked_name }}</span>
                                        @if($review->sppg)
                                            <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size:0.7rem;">{{ $review->sppg->name }}</span>
                                        @endif
                                    </div>
                                    <div class="text-warning">
                                        @for($i = 0; $i < $review->rating; $i++) ★ @endfor
                                        @for($i = $review->rating; $i < 5; $i++) <span class="text-muted opacity-25">★</span> @endfor
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-0">Belum ada ulasan. Jadilah yang pertama memberikan ulasan!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== MODAL: ULASAN DENGAN VERIFIKASI OTP ========== -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border:none; border-radius:24px; overflow:hidden;">
                <!-- Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="reviewModalLabel">Tulis Ulasan</h5>
                        <p class="text-muted small mb-0" id="reviewStepDesc">Langkah 1 dari 3 — Data Diri</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <!-- Progress Bar -->
                    <div class="d-flex gap-2 mb-4">
                        <div class="flex-fill rounded-pill" id="stepBar1" style="height:4px; background:#2563eb;"></div>
                        <div class="flex-fill rounded-pill" id="stepBar2" style="height:4px; background:#e2e8f0;"></div>
                        <div class="flex-fill rounded-pill" id="stepBar3" style="height:4px; background:#e2e8f0;"></div>
                    </div>

                    <!-- Alert untuk pesan error/success -->
                    <div class="alert d-none mb-3" id="reviewAlert" role="alert"></div>

                    <!-- ═══ TAHAP 1: Data Diri ═══ -->
                    <div id="reviewStep1">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-custom" id="reviewName" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Alamat Email</label>
                            <input type="email" class="form-control form-control-custom" id="reviewEmail" placeholder="contoh@email.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">Pilih SPPG</label>
                            <select class="form-select form-control-custom" id="reviewSppg" required>
                                <option value="">— Pilih SPPG —</option>
                                @foreach($sppgList as $sppg)
                                    <option value="{{ $sppg->id }}">{{ $sppg->name }} — {{ $sppg->city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary w-100" style="padding:14px; border-radius:14px; font-weight:600;" id="btnSendOtp">
                            <span id="btnSendOtpText">Kirim Kode OTP</span>
                            <span id="btnSendOtpSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>

                    <!-- ═══ TAHAP 2: Verifikasi OTP ═══ -->
                    <div id="reviewStep2" class="d-none">
                        <div class="text-center mb-4">
                            <div style="background:#eff6ff; width:64px; height:64px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
                                <i class="bi bi-envelope-check text-primary fs-3"></i>
                            </div>
                            <p class="text-muted small mb-0">Kami telah mengirim kode 6 digit ke</p>
                            <p class="fw-bold mb-0" id="otpEmailDisplay"></p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">Masukkan Kode OTP</label>
                            <div class="d-flex gap-2 justify-content-center" id="otpInputGroup">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="0">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="1">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="2">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="3">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="4">
                                <input type="text" class="form-control text-center fw-bold fs-4 otp-input" maxlength="1" style="width:50px; height:56px; border-radius:12px; border:2px solid #e2e8f0;" data-index="5">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary w-100" style="padding:14px; border-radius:14px; font-weight:600;" id="btnVerifyOtp" disabled>
                            <span id="btnVerifyOtpText">Verifikasi OTP</span>
                            <span id="btnVerifyOtpSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                        <button type="button" class="btn btn-link w-100 mt-2 text-muted small" id="btnResendOtp">Kirim ulang kode OTP</button>
                    </div>

                    <!-- ═══ TAHAP 3: Rating & Komentar ═══ -->
                    <div id="reviewStep3" class="d-none">
                        <div class="text-center mb-4">
                            <div style="background:#f0fdf4; width:64px; height:64px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
                                <i class="bi bi-shield-check text-success fs-3"></i>
                            </div>
                            <p class="text-success fw-semibold mb-0">Email berhasil diverifikasi!</p>
                        </div>
                        <div class="mb-3 text-center">
                            <label class="form-label small fw-semibold text-muted d-block">Beri Penilaian</label>
                            <div class="star-rating" id="modalStarContainer" style="font-size:2.5rem;">
                                <span class="star" data-value="1" style="color:#fbbf24; cursor:pointer;">★</span>
                                <span class="star" data-value="2" style="color:#fbbf24; cursor:pointer;">★</span>
                                <span class="star" data-value="3" style="color:#fbbf24; cursor:pointer;">★</span>
                                <span class="star" data-value="4" style="color:#fbbf24; cursor:pointer;">★</span>
                                <span class="star" data-value="5" style="color:#fbbf24; cursor:pointer;">★</span>
                            </div>
                            <input type="hidden" id="modalRatingValue" value="5">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-muted">Komentar Anda</label>
                            <textarea class="form-control form-control-custom" rows="4" id="reviewComment" placeholder="Ceritakan pengalaman Anda..." required></textarea>
                        </div>
                        <button type="button" class="btn btn-primary w-100" style="padding:14px; border-radius:14px; font-weight:600;" id="btnSubmitReview">
                            <span id="btnSubmitText">Kirim Ulasan</span>
                            <span id="btnSubmitSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                        </button>
                    </div>

                    <!-- ═══ TAHAP SUKSES ═══ -->
                    <div id="reviewStepSuccess" class="d-none text-center py-3">
                        <div style="background:#f0fdf4; width:80px; height:80px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
                            <i class="bi bi-check-lg text-success" style="font-size:2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2">Terima Kasih!</h5>
                        <p class="text-muted mb-4">Ulasan Anda berhasil dikirim dan akan segera ditampilkan.</p>
                        <button type="button" class="btn btn-outline-primary" style="border-radius:12px; padding:10px 32px;" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ========== END MODAL: ULASAN ========== -->

    <!-- ========== MODAL: PENGAJUAN SPPG BARU ========== -->
    <div class="modal fade" id="sppgSubmissionModal" tabindex="-1" aria-labelledby="sppgSubmissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border:none; border-radius:24px; overflow:hidden;">
                <div class="modal-header border-0 px-4 pt-4 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="sppgSubmissionModalLabel">Form Pengajuan SPPG Baru</h5>
                        <p class="text-muted small mb-0">Isi data di bawah ini untuk mengusulkan pembangunan SPPG di daerah Anda.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body px-4 pb-4 pt-3">
                    <div class="alert d-none mb-4" id="submissionAlert" role="alert"></div>

                    <form id="sppgSubmissionForm" enctype="multipart/form-data">
                        <div id="submissionFormContent">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-2"></i>Data Pemohon</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-custom" name="nama_pemohon" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-custom" name="email_pemohon" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">No. HP / WhatsApp <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-custom" name="no_hp" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Nama Instansi/Lembaga (Opsional)</label>
                                    <input type="text" class="form-control form-control-custom" name="nama_instansi">
                                </div>
                            </div>

                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-geo-alt me-2"></i>Data Lokasi Usulan</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-muted">Nama SPPG Usulan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-custom" name="nama_sppg_usulan" placeholder="Contoh: SPPG Kecamatan Mawar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Provinsi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-custom" name="provinsi_id" id="inputProvinsi" placeholder="Contoh: Jawa Barat" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-muted">Kabupaten/Kota <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-custom" name="kota_id" id="inputKota" placeholder="Contoh: Kota Bandung" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-muted">Alamat Lengkap <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-custom" name="alamat" id="inputAlamat" rows="2" placeholder="Contoh: Jl. Soekarno-Hatta No. 123, Kecamatan Buahbatu" required></textarea>
                                </div>
                            </div>

                            {{-- ── BAGIAN VERIFIKASI LOKASI ── --}}
                            <div class="border rounded-3 p-3 mb-4" style="background:#f8fafc;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <p class="fw-semibold small mb-0 text-dark"><i class="bi bi-map me-1 text-primary"></i>Verifikasi Lokasi di Peta <span class="text-danger">*</span></p>
                                        <p class="text-muted" style="font-size:0.75rem; margin:2px 0 0;">Klik "Cari Lokasi" lalu geser marker untuk menyesuaikan posisi yang tepat.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnCariLokasi" style="border-radius:8px; white-space:nowrap; font-size:0.8rem;">
                                        <span id="btnCariLokasiText"><i class="bi bi-search me-1"></i>Cari Lokasi</span>
                                        <span id="btnCariLokasiSpinner" class="spinner-border spinner-border-sm ms-1 d-none" role="status"></span>
                                    </button>
                                </div>

                                {{-- Mini Map --}}
                                <div id="submissionMiniMap" style="height:220px; border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; display:none;"></div>

                                {{-- Status lokasi --}}
                                <div id="lokasiStatusBox" class="d-none mt-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-check-circle-fill text-success"></i>
                                        <span class="small text-success fw-semibold">Lokasi berhasil ditentukan</span>
                                    </div>
                                    <p class="text-muted mb-0 mt-1" style="font-size:0.75rem;" id="lokasiAddressDisplay"></p>
                                    <p class="text-muted mb-0" style="font-size:0.72rem; font-family:monospace;" id="lokasiCoordsDisplay"></p>
                                    <p class="text-muted mb-0 mt-1" style="font-size:0.72rem;"><i class="bi bi-info-circle me-1"></i>Anda bisa geser marker di peta untuk menyesuaikan posisi.</p>
                                </div>

                                {{-- Hidden fields untuk koordinat --}}
                                <input type="hidden" name="latitude"  id="hiddenLat">
                                <input type="hidden" name="longitude" id="hiddenLng">
                            </div>

                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-text me-2"></i>Justifikasi &amp; Dokumen</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-muted">Estimasi Jumlah Sekolah Terjangkau <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-custom" name="estimasi_sekolah" min="1" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-muted">Alasan/Urgensi Pembangunan <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-custom" name="alasan" rows="3" required></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold text-muted">Upload Proposal Pendukung (PDF, Maks 20MB) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control form-control-custom" name="dokumen_proposal" accept=".pdf" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" style="padding:14px; border-radius:14px; font-weight:600;" id="btnSubmitSubmission">
                                <span id="btnSubmitSubmissionText">Kirim Pengajuan</span>
                                <span id="btnSubmitSubmissionSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </button>
                        </div>

                        <!-- Success State -->
                        <div id="submissionSuccessState" class="d-none text-center py-5">
                            <div style="background:#f0fdf4; width:80px; height:80px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
                                <i class="bi bi-check-lg text-success" style="font-size:2.5rem;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Pengajuan Berhasil!</h5>
                            <p class="text-muted mb-4">Terima kasih atas partisipasi Anda. Tim kami akan segera meninjau usulan pembangunan SPPG ini.</p>
                            <button type="button" class="btn btn-outline-primary" style="border-radius:12px; padding:10px 32px;" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ========== END MODAL: PENGAJUAN SPPG BARU ========== -->

    <footer class="bg-white py-4 border-top">
        <div class="container text-center">
            <p class="text-muted mb-0 small">&copy; {{ date('Y') }} Satuan Pelayanan Program Gizi - Makan Bergizi Gratis.</p>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Vanilla JS -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            
            // 1. Navbar Scroll Effect
            const navbar = document.getElementById('mainNav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // ========== SNIPPET: LOGIK PENGAJUAN SPPG BARU ==========
            const submissionForm = document.getElementById('sppgSubmissionForm');
            const btnSubmitSub   = document.getElementById('btnSubmitSubmission');
            const subAlertBox    = document.getElementById('submissionAlert');
            const subFormContent = document.getElementById('submissionFormContent');
            const subSuccessState= document.getElementById('submissionSuccessState');

            // Mini-map vars (dikonfigurasi setelah Leaflet dimuat)
            let submissionMiniMapInstance = null;
            let submissionMarker = null;
            let lokasiDikonfirmasi = false;

            // Inisialisasi mini-map (lazy, saat tombol Cari Lokasi diklik)
            function initSubmissionMiniMap(lat, lng) {
                const mapDiv = document.getElementById('submissionMiniMap');
                mapDiv.style.display = 'block';

                if (!submissionMiniMapInstance) {
                    submissionMiniMapInstance = L.map('submissionMiniMap', { zoomControl: true }).setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }).addTo(submissionMiniMapInstance);
                }

                // Hapus marker lama
                if (submissionMarker) submissionMiniMapInstance.removeLayer(submissionMarker);

                // Marker bisa digeser
                submissionMarker = L.marker([lat, lng], { draggable: true }).addTo(submissionMiniMapInstance);
                submissionMiniMapInstance.flyTo([lat, lng], 15);

                // Update hidden fields saat marker digeser
                submissionMarker.on('dragend', function () {
                    const pos = submissionMarker.getLatLng();
                    document.getElementById('hiddenLat').value = pos.lat.toFixed(7);
                    document.getElementById('hiddenLng').value = pos.lng.toFixed(7);
                    document.getElementById('lokasiCoordsDisplay').textContent =
                        `📍 ${pos.lat.toFixed(6)}, ${pos.lng.toFixed(6)}`;
                });

                // Invalidate size supaya peta render sempurna dalam modal
                setTimeout(() => submissionMiniMapInstance.invalidateSize(), 300);
            }

            // ═══════════════════════════════════════════════════════════
            // ADVANCED GEOCODING ENGINE — Nominatim (8 strategi + normalisasi)
            // ═══════════════════════════════════════════════════════════
            const btnCariLokasi = document.getElementById('btnCariLokasi');

            /* ── 1. Tabel singkatan umum alamat Indonesia ── */
            const ABBREV_MAP = {
                'jl\\.':       'Jalan',   'jln\\.':   'Jalan',   'jl ':      'Jalan ',
                'gg\\.':       'Gang',    'gg ':      'Gang ',
                'kec\\.':      'Kecamatan', 'kec ':   'Kecamatan ',
                'kel\\.':      'Kelurahan', 'kel ':   'Kelurahan ',
                'kab\\.':      'Kabupaten', 'kab ':   'Kabupaten ',
                'ds\\.':       'Desa',    'ds ':      'Desa ',
                'kp\\.':       'Kampung', 'kp ':      'Kampung ',
                'blok ':       'Blok ',
                'perum ':      'Perumahan ',
                'komp\\.':     'Kompleks ', 'komp ':  'Kompleks ',
                'raya ':       'Raya ',
            };

            /* ── 2. Normalisasi string alamat ── */
            function normalizeAddr(raw) {
                if (!raw) return '';
                let s = raw;
                // (a) Hapus teks bilingual setelah em-dash / en-dash / slash
                s = s.replace(/\s*[—–\/\|].+$/g, '');
                // (b) Hapus kode pos 5 digit
                s = s.replace(/\b\d{5}\b/g, '');
                // (c) Hapus RT/RW
                s = s.replace(/\bRT[\s\/\.]*RW[\s\/\.]*[\d\/]+\b/gi, '');
                s = s.replace(/\bRT[\s\.]*\d+\b/gi, '');
                s = s.replace(/\bRW[\s\.]*\d+\b/gi, '');
                // (d) Expand singkatan (case-insensitive)
                for (const [abbr, full] of Object.entries(ABBREV_MAP)) {
                    s = s.replace(new RegExp(abbr, 'gi'), full);
                }
                // (e) Bersihkan spasi berlebih & koma ganda
                s = s.replace(/,\s*,/g, ',').replace(/\s{2,}/g, ' ').trim();
                // (f) Hapus koma di awal/akhir
                s = s.replace(/^[,\s]+|[,\s]+$/g, '');
                return s;
            }

            /* ── 3. Ekstrak komponen spesifik dari string alamat ── */
            function extractComponent(str, keywords) {
                // Cari pola "Kata Kunci ... , atau akhir string"
                const pattern = new RegExp(
                    `(?:${keywords.join('|')})[.\\s]+([A-Za-z][A-Za-z\\s]+?)(?=\\s*,|$)`, 'i'
                );
                const m = str.match(pattern);
                return m ? m[1].trim() : null;
            }

            function extractJalan(str) {
                // Ambil hanya nama jalan sebelum koma/kecamatan/kelurahan pertama
                const clean = normalizeAddr(str);
                // Hapus bagian "Kecamatan X, Kelurahan Y, ..." ke belakang
                const cutAt = clean.search(/Kecamatan|Kelurahan|Desa|Kampung/i);
                const streetPart = cutAt > 0 ? clean.substring(0, cutAt) : clean.split(',')[0];
                return streetPart.trim().replace(/,$/, '');
            }

            /* ── Ekstrak nomor rumah dari string alamat ── */
            function extractHouseNo(str) {
                // Cocokkan: No. 123, No.123A, Nomor 123, No 123
                const m = str.match(/\bNo\.?\s*(\d+[A-Za-z]?)\b/i)
                         || str.match(/\bNomor\s+(\d+[A-Za-z]?)\b/i)
                         || str.match(/\b#(\d+[A-Za-z]?)\b/);
                return m ? m[1] : null;
            }

            /* ── Ekstrak nama jalan SAJA (tanpa nomor, kecamatan, kelurahan) ── */
            function extractStreetOnly(str) {
                const clean = normalizeAddr(str);
                // Potong di Kecamatan/Kelurahan/Desa/Kota jika ada
                const cutAdmin = clean.search(/Kecamatan|Kelurahan|Desa|Kampung|Kota\s/i);
                let s = cutAdmin > 0 ? clean.substring(0, cutAdmin) : clean.split(',')[0];
                // Hapus nomor rumah (No. xxx)
                s = s.replace(/\s*,?\s*No\.?\s*\d+[A-Za-z]?\b/gi, '');
                s = s.replace(/\s*,?\s*Nomor\s*\d+[A-Za-z]?\b/gi, '');
                return s.replace(/,$/, '').trim();
            }

            /* ── 4. Normalisasi nama kota (hapus "Kota"/"Kabupaten" prefix untuk query tertentu) ── */
            function bareCity(city) {
                return normalizeAddr(city)
                    .replace(/^(Kota|Kabupaten|Kab\.?)\s+/i, '')
                    .trim();
            }

            /* ── 5. Helper: fetch Nominatim dan parse ── */
            const NOMI_BASE = 'https://nominatim.openstreetmap.org/search';
            const NOMI_HDR  = { 'Accept-Language': 'id' };

            async function nomiQuery(params) {
                const qs = Object.entries(params)
                    .filter(([, v]) => v)
                    .map(([k, v]) => `${k}=${encodeURIComponent(v)}`)
                    .join('&');
                const res = await fetch(`${NOMI_BASE}?${qs}&format=json&limit=1&countrycodes=id`, { headers: NOMI_HDR });
                const data = await res.json();
                return data && data.length > 0 ? data[0] : null;
            }

            /* ── 6. Engine utama: 10 strategi berurutan ── */
            async function tryGeocode(rawStreet, rawCity, rawState) {
                const street     = normalizeAddr(rawStreet);
                const city       = normalizeAddr(rawCity);
                const state      = normalizeAddr(rawState);
                const cityBare   = bareCity(rawCity);
                const jalan      = extractJalan(rawStreet);       // nama jalan + nomor
                const streetOnly = extractStreetOnly(rawStreet);  // nama jalan SAJA
                const houseNo    = extractHouseNo(rawStreet);     // nomor rumah
                const kecamatan  = extractComponent(rawStreet, ['Kecamatan', 'Kec']);
                const kelurahan  = extractComponent(rawStreet, ['Kelurahan', 'Kel']);

                // Format untuk Nominatim structured: "{housenumber} {streetname}"
                // Nominatim mengharapkan nomor DI DEPAN nama jalan
                const streetForNomi = houseNo && streetOnly
                    ? `${houseNo} ${streetOnly}`
                    : (streetOnly || jalan);

                let result;

                // ── S1: Nominatim structured paling presisi (housenumber depan + street + city + state)
                if (houseNo) {
                    result = await nomiQuery({ street: streetForNomi, city: city, state: state, country: 'Indonesia' });
                    if (result) return { ...result, _strategy: 1 };

                    // ── S2: Sama, city tanpa prefix Kota/Kabupaten
                    result = await nomiQuery({ street: streetForNomi, city: cityBare, state: state, country: 'Indonesia' });
                    if (result) return { ...result, _strategy: 2 };

                    // ── S3: Tanpa state (kadang state membingungkan Nominatim)
                    result = await nomiQuery({ street: streetForNomi, city: city, country: 'Indonesia' });
                    if (result) return { ...result, _strategy: 3 };

                    // ── S4: Free-form dengan nomor rumah eksplisit
                    result = await nomiQuery({ q: [streetForNomi, city, 'Indonesia'].join(', ') });
                    if (result) return { ...result, _strategy: 4 };
                }

                // ── S5: Structured tanpa nomor rumah (street name saja + city + state)
                result = await nomiQuery({ street: streetOnly, city: city, state: state, country: 'Indonesia' });
                if (result) return { ...result, _strategy: 5 };

                // ── S6: Structured, street name + bare city
                result = await nomiQuery({ street: streetOnly, city: cityBare, state: state, country: 'Indonesia' });
                if (result) return { ...result, _strategy: 6 };

                // ── S7: Free-form: jalan (dengan nomor) + kota
                result = await nomiQuery({ q: [jalan, city, 'Indonesia'].join(', ') });
                if (result) return { ...result, _strategy: 7 };

                // ── S8: Kecamatan + kota (jika ada komponen kecamatan)
                if (kecamatan) {
                    result = await nomiQuery({ q: [kecamatan, city, state, 'Indonesia'].join(', ') });
                    if (result) return { ...result, _strategy: 8 };
                }

                // ── S9: Kelurahan + kota
                if (kelurahan) {
                    result = await nomiQuery({ q: [kelurahan, city, 'Indonesia'].join(', ') });
                    if (result) return { ...result, _strategy: 9 };
                }

                // ── S10: Kota + Provinsi — fallback wilayah
                result = await nomiQuery({ q: [city, state, 'Indonesia'].join(', ') });
                if (result) return { ...result, _strategy: 10 };

                return null;
            }

            /* ── 7. Handler tombol Cari Lokasi ── */
            if (btnCariLokasi) {
                btnCariLokasi.addEventListener('click', async () => {
                    const alamat   = document.getElementById('inputAlamat')?.value?.trim();
                    const kota     = document.getElementById('inputKota')?.value?.trim();
                    const provinsi = document.getElementById('inputProvinsi')?.value?.trim();

                    if (!alamat || !kota || !provinsi) {
                        subAlertBox.className = 'alert alert-warning mb-4';
                        subAlertBox.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Harap isi <strong>Provinsi, Kabupaten/Kota, dan Alamat Lengkap</strong> terlebih dahulu.';
                        subAlertBox.classList.remove('d-none');
                        return;
                    }
                    subAlertBox.classList.add('d-none');

                    document.getElementById('btnCariLokasiText').innerHTML = 'Mencari...';
                    document.getElementById('btnCariLokasiSpinner').classList.remove('d-none');
                    btnCariLokasi.disabled = true;

                    try {
                        const result = await tryGeocode(alamat, kota, provinsi);

                        if (result) {
                            const lat = parseFloat(result.lat);
                            const lng = parseFloat(result.lon);
                            const displayName = result.display_name;
                            const strategy = result._strategy || 1;

                            document.getElementById('hiddenLat').value = lat.toFixed(7);
                            document.getElementById('hiddenLng').value = lng.toFixed(7);
                            initSubmissionMiniMap(lat, lng);

                            document.getElementById('lokasiStatusBox').classList.remove('d-none');
                            document.getElementById('lokasiAddressDisplay').textContent = displayName;
                            document.getElementById('lokasiCoordsDisplay').textContent = `📍 ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            lokasiDikonfirmasi = true;

                            // Beri tahu user akurasi berdasarkan strategi yang berhasil
                            if (strategy <= 4) {
                                // Presisi tinggi (dengan nomor rumah) — tidak perlu warning
                            } else if (strategy <= 7) {
                                subAlertBox.className = 'alert alert-success mb-4';
                                subAlertBox.innerHTML = '<i class="bi bi-check-circle me-1"></i>Lokasi ditemukan berdasarkan nama jalan. Geser marker jika posisi kurang tepat.';
                                subAlertBox.classList.remove('d-none');
                            } else {
                                subAlertBox.className = 'alert alert-info mb-4';
                                subAlertBox.innerHTML = '<i class="bi bi-info-circle me-1"></i>Lokasi ditemukan berdasarkan <strong>kecamatan/kota</strong>. Silakan <strong>geser marker</strong> ke lokasi yang lebih tepat.';
                                subAlertBox.classList.remove('d-none');
                            }
                        } else {
                            subAlertBox.className = 'alert alert-warning mb-4';
                            subAlertBox.innerHTML = `
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                <strong>Lokasi tidak ditemukan.</strong> Pastikan:<br>
                                <ul class="mb-0 mt-1 ps-3" style="font-size:0.85rem;">
                                  <li>Provinsi: nama resmi tanpa singkatan, contoh <strong>Jawa Barat</strong></li>
                                  <li>Kota: nama resmi, contoh <strong>Kota Bandung</strong> atau <strong>Kabupaten Sumedang</strong></li>
                                  <li>Alamat: nama jalan tanpa RT/RW, contoh <strong>Jalan Sudirman No. 10</strong></li>
                                </ul>`;
                            subAlertBox.classList.remove('d-none');
                        }
                    } catch (err) {
                        subAlertBox.className = 'alert alert-danger mb-4';
                        subAlertBox.innerHTML = '<i class="bi bi-wifi-off me-1"></i>Gagal terhubung. Periksa koneksi internet Anda.';
                        subAlertBox.classList.remove('d-none');
                    } finally {
                        document.getElementById('btnCariLokasiText').innerHTML = '<i class="bi bi-search me-1"></i>Cari Lokasi';
                        document.getElementById('btnCariLokasiSpinner').classList.add('d-none');
                        btnCariLokasi.disabled = false;
                    }
                });
            }

            if (submissionForm) {
                submissionForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    // Wajib verifikasi lokasi sebelum submit
                    const hiddenLat = document.getElementById('hiddenLat').value;
                    const hiddenLng = document.getElementById('hiddenLng').value;
                    if (!hiddenLat || !hiddenLng) {
                        subAlertBox.className = 'alert alert-warning mb-4';
                        subAlertBox.innerHTML = '<i class="bi bi-map me-1"></i>Harap klik <strong>"Cari Lokasi"</strong> dan verifikasi lokasi di peta sebelum mengirim pengajuan.';
                        subAlertBox.classList.remove('d-none');
                        return;
                    }

                    subAlertBox.classList.add('d-none');
                    btnSubmitSub.disabled = true;
                    document.getElementById('btnSubmitSubmissionText').textContent = 'Mengirim...';
                    document.getElementById('btnSubmitSubmissionSpinner').classList.remove('d-none');

                    const formData = new FormData(submissionForm);

                    try {
                        const res = await fetch('{{ route("sppg.submit") }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: formData
                        });

                        const data = await res.json();

                        if (res.ok && data.success) {
                            subFormContent.classList.add('d-none');
                            subSuccessState.classList.remove('d-none');
                        } else {
                            // Tampilkan error detail dari backend
                            let errorMsg = data.message || 'Terjadi kesalahan pada input data.';
                            if (data.errors) {
                                const errList = Object.values(data.errors).flat().join('<br>• ');
                                errorMsg += `<br><br>• ${errList}`;
                            }
                            subAlertBox.className = 'alert alert-danger mb-4';
                            subAlertBox.innerHTML = errorMsg;
                            subAlertBox.classList.remove('d-none');
                        }
                    } catch (err) {
                        subAlertBox.className = 'alert alert-danger mb-4';
                        subAlertBox.innerHTML = 'Gagal terhubung ke server. Silakan coba lagi.';
                        subAlertBox.classList.remove('d-none');
                    } finally {
                        btnSubmitSub.disabled = false;
                        document.getElementById('btnSubmitSubmissionText').textContent = 'Kirim Pengajuan';
                        document.getElementById('btnSubmitSubmissionSpinner').classList.add('d-none');
                    }
                });

                // Reset modal on close
                document.getElementById('sppgSubmissionModal').addEventListener('hidden.bs.modal', () => {
                    submissionForm.reset();
                    subFormContent.classList.remove('d-none');
                    subSuccessState.classList.add('d-none');
                    subAlertBox.classList.add('d-none');
                    document.getElementById('lokasiStatusBox').classList.add('d-none');
                    document.getElementById('submissionMiniMap').style.display = 'none';
                    document.getElementById('hiddenLat').value = '';
                    document.getElementById('hiddenLng').value = '';
                    lokasiDikonfirmasi = false;
                    if (submissionMarker && submissionMiniMapInstance) {
                        submissionMiniMapInstance.removeLayer(submissionMarker);
                        submissionMarker = null;
                    }
                });
            }
            // ========== END SNIPPET: LOGIK PENGAJUAN SPPG BARU ==========

            // ========== SNIPPET: LOGIK 3-TAHAP REVIEW & OTP ==========
            const reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
            const modalEl = document.getElementById('reviewModal');

            // Elements Tahap
            const step1 = document.getElementById('reviewStep1');
            const step2 = document.getElementById('reviewStep2');
            const step3 = document.getElementById('reviewStep3');
            const stepSuccess = document.getElementById('reviewStepSuccess');
            
            // Progress Bar & Title
            const stepBar1 = document.getElementById('stepBar1');
            const stepBar2 = document.getElementById('stepBar2');
            const stepBar3 = document.getElementById('stepBar3');
            const stepDesc = document.getElementById('reviewStepDesc');
            const alertBox = document.getElementById('reviewAlert');

            // Form Inputs
            const inputName = document.getElementById('reviewName');
            const inputEmail = document.getElementById('reviewEmail');
            const inputSppg = document.getElementById('reviewSppg');
            const otpInputs = document.querySelectorAll('.otp-input');
            const inputComment = document.getElementById('reviewComment');
            
            // Buttons
            const btnSendOtp = document.getElementById('btnSendOtp');
            const btnVerifyOtp = document.getElementById('btnVerifyOtp');
            const btnSubmitReview = document.getElementById('btnSubmitReview');

            // Helper: Tampilkan Alert
            const showAlert = (message, type = 'danger') => {
                alertBox.className = `alert alert-${type} mb-3`;
                alertBox.innerHTML = message;
                alertBox.classList.remove('d-none');
            };

            // Reset Form saat modal ditutup
            modalEl.addEventListener('hidden.bs.modal', () => {
                step1.classList.remove('d-none');
                step2.classList.add('d-none');
                step3.classList.add('d-none');
                stepSuccess.classList.add('d-none');
                
                stepBar2.style.background = '#e2e8f0';
                stepBar3.style.background = '#e2e8f0';
                stepDesc.textContent = 'Langkah 1 dari 3 — Data Diri';
                
                inputName.value = '';
                inputEmail.value = '';
                inputSppg.value = '';
                otpInputs.forEach(i => i.value = '');
                inputComment.value = '';
                document.getElementById('modalRatingValue').value = 5;
                document.querySelectorAll('#modalStarContainer .star').forEach(s => s.style.color = '#fbbf24');
                
                alertBox.classList.add('d-none');
            });

            // ── TAHAP 1: Kirim OTP ──
            btnSendOtp.addEventListener('click', async () => {
                if (!inputName.value || !inputEmail.value || !inputSppg.value) {
                    showAlert('Harap lengkapi semua data diri.');
                    return;
                }

                alertBox.classList.add('d-none');
                btnSendOtp.disabled = true;
                document.getElementById('btnSendOtpText').textContent = 'Mengirim...';
                document.getElementById('btnSendOtpSpinner').classList.remove('d-none');

                try {
                    const res = await fetch('{{ route("review.sendOtp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: inputName.value,
                            email: inputEmail.value,
                            sppg_id: inputSppg.value
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok && data.success) {
                        // Pindah ke tahap 2
                        step1.classList.add('d-none');
                        step2.classList.remove('d-none');
                        stepBar2.style.background = '#2563eb';
                        stepDesc.textContent = 'Langkah 2 dari 3 — Verifikasi Email';
                        document.getElementById('otpEmailDisplay').textContent = inputEmail.value;
                        setTimeout(() => otpInputs[0].focus(), 500);
                    } else {
                        showAlert(data.message || 'Gagal mengirim OTP.');
                    }
                } catch (err) {
                    showAlert('Terjadi kesalahan sistem.');
                } finally {
                    btnSendOtp.disabled = false;
                    document.getElementById('btnSendOtpText').textContent = 'Kirim Kode OTP';
                    document.getElementById('btnSendOtpSpinner').classList.add('d-none');
                }
            });

            // ── TAHAP 2: Input OTP ──
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, ''); // Hanya angka
                    if (this.value !== '') {
                        if (index < otpInputs.length - 1) {
                            otpInputs[index + 1].focus();
                        } else {
                            btnVerifyOtp.disabled = false; // Buka tombol
                            btnVerifyOtp.click(); // Auto submit
                        }
                    }
                    
                    // Cek jika semua terisi
                    const allFilled = Array.from(otpInputs).every(i => i.value.length === 1);
                    btnVerifyOtp.disabled = !allFilled;
                });

                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        otpInputs[index - 1].focus();
                        otpInputs[index - 1].value = '';
                    }
                });
            });

            btnVerifyOtp.addEventListener('click', async () => {
                const otp = Array.from(otpInputs).map(i => i.value).join('');
                if (otp.length < 6) return;

                alertBox.classList.add('d-none');
                btnVerifyOtp.disabled = true;
                document.getElementById('btnVerifyOtpText').textContent = 'Memverifikasi...';
                document.getElementById('btnVerifyOtpSpinner').classList.remove('d-none');

                try {
                    const res = await fetch('{{ route("review.verifyOtp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            email: inputEmail.value,
                            otp: otp
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok && data.success) {
                        // Pindah ke tahap 3
                        step2.classList.add('d-none');
                        step3.classList.remove('d-none');
                        stepBar3.style.background = '#2563eb';
                        stepDesc.textContent = 'Langkah 3 dari 3 — Penilaian';
                    } else {
                        showAlert(data.message || 'OTP tidak valid.');
                        otpInputs.forEach(i => i.value = '');
                        otpInputs[0].focus();
                    }
                } catch (err) {
                    showAlert('Terjadi kesalahan sistem.');
                } finally {
                    btnVerifyOtp.disabled = false;
                    document.getElementById('btnVerifyOtpText').textContent = 'Verifikasi OTP';
                    document.getElementById('btnVerifyOtpSpinner').classList.add('d-none');
                }
            });

            // Kirim Ulang OTP
            document.getElementById('btnResendOtp').addEventListener('click', () => {
                btnSendOtp.click(); // Panggil fungsi kirim tahap 1
            });

            // ── TAHAP 3: Rating Bintang & Submit Ulasan ──
            const modalStars = document.querySelectorAll('#modalStarContainer .star');
            const modalRatingValue = document.getElementById('modalRatingValue');

            modalStars.forEach(star => {
                star.addEventListener('click', (e) => {
                    const value = e.target.getAttribute('data-value');
                    modalRatingValue.value = value;
                    modalStars.forEach(s => s.style.color = '#e2e8f0');
                    modalStars.forEach(s => {
                        if (s.getAttribute('data-value') <= value) s.style.color = '#fbbf24';
                    });
                });
            });

            btnSubmitReview.addEventListener('click', async () => {
                if (!inputComment.value) {
                    showAlert('Harap isi komentar Anda.');
                    return;
                }

                alertBox.classList.add('d-none');
                btnSubmitReview.disabled = true;
                document.getElementById('btnSubmitText').textContent = 'Mengirim...';
                document.getElementById('btnSubmitSpinner').classList.remove('d-none');

                try {
                    const res = await fetch('{{ route("review.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: inputName.value,
                            email: inputEmail.value,
                            sppg_id: inputSppg.value,
                            rating: modalRatingValue.value,
                            comment: inputComment.value
                        })
                    });
                    
                    const data = await res.json();
                    
                    if (res.ok && data.success) {
                        // Pindah ke Success Screen
                        step3.classList.add('d-none');
                        stepSuccess.classList.remove('d-none');
                        stepDesc.textContent = 'Selesai';
                        
                        // Tambahkan ke DOM langsung (opsional)
                        const newReview = `
                            <div class="review-item border border-success border-opacity-25" style="background:#f0fdf4;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="review-name">${data.review.masked_name}</span>
                                        <span class="badge bg-success ms-2">Baru</span>
                                    </div>
                                    <div class="text-warning">
                                        ${'★'.repeat(data.review.rating)}${'<span class="text-muted opacity-25">★</span>'.repeat(5 - data.review.rating)}
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">${data.review.comment}</p>
                            </div>
                        `;
                        const reviewList = document.getElementById('reviewsList');
                        // Hapus placeholder kosong jika ada
                        const placeholder = reviewList.querySelector('.text-center.text-muted');
                        if (placeholder) placeholder.remove();
                        
                        reviewList.insertAdjacentHTML('afterbegin', newReview);
                    } else {
                        showAlert(data.message || 'Gagal menyimpan ulasan.');
                    }
                } catch (err) {
                    showAlert('Terjadi kesalahan saat mengirim ulasan.');
                } finally {
                    btnSubmitReview.disabled = false;
                    document.getElementById('btnSubmitText').textContent = 'Kirim Ulasan';
                    document.getElementById('btnSubmitSpinner').classList.add('d-none');
                }
            });
            // ========== END SNIPPET: LOGIK 3-TAHAP REVIEW & OTP ==========
        });
    </script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- ========== SNIPPET: INISIALISASI PETA SPPG ========== -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {

        // ── 1. Inisialisasi Peta ──────────────────────────────────────────
        const map = L.map('mapView').setView([-6.2, 106.816], 11); // Default: Jakarta

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Layer group untuk marker
        const markersLayer = L.layerGroup().addTo(map);

        // ── 2. Custom Icon ────────────────────────────────────────────────
        const sppgIcon = L.divIcon({
            className: '',
            html: '<div style="background:#2563eb; width:32px; height:32px; border-radius:50%; border:3px solid white; box-shadow:0 2px 8px rgba(0,0,0,0.3); display:flex; align-items:center; justify-content:center;"><svg width="16" height="16" fill="white" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg></div>',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -34],
        });

        // ── 3. Fetch data dari API ────────────────────────────────────────
        let allSppgData = [];

        fetch('/api/public/maps/sppg')
            .then(function(res) { return res.json(); })
            .then(function(result) {
                if (!result.success || !result.data) return;
                allSppgData = result.data;
                renderMarkers(allSppgData);

                // Auto-fit bounds jika ada data
                if (allSppgData.length > 0) {
                    const bounds = allSppgData.map(function(s) { return [s.latitude, s.longitude]; });
                    map.fitBounds(bounds, { padding: [50, 50] });
                }

                // Populate dropdown kota dari data
                const cities = Array.from(new Set(allSppgData.map(function(s) { return s.city; }).filter(Boolean))).sort();
                const selectCity = document.getElementById('filterCity');
                selectCity.innerHTML = '<option value="">Semua Kota</option>';
                cities.forEach(function(city) {
                    const opt = document.createElement('option');
                    opt.value = city.toLowerCase();
                    opt.textContent = city;
                    selectCity.appendChild(opt);
                });
            })
            .catch(function(err) {
                console.error('Gagal memuat data peta SPPG:', err);
            });

        // ── 4. Render Markers ─────────────────────────────────────────────
        function renderMarkers(data) {
            markersLayer.clearLayers();

            data.forEach(function(s) {
                if (!s.latitude || !s.longitude) return;

                const marker = L.marker([s.latitude, s.longitude], { icon: sppgIcon });
                marker.bindPopup(
                    '<div style="min-width:200px; font-family:Outfit,sans-serif;">' +
                        '<h6 style="margin:0 0 4px; font-weight:700; color:#0f172a;">' + (s.school_name || 'N/A') + '</h6>' +
                        '<p style="margin:0 0 8px; color:#64748b; font-size:0.85rem;">' + (s.district || '') + ', ' + (s.city || '') + '</p>' +
                        '<div style="display:flex; gap:6px; flex-wrap:wrap;">' +
                            (s.sppg_name ? '<span style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:600;">' + s.sppg_name + '</span>' : '') +
                            (s.school_type ? '<span style="background:#f0fdf4; color:#16a34a; padding:2px 8px; border-radius:12px; font-size:0.75rem; font-weight:600;">' + s.school_type + '</span>' : '') +
                        '</div>' +
                    '</div>'
                );
                markersLayer.addLayer(marker);
            });
        }

        // ── 5. Filter Peta ────────────────────────────────────────────────
        var btnFilter = document.getElementById('btnFilterMap');
        btnFilter.addEventListener('click', function () {
            var city = document.getElementById('filterCity').value.toLowerCase();
            var district = document.getElementById('filterDistrict').value.toLowerCase().trim();

            var filtered = allSppgData;
            if (city) {
                filtered = filtered.filter(function(s) { return (s.city || '').toLowerCase().includes(city); });
            }
            if (district) {
                filtered = filtered.filter(function(s) { return (s.district || '').toLowerCase().includes(district); });
            }

            renderMarkers(filtered);

            // Fit bounds ke hasil filter
            if (filtered.length > 0) {
                var bounds = filtered.map(function(s) { return [s.latitude, s.longitude]; });
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        });
    });
    </script>
    <!-- ========== END SNIPPET: INISIALISASI PETA SPPG ========== -->
</body>
</html>
