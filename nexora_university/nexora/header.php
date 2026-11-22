
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =====================================
// CEK SESSION
// =====================================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role'])
) {
    header("Location: index.php#portal-akademik");
    exit;
}

$nama = $_SESSION['nama'] ?? 'Pengguna';
$role = $_SESSION['role'];

$halaman = basename($_SERVER['PHP_SELF']);

// =====================================
// TENTUKAN DASHBOARD BERDASARKAN ROLE
// =====================================
if ($role === 'admin') {
    $dashboard = 'dashboard_admin.php';
} elseif ($role === 'dosen') {
    $dashboard = 'dashboard_dosen.php';
} else {
    $dashboard = 'dashboard_mahasiswa.php';
}

// =====================================
// FUNGSI MENU AKTIF
// =====================================
function menuAktif($daftar_halaman, $halaman_sekarang) {
    return in_array($halaman_sekarang, $daftar_halaman)
        ? 'active'
        : '';
}

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function h($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>NEXORA University | Academic Portal</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(0,242,254,0.06), transparent 30%),
                #080715;
            color: #f1f5f9;
            min-height: 100vh;
        }

        /* SIDEBAR */

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #100e26;
            border-right: 1px solid rgba(0,242,254,0.2);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            padding: 22px 16px;
            transition: 0.3s;
            overflow-y: auto;
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            color: #00f2fe;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }

        .brand-subtitle {
            color: #94a3b8;
            font-size: 11px;
            letter-spacing: 2px;
        }

        .sidebar .nav-link {
            color: #a8b0c5;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 5px;
            font-size: 13px;
            font-weight: 600;
            transition: 0.3s;
        }

        .sidebar .nav-link:hover {
            background: rgba(0,242,254,0.08);
            color: #00f2fe;
        }

        .sidebar .nav-link.active {
            background: linear-gradient(
                90deg,
                rgba(0,242,254,0.17),
                rgba(120,80,255,0.12)
            );
            color: #00f2fe;
            border-left: 3px solid #00f2fe;
        }

        .menu-heading {
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1.5px;
            padding: 18px 14px 8px;
            text-transform: uppercase;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* MAIN CONTENT */

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            padding: 25px;
        }

        .topbar {
            background: rgba(16,14,38,0.9);
            border: 1px solid rgba(0,242,254,0.18);
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 25px;
        }

        .content-card {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.2);
            border-radius: 16px;
        }

        .text-cyan {
            color: #00f2fe !important;
        }

        .role-badge {
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.3);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .btn-logout {
            border-radius: 10px;
            font-size: 13px;
        }

        .mobile-toggle {
            display: none;
        }

        /* RESPONSIVE */

        @media (max-width: 991px) {

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .mobile-toggle {
                display: inline-block;
            }

        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

    </style>

</head>

<body>

<!-- OVERLAY MOBILE -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="d-flex">

    <!-- =====================================
         SIDEBAR
    ====================================== -->

    <aside class="sidebar" id="sidebar">

        <!-- BRAND -->
        <div class="mb-4 pb-4 border-bottom border-secondary border-opacity-25">

            <a href="<?= h($dashboard); ?>" class="text-decoration-none">

                <div class="brand">
                    <i class="bi bi-mortarboard-fill me-2"></i>NEXORA
                </div>

                <div class="brand-subtitle mt-1">
                    UNIVERSITY PORTAL
                </div>

            </a>

        </div>


        <!-- PROFIL PENGGUNA -->
        <div class="content-card p-3 mb-4">

            <div class="d-flex align-items-center gap-2">

                <div class="rounded-circle d-flex justify-content-center align-items-center"
                     style="width:40px;height:40px;background:rgba(0,242,254,0.12);color:#00f2fe;">

                    <i class="bi bi-person-fill fs-5"></i>

                </div>

                <div class="overflow-hidden">

                    <div class="fw-bold small text-white text-truncate">
                        <?= h($nama); ?>
                    </div>

                    <span class="role-badge">
                        <?= h($role); ?>
                    </span>

                </div>

            </div>

        </div>


        <!-- MENU -->
        <ul class="nav flex-column mb-auto">

            <!-- DASHBOARD -->
            <li class="nav-item">

                <a href="<?= h($dashboard); ?>"
                   class="nav-link <?= menuAktif(
                       ['dashboard_admin.php','dashboard_dosen.php','dashboard_mahasiswa.php'],
                       $halaman
                   ); ?>">

                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard

                </a>

            </li>


            <?php if ($role === 'admin'): ?>

                <!-- MENU ADMIN -->
                <li class="menu-heading">
                    Master Data
                </li>

                <li class="nav-item">

                    <a href="data_mahasiswa.php"
                       class="nav-link <?= menuAktif(['data_mahasiswa.php'], $halaman); ?>">

                        <i class="bi bi-people-fill me-2"></i>
                        Data Mahasiswa

                    </a>

                </li>

                <li class="nav-item">

                    <a href="data_dosen.php"
                       class="nav-link <?= menuAktif(['data_dosen.php'], $halaman); ?>">

                        <i class="bi bi-person-workspace me-2"></i>
                        Data Dosen

                    </a>

                </li>

                <li class="nav-item">

                    <a href="data_matakuliah.php"
                       class="nav-link <?= menuAktif(['data_matakuliah.php'], $halaman); ?>">

                        <i class="bi bi-journal-bookmark-fill me-2"></i>
                        Mata Kuliah

                    </a>

                </li>

                <li class="menu-heading">
                    Akademik
                </li>

                <li class="nav-item">

                    <a href="data_jadwal.php"
                       class="nav-link <?= menuAktif(['data_jadwal.php'], $halaman); ?>">

                        <i class="bi bi-calendar-week-fill me-2"></i>
                        Jadwal Kuliah

                    </a>

                </li>


            <?php elseif ($role === 'dosen'): ?>

                <!-- MENU DOSEN -->
                <li class="menu-heading">
                    Menu Dosen
                </li>

                <li class="nav-item">

                    <a href="jadwal_mengajar.php"
                       class="nav-link <?= menuAktif(
                           ['jadwal_mengajar.php','detail_absensi.php'],
                           $halaman
                       ); ?>">

                        <i class="bi bi-calendar-check-fill me-2"></i>
                        Jadwal Mengajar

                    </a>

                </li>

                <li class="nav-item">

                    <a href="nilai_ujian.php"
                       class="nav-link <?= menuAktif(
                           ['nilai_ujian.php','input_nilai.php'],
                           $halaman
                       ); ?>">

                        <i class="bi bi-clipboard-data-fill me-2"></i>
                        Input Nilai

                    </a>

                </li>


            <?php elseif ($role === 'mahasiswa'): ?>

                <!-- MENU MAHASISWA -->
                <li class="menu-heading">
                    Menu Mahasiswa
                </li>

                <li class="nav-item">

                    <a href="dashboard_mahasiswa.php"
                       class="nav-link <?= menuAktif(['dashboard_mahasiswa.php'], $halaman); ?>">

                        <i class="bi bi-journal-check me-2"></i>
                        KRS & Nilai

                    </a>

                </li>

                <li class="nav-item">

                    <a href="jadwal_kuliah.php"
                       class="nav-link <?= menuAktif(['jadwal_kuliah.php'], $halaman); ?>">

                        <i class="bi bi-calendar-week me-2"></i>
                        Jadwal Kuliah

                    </a>

                </li>

                <li class="nav-item">

                    <a href="absensi_mahasiswa.php"
                       class="nav-link <?= menuAktif(['absensi_mahasiswa.php'], $halaman); ?>">

                        <i class="bi bi-check2-square me-2"></i>
                        Rekap Absensi

                    </a>

                </li>

                <li class="nav-item">

                    <a href="nilai_mahasiswa.php"
                       class="nav-link <?= menuAktif(['nilai_mahasiswa.php'], $halaman); ?>">

                        <i class="bi bi-award-fill me-2"></i>
                        Nilai Akademik

                    </a>

                </li>

            <?php endif; ?>

        </ul>


        <!-- LOGOUT -->
        <div class="sidebar-footer">

            <a href="logout.php"
               class="btn btn-outline-danger w-100 btn-logout"
               onclick="return confirm('Yakin ingin keluar dari NEXORA University?')">

                <i class="bi bi-box-arrow-left me-2"></i>
                Logout

            </a>

            <div class="text-center text-secondary mt-3" style="font-size:10px;">
                NEXORA UNIVERSITY © <?= date('Y'); ?>
            </div>

        </div>

    </aside>


    <!-- =====================================
         MAIN CONTENT
    ====================================== -->

    <main class="main-content flex-grow-1">

        <!-- TOPBAR -->
        <div class="topbar d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center gap-3">

                <button class="btn btn-outline-info mobile-toggle"
                        id="toggleSidebar">

                    <i class="bi bi-list fs-5"></i>

                </button>

                <div>

                    <div class="fw-bold text-white">
                        Academic Information System
                    </div>

                    <small class="text-secondary">
                        NEXORA University Portal
                    </small>

                </div>

            </div>

            <div class="text-end d-none d-sm-block">

                <div class="small text-secondary">
                    Login sebagai
                </div>

                <div class="small fw-bold text-cyan text-uppercase">
                    <?= h($role); ?>
                </div>

            </div>

        </div>
