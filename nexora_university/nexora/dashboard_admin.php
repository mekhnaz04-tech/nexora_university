
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// =====================================
// CEK LOGIN ADMIN
// =====================================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// MENGAMBIL STATISTIK DATABASE
// =====================================
function hitungData($conn, $tabel) {
    $tabelDiizinkan = ['mahasiswa', 'dosen', 'matakuliah', 'jadwal'];

    if (!in_array($tabel, $tabelDiizinkan, true)) {
        return 0;
    }

    $query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tabel`");

    if (!$query) {
        return 0;
    }

    $data = mysqli_fetch_assoc($query);
    return (int) $data['total'];
}

$jml_mhs   = hitungData($conn, 'mahasiswa');
$jml_dosen = hitungData($conn, 'dosen');
$jml_mk    = hitungData($conn, 'matakuliah');
$jml_jdwl  = hitungData($conn, 'jadwal');

// =====================================
// MENGAMBIL DATA PENGGUNA
// =====================================
$users = mysqli_query($conn, "
    SELECT id_user, username, nama, role
    FROM `user`
    ORDER BY id_user DESC
");

if (!$users) {
    die("Gagal mengambil data pengguna: " . e(mysqli_error($conn)));
}

$total_user = mysqli_num_rows($users);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin — NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(255,0,128,0.08), transparent 30%),
                #080715;
            color: #f1f5f9;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(16,14,38,0.96);
            border-bottom: 1px solid rgba(0,242,254,0.25);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
            color: #00f2fe !important;
        }

        .card-nexora {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.20);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .welcome-card {
            background: linear-gradient(
                120deg,
                rgba(255,0,128,0.13),
                rgba(120,80,255,0.13),
                #100e26
            );
            border: 1px solid rgba(255,0,128,0.25);
        }

        .stat-card {
            transition: 0.3s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #00f2fe;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 22px;
        }

        .icon-cyan {
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
        }

        .icon-purple {
            background: rgba(139,92,246,0.15);
            color: #a78bfa;
        }

        .icon-yellow {
            background: rgba(255,193,7,0.13);
            color: #ffc107;
        }

        .icon-pink {
            background: rgba(255,0,128,0.13);
            color: #ff4da6;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .text-cyan {
            color: #00f2fe !important;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-hover-bg: rgba(0,242,254,0.06);
            --bs-table-hover-color: white;
            white-space: nowrap;
        }

        .table thead th {
            color: #00f2fe;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(0,242,254,0.25);
            padding: 15px;
        }

        .table tbody td {
            padding: 14px;
            font-size: 13px;
            border-color: rgba(255,255,255,0.07);
        }

        .menu-card {
            display: block;
            text-decoration: none;
            color: #f1f5f9;
            transition: 0.3s;
            height: 100%;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            border-color: #00f2fe;
            color: #00f2fe;
        }

        .menu-icon {
            font-size: 25px;
            color: #00f2fe;
        }

        .btn-logout {
            border-radius: 30px;
        }

        footer {
            color: #64748b;
            font-size: 12px;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark px-3 px-md-4 py-3">
    <div class="container-fluid">

        <a class="navbar-brand brand" href="dashboard_admin.php">
            <i class="bi bi-shield-lock-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-3">

            <div class="text-end d-none d-sm-block">
                <div class="small fw-bold">
                    <?= e($_SESSION['nama'] ?? 'Administrator'); ?>
                </div>

                <div class="text-secondary small">
                    Administrator
                </div>
            </div>

            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-logout px-3">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
            </a>

        </div>
    </div>
</nav>

<div class="container py-4 py-md-5">

    <!-- WELCOME ADMIN -->
    <div class="card-nexora welcome-card p-4 p-md-5 mb-4">
        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="text-cyan small fw-bold mb-2">
                    ADMINISTRATOR CONTROL PANEL
                </div>

                <h3 class="fw-bold mb-2">
                    Selamat Datang, <?= e($_SESSION['nama'] ?? 'Administrator'); ?>!
                </h3>

                <p class="text-secondary mb-0">
                    Kelola seluruh aktivitas akademik NEXORA University
                    melalui satu pusat kendali administrasi.
                </p>

            </div>

            <div class="col-md-4 text-center d-none d-md-block">
                <i class="bi bi-shield-lock-fill text-cyan"
                   style="font-size: 85px; opacity: 0.8;"></i>
            </div>

        </div>
    </div>

    <!-- STATISTIK DATABASE -->
    <div class="mb-3">
        <h5 class="section-title text-white">
            <i class="bi bi-speedometer2 text-cyan me-2"></i>
            RINGKASAN AKADEMIK
        </h5>

        <p class="text-secondary small">
            Informasi jumlah data yang tersimpan dalam sistem.
        </p>
    </div>

    <div class="row g-3 mb-4">

        <!-- MAHASISWA -->
        <div class="col-6 col-lg-3">
            <div class="card-nexora stat-card p-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon icon-cyan">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <span class="text-secondary small">01</span>
                </div>

                <div class="text-secondary small">Total Mahasiswa</div>

                <h2 class="fw-bold text-cyan mb-0">
                    <?= $jml_mhs; ?>
                </h2>

            </div>
        </div>

        <!-- DOSEN -->
        <div class="col-6 col-lg-3">
            <div class="card-nexora stat-card p-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon icon-purple">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <span class="text-secondary small">02</span>
                </div>

                <div class="text-secondary small">Total Dosen</div>

                <h2 class="fw-bold mb-0" style="color:#a78bfa;">
                    <?= $jml_dosen; ?>
                </h2>

            </div>
        </div>

        <!-- MATA KULIAH -->
        <div class="col-6 col-lg-3">
            <div class="card-nexora stat-card p-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon icon-yellow">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <span class="text-secondary small">03</span>
                </div>

                <div class="text-secondary small">Mata Kuliah</div>

                <h2 class="fw-bold text-warning mb-0">
                    <?= $jml_mk; ?>
                </h2>

            </div>
        </div>

        <!-- JADWAL -->
        <div class="col-6 col-lg-3">
            <div class="card-nexora stat-card p-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon icon-pink">
                        <i class="bi bi-calendar-week-fill"></i>
                    </div>
                    <span class="text-secondary small">04</span>
                </div>

                <div class="text-secondary small">Total Jadwal</div>

                <h2 class="fw-bold mb-0" style="color:#ff4da6;">
                    <?= $jml_jdwl; ?>
                </h2>

            </div>
        </div>

    </div>

    <!-- MENU PENGELOLAAN -->
    <div class="mb-3">

        <h5 class="section-title text-white">
            <i class="bi bi-grid-fill text-cyan me-2"></i>
            MENU PENGELOLAAN AKADEMIK
        </h5>

        <p class="text-secondary small">
            Pilih menu untuk mengelola data akademik universitas.
        </p>

    </div>

    <div class="row g-3 mb-4">

        <!-- DATA MAHASISWA -->
        <div class="col-6 col-lg-3">
            <a href="data_mahasiswa.php" class="card-nexora menu-card p-3">

                <i class="bi bi-people menu-icon"></i>

                <h6 class="fw-bold mt-3 mb-1">
                    Data Mahasiswa
                </h6>

                <small class="text-secondary">
                    Kelola data mahasiswa
                </small>

            </a>
        </div>

        <!-- DATA DOSEN -->
        <div class="col-6 col-lg-3">
            <a href="data_dosen.php" class="card-nexora menu-card p-3">

                <i class="bi bi-person-badge-fill menu-icon"></i>

                <h6 class="fw-bold mt-3 mb-1">
                    Data Dosen
                </h6>

                <small class="text-secondary">
                    Kelola data dosen
                </small>

            </a>
        </div>

        <!-- MATA KULIAH -->
        <div class="col-6 col-lg-3">
            <a href="data_matakuliah.php" class="card-nexora menu-card p-3">

                <i class="bi bi-book menu-icon"></i>

                <h6 class="fw-bold mt-3 mb-1">
                    Data Mata Kuliah
                </h6>

                <small class="text-secondary">
                    Kelola mata kuliah
                </small>

            </a>
        </div>

        <!-- MANAJEMEN JADWAL -->
        <div class="col-6 col-lg-3">
            <a href="data_jadwal.php" class="card-nexora menu-card p-3">

                <i class="bi bi-calendar-week menu-icon"></i>

                <h6 class="fw-bold mt-3 mb-1">
                    Manajemen Jadwal
                </h6>

                <small class="text-secondary">
                    Kelola jadwal perkuliahan dan dosen pengampu
                </small>

            </a>
        </div>

        <!-- MANAJEMEN USER -->
        <div class="col-6 col-lg-3">
            <a href="data_user.php" class="card-nexora menu-card p-3">

                <i class="bi bi-people menu-icon"></i>

                <h6 class="fw-bold mt-3 mb-1">
                    Manajemen User
                </h6>

                <small class="text-secondary">
                    Kelola akun admin, dosen, dan mahasiswa
                </small>

            </a>
        </div>

    </div>

    <!-- DAFTAR PENGGUNA -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

            <div>

                <h5 class="section-title text-white mb-1">
                    <i class="bi bi-people-fill text-cyan me-2"></i>
                    DAFTAR PENGGUNA
                </h5>

                <div class="text-secondary small">
                    Daftar akun yang terdaftar dalam sistem NEXORA.
                </div>

            </div>

            <span class="badge bg-info text-dark">
                <?= $total_user; ?> Pengguna
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username / NIM / NID</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($total_user > 0): ?>

                        <?php while ($u = mysqli_fetch_assoc($users)): ?>

                            <tr>

                                <td>
                                    <?= e($u['id_user']); ?>
                                </td>

                                <td class="fw-bold text-cyan">
                                    <?= e($u['username']); ?>
                                </td>

                                <td>
                                    <?= e($u['nama']); ?>
                                </td>

                                <td>

                                    <?php if ($u['role'] === 'admin'): ?>

                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-lock-fill me-1"></i>
                                            ADMIN
                                        </span>

                                    <?php elseif ($u['role'] === 'dosen'): ?>

                                        <span class="badge bg-primary">
                                            <i class="bi bi-person-workspace me-1"></i>
                                            DOSEN
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-success">
                                            <i class="bi bi-mortarboard-fill me-1"></i>
                                            MAHASISWA
                                        </span>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">
                                Belum ada pengguna terdaftar.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Administrator Control Panel.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
