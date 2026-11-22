
<?php
session_start();
require_once 'koneksi.php';

// Proteksi halaman mahasiswa
if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$nim = $_SESSION['username'];

// Mengambil data absensi berdasarkan NIM mahasiswa
$stmt = mysqli_prepare($conn, "
    SELECT 
        krs.*,
        jadwal.kode_mk,
        jadwal.hari,
        jadwal.jam_mulai,
        jadwal.jam_selesai,
        jadwal.ruangan,
        matakuliah.nama_mk,
        matakuliah.sks,
        dosen.nama_lengkap AS nama_dosen
    FROM krs
    INNER JOIN jadwal 
        ON krs.id_jadwal = jadwal.id_jadwal
    INNER JOIN matakuliah 
        ON jadwal.kode_mk = matakuliah.kode_mk
    INNER JOIN dosen 
        ON jadwal.nid = dosen.nid
    WHERE krs.nim = ?
    ORDER BY 
        FIELD(jadwal.hari, 'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
        jadwal.jam_mulai ASC
");

mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

$total_matkul = mysqli_num_rows($query);
$total_hadir = 0;
$total_pertemuan = 0;

$data_absensi = [];

while ($data = mysqli_fetch_assoc($query)) {
    $jumlah_hadir = 0;

    for ($i = 1; $i <= 5; $i++) {
        if (!empty($data['pertemuan' . $i])) {
            $jumlah_hadir++;
        }
    }

    $data['jumlah_hadir'] = $jumlah_hadir;
    $data['persen'] = ($jumlah_hadir / 5) * 100;

    $total_hadir += $jumlah_hadir;
    $total_pertemuan += 5;

    $data_absensi[] = $data;
}

$persentase_total = $total_pertemuan > 0
    ? ($total_hadir / $total_pertemuan) * 100
    : 0;

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi | NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #080715;
            --bg-card: #100e26;
            --neon-blue: #00f2fe;
            --neon-purple: #7928ca;
            --neon-pink: #ff0080;
            --gradient: linear-gradient(135deg,
                #00f2fe 0%, #7928ca 50%, #ff0080 100%);
        }

        body {
            background:
                radial-gradient(circle at 10% 10%,
                rgba(0,242,254,.10), transparent 35%),
                radial-gradient(circle at 90% 80%,
                rgba(255,0,128,.10), transparent 35%),
                var(--bg-dark);
            color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
        }

        .navbar-nexora {
            background: rgba(8,7,21,.95);
            border-bottom: 1px solid rgba(0,242,254,.25);
            backdrop-filter: blur(15px);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
            background: var(--gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .main-content {
            padding: 110px 0 50px;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
        }

        .text-gradient {
            background: var(--gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nexora-card {
            background: linear-gradient(145deg,
                rgba(22,19,52,.95),
                rgba(10,9,28,.98));
            border: 1px solid rgba(121,40,202,.4);
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0,0,0,.25);
        }

        .stat-card {
            padding: 24px;
            height: 100%;
            transition: .3s;
        }

        .stat-card:hover {
            border-color: var(--neon-blue);
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,242,254,.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            font-size: 23px;
            background: rgba(0,242,254,.12);
            color: var(--neon-blue);
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 27px;
            font-weight: 700;
        }

        .table-nexora {
            color: #e2e8f0;
            vertical-align: middle;
            margin-bottom: 0;
            min-width: 1050px;
        }

        .table-nexora thead th {
            background: rgba(0,242,254,.08);
            color: var(--neon-blue);
            border-bottom: 1px solid rgba(0,242,254,.3);
            padding: 16px 12px;
            font-size: 12px;
            white-space: nowrap;
        }

        .table-nexora td {
            background: transparent;
            color: #e2e8f0;
            border-color: rgba(255,255,255,.08);
            padding: 15px 12px;
            font-size: 13px;
        }

        .table-nexora tbody tr:hover td {
            background: rgba(121,40,202,.12);
        }

        .attendance-present {
            color: #00f2a6;
            background: rgba(0,242,166,.10);
            border: 1px solid rgba(0,242,166,.25);
            padding: 7px 9px;
            border-radius: 8px;
            display: inline-block;
            min-width: 80px;
        }

        .attendance-empty {
            color: #94a3b8;
            background: rgba(148,163,184,.08);
            padding: 7px 15px;
            border-radius: 8px;
            display: inline-block;
        }

        .badge-neon {
            background: rgba(0,242,254,.12);
            color: var(--neon-blue);
            border: 1px solid rgba(0,242,254,.3);
        }

        .progress {
            background: rgba(255,255,255,.08);
            height: 7px;
            border-radius: 20px;
        }

        .progress-bar {
            background: var(--gradient);
        }

        .btn-back {
            color: var(--neon-blue);
            border: 1px solid rgba(0,242,254,.4);
            border-radius: 50px;
            padding: 9px 20px;
            text-decoration: none;
            transition: .3s;
        }

        .btn-back:hover {
            color: #080715;
            background: var(--neon-blue);
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            color: #94a3b8;
        }

        @media(max-width:768px) {
            .main-content {
                padding-top: 95px;
            }

            .page-title {
                font-size: 21px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR NEXORA -->
<nav class="navbar navbar-dark navbar-nexora fixed-top py-3">
    <div class="container">
        <a class="navbar-brand brand" href="dashboard_mahasiswa.php">
            <i class="bi bi-controller me-2"></i>NEXORA
            <small class="text-light fw-normal" style="font-size:10px;">
                UNIVERSITY
            </small>
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-light small d-none d-sm-inline">
                <i class="bi bi-person-circle text-info me-1"></i>
                <?= e($_SESSION['username']); ?>
            </span>

            <a href="dashboard_mahasiswa.php" class="btn-back small">
                <i class="bi bi-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>
</nav>

<main class="container main-content">

    <!-- JUDUL HALAMAN -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="text-info small fw-bold mb-2">
                <i class="bi bi-grid-fill me-1"></i>
                STUDENT ACADEMIC PORTAL
            </div>

            <h2 class="page-title">
                REKAP <span class="text-gradient">ABSENSI</span>
            </h2>

            <p class="text-secondary mb-0">
                Pantau riwayat kehadiran perkuliahan Anda di NEXORA University.
            </p>
        </div>

        <span class="badge badge-neon rounded-pill px-3 py-2">
            <i class="bi bi-calendar-check me-1"></i>
            Academic Attendance
        </span>
    </div>

    <!-- INFORMASI MAHASISWA -->
    <div class="nexora-card p-3 p-md-4 mb-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <div class="stat-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>

            <div class="col">
                <small class="text-secondary">NOMOR INDUK MAHASISWA</small>
                <h5 class="fw-bold mb-0"><?= e($nim); ?></h5>
                <small class="text-info">NEXORA Student</small>
            </div>
        </div>
    </div>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="nexora-card stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-secondary">MATA KULIAH</small>
                        <div class="stat-number text-gradient">
                            <?= $total_matkul; ?>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
                <small class="text-secondary">Mata kuliah terdaftar</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="nexora-card stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-secondary">TOTAL KEHADIRAN</small>
                        <div class="stat-number text-success">
                            <?= $total_hadir; ?>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <small class="text-secondary">Jumlah pertemuan hadir</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="nexora-card stat-card">
                <div class="d-flex justify-content-between">
                    <div>
                        <small class="text-secondary">PERSENTASE HADIR</small>
                        <div class="stat-number text-gradient">
                            <?= number_format($persentase_total, 1); ?>%
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
                <small class="text-secondary">Rata-rata seluruh mata kuliah</small>
            </div>
        </div>

    </div>

    <!-- TABEL ABSENSI -->
    <div class="nexora-card overflow-hidden">

        <div class="p-3 p-md-4 border-bottom"
             style="border-color:rgba(0,242,254,.15)!important;">

            <h5 class="fw-bold mb-1">
                <i class="bi bi-calendar2-week text-info me-2"></i>
                Attendance History
            </h5>

            <small class="text-secondary">
                Riwayat kehadiran berdasarkan mata kuliah yang diambil.
            </small>
        </div>

        <div class="table-responsive">
            <table class="table table-nexora text-center">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode MK</th>
                        <th class="text-start">Mata Kuliah & Dosen</th>
                        <th>Pertemuan 1</th>
                        <th>Pertemuan 2</th>
                        <th>Pertemuan 3</th>
                        <th>Pertemuan 4</th>
                        <th>Pertemuan 5</th>
                        <th>Kehadiran</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($data_absensi) > 0): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($data_absensi as $data): ?>

                    <tr>
                        <td><?= $no++; ?></td>

                        <td>
                            <span class="badge badge-neon">
                                <?= e($data['kode_mk']); ?>
                            </span>
                        </td>

                        <td class="text-start">
                            <div class="fw-bold">
                                <?= e($data['nama_mk']); ?>
                            </div>

                            <small class="text-secondary">
                                <?= e($data['nama_dosen']); ?>
                            </small>

                            <br>

                            <small class="text-info">
                                <?= e($data['hari']); ?> |
                                <?= date('H:i', strtotime($data['jam_mulai'])); ?>
                                - 
                                <?= date('H:i', strtotime($data['jam_selesai'])); ?>
                            </small>
                        </td>

                        <?php for ($i = 1; $i <= 5; $i++):
                            $jam = $data['pertemuan' . $i];
                        ?>

                        <td>
                            <?php if (!empty($jam)): ?>
                                <span class="attendance-present">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <?= date('H:i', strtotime($jam)); ?>
                                </span>
                            <?php else: ?>
                                <span class="attendance-empty">-</span>
                            <?php endif; ?>
                        </td>

                        <?php endfor; ?>

                        <td>
                            <div class="fw-bold mb-2
                                <?= $data['persen'] >= 80
                                    ? 'text-success' : 'text-warning'; ?>">
                                <?= $data['jumlah_hadir']; ?>/5
                                (<?= number_format($data['persen'], 0); ?>%)
                            </div>

                            <div class="progress">
                                <div class="progress-bar"
                                     style="width:<?= $data['persen']; ?>%">
                                </div>
                            </div>
                        </td>
                    </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-calendar-x display-3 text-info"></i>
                                <h5 class="mt-3 text-light">
                                    Belum Ada Data Absensi
                                </h5>
                                <p class="mb-0">
                                    Data absensi perkuliahan Anda belum tersedia.
                                </p>
                            </div>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>

        <div class="p-3 border-top"
             style="border-color:rgba(0,242,254,.15)!important;">
            <small class="text-secondary">
                <i class="bi bi-info-circle text-info me-1"></i>
                Persentase kehadiran dihitung berdasarkan 5 pertemuan
                pada setiap mata kuliah.
            </small>
        </div>

    </div>

    <div class="text-center mt-4">
        <small class="text-secondary">
            © <?= date('Y'); ?> NEXORA University.
            Academic Information System.
        </small>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
