
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
    header("Location: login.php");
    exit;
}

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// =====================================
// PROSES TAMBAH JADWAL
// =====================================
if (isset($_POST['tambah'])) {

    $kode_mk    = trim($_POST['kode_mk'] ?? '');
    $nid        = trim($_POST['nid'] ?? '');
    $hari       = $_POST['hari'] ?? '';
    $jam_mulai  = $_POST['jam_mulai'] ?? '';
    $jam_selesai = $_POST['jam_selesai'] ?? '';
    $ruangan    = trim($_POST['ruangan'] ?? '');

    $hari_valid = [
        'Senin', 'Selasa', 'Rabu', 'Kamis',
        'Jumat', 'Sabtu', 'Minggu'
    ];

    if (
        $kode_mk === '' || $nid === '' || $ruangan === '' ||
        !in_array($hari, $hari_valid, true) ||
        $jam_mulai === '' || $jam_selesai === '' ||
        $jam_mulai >= $jam_selesai
    ) {
        header("Location: data_jadwal.php?status=input_salah");
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        INSERT INTO jadwal
            (kode_mk, nid, hari, jam_mulai, jam_selesai, ruangan)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $kode_mk,
        $nid,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $ruangan
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: data_jadwal.php?status=tambah");
        exit;
    }

    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    header("Location: data_jadwal.php?status=gagal&pesan=" . urlencode($error));
    exit;
}

// =====================================
// PROSES EDIT JADWAL
// =====================================
if (isset($_POST['edit'])) {

    $id_jadwal  = (int)($_POST['id_jadwal'] ?? 0);
    $kode_mk    = trim($_POST['kode_mk'] ?? '');
    $nid        = trim($_POST['nid'] ?? '');
    $hari       = $_POST['hari'] ?? '';
    $jam_mulai  = $_POST['jam_mulai'] ?? '';
    $jam_selesai = $_POST['jam_selesai'] ?? '';
    $ruangan    = trim($_POST['ruangan'] ?? '');

    $hari_valid = [
        'Senin', 'Selasa', 'Rabu', 'Kamis',
        'Jumat', 'Sabtu', 'Minggu'
    ];

    if (
        $id_jadwal <= 0 || $kode_mk === '' ||
        $nid === '' || $ruangan === '' ||
        !in_array($hari, $hari_valid, true) ||
        $jam_mulai === '' || $jam_selesai === '' ||
        $jam_mulai >= $jam_selesai
    ) {
        header("Location: data_jadwal.php?status=input_salah");
        exit;
    }

    $stmt = mysqli_prepare($conn, "
        UPDATE jadwal
        SET
            kode_mk = ?,
            nid = ?,
            hari = ?,
            jam_mulai = ?,
            jam_selesai = ?,
            ruangan = ?
        WHERE id_jadwal = ?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $kode_mk,
        $nid,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $ruangan,
        $id_jadwal
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        header("Location: data_jadwal.php?status=edit");
        exit;
    }

    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    header("Location: data_jadwal.php?status=gagal&pesan=" . urlencode($error));
    exit;
}

// =====================================
// PROSES HAPUS JADWAL
// =====================================
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {

    $id_jadwal = (int)$_GET['hapus'];

    $stmt = mysqli_prepare($conn, "
        DELETE FROM jadwal
        WHERE id_jadwal = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id_jadwal);

    if (mysqli_stmt_execute($stmt)) {

        $berhasil = mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($berhasil) {
            header("Location: data_jadwal.php?status=hapus");
        } else {
            header("Location: data_jadwal.php?status=tidak_ditemukan");
        }

        exit;
    }

    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);

    header("Location: data_jadwal.php?status=gagal_hapus&pesan=" . urlencode($error));
    exit;
}

// =====================================
// AMBIL DATA JADWAL
// =====================================
$query_jadwal = mysqli_query($conn, "
    SELECT
        j.id_jadwal,
        j.kode_mk,
        j.nid,
        j.hari,
        j.jam_mulai,
        j.jam_selesai,
        j.ruangan,
        m.nama_mk,
        m.sks,
        d.nama_lengkap AS nama_dosen,
        (
            SELECT COUNT(*)
            FROM krs k
            WHERE k.id_jadwal = j.id_jadwal
        ) AS total_mahasiswa
    FROM jadwal j
    INNER JOIN matakuliah m
        ON j.kode_mk = m.kode_mk
    INNER JOIN dosen d
        ON j.nid = d.nid
    ORDER BY
        FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
        j.jam_mulai ASC
");

if (!$query_jadwal) {
    die("Gagal mengambil data jadwal: " . e(mysqli_error($conn)));
}

// =====================================
// AMBIL DATA MATA KULIAH
// =====================================
$query_mk = mysqli_query($conn, "
    SELECT kode_mk, nama_mk, sks
    FROM matakuliah
    ORDER BY nama_mk ASC
");

// =====================================
// AMBIL DATA DOSEN
// =====================================
$query_dosen = mysqli_query($conn, "
    SELECT nid, nama_lengkap
    FROM dosen
    ORDER BY nama_lengkap ASC
");

if (!$query_mk || !$query_dosen) {
    die("Gagal mengambil data referensi: " . e(mysqli_error($conn)));
}

$total_jadwal = mysqli_num_rows($query_jadwal);
$nama_admin = $_SESSION['nama'] ?? 'Administrator';

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Jadwal | NEXORA University</title>

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
            max-width: 1350px;
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
            padding: 23px;
        }

        .neon-icon {
            color: var(--cyan);
            font-size: 19px;
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 27px;
            font-weight: 700;
            color: var(--cyan);
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
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
            letter-spacing: .5px;
            padding: 15px 12px;
            border-color: var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            background: transparent;
            color: #e2eaff;
            padding: 14px 12px;
            border-color: var(--border);
            font-size: 12px;
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background: rgba(0,242,254,.035);
        }

        .course-name {
            font-weight: 700;
        }

        .muted-text {
            color: var(--muted);
            font-size: 11px;
        }

        .badge-neon {
            background: rgba(0,242,254,.10);
            color: var(--cyan);
            border: 1px solid rgba(0,242,254,.20);
            padding: 6px 10px;
            border-radius: 20px;
        }

        .badge-purple {
            background: rgba(155,92,255,.12);
            color: #c5a4ff;
            border: 1px solid rgba(155,92,255,.22);
            padding: 6px 10px;
            border-radius: 20px;
        }

        .btn-action {
            font-size: 11px;
            border-radius: 7px;
            margin: 2px;
        }

        .btn-edit {
            background: rgba(255,193,7,.10);
            color: #ffd166;
            border: 1px solid rgba(255,193,7,.25);
        }

        .btn-edit:hover {
            background: #ffc107;
            color: #111;
        }

        .btn-delete {
            background: rgba(255,75,100,.10);
            color: #ff7186;
            border: 1px solid rgba(255,75,100,.25);
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

        .modal-header, .modal-footer {
            border-color: var(--border);
        }

        .form-control, .form-select {
            background-color: #0b1428;
            color: white;
            border: 1px solid #30405f;
            padding: 11px 12px;
        }

        .form-control:focus, .form-select:focus {
            background-color: #0b1428;
            color: white;
            border-color: var(--cyan);
            box-shadow: 0 0 0 .2rem rgba(0,242,254,.12);
        }

        .form-select option {
            background: #111a30;
            color: white;
        }

        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #cbd5e1;
        }

        .empty-state {
            text-align: center;
            padding: 45px 15px !important;
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
                padding: 15px;
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
                MANAJEMEN JADWAL
            </div>

            <div class="page-subtitle mt-2">
                Pengelolaan jadwal perkuliahan NEXORA University
            </div>
        </div>

        <a href="dashboard_admin.php" class="btn btn-back btn-sm px-3 py-2">
            <i class="bi bi-arrow-left me-1"></i>
            Dashboard Admin
        </a>

    </div>

    <!-- NOTIFIKASI -->
    <?php
    $status = $_GET['status'] ?? '';

    $notifikasi = [
        'tambah' => ['success', 'Jadwal berhasil ditambahkan.'],
        'edit' => ['success', 'Jadwal berhasil diperbarui.'],
        'hapus' => ['success', 'Jadwal berhasil dihapus.'],
        'input_salah' => ['warning', 'Periksa kembali data. Pastikan semua kolom terisi dan jam selesai setelah jam mulai.'],
        'tidak_ditemukan' => ['warning', 'Jadwal tidak ditemukan.'],
        'gagal' => ['danger', 'Jadwal gagal disimpan. Periksa kode mata kuliah, NID dosen, dan data lainnya.'],
        'gagal_hapus' => ['danger', 'Jadwal gagal dihapus. Jadwal mungkin masih digunakan dalam data KRS.']
    ];
    ?>

    <?php if (isset($notifikasi[$status])): ?>
        <div class="alert alert-<?= e($notifikasi[$status][0]); ?> alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>
            <?= e($notifikasi[$status][1]); ?>

            <?php if (!empty($_GET['pesan'])): ?>
                <div class="small mt-1">
                    <?= e($_GET['pesan']); ?>
                </div>
            <?php endif; ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- RINGKASAN -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="nex-card p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-calendar-check neon-icon fs-2"></i>

                    <div>
                        <div class="stat-number">
                            <?= $total_jadwal; ?>
                        </div>

                        <div class="stat-label">
                            Total Jadwal Perkuliahan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="nex-card p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-book neon-icon fs-2"></i>

                    <div>
                        <div class="stat-number">
                            <?= mysqli_num_rows($query_mk); ?>
                        </div>

                        <div class="stat-label">
                            Mata Kuliah Tersedia
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="nex-card p-4 h-100">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-person-workspace neon-icon fs-2"></i>

                    <div>
                        <div class="stat-number">
                            <?= mysqli_num_rows($query_dosen); ?>
                        </div>

                        <div class="stat-label">
                            Dosen Tersedia
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- TABEL JADWAL -->
    <div class="nex-card">

        <div class="card-heading d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h5>
                    <i class="bi bi-table neon-icon me-2"></i>
                    Data Jadwal Perkuliahan
                </h5>

                <small class="text-secondary">
                    Daftar seluruh jadwal yang telah dibuat
                </small>
            </div>

            <button type="button"
                    class="btn btn-nexora"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah">

                <i class="bi bi-plus-circle me-2"></i>
                Tambah Jadwal

            </button>

        </div>

        <div class="card-body-custom">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen Pengampu</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                            <th>SKS</th>
                            <th>Peserta</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($total_jadwal > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query_jadwal)): ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <div class="course-name">
                                        <?= e($row['nama_mk']); ?>
                                    </div>

                                    <div class="muted-text">
                                        <?= e($row['kode_mk']); ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="course-name">
                                        <?= e($row['nama_dosen']); ?>
                                    </div>

                                    <div class="muted-text">
                                        NID: <?= e($row['nid']); ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-neon">
                                        <?= e($row['hari']); ?>
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <?= e(date('H:i', strtotime($row['jam_mulai']))); ?>
                                    -
                                    <?= e(date('H:i', strtotime($row['jam_selesai']))); ?>
                                </td>

                                <td>
                                    <span class="badge-purple">
                                        <?= e($row['ruangan']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int)$row['sks']; ?> SKS
                                </td>

                                <td>
                                    <span class="badge-neon">
                                        <i class="bi bi-people me-1"></i>
                                        <?= (int)$row['total_mahasiswa']; ?>
                                    </span>
                                </td>

                                <td class="text-center text-nowrap">

                                    <!-- TOMBOL DETAIL PESERTA -->
                                    <a href="jadwal_detail.php?id=<?= (int)$row['id_jadwal']; ?>"
                                       class="btn btn-outline-info btn-sm btn-action">
                                        <i class="bi bi-eye me-1"></i>
                                        Detail Peserta
                                    </a>

                                    <!-- TOMBOL EDIT -->
                                    <button type="button"
                                            class="btn btn-edit btn-sm btn-action"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit<?= (int)$row['id_jadwal']; ?>">

                                        <i class="bi bi-pencil-square me-1"></i>
                                        Edit

                                    </button>

                                    <!-- TOMBOL HAPUS -->
                                    <a href="data_jadwal.php?hapus=<?= (int)$row['id_jadwal']; ?>"
                                       class="btn btn-delete btn-sm btn-action"
                                       onclick="return confirm('Yakin ingin menghapus jadwal ini? Jadwal yang masih memiliki data KRS tidak dapat dihapus.')">

                                        <i class="bi bi-trash me-1"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                            <!-- MODAL EDIT -->
                            <div class="modal fade"
                                 id="modalEdit<?= (int)$row['id_jadwal']; ?>"
                                 tabindex="-1">

                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <div class="modal-content">

                                        <form method="POST">

                                            <div class="modal-header">

                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square neon-icon me-2"></i>
                                                    Edit Jadwal Perkuliahan
                                                </h5>

                                                <button type="button"
                                                        class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal">
                                                </button>

                                            </div>

                                            <div class="modal-body p-4">

                                                <input type="hidden"
                                                       name="id_jadwal"
                                                       value="<?= (int)$row['id_jadwal']; ?>">

                                                <div class="row g-3">

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Mata Kuliah
                                                        </label>

                                                        <select name="kode_mk"
                                                                class="form-select"
                                                                required>

                                                            <?php
                                                            mysqli_data_seek($query_mk, 0);
                                                            while ($mk = mysqli_fetch_assoc($query_mk)):
                                                            ?>

                                                                <option value="<?= e($mk['kode_mk']); ?>"
                                                                    <?= $mk['kode_mk'] == $row['kode_mk'] ? 'selected' : ''; ?>>

                                                                    <?= e($mk['kode_mk']); ?> -
                                                                    <?= e($mk['nama_mk']); ?>

                                                                </option>

                                                            <?php endwhile; ?>

                                                        </select>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Dosen Pengampu
                                                        </label>

                                                        <select name="nid"
                                                                class="form-select"
                                                                required>

                                                            <?php
                                                            mysqli_data_seek($query_dosen, 0);
                                                            while ($dsn = mysqli_fetch_assoc($query_dosen)):
                                                            ?>

                                                                <option value="<?= e($dsn['nid']); ?>"
                                                                    <?= $dsn['nid'] == $row['nid'] ? 'selected' : ''; ?>>

                                                                    <?= e($dsn['nid']); ?> -
                                                                    <?= e($dsn['nama_lengkap']); ?>

                                                                </option>

                                                            <?php endwhile; ?>

                                                        </select>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Hari
                                                        </label>

                                                        <select name="hari" class="form-select" required>

                                                            <?php
                                                            $hari_list = [
                                                                'Senin', 'Selasa', 'Rabu',
                                                                'Kamis', 'Jumat', 'Sabtu', 'Minggu'
                                                            ];
                                                            foreach ($hari_list as $h):
                                                            ?>

                                                                <option value="<?= $h; ?>"
                                                                    <?= $h == $row['hari'] ? 'selected' : ''; ?>>
                                                                    <?= $h; ?>
                                                                </option>

                                                            <?php endforeach; ?>

                                                        </select>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Ruangan
                                                        </label>

                                                        <input type="text"
                                                               name="ruangan"
                                                               class="form-control"
                                                               value="<?= e($row['ruangan']); ?>"
                                                               maxlength="5"
                                                               required>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Jam Mulai
                                                        </label>

                                                        <input type="time"
                                                               name="jam_mulai"
                                                               class="form-control"
                                                               value="<?= e(substr($row['jam_mulai'], 0, 5)); ?>"
                                                               required>

                                                    </div>

                                                    <div class="col-md-6">

                                                        <label class="form-label">
                                                            Jam Selesai
                                                        </label>

                                                        <input type="time"
                                                               name="jam_selesai"
                                                               class="form-control"
                                                               value="<?= e(substr($row['jam_selesai'], 0, 5)); ?>"
                                                               required>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="modal-footer">

                                                <button type="button"
                                                        class="btn btn-back"
                                                        data-bs-dismiss="modal">
                                                    Batal
                                                </button>

                                                <button type="submit"
                                                        name="edit"
                                                        class="btn btn-nexora">

                                                    <i class="bi bi-save me-1"></i>
                                                    Simpan Perubahan

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>
                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9" class="empty-state">

                                <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>

                                <strong>Belum Ada Jadwal Perkuliahan</strong>

                                <div class="small mt-1">
                                    Silakan tambahkan jadwal perkuliahan baru.
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

<!-- MODAL TAMBAH JADWAL -->
<div class="modal fade" id="modalTambah" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-plus neon-icon me-2"></i>
                        Tambah Jadwal Perkuliahan
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body p-4">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Mata Kuliah
                            </label>

                            <select name="kode_mk" class="form-select" required>

                                <option value="">
                                    -- Pilih Mata Kuliah --
                                </option>

                                <?php
                                mysqli_data_seek($query_mk, 0);
                                while ($mk = mysqli_fetch_assoc($query_mk)):
                                ?>

                                    <option value="<?= e($mk['kode_mk']); ?>">
                                        <?= e($mk['kode_mk']); ?> -
                                        <?= e($mk['nama_mk']); ?>
                                        (<?= (int)$mk['sks']; ?> SKS)
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Dosen Pengampu
                            </label>

                            <select name="nid" class="form-select" required>

                                <option value="">
                                    -- Pilih Dosen --
                                </option>

                                <?php
                                mysqli_data_seek($query_dosen, 0);
                                while ($dsn = mysqli_fetch_assoc($query_dosen)):
                                ?>

                                    <option value="<?= e($dsn['nid']); ?>">
                                        <?= e($dsn['nid']); ?> -
                                        <?= e($dsn['nama_lengkap']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Hari Perkuliahan
                            </label>

                            <select name="hari" class="form-select" required>

                                <option value="">
                                    -- Pilih Hari --
                                </option>

                                <option>Senin</option>
                                <option>Selasa</option>
                                <option>Rabu</option>
                                <option>Kamis</option>
                                <option>Jumat</option>
                                <option>Sabtu</option>
                                <option>Minggu</option>

                            </select>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Ruangan
                            </label>

                            <input type="text"
                                   name="ruangan"
                                   class="form-control"
                                   maxlength="5"
                                   placeholder="Contoh: R101"
                                   required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Jam Mulai
                            </label>

                            <input type="time"
                                   name="jam_mulai"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Jam Selesai
                            </label>

                            <input type="time"
                                   name="jam_selesai"
                                   class="form-control"
                                   required>

                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-back"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            name="tambah"
                            class="btn btn-nexora">

                        <i class="bi bi-save me-1"></i>
                        Simpan Jadwal

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
