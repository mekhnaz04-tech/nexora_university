
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// =====================================
// CEK LOGIN DOSEN
// =====================================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'dosen'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

$nid = trim($_SESSION['username']);

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// AMBIL IDENTITAS DOSEN
// =====================================
$stmt_dosen = $conn->prepare("
    SELECT nama_lengkap
    FROM dosen
    WHERE TRIM(nid) = ?
");

if (!$stmt_dosen) {
    die("Query dosen gagal: " . $conn->error);
}

$stmt_dosen->bind_param("s", $nid);
$stmt_dosen->execute();

$result_dosen = $stmt_dosen->get_result();
$data_dosen = $result_dosen->fetch_assoc();

$nama = $data_dosen['nama_lengkap']
    ?? ($_SESSION['nama'] ?? 'Dosen');

$stmt_dosen->close();

// =====================================
// AMBIL JADWAL MENGAJAR DOSEN
// =====================================
$sql_jadwal = "
    SELECT
        j.id_jadwal,
        j.kode_mk,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        j.ruangan,
        m.nama_mk,
        m.sks
    FROM jadwal j
    LEFT JOIN matakuliah m
        ON j.kode_mk = m.kode_mk
    WHERE TRIM(j.nid) = ?
    ORDER BY
        FIELD(
            j.hari,
            'Senin', 'Selasa', 'Rabu',
            'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ),
        j.jam_mulai ASC
";

$stmt_jadwal = $conn->prepare($sql_jadwal);

if (!$stmt_jadwal) {
    die("Query jadwal gagal: " . $conn->error);
}

$stmt_jadwal->bind_param("s", $nid);
$stmt_jadwal->execute();

$result_jadwal = $stmt_jadwal->get_result();

$data_jadwal = [];

while ($row = $result_jadwal->fetch_assoc()) {
    $data_jadwal[] = $row;
}

$stmt_jadwal->close();

// =====================================
// HITUNG STATISTIK
// =====================================
$total_jadwal = count($data_jadwal);
$total_sks = 0;

foreach ($data_jadwal as $jadwal) {
    $total_sks += (int)($jadwal['sks'] ?? 0);
}

$total_mk = count(
    array_unique(array_column($data_jadwal, 'kode_mk'))
);
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Dosen | NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(121,40,202,0.10), transparent 30%),
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
            border: 1px solid rgba(121,40,202,0.35);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .welcome-card {
            background: linear-gradient(
                120deg,
                rgba(121,40,202,0.17),
                rgba(0,242,254,0.10),
                #100e26
            );
        }

        .stat-card {
            height: 100%;
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #00f2fe;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
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
            border-bottom: 1px solid rgba(0,242,254,0.3);
            padding: 15px;
        }

        .table tbody td {
            padding: 14px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
        }

        .btn-absensi {
            background: transparent;
            color: #00f2fe;
            border: 1px solid #00f2fe;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-absensi:hover {
            background: #00f2fe;
            color: #080715;
        }

        .btn-nilai {
            background: transparent;
            color: #a78bfa;
            border: 1px solid #a78bfa;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-nilai:hover {
            background: #a78bfa;
            color: #080715;
        }

        .btn-jadwal {
            background: rgba(0,242,254,0.10);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.4);
            border-radius: 9px;
            font-weight: 600;
        }

        .btn-jadwal:hover {
            background: #00f2fe;
            color: #080715;
        }

        .badge-ruang {
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.25);
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

        <a class="navbar-brand brand" href="dashboard_dosen.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-2">

            <!-- MENU JADWAL MENGAJAR -->
            <a href="jadwal_mengajar.php" class="btn btn-jadwal btn-sm">
                <i class="bi bi-calendar-week me-1"></i>
                <span class="d-none d-sm-inline">Jadwal Mengajar</span>
            </a>

            <!-- MENU INPUT NILAI UJIAN -->
            <a href="nilai_ujian.php" class="btn btn-nilai btn-sm">
                <i class="bi bi-clipboard-data me-1"></i>
                <span class="d-none d-sm-inline">Input Nilai Ujian</span>
            </a>

            <div class="text-end d-none d-md-block ms-2">
                <div class="small fw-bold">
                    <?= e($nama); ?>
                </div>
                <div class="small text-secondary">
                    NID: <?= e($nid); ?>
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

    <!-- WELCOME DOSEN -->
    <div class="card-nexora welcome-card p-4 p-md-5 mb-4">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="text-cyan small fw-bold mb-2">
                    LECTURER ACADEMIC PORTAL
                </div>

                <h3 class="fw-bold mb-2">
                    Selamat Datang, <?= e($nama); ?>!
                </h3>

                <p class="text-secondary mb-3">
                    Selamat datang di portal dosen NEXORA University.
                    Kelola jadwal mengajar, absensi, dan penilaian mahasiswa.
                </p>

                <span class="badge bg-info text-dark">
                    <i class="bi bi-person-workspace me-1"></i>
                    DOSEN
                </span>

            </div>

            <div class="col-md-4 text-center d-none d-md-block">
                <i class="bi bi-person-workspace text-cyan"
                   style="font-size: 90px; opacity: 0.8;"></i>
            </div>

        </div>
    </div>

    <!-- RINGKASAN -->
    <div class="mb-3">
        <h5 class="section-title text-white">
            <i class="bi bi-speedometer2 text-cyan me-2"></i>
            RINGKASAN MENGAJAR
        </h5>

        <p class="text-secondary small">
            Ringkasan aktivitas akademik dosen.
        </p>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-4">
            <div class="card-nexora stat-card p-3">
                <div class="stat-icon mb-3">
                    <i class="bi bi-calendar-week"></i>
                </div>

                <div class="text-secondary small">
                    Total Jadwal Mengajar
                </div>

                <h2 class="fw-bold text-cyan mb-0">
                    <?= $total_jadwal; ?>
                </h2>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="card-nexora stat-card p-3">
                <div class="stat-icon mb-3">
                    <i class="bi bi-journal-bookmark-fill"></i>
                </div>

                <div class="text-secondary small">
                    Mata Kuliah Diampu
                </div>

                <h2 class="fw-bold mb-0" style="color:#a78bfa;">
                    <?= $total_mk; ?>
                </h2>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="card-nexora stat-card p-3">
                <div class="stat-icon mb-3">
                    <i class="bi bi-collection-fill"></i>
                </div>

                <div class="text-secondary small">
                    Total SKS Jadwal
                </div>

                <h2 class="fw-bold text-warning mb-0">
                    <?= $total_sks; ?>
                </h2>
            </div>
        </div>

    </div>

    <!-- JADWAL MENGAJAR -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

            <div>
                <h5 class="section-title text-white mb-1">
                    <i class="bi bi-calendar-week text-cyan me-2"></i>
                    JADWAL MENGAJAR ANDA
                </h5>

                <div class="text-secondary small">
                    Kelola absensi dan nilai mahasiswa pada setiap kelas.
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-info text-dark">
                    <?= $total_jadwal; ?> Jadwal
                </span>

                <a href="jadwal_mengajar.php" class="btn btn-jadwal btn-sm">
                    <i class="bi bi-arrow-up-right-square me-1"></i>
                    Lihat Semua
                </a>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Jadwal</th>
                        <th>Kode MK</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($total_jadwal > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_jadwal as $row): ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td class="text-secondary">
                                    #<?= e($row['id_jadwal']); ?>
                                </td>

                                <td class="text-cyan fw-bold">
                                    <?= e($row['kode_mk']); ?>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?= e($row['nama_mk'] ?? 'Mata kuliah belum tersedia'); ?>
                                    </div>
                                </td>

                                <td><?= e($row['sks'] ?? '-'); ?></td>

                                <td><?= e($row['hari']); ?></td>

                                <td>
                                    <?= e(substr($row['jam_mulai'], 0, 5)); ?>
                                    -
                                    <?= e(substr($row['jam_selesai'], 0, 5)); ?>
                                </td>

                                <td>
                                    <span class="badge badge-ruang">
                                        <?= e($row['ruangan']); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex flex-wrap gap-2">

                                        <a href="detail_absensi.php?id=<?= (int)$row['id_jadwal']; ?>"
                                           class="btn btn-absensi btn-sm">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            Absensi
                                        </a>

                                        <a href="input_nilai.php?id=<?= (int)$row['id_jadwal']; ?>"
                                           class="btn btn-nilai btn-sm">
                                            <i class="bi bi-award-fill me-1"></i>
                                            Input Nilai
                                        </a>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="text-center py-5">

                                <i class="bi bi-calendar-x text-secondary"
                                   style="font-size: 40px;"></i>

                                <div class="text-secondary mt-2">
                                    Belum ada jadwal mengajar yang ditetapkan untuk akun ini.
                                </div>

                                <small class="text-secondary">
                                    Pastikan username dosen sama dengan NID pada tabel dosen dan jadwal.
                                </small>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

    <!-- INFORMASI -->
    <div class="card-nexora p-3 mt-4">

        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill text-cyan fs-4"></i>

            <div>
                <div class="fw-bold small">
                    Informasi Dosen
                </div>

                <div class="text-secondary small">
                    Gunakan menu Absensi untuk mencatat kehadiran mahasiswa
                    dan menu Input Nilai untuk mengelola hasil penilaian akademik.
                </div>
            </div>
        </div>

    </div>

    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Lecturer Academic Portal.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
