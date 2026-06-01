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
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 16px;
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            min-width: 300px;
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

            <div class="row g-4">
                @php
                    $highlightItem = collect($weeklyMenus)->firstWhere('is_today', true) ?? $weeklyMenus[0];
                    $otherItems = collect($weeklyMenus)->filter(fn($item) => $item['day'] !== $highlightItem['day']);
                @endphp
                
                {{-- Kolom Kiri (Daftar Menu Harian) --}}
                <div class="col-lg-8">
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        @foreach($otherItems as $item)
                            <div class="col">
                                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="transition: all 0.3s ease;">
                                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-3 pb-2">
                                        <span class="fw-bold text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 0.5px;">{{ strtoupper($item['day_name']) }}</span>
                                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    </div>
                                    <img src="{{ $item['image'] }}" alt="{{ $item['menu'] }}" class="card-img-top rounded-0" style="height: 180px; object-fit: cover;">
                                    <div class="card-footer bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 pb-3 px-3">
                                        <span class="fw-bold text-dark text-truncate" style="max-width: 65%;">{{ $item['menu'] }}</span>
                                        <span class="fw-bold text-primary">{{ $item['calories'] }} Kcal</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Kolom Kanan (Menu Highlight Hari Ini) --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-lg rounded-4 h-100 overflow-hidden bg-white">
                        <div class="position-relative">
                            <img src="{{ $highlightItem['image'] }}" alt="{{ $highlightItem['menu'] }}" class="w-100" style="height: 280px; object-fit: cover;">
                            
                            <span class="position-absolute top-0 end-0 m-3 badge bg-primary px-3 py-2 rounded-pill fw-bold shadow-sm" style="font-size: 0.75rem; letter-spacing: 0.5px;">HARI INI</span>
                            
                            <div class="position-absolute bottom-0 start-0 end-0 p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 60%, rgba(0,0,0,0) 100%);">
                                <h4 class="fw-bold mb-0">{{ $highlightItem['menu'] }}</h4>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <h6 class="fw-bold text-dark mb-3">Kandungan Gizi &amp; Nutrisi</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-info text-dark px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-egg-fried me-1"></i> {{ $highlightItem['protein'] }}g Protein</span>
                                <span class="badge bg-success text-white px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-lightning-charge me-1"></i> {{ $highlightItem['calories'] }} Kcal</span>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-basket me-1"></i> {{ $highlightItem['carbs'] }}g Karbo</span>
                                <span class="badge bg-danger text-white px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.75rem;"><i class="bi bi-droplet me-1"></i> {{ $highlightItem['fat'] }}g Lemak</span>
                            </div>
                        </div>

                        <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-block bg-success rounded-circle" style="width: 10px; height: 10px; animation: pulse 2s infinite;"></span>
                                <span class="small fw-semibold text-success" style="font-size: 0.8rem;">Status: Sedang Dikirim</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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

            <div class="map-container">
                <div class="map-filter">
                    <h5 class="fw-bold mb-3">Filter Lokasi</h5>
                    <form id="mapFilterForm">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Kota / Kabupaten</label>
                            <select class="form-select form-control-custom" id="filterCity">
                                <option value="">Pilih Kota</option>
                                <option value="jakarta">Jakarta</option>
                                <option value="bandung">Bandung</option>
                                <option value="surabaya">Surabaya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Kecamatan</label>
                            <input type="text" class="form-control form-control-custom" id="filterDistrict" placeholder="Cari kecamatan...">
                        </div>
                        <button type="button" class="btn btn-primary w-100 btn-custom" style="padding: 10px;" id="btnFilterMap">Terapkan Filter</button>
                    </form>
                </div>
                
                <div id="mapView" style="width: 100%; height: 100%; display:flex; align-items:center; justify-content:center;">
                    <p class="text-muted fw-semibold">Peta Interaktif Dimuat Di Sini</p>
                    <!-- CALL ADMIN MAP FUNCTION HERE -->
                    <!-- contoh: initAdminMap('mapView'); -->
                </div>
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
                        
                        <form id="ratingForm" class="text-start">
                            <div class="mb-4 text-center">
                                <div class="star-rating" id="starContainer">
                                    <span class="star" data-value="1">★</span>
                                    <span class="star" data-value="2">★</span>
                                    <span class="star" data-value="3">★</span>
                                    <span class="star" data-value="4">★</span>
                                    <span class="star" data-value="5">★</span>
                                </div>
                                <input type="hidden" id="ratingValue" name="rating" value="5">
                            </div>
                            
                            <div class="mb-3">
                                <textarea class="form-control form-control-custom" rows="4" placeholder="Tuliskan ulasan Anda..." required></textarea>
                            </div>

                            <div class="row align-items-end mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-semibold">Keamanan (Captcha)</label>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light px-3 py-2 rounded-3 fw-bold text-dark border me-3" style="letter-spacing: 3px;">
                                            1234
                                        </div>
                                        <input type="text" id="captchaInput" class="form-control form-control-custom" placeholder="Masukkan 1234">
                                    </div>
                                    <small class="text-danger d-none mt-1" id="captchaError">Kode tidak sesuai</small>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <button type="submit" id="btnSubmitReview" class="btn btn-custom w-100" disabled>Kirim Ulasan</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <h4 class="fw-bold mb-4">Ulasan Masyarakat</h4>
                    <div class="reviews-list">
                        @foreach($reviews as $review)
                            @php
                                // Masking logic: Budi Santoso -> B*** S***
                                $words = explode(' ', $review['name']);
                                $maskedName = '';
                                foreach ($words as $word) {
                                    if (strlen($word) > 1) {
                                        $maskedName .= substr($word, 0, 1) . str_repeat('*', strlen($word) - 1) . ' ';
                                    } else {
                                        $maskedName .= $word . ' ';
                                    }
                                }
                                $maskedName = trim($maskedName);
                            @endphp
                            <div class="review-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="review-name">{{ $maskedName }}</div>
                                    <div class="text-warning">
                                        @for($i = 0; $i < $review['rating']; $i++) ★ @endfor
                                        @for($i = $review['rating']; $i < 5; $i++) <span class="text-muted opacity-25">★</span> @endfor
                                    </div>
                                </div>
                                <p class="text-muted mb-0 small">{{ $review['comment'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

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

            // 2. Map Filter Interaction
            const btnFilter = document.getElementById('btnFilterMap');
            btnFilter.addEventListener('click', () => {
                const city = document.getElementById('filterCity').value;
                const district = document.getElementById('filterDistrict').value;
                
                console.log(`Filtering map for City: ${city}, District: ${district}`);
                // CALL ADMIN MAP FUNCTION HERE
                // example: updateAdminMap(city, district);
                alert(`Filter diterapkan: \nKota: ${city || 'Semua'} \nKecamatan: ${district || 'Semua'}`);
            });

            // 3. Rating Stars Logic
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('ratingValue');
            
            // Default select 5 stars
            stars.forEach(s => s.style.color = '#fbbf24');

            stars.forEach(star => {
                star.addEventListener('click', (e) => {
                    const value = e.target.getAttribute('data-value');
                    ratingValue.value = value;
                    
                    // Reset all stars
                    stars.forEach(s => s.style.color = '#e2e8f0');
                    // Highlight selected stars
                    stars.forEach(s => {
                        if (s.getAttribute('data-value') <= value) {
                            s.style.color = '#fbbf24';
                        }
                    });
                });
            });

            // 4. Captcha Validation Logic
            const captchaInput = document.getElementById('captchaInput');
            const btnSubmit = document.getElementById('btnSubmitReview');
            const captchaError = document.getElementById('captchaError');
            const EXPECTED_CAPTCHA = '1234';

            captchaInput.addEventListener('input', (e) => {
                const val = e.target.value.trim();
                if (val === EXPECTED_CAPTCHA) {
                    btnSubmit.disabled = false;
                    captchaError.classList.add('d-none');
                } else {
                    btnSubmit.disabled = true;
                    if(val.length >= 4) {
                        captchaError.classList.remove('d-none');
                    } else {
                        captchaError.classList.add('d-none');
                    }
                }
            });

            // Handle Submit
            document.getElementById('ratingForm').addEventListener('submit', (e) => {
                e.preventDefault();
                alert('Terima kasih! Ulasan Anda telah disimulasikan terkirim.');
                e.target.reset();
                btnSubmit.disabled = true;
                // Reset visual stars to 5 default
                ratingValue.value = 5;
                stars.forEach(s => s.style.color = '#fbbf24');
            });
        });
    </script>
</body>
</html>
