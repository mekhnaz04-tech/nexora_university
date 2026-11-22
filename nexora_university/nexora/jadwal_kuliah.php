
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// =====================================
// CEK LOGIN MAHASISWA
// =====================================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'mahasiswa'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

// NIM mahasiswa dari session login
$nim = trim($_SESSION['username']);

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// AMBIL IDENTITAS MAHASISWA
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

$nama_mahasiswa = $data_mhs['nama_lengkap']
    ?? ($_SESSION['nama'] ?? 'Mahasiswa');

$jurusan = $data_mhs['jurusan'] ?? '-';

$stmt_mhs->close();

// =====================================
// AMBIL JADWAL KULIAH MAHASISWA
// =====================================
$sql = "
    SELECT
        j.id_jadwal,
        j.kode_mk,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        j.ruangan,
        m.nama_mk,
        m.sks,
        d.nama_lengkap AS nama_dosen
    FROM krs k
    INNER JOIN jadwal j
        ON k.id_jadwal = j.id_jadwal
    INNER JOIN matakuliah m
        ON j.kode_mk = m.kode_mk
    LEFT JOIN dosen d
        ON j.nid = d.nid
    WHERE TRIM(k.nim) = ?
    ORDER BY
        FIELD(
            j.hari,
            'Senin', 'Selasa', 'Rabu',
            'Kamis', 'Jumat', 'Sabtu', 'Minggu'
        ),
        j.jam_mulai ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Query jadwal kuliah gagal: " . $conn->error);
}

$stmt->bind_param("s", $nim);
$stmt->execute();

$query = $stmt->get_result();

$jadwal_data = [];
$total_sks = 0;

while ($data = $query->fetch_assoc()) {
    $jadwal_data[] = $data;
    $total_sks += (int)($data['sks'] ?? 0);
}

$total_jadwal = count($jadwal_data);
$no = 1;
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jadwal Kuliah Saya | NEXORA University</title>

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

        .text-cyan {
            color: #00f2fe !important;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        .stat-box {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(0,242,254,0.15);
            border-radius: 12px;
            padding: 18px;
            height: 100%;
        }

        .stat-box h3 {
            color: #00f2fe;
            font-weight: 800;
            margin-bottom: 0;
        }

        .stat-box p {
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 7px;
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
            padding: 15px 12px;
        }

        .table tbody td {
            padding: 14px 12px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
            vertical-align: middle;
        }

        .table tfoot th {
            color: #00f2fe;
            padding: 15px;
            border-top: 1px solid rgba(0,242,254,0.3);
        }

        .badge-hari {
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.25);
            padding: 6px 10px;
            border-radius: 7px;
        }

        .badge-ruangan {
            background: rgba(167,139,250,0.12);
            color: #c4b5fd;
            border: 1px solid rgba(167,139,250,0.25);
            padding: 6px 10px;
            border-radius: 7px;
        }

        .btn-kembali {
            background: rgba(0,242,254,0.10);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.4);
            border-radius: 9px;
            font-weight: 600;
        }

        .btn-kembali:hover {
            background: #00f2fe;
            color: #080715;
        }

        .empty-state {
            padding: 45px 20px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 45px;
            color: #64748b;
            display: block;
            margin-bottom: 12px;
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

        <a class="navbar-brand brand" href="dashboard_mahasiswa.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-2">

            <a href="dashboard_mahasiswa.php" class="btn btn-kembali btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                <span class="d-none d-sm-inline">Dashboard</span>
            </a>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
            </a>

        </div>
    </div>
</nav>

<div class="container py-4 py-md-5">

    <!-- HEADER HALAMAN -->
    <div class="card-nexora welcome-card p-4 p-md-5 mb-4">

        <div class="row align-items-center">

            <div class="col-md-8">

                <div class="text-cyan small fw-bold mb-2">
                    STUDENT ACADEMIC PORTAL
                </div>

                <h3 class="fw-bold mb-2">
                    Jadwal Kuliah Saya
                </h3>

                <p class="text-secondary mb-3">
                    Informasi jadwal perkuliahan yang telah diambil
                    melalui sistem Kartu Rencana Studi (KRS).
                </p>

                <span class="badge bg-info text-dark">
                    <i class="bi bi-person-fill me-1"></i>
                    MAHASISWA
                </span>

            </div>

            <div class="col-md-4 text-center d-none d-md-block">
                <i class="bi bi-calendar-week text-cyan"
                   style="font-size: 85px; opacity: 0.8;"></i>
            </div>

        </div>
    </div>

    <!-- IDENTITAS MAHASISWA -->
    <div class="card-nexora p-4 mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <div class="text-cyan small fw-bold mb-2">
                    <i class="bi bi-person-badge me-1"></i>
                    IDENTITAS MAHASISWA
                </div>

                <h5 class="fw-bold mb-1">
                    <?= e($nama_mahasiswa); ?>
                </h5>

                <div class="text-secondary small">
                    NIM: <?= e($nim); ?>
                    <span class="mx-2">|</span>
                    Program Studi: <?= e($jurusan); ?>
                </div>
            </div>

            <span class="badge bg-info text-dark px-3 py-2">
                <i class="bi bi-shield-check me-1"></i>
                Mahasiswa Aktif
            </span>

        </div>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-6">
            <div class="stat-box">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>Total Mata Kuliah</p>
                        <h3><?= $total_jadwal; ?></h3>
                    </div>
                    <i class="bi bi-calendar-check fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-box">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p>Total SKS Diambil</p>
                        <h3><?= $total_sks; ?></h3>
                    </div>
                    <i class="bi bi-journal-bookmark fs-1 text-info opacity-50"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- TABEL JADWAL -->
    <div class="card-nexora overflow-hidden">

        <div class="p-4 border-bottom border-secondary border-opacity-25">

            <h5 class="section-title mb-1">
                <i class="bi bi-calendar-week text-cyan me-2"></i>
                DAFTAR JADWAL PERKULIAHAN
            </h5>

            <small class="text-secondary">
                Jadwal mata kuliah yang telah Anda ambil.
            </small>

        </div>

        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0">

                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th class="text-center">SKS</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th class="text-center">Ruangan</th>
                        <th>Dosen Pengampu</th>
                    </tr>
                </thead>

                <tbody>

                <?php if ($total_jadwal > 0): ?>

                    <?php foreach ($jadwal_data as $data): ?>

                    <tr>

                        <td class="text-center">
                            <?= $no++; ?>
                        </td>

                        <td class="text-cyan fw-bold">
                            <?= e($data['kode_mk']); ?>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                <?= e($data['nama_mk']); ?>
                            </div>
                        </td>

                        <td class="text-center">
                            <?= (int)$data['sks']; ?> SKS
                        </td>

                        <td>
                            <span class="badge-hari">
                                <?= e($data['hari']); ?>
                            </span>
                        </td>

                        <td>
                            <i class="bi bi-clock text-cyan me-1"></i>

                            <?= e(date('H:i', strtotime($data['jam_mulai']))); ?>
                            -
                            <?= e(date('H:i', strtotime($data['jam_selesai']))); ?>
                            WIB
                        </td>

                        <td class="text-center">
                            <span class="badge-ruangan">
                                <?= e($data['ruangan']); ?>
                            </span>
                        </td>

                        <td>
                            <?= e($data['nama_dosen']); ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="8">

                            <div class="empty-state">

                                <i class="bi bi-calendar-x"></i>

                                <h6 class="fw-bold text-light">
                                    Belum Ada Jadwal Kuliah
                                </h6>

                                <p class="mb-0">
                                    Belum ada jadwal kuliah yang terdaftar
                                    untuk NIM Anda pada tabel KRS.
                                </p>

                            </div>

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

                <?php if ($total_jadwal > 0): ?>

                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">
                            Total SKS Diambil:
                        </th>

                        <th class="text-center">
                            <?= $total_sks; ?> SKS
                        </th>

                        <th colspan="4"></th>
                    </tr>
                </tfoot>

                <?php endif; ?>

            </table>

        </div>
    </div>

    <!-- INFORMASI -->
    <div class="card-nexora p-3 mt-4">

        <div class="d-flex align-items-center gap-3">

            <i class="bi bi-info-circle-fill text-cyan fs-4"></i>

            <div>
                <div class="fw-bold small">
                    Informasi Akademik
                </div>

                <div class="text-secondary small">
                    Jadwal kuliah ditampilkan berdasarkan mata kuliah
                    yang sudah terdaftar pada KRS mahasiswa.
                    Jika jadwal belum muncul, pastikan data KRS sudah diinput.
                </div>
            </div>

        </div>

    </div>

    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Student Academic Portal.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$stmt->close();
?>
