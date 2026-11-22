
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// CEK LOGIN DOSEN
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

function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// AMBIL IDENTITAS DOSEN
$stmt_dosen = $conn->prepare("
    SELECT nama_lengkap
    FROM dosen
    WHERE TRIM(nid) = ?
");

$stmt_dosen->bind_param("s", $nid);
$stmt_dosen->execute();
$result_dosen = $stmt_dosen->get_result();
$dosen = $result_dosen->fetch_assoc();

$nama = $dosen['nama_lengkap'] ?? ($_SESSION['nama'] ?? 'Dosen');
$stmt_dosen->close();

// AMBIL JADWAL MENGAJAR
$sql = "
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
    INNER JOIN matakuliah m
        ON j.kode_mk = m.kode_mk
    WHERE TRIM(j.nid) = ?
    ORDER BY
        FIELD(j.hari, 'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
        j.jam_mulai ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nid);
$stmt->execute();

$result = $stmt->get_result();
$data_jadwal = [];

while ($row = $result->fetch_assoc()) {
    $data_jadwal[] = $row;
}

$stmt->close();

$total_jadwal = count($data_jadwal);
$total_sks = 0;

foreach ($data_jadwal as $jadwal) {
    $total_sks += (int)$jadwal['sks'];
}

$no = 1;
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Jadwal Mengajar | NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(121,40,202,.10), transparent 30%),
                #080715;
            color: #f1f5f9;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(16,14,38,.96);
            border-bottom: 1px solid rgba(0,242,254,.25);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
            color: #00f2fe !important;
        }

        .main-container {
            max-width: 1250px;
        }

        .card-nexora {
            background: #100e26;
            border: 1px solid rgba(121,40,202,.35);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,.2);
            overflow: hidden;
        }

        .card-header-nexora {
            background: linear-gradient(100deg, rgba(121,40,202,.16), rgba(0,242,254,.07));
            border-bottom: 1px solid rgba(0,242,254,.18);
            padding: 20px 24px;
        }

        .text-cyan {
            color: #00f2fe !important;
        }

        .nid-badge {
            background: rgba(0,242,254,.10);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,.3);
            padding: 8px 13px;
            border-radius: 8px;
            font-size: 12px;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
            --bs-table-hover-bg: rgba(0,242,254,.06);
            --bs-table-hover-color: white;
            white-space: nowrap;
        }

        .table thead th {
            background: #191630;
            color: #00f2fe;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(0,242,254,.3);
            padding: 15px;
        }

        .table tbody td {
            background: transparent;
            color: #e2e8f0;
            padding: 14px;
            border-color: rgba(255,255,255,.07);
            font-size: 13px;
        }

        .badge-day {
            background: rgba(0,242,254,.12);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,.25);
        }

        .badge-room {
            background: rgba(167,139,250,.13);
            color: #a78bfa;
        }

        .btn-absensi {
            color: #00f2fe;
            border: 1px solid #00f2fe;
            background: transparent;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-absensi:hover {
            background: #00f2fe;
            color: #080715;
        }

        .btn-back {
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,.4);
            background: rgba(0,242,254,.08);
            border-radius: 8px;
        }

        .btn-back:hover {
            background: #00f2fe;
            color: #080715;
        }

        .empty-state {
            color: #94a3b8;
            padding: 45px 15px !important;
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
            <i class="bi bi-mortarboard-fill me-2"></i>NEXORA
        </a>

        <div class="d-flex align-items-center gap-2">
            <span class="text-secondary small d-none d-md-inline">
                <?= e($nama); ?>
            </span>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>

    </div>
</nav>

<div class="container main-container py-4 py-md-5">

    <!-- JUDUL HALAMAN -->
    <div class="mb-4">
        <div class="text-cyan small fw-bold mb-2">
            LECTURER ACADEMIC PORTAL
        </div>

        <h3 class="fw-bold mb-2">
            <i class="bi bi-calendar-check text-cyan me-2"></i>
            Jadwal Mengajar Saya
        </h3>

        <p class="text-secondary mb-0">
            Informasi jadwal perkuliahan dan pengelolaan absensi mahasiswa.
        </p>
    </div>

    <!-- CARD DAFTAR JADWAL -->
    <div class="card-nexora">

        <div class="card-header-nexora d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h5 class="fw-bold mb-1 text-white">
                    <i class="bi bi-journal-bookmark text-cyan me-2"></i>
                    Daftar Jadwal Perkuliahan
                </h5>

                <small class="text-secondary">
                    Jadwal mengajar berdasarkan akun dosen yang sedang login.
                </small>
            </div>

            <span class="nid-badge">
                <i class="bi bi-person-badge me-1"></i>
                NID: <?= e($nid); ?>
            </span>

        </div>

        <div class="p-3 p-md-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                <span class="text-secondary small">
                    Total jadwal:
                    <strong class="text-cyan"><?= $total_jadwal; ?></strong>
                    &nbsp; | &nbsp;
                    Total SKS:
                    <strong class="text-warning"><?= $total_sks; ?></strong>
                </span>

                <a href="dashboard_dosen.php" class="btn btn-back btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>
                    Dashboard
                </a>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center">SKS</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th class="text-center">Ruangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($total_jadwal > 0): ?>

                        <?php foreach ($data_jadwal as $data): ?>

                        <tr>
                            <td class="text-center"><?= $no++; ?></td>

                            <td class="fw-bold text-cyan">
                                <?= e($data['kode_mk']); ?>
                            </td>

                            <td class="fw-semibold">
                                <?= e($data['nama_mk']); ?>
                            </td>

                            <td class="text-center">
                                <?= (int)$data['sks']; ?> SKS
                            </td>

                            <td>
                                <span class="badge badge-day px-3 py-2">
                                    <?= e($data['hari']); ?>
                                </span>
                            </td>

                            <td>
                                <i class="bi bi-clock text-cyan me-1"></i>
                                <?= date('H:i', strtotime($data['jam_mulai'])); ?>
                                -
                                <?= date('H:i', strtotime($data['jam_selesai'])); ?>
                            </td>

                            <td class="text-center">
                                <span class="badge badge-room px-3 py-2">
                                    <?= e($data['ruangan']); ?>
                                </span>
                            </td>

                            <td class="text-center">
                                <a
                                    href="detail_absensi.php?id=<?= (int)$data['id_jadwal']; ?>"
                                    class="btn btn-absensi btn-sm px-3"
                                >
                                    <i class="bi bi-check2-square me-1"></i>
                                    Absensi
                                </a>
                            </td>
                        </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="text-center empty-state">
                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                <h6 class="text-white">Belum Ada Jadwal Mengajar</h6>
                                <small>
                                    Belum ada jadwal yang terdaftar untuk NID <?= e($nid); ?>.
                                </small>
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>
        </div>
    </div>

    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University. Lecturer Academic Portal.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
