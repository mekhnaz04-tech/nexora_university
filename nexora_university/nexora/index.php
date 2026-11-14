<?php
session_start();
$has_auth_msg = isset($_SESSION['auth_msg']);
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXORA Esports University — Kampus Inovasi Esports & Teknologi Masa Depan</title>
    
    <!-- Google Fonts: Orbitron (Futuristik) & Plus Jakarta Sans (Modern Akademik) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-dark: #080715;
            --bg-card: #100e26;
            --neon-blue: #00f2fe;
            --electric-blue: #0d6efd;
            --neon-purple: #7928ca;
            --neon-pink: #ff0080;
            --gold-accent: #f5c542;
            --gradient-primary: linear-gradient(135deg, #00f2fe 0%, #7928ca 50%, #ff0080 100%);
            --gradient-card: linear-gradient(145deg, rgba(22, 19, 52, 0.85), rgba(10, 9, 28, 0.95));
        }

        body {
            background-color: var(--bg-dark);
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .font-orbitron {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.5px;
        }

        /* Teks Gradasi Neon */
        .text-gradient {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-neon-blue { color: var(--neon-blue); }
        .text-neon-pink { color: var(--neon-pink); }

        /* Tombol Gradasi Futuristik */
        .btn-nexora {
            background: var(--gradient-primary);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 0.75rem 1.75rem;
            border-radius: 50px;
            box-shadow: 0 0 20px rgba(121, 40, 202, 0.5);
            transition: all 0.3s ease;
        }

        .btn-nexora:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 30px rgba(0, 242, 254, 0.7), 0 0 15px rgba(255, 0, 128, 0.6);
            color: #ffffff;
        }

        .btn-outline-nexora {
            background: rgba(16, 14, 38, 0.6);
            border: 2px solid var(--neon-blue);
            color: #ffffff;
            font-weight: 600;
            padding: 0.7rem 1.6rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-outline-nexora:hover {
            background: var(--neon-blue);
            color: #080715;
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.6);
        }

        /* Navbar Glassmorphism */
        .navbar-nexora {
            background: rgba(8, 7, 21, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0, 242, 254, 0.18);
        }

        .nav-link {
            color: #cbd5e1 !important;
            font-weight: 600;
            transition: color 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--neon-blue) !important;
        }

        /* Hero Section dengan Arena Esports */
        .hero-section {
            position: relative;
            min-height: 95vh;
            display: flex;
            align-items: center;
            padding-top: 90px;
            background: 
                radial-gradient(circle at 15% 25%, rgba(0, 242, 254, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 85% 30%, rgba(255, 0, 128, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 50% 80%, rgba(121, 40, 202, 0.35) 0%, transparent 60%),
                linear-gradient(180deg, rgba(8, 7, 21, 0.68) 0%, rgba(8, 7, 21, 0.88) 75%, #080715 100%),
                url('https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
        }

        .hero-badge {
            background: rgba(121, 40, 202, 0.25);
            border: 1px solid rgba(0, 242, 254, 0.45);
            color: var(--neon-blue);
            padding: 0.45rem 1.1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.2);
        }

        /* Kartu Futuristik */
        .card-nexora {
            background: var(--gradient-card);
            border: 1px solid rgba(121, 40, 202, 0.35);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
        }

        .card-nexora:hover {
            transform: translateY(-8px);
            border-color: var(--neon-blue);
            box-shadow: 0 15px 35px rgba(0, 242, 254, 0.18), 0 5px 20px rgba(255, 0, 128, 0.15);
        }

        /* Varian Card Portal Tanpa Hover Loncat agar Nyaman Diisi */
        .card-portal {
            background: var(--gradient-card);
            border: 1px solid rgba(0, 242, 254, 0.35);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.65), 0 0 30px rgba(121, 40, 202, 0.25);
        }

        .prodi-img-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .prodi-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card-nexora:hover .prodi-img-wrapper img {
            transform: scale(1.08);
        }

        .prodi-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, transparent 30%, rgba(8, 7, 21, 0.95) 100%);
        }

        .degree-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(8, 7, 21, 0.85);
            border: 1px solid var(--neon-pink);
            color: #fff;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 0.35rem 0.85rem;
            border-radius: 50px;
            backdrop-filter: blur(6px);
        }

        /* Ikon Keunggulan */
        .feature-icon-box {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(0, 242, 254, 0.15), rgba(255, 0, 128, 0.15));
            border: 1px solid rgba(0, 242, 254, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--neon-blue);
            margin-bottom: 1.25rem;
        }

        /* Role Selector Pills untuk Portal Login/Register */
        .role-card {
            cursor: pointer;
            background: rgba(16, 14, 38, 0.8);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 0.85rem 0.5rem;
            text-align: center;
            transition: all 0.25s ease;
        }

        .role-card:hover {
            border-color: rgba(0, 242, 254, 0.5);
        }

        .role-card.active {
            background: linear-gradient(145deg, rgba(0, 242, 254, 0.18), rgba(121, 40, 202, 0.28));
            border-color: var(--neon-blue);
            box-shadow: 0 0 18px rgba(0, 242, 254, 0.3);
        }

        .role-card.active i {
            color: var(--neon-blue);
        }

        /* Custom Form Control */
        .form-control-nexora, .form-select-nexora {
            background-color: rgba(8, 7, 21, 0.9);
            border: 1px solid rgba(121, 40, 202, 0.45);
            color: #fff;
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }

        .form-control-nexora:focus, .form-select-nexora:focus {
            background-color: rgba(12, 10, 32, 0.95);
            border-color: var(--neon-blue);
            box-shadow: 0 0 0 0.25rem rgba(0, 242, 254, 0.2);
            color: #fff;
        }

        .nav-pills-nexora .nav-link {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            color: #cbd5e1 !important;
            border: 1px solid transparent;
        }

        .nav-pills-nexora .nav-link.active {
            background: var(--gradient-primary);
            color: #fff !important;
            box-shadow: 0 4px 15px rgba(121, 40, 202, 0.5);
        }

        /* Glow Divider */
        .section-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-blue), var(--neon-purple), var(--neon-pink), transparent);
            opacity: 0.45;
        }

        /* Campus Life Card Overlay */
        .campus-card {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            height: 310px;
            border: 1px solid rgba(255, 0, 128, 0.3);
        }

        .campus-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .campus-card:hover img {
            transform: scale(1.07);
        }

        .campus-card-content {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(8,7,21,0.1) 0%, rgba(8,7,21,0.92) 85%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 1.5rem;
        }

        /* Dedicated Portal Landing Page Styling */
        #portal-view {
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 20%, rgba(0, 242, 254, 0.2) 0%, transparent 45%),
                radial-gradient(circle at 88% 80%, rgba(255, 0, 128, 0.22) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(121, 40, 202, 0.25) 0%, transparent 65%),
                linear-gradient(180deg, rgba(8, 7, 21, 0.88) 0%, rgba(8, 7, 21, 0.96) 100%),
                url('https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat fixed;
            animation: fadeInPortal 0.35s ease;
        }

        @keyframes fadeInPortal {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- =====================================================================
         TAMPILAN 1: LANDING PAGE UTAMA UNIVERSITAS
    ====================================================================== -->
    <div id="main-landing-view" class="<?= $has_auth_msg ? 'd-none' : ''; ?>">
        <!-- NAVBAR RESPONSIF DENGAN EMBLEM LOGO RESMI NEXORA UNIVERSITY -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-nexora fixed-top py-2">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-3" href="#beranda">
                    <!-- Logo Resmi Universitas NEXORA -->
                    <svg width="56" height="62" viewBox="0 0 120 132" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Logo Resmi NEXORA Esports University">
                        <defs>
                            <linearGradient id="shieldBorderGrad" x1="0" y1="0" x2="120" y2="132" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#00F2FE"/>
                                <stop offset="50%" stop-color="#F5C542"/>
                                <stop offset="100%" stop-color="#FF0080"/>
                            </linearGradient>
                            <linearGradient id="shieldBodyGrad" x1="20" y1="15" x2="100" y2="115" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#141038"/>
                                <stop offset="55%" stop-color="#0B0922"/>
                                <stop offset="100%" stop-color="#1E0E3A"/>
                            </linearGradient>
                            <linearGradient id="goldLaurelGrad" x1="10" y1="20" x2="110" y2="110" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#FFE082"/>
                                <stop offset="50%" stop-color="#F5C542"/>
                                <stop offset="100%" stop-color="#D4941A"/>
                            </linearGradient>
                            <linearGradient id="neonCoreGrad" x1="35" y1="40" x2="85" y2="85" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#00F2FE"/>
                                <stop offset="50%" stop-color="#7928CA"/>
                                <stop offset="100%" stop-color="#FF0080"/>
                            </linearGradient>
                        </defs>

                        <path d="M22 96C11 82 8 62 15 42C17 48 21 52 24 55C18 68 20 82 28 93L22 96Z" fill="url(#goldLaurelGrad)" opacity="0.9"/>
                        <path d="M16 76C9 65 9 50 16 35C18 40 21 44 24 46C19 57 19 67 23 75L16 76Z" fill="url(#goldLaurelGrad)" opacity="0.75"/>
                        <path d="M98 96C109 82 112 62 105 42C103 48 99 52 96 55C102 68 100 82 92 93L98 96Z" fill="url(#goldLaurelGrad)" opacity="0.9"/>
                        <path d="M104 76C111 65 111 50 104 35C102 40 99 44 96 46C101 57 101 67 97 75L104 76Z" fill="url(#goldLaurelGrad)" opacity="0.75"/>

                        <path d="M44 16L50 8L60 14L70 8L76 16H44Z" fill="url(#goldLaurelGrad)"/>
                        <circle cx="60" cy="5" r="3" fill="#00F2FE"/>

                        <path d="M60 18L96 28V64C96 89 79 106 60 116C41 106 24 89 24 64V28L60 18Z" fill="url(#shieldBodyGrad)" stroke="url(#shieldBorderGrad)" stroke-width="3.5"/>
                        <path d="M60 23L91 32V63C91 85 76 100 60 109C44 100 29 85 29 63V32L60 23Z" stroke="#F5C542" stroke-opacity="0.45" stroke-width="1"/>

                        <path d="M36 42H46L50 46M84 42H74L70 46M36 74H45L49 70M84 74H75L71 70" stroke="#00F2FE" stroke-opacity="0.45" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M44 37C50 35 56 37 60 40C64 37 70 35 76 37V46C70 44 64 46 60 49C56 46 50 44 44 46V37Z" fill="#F5C542" fill-opacity="0.9"/>

                        <path d="M43 60C43 55.5 46.5 52 51 52H69C73.5 52 77 55.5 77 60L79 71C79.6 74.5 76.8 77.5 73.3 77.5C71.2 77.5 69.3 76.4 68.3 74.6L66 70.5H54L51.7 74.6C50.7 76.4 48.8 77.5 46.7 77.5C43.2 77.5 40.4 74.5 41 71L43 60Z" fill="url(#neonCoreGrad)" stroke="#FFFFFF" stroke-width="1.2"/>
                        <path d="M49 58V64M46 61H52" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round"/>
                        <circle cx="70" cy="59" r="1.5" fill="#00F2FE"/>
                        <circle cx="73" cy="62" r="1.5" fill="#F5C542"/>
                        <circle cx="67" cy="62" r="1.5" fill="#FF0080"/>

                        <path d="M55 93V81L65 93V81" stroke="#00F2FE" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                        <path d="M12 103C34 112 86 112 108 103L104 118C84 126 36 126 16 118L12 103Z" fill="#0B0922" stroke="url(#goldLaurelGrad)" stroke-width="2"/>
                        <text x="60" y="114.5" text-anchor="middle" fill="#F5C542" font-family="Orbitron, sans-serif" font-size="6.3" font-weight="800" letter-spacing="0.6">NEXORA UNIVERSITY</text>
                    </svg>

                    <div class="d-flex flex-column">
                        <span class="font-orbitron fw-bold fs-5 text-white lh-1">NEXORA</span>
                        <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 2px; color: var(--neon-blue);">Esports University</span>
                    </div>
                </a>

                <button class="navbar-toggler border-info" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto gap-lg-3 my-3 my-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="#beranda">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#program-studi">Program Studi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#keunggulan">Keunggulan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#kehidupan-kampus">Kehidupan Kampus</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" onclick="openAuthPortal('login')">Portal Akademik</a>
                        </li>
                    </ul>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-nexora btn-sm px-3" onclick="openAuthPortal('login')">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                        <button class="btn btn-nexora btn-sm px-3" onclick="openAuthPortal('register')">
                            <i class="bi bi-person-plus-fill me-1"></i> Daftar PMB
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- HERO SECTION -->
        <section id="beranda" class="hero-section">
            <div class="container py-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <div class="hero-badge mb-4">
                            <i class="bi bi-stars"></i>
                            <span>PENERIMAAN MAHASISWA BARU 2026/2027 DIBUKA</span>
                        </div>
                        <h1 class="display-4 fw-black text-white mb-4 lh-sm">
                            Cetak Masa Depan Industri <span class="text-gradient">Esports & Teknologi Digital</span> Global
                        </h1>
                        <p class="lead text-light opacity-75 mb-4 pe-lg-4">
                            NEXORA Esports University adalah perguruan tinggi pertama berstandar internasional yang memadukan kurikulum akademik terakreditasi, manajemen bisnis esports, rekayasa pengembangan gim, dan produksi siaran tingkat turnamen dunia.
                        </p>
                        <div class="d-flex flex-wrap gap-3 mb-5">
                            <button onclick="openAuthPortal('register')" class="btn btn-nexora btn-lg">
                                Daftar Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                            </button>
                            <a href="#program-studi" class="btn btn-outline-nexora btn-lg">
                                Jelajahi Program Studi
                            </a>
                        </div>

                        <!-- Statistik Singkat Kampus -->
                        <div class="row g-3 pt-3 border-top border-secondary border-opacity-25">
                            <div class="col-4">
                                <h3 class="fw-bold text-neon-blue mb-0">3.500+</h3>
                                <small class="text-secondary">Mahasiswa Aktif</small>
                            </div>
                            <div class="col-4">
                                <h3 class="fw-bold text-white mb-0">45+</h3>
                                <small class="text-secondary">Mitra Tim & Studio Global</small>
                            </div>
                            <div class="col-4">
                                <h3 class="fw-bold text-neon-pink mb-0">96%</h3>
                                <small class="text-secondary">Lulusan Terserap Industri</small>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Kartu Highlight Arena Utama Kampus -->
                    <div class="col-lg-5">
                        <div class="card-nexora p-3 position-relative">
                            <div class="position-relative rounded-4 overflow-hidden">
                                <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?auto=format&fit=crop&w=900&q=80" 
                                     onerror="this.src='https://placehold.co/800x550/100e26/00f2fe?text=NEXORA+Cyber+Arena'" 
                                     alt="NEXORA Grand Esports Arena" class="img-fluid w-100" style="height: 360px; object-fit: cover;">
                                <div class="prodi-overlay"></div>
                                <div class="position-absolute bottom-0 start-0 p-4 w-100">
                                    <span class="badge bg-danger mb-2 px-3 py-2 rounded-pill">
                                        <i class="bi bi-broadcast me-1"></i> NEXORA GRAND ARENA • 1.200 KAPASITAS
                                    </span>
                                    <h5 class="text-white fw-bold mb-1">Fasilitas Turnamen Tier-1 di Dalam Kampus</h5>
                                    <p class="small text-light opacity-75 mb-0">Dilengkapi panggung hidrolik, layar LED 360°, dan ruang kontrol siaran 4K HDR.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <!-- SECTION PROGRAM STUDI UNGGULAN -->
        <section id="program-studi" class="py-5 my-4">
            <div class="container">
                <div class="text-center max-w-700 mx-auto mb-5">
                    <span class="text-uppercase fw-bold text-neon-blue small tracking-wider">Fakultas & Kurikulum Terakreditasi</span>
                    <h2 class="display-6 fw-bold text-white mt-2">Program Studi <span class="text-gradient">Sarjana (S1)</span></h2>
                    <p class="text-secondary mx-auto" style="max-width: 680px;">
                        Dirancang bersama praktisi liga esports internasional dan pemimpin industri kreatif digital untuk mencetak profesional siap kerja.
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Prodi 1 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card-nexora d-flex flex-column">
                            <div class="prodi-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=800&q=80" 
                                     onerror="this.src='https://placehold.co/800x500/141038/00f2fe?text=Esports+Management'" 
                                     alt="Esports Management & Analytics">
                                <div class="prodi-overlay"></div>
                                <span class="degree-badge">Gelar: S.M. (Sarjana Manajemen)</span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 text-neon-blue small fw-bold mb-2">
                                    <i class="bi bi-graph-up-arrow"></i>
                                    <span>FAKULTAS BISNIS & STRATEGI ESPORTS</span>
                                </div>
                                <h4 class="text-white fw-bold mb-3">Esports Management & Analytics</h4>
                                <p class="text-secondary small mb-4">
                                    Mempelajari tata kelola organisasi tim profesional, manajemen turnamen global, sponsor & monetisasi, psikologi atlet, serta analisis performa berbasis big data dan AI.
                                </p>
                                <ul class="list-unstyled small text-light opacity-75 mb-4">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-neon-blue me-2"></i>Data Science & In-Game Telemetry</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-neon-blue me-2"></i>Event & Tournament Operations</li>
                                    <li><i class="bi bi-check-circle-fill text-neon-blue me-2"></i>Sports Law, Kontrak & Brand Partnership</li>
                                </ul>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-nexora btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#prodiModal" onclick="showProdiDetail('Esports Management & Analytics', 'S.M. (Sarjana Manajemen)', 'Program studi ini mempersiapkan manajer tim, analis taktik berbasis data, direktur turnamen, dan eksekutif agensi esports. Mahasiswa akan melakukan magang langsung di liga profesional dan mengelola tim kampus resmi NEXORA.', ['Manajemen Ekosistem & Bisnis Esports', 'Analitik Performa Pemain & Big Data', 'Hukum Kontrak, Hak Siar & Sponsorship', 'Psikologi Kompetitif & Pembinaan Atlet'])">
                                        Detail Kurikulum
                                    </button>
                                    <button class="btn btn-nexora btn-sm" onclick="selectProdiAndRegister('Esports Management & Analytics')">
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prodi 2 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card-nexora d-flex flex-column">
                            <div class="prodi-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=800&q=80" 
                                     onerror="this.src='https://placehold.co/800x500/141038/7928ca?text=Game+Development'" 
                                     alt="Game Development & Technology">
                                <div class="prodi-overlay"></div>
                                <span class="degree-badge">Gelar: S.Kom. (Sarjana Komputer)</span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 text-neon-pink small fw-bold mb-2">
                                    <i class="bi bi-cpu-fill"></i>
                                    <span>FAKULTAS TEKNOLOGI INTERAKTIF</span>
                                </div>
                                <h4 class="text-white fw-bold mb-3">Game Development & Technology</h4>
                                <p class="text-secondary small mb-4">
                                    Fokus pada rekayasa perangkat lunak gim kompetitif, pemrograman Unreal Engine 5 & Unity, arsitektur server multiplayer latensi rendah, kecerdasan buatan (AI), dan XR/VR.
                                </p>
                                <ul class="list-unstyled small text-light opacity-75 mb-4">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-neon-pink me-2"></i>Unreal Engine 5, C++ & Vulkan Graphics</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-neon-pink me-2"></i>Multiplayer Netcode & Anti-Cheat Security</li>
                                    <li><i class="bi bi-check-circle-fill text-neon-pink me-2"></i>Game Design, Level Balancing & UI/UX</li>
                                </ul>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-nexora btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#prodiModal" onclick="showProdiDetail('Game Development & Technology', 'S.Kom. (Sarjana Komputer)', 'Menguasai seluruh siklus pengembangan gim komersial dan kompetitif dari prototipe hingga rilis lintas platform. Didukung laboratorium RTX 4090 dan perangkat motion capture.', ['Pemrograman Engine & Grafika 3D Real-Time', 'Arsitektur Server Multiplayer & Keamanan Siber', 'Kecerdasan Buatan (AI) untuk Gim', 'Desain Mekanik Kompetitif & Monetisasi Gim'])">
                                        Detail Kurikulum
                                    </button>
                                    <button class="btn btn-nexora btn-sm" onclick="selectProdiAndRegister('Game Development & Technology')">
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Prodi 3 -->
                    <div class="col-lg-4 col-md-6">
                        <div class="card-nexora d-flex flex-column">
                            <div class="prodi-img-wrapper">
                                <img src="https://images.unsplash.com/photo-1598550476439-6847785fcea6?auto=format&fit=crop&w=800&q=80" 
                                     onerror="this.src='https://placehold.co/800x500/141038/ff0080?text=Digital+Broadcasting'" 
                                     alt="Digital Content & Broadcasting">
                                <div class="prodi-overlay"></div>
                                <span class="degree-badge">Gelar: S.I.Kom. (Sarjana Ilmu Komunikasi)</span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 text-warning small fw-bold mb-2">
                                    <i class="bi bi-camera-reels-fill"></i>
                                    <span>FAKULTAS MEDIA & PENYIARAN DIGITAL</span>
                                </div>
                                <h4 class="text-white fw-bold mb-3">Digital Content & Broadcasting</h4>
                                <p class="text-secondary small mb-4">
                                    Mencetak produser siaran langsung turnamen, shoutcaster profesional, kreator konten digital, serta spesialis efek visual AR/HUD real-time untuk industri hiburan interaktif.
                                </p>
                                <ul class="list-unstyled small text-light opacity-75 mb-4">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Live Tournament Production & OB Van</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-warning me-2"></i>Shoutcasting, Analyst Desk & Public Speaking</li>
                                    <li><i class="bi bi-check-circle-fill text-warning me-2"></i>Motion Graphics, Virtual Studio & Storytelling</li>
                                </ul>
                                <div class="mt-auto d-flex gap-2">
                                    <button class="btn btn-outline-nexora btn-sm flex-grow-1" data-bs-toggle="modal" data-bs-target="#prodiModal" onclick="showProdiDetail('Digital Content & Broadcasting', 'S.I.Kom. (Sarjana Ilmu Komunikasi)', 'Mempelajari produksi siaran langsung berskala stadion, teknik komentar pertandingan (shoutcasting), penyutradaraan kamera virtual in-game (observer), dan strategi pertumbuhan kreator.', ['Produksi Siaran Multi-Kamera & Live Streaming', 'Teknik Shoutcasting & Jurnalisme Esports', 'Grafis Siaran Real-Time & Virtual Production', 'Manajemen Komunitas & Ekonomi Kreator'])">
                                        Detail Kurikulum
                                    </button>
                                    <button class="btn btn-nexora btn-sm" onclick="selectProdiAndRegister('Digital Content & Broadcasting')">
                                        Daftar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <!-- SECTION KEUNGGULAN UNIVERSITAS -->
        <section id="keunggulan" class="py-5 my-4">
            <div class="container">
                <div class="row align-items-end mb-5">
                    <div class="col-lg-7">
                        <span class="text-uppercase fw-bold text-neon-pink small">Mengapa Memilih NEXORA?</span>
                        <h2 class="display-6 fw-bold text-white mt-2">Ekosistem Akademik & <span class="text-gradient">Infrastruktur Kelas Dunia</span></h2>
                    </div>
                    <div class="col-lg-5 text-lg-end">
                        <p class="text-secondary mb-0">Setiap sudut kampus dirancang untuk mendukung riset, latihan kompetitif, dan inovasi teknologi tanpa batas.</p>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="card-nexora p-4">
                            <div class="feature-icon-box">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-2">Arena Turnamen Internasional</h5>
                            <p class="text-secondary small mb-0">
                                Arena utama berkapasitas 1.200 penonton dengan jaringan fiber optik khusus latensi &lt;2ms dan panggung turnamen standar dunia.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-nexora p-4">
                            <div class="feature-icon-box">
                                <i class="bi bi-gpu-card"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-2">Lab Game Dev RTX & Mocap</h5>
                            <p class="text-secondary small mb-0">
                                Workstation bertenaga GPU seri RTX terbaru, ruang Motion Capture optik, serta perangkat VR/AR generasi mutakhir untuk riset mahasiswa.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-nexora p-4">
                            <div class="feature-icon-box">
                                <i class="bi bi-broadcast-pin"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-2">Studio Broadcast & Soundstage</h5>
                            <p class="text-secondary small mb-0">
                                Studio siaran dengan teknologi Unreal Virtual Set, ruang kedap suara podcast/shoutcasting, dan ruang kendali produksi 4K.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="card-nexora p-4">
                            <div class="feature-icon-box">
                                <i class="bi bi-award-fill"></i>
                            </div>
                            <h5 class="text-white fw-bold mb-2">Beasiswa Atlet & Talenta</h5>
                            <p class="text-secondary small mb-0">
                                Program beasiswa penuh hingga 100% bagi atlet esports berprestasi, pengembang gim independen, dan kreator digital berbakat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <!-- SECTION KEHIDUPAN DAN KEGIATAN KAMPUS -->
        <section id="kehidupan-kampus" class="py-5 my-4">
            <div class="container">
                <div class="text-center mb-5">
                    <span class="text-uppercase fw-bold text-neon-blue small">Komunitas & Atmosfer Kompetitif</span>
                    <h2 class="display-6 fw-bold text-white mt-2">Kehidupan & <span class="text-gradient">Kegiatan Kampus</span></h2>
                    <p class="text-secondary mx-auto" style="max-width: 650px;">
                        Lebih dari sekadar ruang kuliah—rasakan budaya kolaborasi, turnamen rutin, dan inkubasi karier sejak semester pertama.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="campus-card">
                            <img src="https://images.unsplash.com/photo-1538481199705-c710c4e965fc?auto=format&fit=crop&w=800&q=80" 
                                 onerror="this.src='https://placehold.co/800x600/141038/00f2fe?text=NEXORA+Cup'" 
                                 alt="Turnamen Tahunan NEXORA Cup">
                            <div class="campus-card-content">
                                <span class="badge bg-primary w-auto align-self-start mb-2">Turnamen Tahunan</span>
                                <h5 class="text-white fw-bold mb-1">NEXORA Championship Cup</h5>
                                <p class="small text-light opacity-75 mb-0">
                                    Liga antar-fakultas dan kejuaraan nasional yang dikelola sepenuhnya oleh mahasiswa sebagai laboratorium praktik langsung.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="campus-card">
                            <img src="https://images.unsplash.com/photo-1542751110-97427bbecf20?auto=format&fit=crop&w=800&q=80" 
                                 onerror="this.src='https://placehold.co/800x600/141038/7928ca?text=Varsity+Bootcamp'" 
                                 alt="Bootcamp Tim Varsity Kampus">
                            <div class="campus-card-content">
                                <span class="badge bg-info text-dark w-auto align-self-start mb-2">Varsity Esports</span>
                                <h5 class="text-white fw-bold mb-1">NEXORA Guardians Bootcamp</h5>
                                <p class="small text-light opacity-75 mb-0">
                                    Pelatihan intensif tim varsity kampus didampingi pelatih profesional, analis data, ahli gizi, dan pelatih kebugaran fisik.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-12">
                        <div class="campus-card">
                            <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80" 
                                 onerror="this.src='https://placehold.co/800x600/141038/ff0080?text=Creator+Guild'" 
                                 alt="Komunitas Kreator & Game Jam">
                            <div class="campus-card-content">
                                <span class="badge bg-danger w-auto align-self-start mb-2">Inkubator & Komunitas</span>
                                <h5 class="text-white fw-bold mb-1">Game Jam & Creator Guild</h5>
                                <p class="small text-light opacity-75 mb-0">
                                    Hackathon pembuatan gim 48 jam serta komunitas kreator konten & cosplayer yang aktif berkolaborasi dengan publisher global.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="section-divider"></div>

        <!-- CALL-TO-ACTION BANNER MENUJU PORTAL AKADEMIK -->
        <section class="py-5 my-4">
            <div class="container">
                <div class="card-nexora p-4 p-md-5 text-center position-relative">
                    <span class="badge bg-info bg-opacity-10 text-neon-blue border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
                        <i class="bi bi-shield-lock-fill me-1"></i> SISTEM INFORMASI AKADEMIK TERPADU (SIAKAD)
                    </span>
                    <h2 class="display-6 fw-bold text-white mb-3">Siap Mengakses <span class="text-gradient">Portal Akademik NEXORA?</span></h2>
                    <p class="text-secondary mx-auto mb-4" style="max-width: 650px;">
                        Masuk ke dashboard Mahasiswa, Dosen, atau Administrator Kampus, maupun lakukan pendaftaran akun baru Penerimaan Mahasiswa Baru (PMB) di halaman portal khusus.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <button class="btn btn-nexora btn-lg px-4" onclick="openAuthPortal('login')">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Masuk ke Portal Akademik
                        </button>
                        <button class="btn btn-outline-nexora btn-lg px-4" onclick="openAuthPortal('register')">
                            <i class="bi bi-person-plus-fill me-2"></i> Registrasi Akun Baru
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER PROFESIONAL UNIVERSITAS -->
        <footer class="pt-5 pb-4 mt-5" style="background: #05040e; border-top: 1px solid rgba(0, 242, 254, 0.2);">
            <div class="container">
                <div class="row g-4 pb-4">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-shield-shaded fs-2 text-neon-blue"></i>
                            <div>
                                <h5 class="font-orbitron fw-bold text-white mb-0">NEXORA</h5>
                                <small class="text-neon-blue text-uppercase" style="letter-spacing: 1.5px;">Esports University</small>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">
                            Institusi pendidikan tinggi pionir di Asia Tenggara yang mengintegrasikan keunggulan akademik, inovasi teknologi gim, dan ekosistem esports profesional.
                        </p>
                        <div class="d-flex gap-2">
                            <a href="#beranda" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-youtube"></i></a>
                            <a href="#beranda" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-instagram"></i></a>
                            <a href="#beranda" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-twitch"></i></a>
                            <a href="#beranda" class="btn btn-outline-secondary btn-sm rounded-circle"><i class="bi bi-discord"></i></a>
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-4">
                        <h6 class="text-white fw-bold mb-3">Navigasi</h6>
                        <ul class="list-unstyled small d-flex flex-column gap-2">
                            <li><a href="#beranda" class="text-secondary text-decoration-none">Beranda</a></li>
                            <li><a href="#program-studi" class="text-secondary text-decoration-none">Program Studi</a></li>
                            <li><a href="#keunggulan" class="text-secondary text-decoration-none">Keunggulan Kampus</a></li>
                            <li><a href="#kehidupan-kampus" class="text-secondary text-decoration-none">Kehidupan Kampus</a></li>
                            <li><a onclick="openAuthPortal('login')" class="text-secondary text-decoration-none" style="cursor:pointer;">Portal Akademik</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <h6 class="text-white fw-bold mb-3">Program Sarjana</h6>
                        <ul class="list-unstyled small d-flex flex-column gap-2">
                            <li><a href="#program-studi" class="text-secondary text-decoration-none">Esports Management & Analytics</a></li>
                            <li><a href="#program-studi" class="text-secondary text-decoration-none">Game Development & Technology</a></li>
                            <li><a href="#program-studi" class="text-secondary text-decoration-none">Digital Content & Broadcasting</a></li>
                            <li><a onclick="openAuthPortal('register')" class="text-secondary text-decoration-none" style="cursor:pointer;">Beasiswa Atlet & Talenta</a></li>
                        </ul>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <h6 class="text-white fw-bold mb-3">Kontak Kampus</h6>
                        <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
                            <li><i class="bi bi-geo-alt-fill text-neon-blue me-2"></i>NEXORA Cyber Campus, Jl. Sudirman Kav. 88, Jakarta Selatan</li>
                            <li><i class="bi bi-telephone-fill text-neon-blue me-2"></i>(021) 5099-8800 / WhatsApp PMB</li>
                            <li><i class="bi bi-envelope-fill text-neon-blue me-2"></i>admisi@nexora.ac.id</li>
                            <li><i class="bi bi-patch-check-fill text-warning me-2"></i>Terakreditasi Unggul BAN-PT & Mitra IESF</li>
                        </ul>
                    </div>
                </div>

                <div class="border-top border-secondary border-opacity-25 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                    <p class="mb-2 mb-md-0">&copy; 2026 NEXORA Esports University. Hak Cipta Dilindungi Undang-Undang.</p>
                    <span>Motto: <em>"Scientia, Virtus, et Victoria Digitalis"</em></span>
                </div>
            </div>
        </footer>
    </div>

    <!-- =====================================================================
         TAMPILAN 2: HALAMAN KHUSUS PORTAL AKADEMIK (LOGIN & REGISTRASI)
    ====================================================================== -->
    <div id="portal-view" class="<?= $has_auth_msg ? '' : 'd-none'; ?>">
        <!-- Top Bar Khusus Halaman Portal -->
        <nav class="navbar navbar-dark navbar-nexora py-3">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center gap-3" href="javascript:void(0)" onclick="closeAuthPortal()">
                    <svg width="44" height="48" viewBox="0 0 120 132" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M60 18L96 28V64C96 89 79 106 60 116C41 106 24 89 24 64V28L60 18Z" fill="#141038" stroke="#00F2FE" stroke-width="4"/>
                        <path d="M43 60C43 55.5 46.5 52 51 52H69C73.5 52 77 55.5 77 60L79 71C79.6 74.5 76.8 77.5 73.3 77.5C71.2 77.5 69.3 76.4 68.3 74.6L66 70.5H54L51.7 74.6C50.7 76.4 48.8 77.5 46.7 77.5C43.2 77.5 40.4 74.5 41 71L43 60Z" fill="#7928CA" stroke="#FFFFFF" stroke-width="1.5"/>
                    </svg>
                    <div class="d-flex flex-column">
                        <span class="font-orbitron fw-bold fs-5 text-white lh-1">NEXORA</span>
                        <span class="text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 2px; color: var(--neon-blue);">Academic Portal</span>
                    </div>
                </a>

                <button class="btn btn-outline-nexora btn-sm px-3" onclick="closeAuthPortal()">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda Kampus
                </button>
            </div>
        </nav>

        <!-- Konten Utama Halaman Portal -->
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <!-- Kolom Kiri: Panduan Akses Peran -->
                <div class="col-lg-5">
                    <span class="badge bg-info bg-opacity-10 text-neon-blue border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
                        <i class="bi bi-cpu-fill me-1"></i> SINGLE SIGN-ON (SSO) NEXORA
                    </span>
                    <h1 class="display-6 fw-bold text-white mb-3">
                        Portal Akademik & <span class="text-gradient">Pendaftaran Mahasiswa</span>
                    </h1>
                    <p class="text-secondary mb-4">
                        Silakan pilih peran akun Anda (Mahasiswa, Dosen, atau Admin) untuk masuk ke sistem perkuliahan atau mendaftarkan akun baru.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <div class="p-3 rounded-4" style="background: rgba(16, 14, 38, 0.75); border: 1px solid rgba(0, 242, 254, 0.2);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 bg-info bg-opacity-10 text-neon-blue">
                                    <i class="bi bi-mortarboard-fill fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="text-white fw-bold mb-0">Mahasiswa</h6>
                                    <small class="text-secondary">Login dengan NIM (10 Karakter) untuk KRS, presensi, dan nilai.</small>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-4" style="background: rgba(16, 14, 38, 0.75); border: 1px solid rgba(121, 40, 202, 0.25);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-person-video3 fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="text-white fw-bold mb-0">Dosen & Pelatih</h6>
                                    <small class="text-secondary">Login dengan NID (5 Karakter) untuk jadwal mengajar dan input nilai.</small>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-4" style="background: rgba(16, 14, 38, 0.75); border: 1px solid rgba(255, 0, 128, 0.25);">
                            <div class="d-flex align-items-center gap-3">
                                <div class="p-2 rounded-3 bg-danger bg-opacity-10 text-neon-pink">
                                    <i class="bi bi-shield-lock-fill fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="text-white fw-bold mb-0">Administrator</h6>
                                    <small class="text-secondary">Kelola mata kuliah, jadwal perkuliahan, dan seluruh pengguna.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Kartu Form Login & Registrasi -->
                <div class="col-lg-7">
                    <div class="card-portal p-4 p-md-5">
                        <!-- Pilihan Peran Pengguna (Role Selector) -->
                        <label class="form-label small text-uppercase fw-bold text-neon-blue mb-2">1. Pilih Peran Pengguna (Role)</label>
                        <div class="row g-2 mb-4" id="roleSelector">
                            <div class="col-4">
                                <div class="role-card active" data-role="Mahasiswa" onclick="selectRole('Mahasiswa', this)">
                                    <i class="bi bi-mortarboard-fill fs-4 d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Mahasiswa</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="role-card" data-role="Dosen" onclick="selectRole('Dosen', this)">
                                    <i class="bi bi-person-workspace fs-4 d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Dosen</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="role-card" data-role="Admin" onclick="selectRole('Admin', this)">
                                    <i class="bi bi-shield-check fs-4 d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Admin</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Login vs Registrasi -->
                        <ul class="nav nav-pills nav-pills-nexora justify-content-center mb-4 p-1 rounded-pill" style="background: rgba(8,7,21,0.7); border: 1px solid rgba(255,255,255,0.08);" id="authTabs" role="tablist">
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link active w-100 fw-bold" id="login-tab" data-bs-toggle="pill" data-bs-target="#loginPane" type="button" role="tab">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login Portal
                                </button>
                            </li>
                            <li class="nav-item flex-fill text-center" role="presentation">
                                <button class="nav-link w-100 fw-bold" id="register-tab" data-bs-toggle="pill" data-bs-target="#registerPane" type="button" role="tab">
                                    <i class="bi bi-person-plus-fill me-1"></i> Registrasi Akun
                                </button>
                            </li>
                        </ul>

                        <!-- Notifikasi Umpan Balik PHP Session atau JavaScript -->
                        <?php if ($has_auth_msg): ?>
                            <div class="alert alert-<?= $_SESSION['auth_type'] ?? 'info'; ?> rounded-4 mb-4" role="alert">
                                <?= $_SESSION['auth_msg']; ?>
                            </div>
                            <?php unset($_SESSION['auth_msg'], $_SESSION['auth_type']); ?>
                        <?php endif; ?>
                        <div id="authFeedback" class="alert d-none rounded-4 mb-4" role="alert"></div>

                        <div class="tab-content" id="authTabContent">
                            <!-- TAB LOGIN -->
                            <div class="tab-pane fade show active" id="loginPane" role="tabpanel">
                                <form id="loginForm" action="proses_login.php" method="POST">
                                    <input type="hidden" name="role" class="selectedRoleInput" value="mahasiswa">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold" id="loginIdLabel">NIM Mahasiswa (10 Karakter)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-neon-blue"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" name="identifier" maxlength="10" class="form-control form-control-nexora" id="loginIdentifier" placeholder="contoh: 2026010001" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Kata Sandi</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-neon-blue"><i class="bi bi-key-fill"></i></span>
                                            <input type="password" name="password" class="form-control form-control-nexora" id="loginPassword" placeholder="Masukkan kata sandi Anda" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                            <label class="form-check-label small text-secondary" for="rememberMe">Ingat sesi saya</label>
                                        </div>
                                        <a href="javascript:void(0)" onclick="showForgotMsg()" class="small text-neon-blue text-decoration-none">Lupa kata sandi?</a>
                                    </div>
                                    <button type="submit" class="btn btn-nexora w-100 py-3">
                                        Masuk sebagai <span class="selectedRoleText">Mahasiswa</span> <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- TAB REGISTRASI -->
                            <div class="tab-pane fade" id="registerPane" role="tabpanel">
                                <form id="registerForm" action="proses_register.php" method="POST" onsubmit="return validateRegisterForm(event)">
                                    <input type="hidden" name="role" class="selectedRoleInput" value="mahasiswa">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label small fw-semibold" id="regUsernameLabel">NIM (10 Digit)</label>
                                            <input type="text" name="username" maxlength="10" class="form-control form-control-nexora" id="regUsername" placeholder="2026010001" required>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" maxlength="100" class="form-control form-control-nexora" id="regFullName" placeholder="Nama lengkap sesuai identitas" required>
                                        </div>

                                        <!-- Field Dinamis Berdasarkan Peran -->
                                        <div class="col-12" id="regDynamicFieldContainer">
                                            <label class="form-label small fw-semibold">Jurusan / Program Studi</label>
                                            <select name="info_tambahan" class="form-select form-select-nexora" id="regProdiSelect">
                                                <option value="Esports Management & Analytics">Esports Management & Analytics</option>
                                                <option value="Game Development & Technology">Game Development & Technology</option>
                                                <option value="Digital Content & Broadcasting">Digital Content & Broadcasting</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Buat Kata Sandi</label>
                                            <input type="password" name="password" class="form-control form-control-nexora" id="regPassword" placeholder="Minimal 6 karakter" minlength="6" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">Konfirmasi Kata Sandi</label>
                                            <input type="password" name="confirm_password" class="form-control form-control-nexora" id="regConfirmPassword" placeholder="Ulangi kata sandi" minlength="6" required>
                                        </div>
                                    </div>

                                    <div class="form-check my-3">
                                        <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                        <label class="form-check-label small text-secondary" for="agreeTerms">
                                            Saya menyetujui Kebijakan Privasi & Tata Tertib Akademik NEXORA Esports University.
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-nexora w-100 py-3">
                                        Daftar Akun <span class="selectedRoleText">Mahasiswa</span> Sekarang <i class="bi bi-check2-circle ms-1"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         MODAL DETAIL KURIKULUM PROGRAM STUDI
    ====================================================================== -->
    <div class="modal fade" id="prodiModal" tabindex="-1" aria-labelledby="prodiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background: #100e26; border: 1px solid var(--neon-blue); border-radius: 20px;">
                <div class="modal-header border-secondary border-opacity-25">
                    <div>
                        <span class="badge bg-primary mb-1" id="modalProdiDegree">Gelar</span>
                        <h5 class="modal-title text-white fw-bold" id="prodiModalLabel">Detail Program Studi</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-light opacity-75 small mb-3" id="modalProdiDesc"></p>
                    <h6 class="text-neon-blue fw-bold small text-uppercase mb-2">Mata Kuliah & Fokus Utama:</h6>
                    <ul class="list-group list-group-flush rounded-3" id="modalProdiCourses"></ul>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-nexora btn-sm" id="modalRegisterBtn" data-bs-dismiss="modal">
                        Daftar Program Ini <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentRole = 'Mahasiswa';

        // Beralih dari Landing Page Utama ke Halaman Khusus Portal Akademik
        function openAuthPortal(tabType) {
            document.getElementById('main-landing-view').classList.add('d-none');
            document.getElementById('portal-view').classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });

            const tabTriggerEl = document.getElementById(tabType === 'register' ? 'register-tab' : 'login-tab');
            if (tabTriggerEl) {
                const tab = new bootstrap.Tab(tabTriggerEl);
                tab.show();
            }
        }

        // Kembali dari Halaman Portal ke Landing Page Utama Universitas
        function closeAuthPortal() {
            document.getElementById('portal-view').classList.add('d-none');
            document.getElementById('main-landing-view').classList.remove('d-none');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Mengubah Peran Pengguna (Mahasiswa, Dosen, Admin) & Menyesuaikan Form
        function selectRole(role, element) {
            currentRole = role;

            document.querySelectorAll('.role-card').forEach(card => card.classList.remove('active'));
            if (element) {
                element.classList.add('active');
            } else {
                const targetCard = document.querySelector(`.role-card[data-role="${role}"]`);
                if (targetCard) targetCard.classList.add('active');
            }

            document.querySelectorAll('.selectedRoleText').forEach(span => {
                span.textContent = role;
            });
            document.querySelectorAll('.selectedRoleInput').forEach(input => {
                input.value = role.toLowerCase();
            });

            const loginIdLabel = document.getElementById('loginIdLabel');
            const loginIdentifier = document.getElementById('loginIdentifier');
            const regUsernameLabel = document.getElementById('regUsernameLabel');
            const regUsername = document.getElementById('regUsername');
            const regDynamicContainer = document.getElementById('regDynamicFieldContainer');

            if (role === 'Mahasiswa') {
                loginIdLabel.textContent = 'NIM Mahasiswa (10 Karakter)';
                loginIdentifier.placeholder = 'contoh: 2026010001';
                loginIdentifier.maxLength = 10;

                regUsernameLabel.textContent = 'NIM (10 Digit)';
                regUsername.placeholder = '2026010001';
                regUsername.maxLength = 10;

                regDynamicContainer.innerHTML = `
                    <label class="form-label small fw-semibold">Jurusan / Program Studi</label>
                    <select name="info_tambahan" class="form-select form-select-nexora" id="regProdiSelect">
                        <option value="Esports Management & Analytics">Esports Management & Analytics</option>
                        <option value="Game Development & Technology">Game Development & Technology</option>
                        <option value="Digital Content & Broadcasting">Digital Content & Broadcasting</option>
                    </select>
                `;
            } else if (role === 'Dosen') {
                loginIdLabel.textContent = 'NID Dosen (5 Karakter)';
                loginIdentifier.placeholder = 'contoh: D0101';
                loginIdentifier.maxLength = 5;

                regUsernameLabel.textContent = 'NID Dosen (5 Karakter)';
                regUsername.placeholder = 'D0101';
                regUsername.maxLength = 5;

                regDynamicContainer.innerHTML = `
                    <div class="p-2 rounded-3 small text-info" style="background: rgba(0,242,254,0.08); border: 1px solid rgba(0,242,254,0.2);">
                        <i class="bi bi-info-circle me-1"></i> NID (5 karakter) akan otomatis terdaftar pada tabel <strong>dosen</strong> dan <strong>user</strong>.
                    </div>
                `;
            } else if (role === 'Admin') {
                loginIdLabel.textContent = 'Username Admin (Maks. 10 Karakter)';
                loginIdentifier.placeholder = 'contoh: admin01';
                loginIdentifier.maxLength = 10;

                regUsernameLabel.textContent = 'Username Admin (Maks. 10)';
                regUsername.placeholder = 'admin01';
                regUsername.maxLength = 10;

                regDynamicContainer.innerHTML = `
                    <div class="p-2 rounded-3 small text-warning" style="background: rgba(245,197,66,0.08); border: 1px solid rgba(245,197,66,0.2);">
                        <i class="bi bi-shield-lock me-1"></i> Akun Administrator memiliki akses penuh pengelolaan jadwal, mata kuliah, dan pengguna.
                    </div>
                `;
            }

            hideFeedback();
        }

        // Memilih Program Studi dan Langsung Membuka Halaman Portal Registrasi
        function selectProdiAndRegister(prodiName) {
            selectRole('Mahasiswa');
            openAuthPortal('register');
            setTimeout(() => {
                const prodiSelect = document.getElementById('regProdiSelect');
                if (prodiSelect) {
                    prodiSelect.value = prodiName;
                }
            }, 100);
        }

        // Modal Detail Kurikulum Program Studi
        function showProdiDetail(title, degree, desc, courses) {
            document.getElementById('prodiModalLabel').textContent = title;
            document.getElementById('modalProdiDegree').textContent = degree;
            document.getElementById('modalProdiDesc').textContent = desc;

            const coursesList = document.getElementById('modalProdiCourses');
            coursesList.innerHTML = '';
            courses.forEach(course => {
                const li = document.createElement('li');
                li.className = 'list-group-item bg-transparent text-light border-secondary border-opacity-25 small';
                li.innerHTML = `<i class="bi bi-caret-right-fill text-neon-pink me-2"></i>${course}`;
                coursesList.appendChild(li);
            });

            document.getElementById('modalRegisterBtn').onclick = function() {
                selectProdiAndRegister(title);
            };
        }

        // Validasi Konfirmasi Password sebelum Dikirim ke proses_register.php
        function validateRegisterForm(event) {
            const pass = document.getElementById('regPassword').value;
            const confirmPass = document.getElementById('regConfirmPassword').value;

            if (pass !== confirmPass) {
                event.preventDefault();
                showFeedback(
                    'danger',
                    `<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Konfirmasi Kata Sandi Tidak Cocok!</strong> Silakan periksa kembali kata sandi Anda.`
                );
                return false;
            }
            return true;
        }

        function showForgotMsg() {
            showFeedback(
                'info',
                `<i class="bi bi-info-circle-fill me-2"></i>Silakan hubungi Administrator Kampus untuk mereset kata sandi NIM/NID Anda.`
            );
        }

        function showFeedback(type, htmlContent) {
            const feedbackBox = document.getElementById('authFeedback');
            feedbackBox.className = `alert alert-${type} rounded-4 mb-4`;
            feedbackBox.innerHTML = htmlContent;
        }

        function hideFeedback() {
            const feedbackBox = document.getElementById('authFeedback');
            feedbackBox.classList.add('d-none');
        }
    </script>
</body>
</html>