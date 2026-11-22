
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "koneksi.php";

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
// VALIDASI ID JADWAL
// =====================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: data_jadwal.php");
    exit;
}

$id_jadwal = (int) $_GET['id'];

if ($id_jadwal <= 0) {
    header("Location: data_jadwal.php");
    exit;
}

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// AMBIL DETAIL JADWAL
// =====================================
$stmt_jadwal = mysqli_prepare($conn, "
    SELECT
        j.id_jadwal,
        j.kode_mk,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        j.ruangan,
        j.nid,
        m.nama_mk,
        m.sks,
        d.nama_lengkap AS nama_dosen
    FROM jadwal j
    INNER JOIN matakuliah m
        ON j.kode_mk = m.kode_mk
    INNER JOIN dosen d
        ON j.nid = d.nid
    WHERE j.id_jadwal = ?
");

mysqli_stmt_bind_param($stmt_jadwal, "i", $id_jadwal);
mysqli_stmt_execute($stmt_jadwal);

mysqli_stmt_bind_result(
    $stmt_jadwal,
    $jadwal_id,
    $kode_mk,
    $hari,
    $jam_mulai,
    $jam_selesai,
    $ruangan,
    $nid,
    $nama_mk,
    $sks,
    $nama_dosen
);

if (!mysqli_stmt_fetch($stmt_jadwal)) {
    mysqli_stmt_close($stmt_jadwal);
    header("Location: data_jadwal.php");
    exit;
}

mysqli_stmt_close($stmt_jadwal);

// Simpan detail jadwal
$jadwal = [
    'id_jadwal' => $jadwal_id,
    'kode_mk' => $kode_mk,
    'hari' => $hari,
    'jam_mulai' => $jam_mulai,
    'jam_selesai' => $jam_selesai,
    'ruangan' => $ruangan,
    'nid' => $nid,
    'nama_mk' => $nama_mk,
    'sks' => $sks,
    'nama_dosen' => $nama_dosen
];

// =====================================
// PROSES TAMBAH MAHASISWA
// =====================================
if (isset($_POST['simpan'])) {

    $nim = trim($_POST['nim'] ?? '');

    if ($nim === '') {
        header("Location: jadwal_detail.php?id=$id_jadwal&status=input_kosong");
        exit;
    }

    // Cek mahasiswa sudah terdaftar
    $cek = mysqli_prepare($conn, "
        SELECT id_krs
        FROM krs
        WHERE id_jadwal = ?
        AND TRIM(nim) = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($cek, "is", $id_jadwal, $nim);
    mysqli_stmt_execute($cek);

    mysqli_stmt_store_result($cek);

    $sudah_ada = mysqli_stmt_num_rows($cek) > 0;

    mysqli_stmt_close($cek);

    if ($sudah_ada) {
        header("Location: jadwal_detail.php?id=$id_jadwal&status=sudah_ada");
        exit;
    }

    // Pastikan mahasiswa tersedia
    $cek_mhs = mysqli_prepare($conn, "
        SELECT nim
        FROM mahasiswa
        WHERE TRIM(nim) = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($cek_mhs, "s", $nim);
    mysqli_stmt_execute($cek_mhs);

    mysqli_stmt_store_result($cek_mhs);

    $mahasiswa_ada = mysqli_stmt_num_rows($cek_mhs) > 0;

    mysqli_stmt_close($cek_mhs);

    if (!$mahasiswa_ada) {
        header("Location: jadwal_detail.php?id=$id_jadwal&status=mahasiswa_tidak_ada");
        exit;
    }

    // Masukkan mahasiswa ke KRS
    $simpan = mysqli_prepare($conn, "
        INSERT INTO krs (nim, id_jadwal)
        VALUES (?, ?)
    ");

    mysqli_stmt_bind_param($simpan, "si", $nim, $id_jadwal);

    if (mysqli_stmt_execute($simpan)) {
        mysqli_stmt_close($simpan);
        header("Location: jadwal_detail.php?id=$id_jadwal&status=tambah");
        exit;
    } else {
        $pesan_error = mysqli_stmt_error($simpan);
        mysqli_stmt_close($simpan);
    }
}

// =====================================
// PROSES HAPUS MAHASISWA
// =====================================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {

    $id_krs = (int) $_GET['hapus'];

    if ($id_krs > 0) {

        $hapus = mysqli_prepare($conn, "
            DELETE FROM krs
            WHERE id_krs = ?
            AND id_jadwal = ?
        ");

        mysqli_stmt_bind_param($hapus, "ii", $id_krs, $id_jadwal);

        if (mysqli_stmt_execute($hapus) && mysqli_stmt_affected_rows($hapus) > 0) {
            mysqli_stmt_close($hapus);
            header("Location: jadwal_detail.php?id=$id_jadwal&status=hapus");
            exit;
        }

        mysqli_stmt_close($hapus);

        header("Location: jadwal_detail.php?id=$id_jadwal&status=gagal_hapus");
        exit;
    }
}

// =====================================
// AMBIL DAFTAR MAHASISWA DALAM KELAS
// =====================================
$query_mhs = mysqli_prepare($conn, "
    SELECT
        k.id_krs,
        m.nim,
        m.nama_lengkap,
        m.jurusan
    FROM krs k
    INNER JOIN mahasiswa m
        ON k.nim = m.nim
    WHERE k.id_jadwal = ?
    ORDER BY m.nim ASC
");

mysqli_stmt_bind_param($query_mhs, "i", $id_jadwal);
mysqli_stmt_execute($query_mhs);

mysqli_stmt_bind_result(
    $query_mhs,
    $id_krs,
    $nim_mhs,
    $nama_mhs,
    $jurusan_mhs
);

$daftar_mahasiswa = [];

while (mysqli_stmt_fetch($query_mhs)) {
    $daftar_mahasiswa[] = [
        'id_krs' => $id_krs,
        'nim' => $nim_mhs,
        'nama_lengkap' => $nama_mhs,
        'jurusan' => $jurusan_mhs
    ];
}

mysqli_stmt_close($query_mhs);

$total_mhs = count($daftar_mahasiswa);

// =====================================
// AMBIL SEMUA DATA MAHASISWA
// =====================================
$list_mahasiswa = mysqli_query($conn, "
    SELECT nim, nama_lengkap, jurusan
    FROM mahasiswa
    ORDER BY nama_lengkap ASC
");

if (!$list_mahasiswa) {
    die("Gagal mengambil data mahasiswa: " . e(mysqli_error($conn)));
}

$no = 1;
$nama_admin = $_SESSION['nama'] ?? 'Administrator';

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Detail Jadwal | NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #080715;
            --panel: #100e26;
            --cyan: #00f2fe;
            --purple: #9b5cff;
            --text: #edf3ff;
            --muted: #8c9bb8;
            --border: rgba(255,255,255,.09);
        }

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at 10% 0%, rgba(0,242,254,.08), transparent 30%),
                radial-gradient(circle at 90% 10%, rgba(155,92,255,.10), transparent 30%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(16,14,38,.96);
            border-bottom: 1px solid rgba(0,242,254,.20);
            backdrop-filter: blur(15px);
        }

        .brand {
            font-family: 'Orbitron', sans-serif;
            font-weight: 800;
            letter-spacing: 2px;
            color: white;
            text-decoration: none;
        }

        .brand span {
            color: var(--cyan);
        }

        .main-container {
            max-width: 1250px;
            margin: auto;
            padding: 35px 20px 60px;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 23px;
            font-weight: 700;
        }

        .page-subtitle {
            color: var(--muted);
            font-size: 13px;
        }

        .nex-card {
            background: linear-gradient(145deg, var(--panel), #15112e);
            border: 1px solid rgba(0,242,254,.16);
            border-radius: 17px;
            box-shadow: 0 15px 40px rgba(0,0,0,.18);
        }

        .card-heading {
            padding: 20px 23px;
            border-bottom: 1px solid var(--border);
        }

        .card-heading h5 {
            font-weight: 800;
            font-size: 15px;
            margin: 0;
        }

        .card-body-custom {
            padding: 24px;
        }

        .detail-label {
            color: var(--muted);
            font-size: 12px;
            margin-bottom: 6px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 700;
        }

        .detail-box {
            background: rgba(255,255,255,.035);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            height: 100%;
        }

        .neon-icon {
            color: var(--cyan);
            font-size: 19px;
        }

        .badge-neon {
            background: rgba(0,242,254,.10);
            color: var(--cyan);
            border: 1px solid rgba(0,242,254,.20);
            padding: 7px 12px;
            border-radius: 20px;
        }

        .btn-nexora {
            background: linear-gradient(100deg, #00cfe8, #7957ff);
            border: none;
            color: #071020;
            font-weight: 800;
            border-radius: 9px;
            padding: 10px 16px;
        }

        .btn-nexora:hover {
            color: white;
            opacity: .9;
        }

        .btn-back {
            background: rgba(255,255,255,.07);
            border: 1px solid var(--border);
            color: white;
            border-radius: 9px;
        }

        .btn-back:hover {
            background: rgba(255,255,255,.13);
            color: white;
        }

        .table {
            color: var(--text);
            margin: 0;
        }

        .table thead th {
            background: #1b2945;
            color: #b9c9e8;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
            padding: 15px;
            border-color: var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            background: transparent;
            color: #e2eaff;
            padding: 15px;
            border-color: var(--border);
            font-size: 13px;
        }

        .table tbody tr:hover td {
            background: rgba(0,242,254,.035);
        }

        .student-name {
            font-weight: 700;
        }

        .student-nim {
            color: var(--cyan);
            font-weight: 700;
        }

        .btn-delete {
            background: rgba(255,75,100,.12);
            border: 1px solid rgba(255,75,100,.25);
            color: #ff7186;
            font-size: 12px;
            border-radius: 7px;
        }

        .btn-delete:hover {
            background: #ff4d6d;
            color: white;
        }

        .modal-content {
            background: #111a30;
            color: white;
            border: 1px solid var(--border);
            border-radius: 15px;
        }

        .modal-header,
        .modal-footer {
            border-color: var(--border);
        }

        .form-select {
            background-color: #0b1428;
            color: white;
            border: 1px solid #30405f;
            padding: 12px;
        }

        .form-select:focus {
            background-color: #0b1428;
            color: white;
            border-color: var(--cyan);
            box-shadow: 0 0 0 .2rem rgba(0,242,254,.12);
        }

        .form-select option {
            background: #111a30;
            color: white;
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 25px;
            font-weight: 700;
            color: var(--cyan);
        }

        .empty-state {
            padding: 40px 15px !important;
            text-align: center;
            color: var(--muted) !important;
        }

        footer {
            color: #64748b;
            font-size: 12px;
        }

        @media(max-width: 576px) {
            .page-title {
                font-size: 18px;
            }

            .card-body-custom {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark px-3 px-md-4 py-3">
    <div class="container-fluid">

        <a class="navbar-brand brand" href="dashboard_admin.php">
            NEX<span>ORA</span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-3">

            <span class="text-secondary small d-none d-sm-block">
                <i class="bi bi-person-circle me-1"></i>
                <?= e($nama_admin); ?>
            </span>

            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
            </a>

        </div>
    </div>
</nav>

<main class="main-container">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <div class="page-title">
                <i class="bi bi-calendar-week neon-icon me-2"></i>
                DETAIL JADWAL KELAS
            </div>

            <div class="page-subtitle mt-2">
                Kelola peserta mahasiswa pada jadwal perkuliahan NEXORA University
            </div>
        </div>

        <a href="data_jadwal.php" class="btn btn-back btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Manajemen Jadwal
        </a>

    </div>

    <!-- NOTIFIKASI -->
    <?php if (isset($_GET['status'])): ?>

        <?php
        $status = $_GET['status'];

        $notifikasi = [
            'tambah' => ['success', 'Mahasiswa berhasil ditambahkan ke kelas.'],
            'hapus' => ['success', 'Mahasiswa berhasil dihapus dari kelas.'],
            'sudah_ada' => ['warning', 'Mahasiswa sudah terdaftar pada kelas ini.'],
            'mahasiswa_tidak_ada' => ['danger', 'Data mahasiswa tidak ditemukan.'],
            'input_kosong' => ['warning', 'Silakan pilih mahasiswa terlebih dahulu.'],
            'gagal_hapus' => ['danger', 'Mahasiswa gagal dihapus dari kelas.']
        ];
        ?>

        <?php if (isset($notifikasi[$status])): ?>

            <div class="alert alert-<?= e($notifikasi[$status][0]); ?> alert-dismissible fade show">
                <i class="bi bi-info-circle me-2"></i>
                <?= e($notifikasi[$status][1]); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

        <?php endif; ?>

    <?php endif; ?>

    <?php if (!empty($pesan_error)): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Gagal menambahkan mahasiswa: <?= e($pesan_error); ?>
        </div>

    <?php endif; ?>

    <!-- DETAIL JADWAL -->
    <div class="nex-card mb-4">

        <div class="card-heading d-flex align-items-center gap-2">
            <i class="bi bi-info-circle neon-icon"></i>
            <h5>Informasi Perkuliahan</h5>
        </div>

        <div class="card-body-custom">

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="detail-box">
                        <div class="detail-label">MATA KULIAH</div>

                        <div class="detail-value">
                            <?= e($jadwal['kode_mk']); ?> -
                            <?= e($jadwal['nama_mk']); ?>
                        </div>

                        <div class="mt-2">
                            <span class="badge-neon">
                                <?= e($jadwal['sks']); ?> SKS
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="detail-box">
                        <div class="detail-label">DOSEN PENGAMPU</div>

                        <div class="detail-value">
                            <?= e($jadwal['nid']); ?> -
                            <?= e($jadwal['nama_dosen']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-box">
                        <div class="detail-label">HARI PERKULIAHAN</div>

                        <div class="detail-value">
                            <i class="bi bi-calendar3 neon-icon me-2"></i>
                            <?= e($jadwal['hari']); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-box">
                        <div class="detail-label">WAKTU</div>

                        <div class="detail-value">
                            <i class="bi bi-clock neon-icon me-2"></i>
                            <?= e(date('H:i', strtotime($jadwal['jam_mulai']))); ?>
                            -
                            <?= e(date('H:i', strtotime($jadwal['jam_selesai']))); ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="detail-box">
                        <div class="detail-label">RUANGAN</div>

                        <div class="detail-value">
                            <i class="bi bi-door-open neon-icon me-2"></i>
                            <?= e($jadwal['ruangan']); ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- DAFTAR PESERTA -->
    <div class="nex-card">

        <div class="card-heading d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h5>
                    <i class="bi bi-people-fill neon-icon me-2"></i>
                    Peserta Perkuliahan
                </h5>

                <small class="text-secondary">
                    Daftar mahasiswa yang mengambil mata kuliah ini
                </small>
            </div>

            <button type="button"
                    class="btn btn-nexora"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambahMhs">

                <i class="bi bi-person-plus-fill me-2"></i>
                Tambah Mahasiswa

            </button>

        </div>

        <div class="card-body-custom">

            <div class="d-flex align-items-center gap-3 mb-3">

                <div class="stat-number">
                    <?= $total_mhs; ?>
                </div>

                <div>
                    <div class="fw-bold">Mahasiswa Terdaftar</div>
                    <small class="text-secondary">Total peserta kelas</small>
                </div>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Program Studi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($total_mhs > 0): ?>

                        <?php foreach ($daftar_mahasiswa as $mhs): ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <span class="student-nim">
                                        <?= e($mhs['nim']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="student-name">
                                        <?= e($mhs['nama_lengkap']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="badge-neon">
                                        <?= e($mhs['jurusan']); ?>
                                    </span>
                                </td>

                                <td class="text-center">

                                    <a href="jadwal_detail.php?id=<?= $id_jadwal; ?>&hapus=<?= (int)$mhs['id_krs']; ?>"
                                       class="btn btn-delete btn-sm"
                                       onclick="return confirm('Yakin ingin menghapus mahasiswa ini dari kelas?')">

                                        <i class="bi bi-trash me-1"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-state">

                                <i class="bi bi-people fs-1 d-block mb-3"></i>

                                <strong>Belum Ada Mahasiswa</strong>

                                <div class="small mt-1">
                                    Tambahkan mahasiswa untuk mengikuti perkuliahan ini.
                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>

            </div>

        </div>
    </div>

    <footer class="text-center mt-5">
        © <?= date('Y'); ?> NEXORA University. Academic Management System.
    </footer>

</main>

<!-- MODAL TAMBAH MAHASISWA -->
<div class="modal fade" id="modalTambahMhs" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="jadwal_detail.php?id=<?= $id_jadwal; ?>">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus neon-icon me-2"></i>
                        Tambahkan Mahasiswa
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body p-4">

                    <label class="form-label fw-bold">
                        Pilih Mahasiswa
                    </label>

                    <select name="nim" class="form-select" required>

                        <option value="">
                            -- Pilih Mahasiswa --
                        </option>

                        <?php while ($m = mysqli_fetch_assoc($list_mahasiswa)): ?>

                            <option value="<?= e(trim($m['nim'])); ?>">

                                <?= e($m['nim']); ?> -
                                <?= e($m['nama_lengkap']); ?>
                                (<?= e($m['jurusan']); ?>)

                            </option>

                        <?php endwhile; ?>

                    </select>

                    <small class="text-secondary d-block mt-2">
                        Mahasiswa yang sudah terdaftar pada kelas ini tidak dapat ditambahkan kembali.
                    </small>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-back"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            name="simpan"
                            class="btn btn-nexora">

                        <i class="bi bi-check-circle me-1"></i>
                        Tambahkan

                    </button>

                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
