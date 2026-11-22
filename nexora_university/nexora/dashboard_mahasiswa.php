
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// =====================================
// CEK LOGIN MAHASISWA
// =====================================
if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'mahasiswa'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

$nim = trim($_SESSION['username']);

// Fungsi untuk mengamankan output HTML
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// AMBIL DATA MAHASISWA
// =====================================
$stmt_mhs = $conn->prepare("
    SELECT nama_lengkap, jurusan
    FROM mahasiswa
    WHERE TRIM(nim) = ?
");

if (!$stmt_mhs) {
    die("Query mahasiswa gagal: " . $conn->error);
}

$stmt_mhs->bind_param("s", $nim);
$stmt_mhs->execute();

$result_mhs = $stmt_mhs->get_result();
$data_mhs = $result_mhs->fetch_assoc();

$nama = $data_mhs['nama_lengkap'] ?? ($_SESSION['nama'] ?? 'Mahasiswa');
$jurusan = $data_mhs['jurusan'] ?? '-';

$stmt_mhs->close();

// =====================================
// AMBIL DATA KRS DAN NILAI
// =====================================
$query_krs = "
    SELECT
        krs.*,
        matakuliah.kode_mk,
        matakuliah.nama_mk,
        matakuliah.sks,
        jadwal.hari,
        jadwal.jam_mulai,
        jadwal.jam_selesai,
        jadwal.ruangan,
        dosen.nama_lengkap AS nama_dosen
    FROM krs
    JOIN jadwal
        ON krs.id_jadwal = jadwal.id_jadwal
    JOIN matakuliah
        ON jadwal.kode_mk = matakuliah.kode_mk
    JOIN dosen
        ON jadwal.nid = dosen.nid
    WHERE TRIM(krs.nim) = ?
    ORDER BY matakuliah.kode_mk ASC
";

$stmt_krs = $conn->prepare($query_krs);

if (!$stmt_krs) {
    die("Query KRS gagal: " . $conn->error);
}

$stmt_krs->bind_param("s", $nim);
$stmt_krs->execute();

$result_krs = $stmt_krs->get_result();

// =====================================
// HITUNG TOTAL SKS DAN MATA KULIAH
// =====================================
$total_mk = $result_krs->num_rows;
$total_sks = 0;

$data_krs = [];

while ($row = $result_krs->fetch_assoc()) {
    $data_krs[] = $row;
    $total_sks += (int)$row['sks'];
}

$stmt_krs->close();
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Mahasiswa — NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(0,242,254,0.08), transparent 30%),
                #080715;
            color: #f1f5f9;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(16, 14, 38, 0.95);
            border-bottom: 1px solid rgba(0,242,254,0.25);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 1px;
            color: #00f2fe !important;
            font-weight: 700;
        }

        .card-nexora {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.25);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .welcome-card {
            background: linear-gradient(
                120deg,
                rgba(0,242,254,0.13),
                rgba(120,80,255,0.13),
                #100e26
            );
        }

        .stat-card {
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: #00f2fe;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            font-size: 22px;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-hover-bg: rgba(0,242,254,0.06);
            --bs-table-hover-color: #fff;
            white-space: nowrap;
        }

        .table thead th {
            color: #00f2fe;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0,242,254,0.3);
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
        }

        .text-cyan {
            color: #00f2fe !important;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .btn-logout {
            border-radius: 30px;
        }

        .btn-hasil {
            border-radius: 30px;
            transition: 0.3s;
        }

        .btn-hasil:hover {
            background: #00f2fe;
            color: #080715;
            border-color: #00f2fe;
        }

        .btn-jadwal {
            border-radius: 30px;
            color: #00f2fe;
            border: 1px solid #00f2fe;
            transition: 0.3s;
        }

        .btn-jadwal:hover {
            background: #00f2fe;
            color: #080715;
        }

        footer {
            color: #64748b;
            font-size: 12px;
        }
    </style>
</head>

<body>

<!-- =====================================
     NAVBAR
===================================== -->
<nav class="navbar navbar-dark px-3 px-md-4 py-3">
    <div class="container-fluid">

        <a class="navbar-brand brand" href="dashboard_mahasiswa.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-2 gap-md-3">

            <!-- IDENTITAS MAHASISWA -->
            <div class="text-end d-none d-sm-block">
                <div class="small fw-bold">
                    <?= e($nama); ?>
                </div>

                <div class="text-secondary small">
                    NIM: <?= e($nim); ?>
                </div>
            </div>

            <!-- MENU JADWAL KULIAH SAYA -->
            <a href="jadwal_kuliah.php"
               class="btn btn-outline-info btn-sm btn-jadwal px-3">

                <i class="bi bi-calendar-week me-1"></i>
                <span>Jadwal Kuliah Saya</span>

            </a>

            <!-- MENU HASIL STUDI -->
            <a href="nilai_mahasiswa.php"
               class="btn btn-outline-info btn-sm btn-hasil px-3">

                <i class="bi bi-award-fill me-1"></i>
                <span>Hasil Studi</span>

            </a>

            <!-- MENU LOGOUT -->
            <a href="logout.php"
               class="btn btn-outline-danger btn-sm btn-logout px-3">

                <i class="bi bi-box-arrow-right me-1"></i>
                Logout

            </a>

        </div>
    </div>
</nav>

<!-- =====================================
     KONTEN UTAMA
===================================== -->
<div class="container py-4 py-md-5">

    <!-- WELCOME -->
    <div class="card-nexora welcome-card p-4 p-md-5 mb-4">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="text-cyan small fw-bold mb-2">
                    STUDENT ACADEMIC PORTAL
                </div>

                <h3 class="fw-bold mb-2">
                    Selamat Datang, <?= e($nama); ?>!
                </h3>

                <p class="text-secondary mb-0">
                    Selamat datang di portal akademik NEXORA University.
                    Pantau mata kuliah, jadwal, dan perkembangan nilai akademik Anda.
                </p>

                <div class="mt-3">
                    <span class="badge bg-info text-dark">
                        <?= e($jurusan); ?>
                    </span>
                </div>

            </div>

            <div class="col-md-4 text-center d-none d-md-block">
                <i class="bi bi-mortarboard-fill text-cyan"
                   style="font-size: 90px; opacity: 0.8;"></i>
            </div>

        </div>

    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-6">

            <div class="card-nexora stat-card p-3 h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-journal-bookmark-fill"></i>
                    </div>

                    <div>
                        <div class="text-secondary small">
                            Mata Kuliah Diambil
                        </div>

                        <h3 class="fw-bold mb-0">
                            <?= $total_mk; ?>
                        </h3>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card-nexora stat-card p-3 h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-collection-fill"></i>
                    </div>

                    <div>
                        <div class="text-secondary small">
                            Total SKS
                        </div>

                        <h3 class="fw-bold mb-0">
                            <?= $total_sks; ?>
                        </h3>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- TABEL KRS DAN NILAI -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">

            <div>
                <h5 class="section-title text-white mb-1">
                    <i class="bi bi-journal-check text-cyan me-2"></i>
                    KRS & HASIL STUDI
                </h5>

                <div class="text-secondary small">
                    Daftar mata kuliah dan hasil penilaian akademik
                </div>
            </div>

            <span class="badge bg-info text-dark">
                <?= $total_mk; ?> Mata Kuliah
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode MK</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Jadwal & Ruang</th>
                        <th>Dosen</th>
                        <th>TM</th>
                        <th>UTS</th>
                        <th>UAS</th>
                        <th>Nilai Akhir</th>
                        <th>Grade</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($total_mk > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_krs as $row): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td class="text-cyan fw-bold">
                                    <?= e($row['kode_mk']); ?>
                                </td>

                                <td>
                                    <?= e($row['nama_mk']); ?>
                                </td>

                                <td>
                                    <?= e($row['sks']); ?>
                                </td>

                                <td>
                                    <?= e($row['hari']); ?><br>

                                    <small class="text-secondary">
                                        <?= e(substr($row['jam_mulai'], 0, 5)); ?>
                                        -
                                        <?= e(substr($row['jam_selesai'], 0, 5)); ?>
                                        (<?= e($row['ruangan']); ?>)
                                    </small>
                                </td>

                                <td>
                                    <?= e($row['nama_dosen']); ?>
                                </td>

                                <td>
                                    <?= e($row['nilai_tm']); ?>
                                </td>

                                <td>
                                    <?= e($row['nilai_uts']); ?>
                                </td>

                                <td>
                                    <?= e($row['nilai_uas']); ?>
                                </td>

                                <td class="fw-bold text-cyan">
                                    <?= e($row['nilai_akhir']); ?>
                                </td>

                                <td>
                                    <span class="badge bg-primary px-3 py-2">
                                        <?= e($row['grade']); ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="11" class="text-center py-5">

                                <i class="bi bi-journal-x text-secondary"
                                   style="font-size: 35px;"></i>

                                <div class="text-secondary mt-2">
                                    Belum ada data KRS yang diambil.
                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- INFORMASI -->
    <div class="card-nexora p-3 mt-4">

        <div class="d-flex gap-3 align-items-center">

            <i class="bi bi-info-circle-fill text-cyan fs-4"></i>

            <div>
                <div class="fw-bold small">
                    Informasi Akademik
                </div>

                <div class="text-secondary small">
                    Jika data mata kuliah atau nilai belum muncul,
                    silakan hubungi bagian akademik atau dosen pengampu.
                </div>
            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Student Academic Information System.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
