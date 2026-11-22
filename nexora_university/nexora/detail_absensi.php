
<?php
session_start();
require_once 'koneksi.php';

date_default_timezone_set('Asia/Jakarta');

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

$nid = $_SESSION['username'];

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// TOKEN KEAMANAN FORM
// =====================================
if (empty($_SESSION['csrf_absensi'])) {
    $_SESSION['csrf_absensi'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_absensi'];

// =====================================
// VALIDASI ID JADWAL
// =====================================
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: dashboard_dosen.php");
    exit;
}

$id_jadwal = (int) $_GET['id'];

// =====================================
// AMBIL DETAIL JADWAL DOSEN
// HANYA JADWAL MILIK DOSEN LOGIN
// =====================================
$stmt_jadwal = mysqli_prepare($conn, "
    SELECT
        jadwal.id_jadwal,
        jadwal.kode_mk,
        jadwal.hari,
        jadwal.jam_mulai,
        jadwal.jam_selesai,
        jadwal.ruangan,
        matakuliah.nama_mk,
        matakuliah.sks,
        dosen.nama_lengkap AS nama_dosen
    FROM jadwal
    INNER JOIN matakuliah
        ON jadwal.kode_mk = matakuliah.kode_mk
    INNER JOIN dosen
        ON jadwal.nid = dosen.nid
    WHERE jadwal.id_jadwal = ?
      AND jadwal.nid = ?
");

mysqli_stmt_bind_param($stmt_jadwal, "is", $id_jadwal, $nid);
mysqli_stmt_execute($stmt_jadwal);

$result_jadwal = mysqli_stmt_get_result($stmt_jadwal);
$jadwal = mysqli_fetch_assoc($result_jadwal);

if (!$jadwal) {
    http_response_code(403);
    exit("Jadwal tidak ditemukan atau bukan milik akun dosen ini.");
}

// =====================================
// PROSES ABSENSI DAN PEMBATALAN
// MENGGUNAKAN POST
// =====================================
$pesan = "";
$jenis_pesan = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($csrf, $token)) {
        exit("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $aksi = $_POST['aksi'] ?? '';
    $pertemuan = $_POST['pertemuan'] ?? '';
    $id_krs = $_POST['id_krs'] ?? '';

    if (
        !in_array($pertemuan, ['1', '2', '3', '4', '5'], true) ||
        !ctype_digit((string)$id_krs) ||
        !in_array($aksi, ['absen', 'batal'], true)
    ) {
        exit("Data absensi tidak valid.");
    }

    $id_krs = (int) $id_krs;
    $kolom = "pertemuan" . $pertemuan;

    if ($aksi === 'absen') {

        // Catat jam hadir jika sebelumnya masih kosong.
        $sql = "
            UPDATE krs
            SET $kolom = ?
            WHERE id_krs = ?
              AND id_jadwal = ?
              AND $kolom IS NULL
        ";

        $jam_sekarang = date('H:i:s');

        $stmt_update = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt_update,
            "sii",
            $jam_sekarang,
            $id_krs,
            $id_jadwal
        );

        mysqli_stmt_execute($stmt_update);

        if (mysqli_stmt_affected_rows($stmt_update) > 0) {
            $pesan = "Absensi berhasil dicatat pada pukul " . $jam_sekarang;
        } else {
            $pesan = "Absensi tidak berubah. Data mungkin sudah tercatat.";
            $jenis_pesan = "warning";
        }

    } elseif ($aksi === 'batal') {

        // Batalkan absensi pertemuan yang dipilih.
        $sql = "
            UPDATE krs
            SET $kolom = NULL
            WHERE id_krs = ?
              AND id_jadwal = ?
              AND $kolom IS NOT NULL
        ";

        $stmt_update = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt_update,
            "ii",
            $id_krs,
            $id_jadwal
        );

        mysqli_stmt_execute($stmt_update);

        if (mysqli_stmt_affected_rows($stmt_update) > 0) {
            $pesan = "Absensi berhasil dibatalkan.";
        } else {
            $pesan = "Tidak ada absensi yang dapat dibatalkan.";
            $jenis_pesan = "warning";
        }
    }
}

// =====================================
// AMBIL DATA MAHASISWA DALAM KELAS
// =====================================
$stmt_mhs = mysqli_prepare($conn, "
    SELECT
        krs.id_krs,
        krs.nim,
        krs.pertemuan1,
        krs.pertemuan2,
        krs.pertemuan3,
        krs.pertemuan4,
        krs.pertemuan5,
        mahasiswa.nama_lengkap,
        mahasiswa.jurusan
    FROM krs
    INNER JOIN mahasiswa
        ON krs.nim = mahasiswa.nim
    WHERE krs.id_jadwal = ?
    ORDER BY mahasiswa.nim ASC
");

mysqli_stmt_bind_param($stmt_mhs, "i", $id_jadwal);
mysqli_stmt_execute($stmt_mhs);

$result_mhs = mysqli_stmt_get_result($stmt_mhs);

$data_mahasiswa = [];

while ($row = mysqli_fetch_assoc($result_mhs)) {
    $data_mahasiswa[] = $row;
}

// =====================================
// HITUNG STATISTIK KEHADIRAN
// =====================================
$total_mhs = count($data_mahasiswa);
$total_hadir = 0;
$total_belum = 0;

foreach ($data_mahasiswa as $mhs) {
    for ($i = 1; $i <= 5; $i++) {
        $kolom = "pertemuan" . $i;

        if (!empty($mhs[$kolom])) {
            $total_hadir++;
        } else {
            $total_belum++;
        }
    }
}

$total_kesempatan = $total_mhs * 5;

$persentase = $total_kesempatan > 0
    ? round(($total_hadir / $total_kesempatan) * 100, 1)
    : 0;

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Absensi — NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(0,242,254,0.07), transparent 30%),
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
            color: #00f2fe !important;
            letter-spacing: 1px;
        }

        .card-nexora {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.23);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .welcome-card {
            background: linear-gradient(
                120deg,
                rgba(0,242,254,0.12),
                rgba(120,80,255,0.13),
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

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
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
            border-bottom: 1px solid rgba(0,242,254,0.3);
            padding: 15px;
        }

        .table tbody td {
            padding: 13px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
        }

        .btn-absen {
            border: 1px solid #00f2fe;
            color: #00f2fe;
            background: transparent;
            font-size: 12px;
            border-radius: 8px;
        }

        .btn-absen:hover {
            background: #00f2fe;
            color: #080715;
        }

        .badge-hadir {
            background: rgba(25,135,84,0.2);
            color: #4ade80;
            border: 1px solid rgba(74,222,128,0.35);
            border-radius: 8px;
            font-size: 12px;
            padding: 8px;
        }

        .btn-batal {
            background: transparent;
            border: none;
            color: inherit;
            padding: 0;
            font-size: 12px;
        }

        .btn-batal:hover {
            color: #fff;
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

        <div class="d-flex align-items-center gap-3">

            <div class="text-end d-none d-sm-block">
                <div class="small fw-bold">
                    <?= e($_SESSION['nama'] ?? 'Dosen'); ?>
                </div>

                <div class="small text-secondary">
                    Portal Dosen
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

    <!-- TOMBOL KEMBALI -->
    <div class="mb-3">

        <a href="dashboard_dosen.php" class="btn btn-outline-info btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Dashboard
        </a>

    </div>


    <!-- INFORMASI KELAS -->
    <div class="card-nexora welcome-card p-4 mb-4">

        <div class="text-cyan small fw-bold mb-2">
            LECTURER ATTENDANCE PORTAL
        </div>

        <h4 class="fw-bold mb-3">
            <i class="bi bi-journal-check text-cyan me-2"></i>
            Lembar Absensi Perkuliahan
        </h4>

        <div class="row g-3">

            <div class="col-md-6">

                <div class="small text-secondary">Mata Kuliah</div>

                <div class="fw-bold">
                    <?= e($jadwal['kode_mk']); ?> -
                    <?= e($jadwal['nama_mk']); ?>
                </div>

                <div class="small text-cyan">
                    <?= e($jadwal['sks']); ?> SKS
                </div>

                <div class="small text-secondary mt-3">Dosen Pengampu</div>

                <div class="fw-semibold">
                    <?= e($jadwal['nama_dosen']); ?>
                </div>

            </div>

            <div class="col-md-6">

                <div class="small text-secondary">Hari / Waktu</div>

                <div class="fw-bold">
                    <?= e($jadwal['hari']); ?>,
                    <?= e(date('H:i', strtotime($jadwal['jam_mulai']))); ?>
                    -
                    <?= e(date('H:i', strtotime($jadwal['jam_selesai']))); ?>
                    WIB
                </div>

                <div class="small text-secondary mt-3">Ruangan</div>

                <span class="badge bg-info text-dark">
                    <?= e($jadwal['ruangan']); ?>
                </span>

            </div>

        </div>

    </div>


    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>

        <div class="alert alert-<?= e($jenis_pesan); ?> alert-dismissible fade show" role="alert">

            <i class="bi bi-info-circle-fill me-2"></i>
            <?= e($pesan); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>


    <!-- STATISTIK ABSENSI -->
    <div class="row g-3 mb-4">

        <div class="col-6 col-lg-3">

            <div class="card-nexora p-3 h-100">

                <div class="stat-icon mb-3">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div class="text-secondary small">
                    Jumlah Mahasiswa
                </div>

                <h3 class="fw-bold mb-0">
                    <?= $total_mhs; ?>
                </h3>

            </div>

        </div>


        <div class="col-6 col-lg-3">

            <div class="card-nexora p-3 h-100">

                <div class="stat-icon mb-3">
                    <i class="bi bi-person-check-fill"></i>
                </div>

                <div class="text-secondary small">
                    Total Kehadiran
                </div>

                <h3 class="fw-bold text-success mb-0">
                    <?= $total_hadir; ?>
                </h3>

            </div>

        </div>


        <div class="col-6 col-lg-3">

            <div class="card-nexora p-3 h-100">

                <div class="stat-icon mb-3">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="text-secondary small">
                    Belum Absen
                </div>

                <h3 class="fw-bold text-warning mb-0">
                    <?= $total_belum; ?>
                </h3>

            </div>

        </div>


        <div class="col-6 col-lg-3">

            <div class="card-nexora p-3 h-100">

                <div class="stat-icon mb-3">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

                <div class="text-secondary small">
                    Persentase Kehadiran
                </div>

                <h3 class="fw-bold text-cyan mb-0">
                    <?= $persentase; ?>%
                </h3>

            </div>

        </div>

    </div>


    <!-- TABEL ABSENSI -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

            <div>

                <h5 class="section-title text-white mb-1">
                    <i class="bi bi-people-fill text-cyan me-2"></i>
                    DAFTAR KEHADIRAN MAHASISWA
                </h5>

                <div class="small text-secondary">
                    Klik tombol Absen untuk mencatat jam kehadiran mahasiswa.
                </div>

            </div>

            <span class="badge bg-info text-dark">
                <?= $total_mhs; ?> Mahasiswa
            </span>

        </div>


        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>

                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <th class="text-center">
                                Pertemuan <?= $i; ?>
                            </th>
                        <?php endfor; ?>

                    </tr>

                </thead>

                <tbody>

                    <?php if ($total_mhs > 0): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_mahasiswa as $mhs): ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td class="text-cyan fw-bold">
                                    <?= e($mhs['nim']); ?>
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        <?= e($mhs['nama_lengkap']); ?>
                                    </div>

                                    <small class="text-secondary">
                                        <?= e($mhs['jurusan']); ?>
                                    </small>
                                </td>


                                <?php for ($i = 1; $i <= 5; $i++): ?>

                                    <?php
                                    $kolom = "pertemuan" . $i;
                                    $jam_absen = $mhs[$kolom];
                                    ?>

                                    <td class="text-center">

                                        <?php if (empty($jam_absen)): ?>

                                            <!-- FORM ABSEN -->
                                            <form method="POST"
                                                  action="detail_absensi.php?id=<?= $id_jadwal; ?>"
                                                  onsubmit="return confirm('Catat kehadiran <?= e($mhs['nama_lengkap']); ?> pada pertemuan <?= $i; ?>?');">

                                                <input type="hidden" name="csrf_token"
                                                       value="<?= e($csrf); ?>">

                                                <input type="hidden" name="aksi"
                                                       value="absen">

                                                <input type="hidden" name="pertemuan"
                                                       value="<?= $i; ?>">

                                                <input type="hidden" name="id_krs"
                                                       value="<?= (int)$mhs['id_krs']; ?>">

                                                <button type="submit"
                                                        class="btn btn-absen btn-sm w-100">

                                                    <i class="bi bi-hand-index-thumb me-1"></i>
                                                    Absen

                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <!-- ABSENSI SUDAH TERCATAT -->
                                            <form method="POST"
                                                  action="detail_absensi.php?id=<?= $id_jadwal; ?>"
                                                  onsubmit="return confirm('Batalkan absensi <?= e($mhs['nama_lengkap']); ?> pada pertemuan <?= $i; ?>?');">

                                                <input type="hidden" name="csrf_token"
                                                       value="<?= e($csrf); ?>">

                                                <input type="hidden" name="aksi"
                                                       value="batal">

                                                <input type="hidden" name="pertemuan"
                                                       value="<?= $i; ?>">

                                                <input type="hidden" name="id_krs"
                                                       value="<?= (int)$mhs['id_krs']; ?>">

                                                <button type="submit" class="btn-batal">

                                                    <span class="badge-hadir d-inline-block">

                                                        <i class="bi bi-check-circle-fill me-1"></i>

                                                        <?= e(date('H:i:s', strtotime($jam_absen))); ?>

                                                        <small class="d-block mt-1">
                                                            Batalkan
                                                        </small>

                                                    </span>

                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </td>

                                <?php endfor; ?>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="8" class="text-center py-5">

                                <i class="bi bi-people text-secondary"
                                   style="font-size: 40px;"></i>

                                <div class="text-secondary mt-2">
                                    Belum ada mahasiswa yang terdaftar pada kelas ini.
                                </div>

                                <small class="text-secondary">
                                    Pastikan mahasiswa sudah memiliki data KRS untuk jadwal ini.
                                </small>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Lecturer Attendance Portal.
    </footer>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
