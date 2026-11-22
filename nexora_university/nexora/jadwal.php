
<?php
session_start();
require_once "koneksi.php";

// ===============================
// CEK LOGIN ADMIN
// ===============================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

// Fungsi keamanan output
function e($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

$pesan = "";
$error = "";

// ===============================
// PROSES TAMBAH JADWAL
// ===============================
if (isset($_POST['simpan'])) {
    $kode_mk = $_POST['kode_mk'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $ruangan = $_POST['ruangan'];
    $nid = $_POST['nid'];

    $stmt = mysqli_prepare($conn, "
        INSERT INTO jadwal
        (kode_mk, hari, jam_mulai, jam_selesai, ruangan, nid)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $kode_mk,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $ruangan,
        $nid
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: jadwal.php?status=tambah");
        exit;
    } else {
        $error = "Gagal menambahkan jadwal: " . mysqli_stmt_error($stmt);
    }
}

// ===============================
// PROSES UPDATE JADWAL
// ===============================
if (isset($_POST['update'])) {
    $id_jadwal = (int)$_POST['id_jadwal'];
    $kode_mk = $_POST['kode_mk'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $ruangan = $_POST['ruangan'];
    $nid = $_POST['nid'];

    $stmt = mysqli_prepare($conn, "
        UPDATE jadwal SET
            kode_mk = ?,
            hari = ?,
            jam_mulai = ?,
            jam_selesai = ?,
            ruangan = ?,
            nid = ?
        WHERE id_jadwal = ?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $kode_mk,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $ruangan,
        $nid,
        $id_jadwal
    );

    if (mysqli_stmt_execute($stmt)) {
        header("Location: jadwal.php?status=update");
        exit;
    } else {
        $error = "Gagal memperbarui jadwal: " . mysqli_stmt_error($stmt);
    }
}

// ===============================
// PROSES HAPUS JADWAL
// ===============================
if (isset($_POST['hapus'])) {
    $id_jadwal = (int)$_POST['id_jadwal'];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM jadwal WHERE id_jadwal = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_jadwal);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: jadwal.php?status=hapus");
        exit;
    } else {
        $error = "Gagal menghapus jadwal. Pastikan jadwal tidak sedang digunakan.";
    }
}

// ===============================
// PESAN NOTIFIKASI
// ===============================
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'tambah') {
        $pesan = "Jadwal kuliah berhasil ditambahkan!";
    } elseif ($_GET['status'] == 'update') {
        $pesan = "Jadwal kuliah berhasil diperbarui!";
    } elseif ($_GET['status'] == 'hapus') {
        $pesan = "Jadwal kuliah berhasil dihapus!";
    }
}

// ===============================
// AMBIL DATA MASTER
// ===============================
$data_mk = mysqli_query(
    $conn,
    "SELECT * FROM matakuliah ORDER BY nama_mk ASC"
)->fetch_all(MYSQLI_ASSOC);

$data_dosen = mysqli_query(
    $conn,
    "SELECT * FROM dosen ORDER BY nama_lengkap ASC"
)->fetch_all(MYSQLI_ASSOC);

// ===============================
// AMBIL DATA JADWAL
// ===============================
$query = mysqli_query($conn, "
    SELECT
        jadwal.*,
        matakuliah.nama_mk,
        matakuliah.sks,
        dosen.nama_lengkap AS nama_dosen
    FROM jadwal
    INNER JOIN matakuliah
        ON jadwal.kode_mk = matakuliah.kode_mk
    INNER JOIN dosen
        ON jadwal.nid = dosen.nid
    ORDER BY
        FIELD(jadwal.hari, 'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
        jadwal.jam_mulai
");

$data_jadwal = $query->fetch_all(MYSQLI_ASSOC);

$daftar_hari = [
    'Senin', 'Selasa', 'Rabu', 'Kamis',
    'Jumat', 'Sabtu', 'Minggu'
];

$total_jadwal = count($data_jadwal);
$total_mk = count($data_mk);
$total_dosen = count($data_dosen);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Jadwal | NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background: #080714;
            color: #f4f5ff;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .main-container {
            max-width: 1450px;
            margin: auto;
            padding: 35px 25px;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 23px;
            font-weight: 700;
            color: #fff;
        }

        .page-subtitle {
            color: #8588a8;
            font-size: 14px;
        }

        .neon {
            color: #00e5ff;
        }

        .stat-card {
            background: #111027;
            border: 1px solid #38205c;
            border-radius: 17px;
            padding: 22px;
            height: 100%;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            background: #103043;
            color: #00e5ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            margin-bottom: 15px;
        }

        .stat-label {
            color: #888ba9;
            font-size: 13px;
        }

        .stat-number {
            font-size: 27px;
            font-weight: 800;
            color: #00e5ff;
        }

        .content-card {
            background: #111027;
            border: 1px solid #38205c;
            border-radius: 18px;
            overflow: hidden;
        }

        .content-header {
            padding: 22px;
            border-bottom: 1px solid #30264d;
        }

        .section-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }

        .table {
            color: #e5e6f5;
            margin-bottom: 0;
        }

        .table thead th {
            background: #17152f;
            color: #00e5ff;
            border-bottom: 1px solid #38205c;
            border-top: none;
            font-size: 11px;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 16px 13px;
        }

        .table tbody td {
            background: #111027;
            color: #d4d5e8;
            border-color: #292441;
            padding: 15px 13px;
            font-size: 12px;
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background: #191633;
        }

        .badge-hari {
            background: #172e49;
            color: #00e5ff;
            padding: 7px 11px;
            border-radius: 7px;
        }

        .badge-ruangan {
            background: #292044;
            color: #c6a4ff;
            padding: 7px 10px;
            border-radius: 7px;
        }

        .btn-add {
            background: linear-gradient(100deg, #00cfe8, #7862ff);
            border: none;
            color: #080714;
            font-weight: 800;
            border-radius: 9px;
            padding: 10px 16px;
        }

        .btn-add:hover {
            color: white;
            opacity: .9;
        }

        .btn-edit {
            background: #292044;
            border: 1px solid #7551bd;
            color: #c9a8ff;
        }

        .btn-delete {
            background: #401d36;
            border: 1px solid #a43c70;
            color: #ff8ab9;
        }

        .btn-edit:hover,
        .btn-delete:hover {
            color: white;
        }

        .modal-content {
            background: #111027;
            color: white;
            border: 1px solid #49316e;
            border-radius: 15px;
        }

        .modal-header,
        .modal-footer {
            border-color: #30264d;
        }

        .form-label {
            color: #bfc1d9;
            font-size: 13px;
        }

        .form-control,
        .form-select {
            background: #0b0a1b;
            border: 1px solid #393354;
            color: white;
            border-radius: 8px;
            padding: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            background: #0b0a1b;
            color: white;
            border-color: #00d9f5;
            box-shadow: 0 0 0 3px #00d9f522;
        }

        .form-select option {
            background: #111027;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px !important;
            color: #8588a8 !important;
        }

        .empty-state i {
            font-size: 40px;
            color: #00d9f5;
            display: block;
            margin-bottom: 12px;
        }

        .alert {
            border-radius: 10px;
            font-size: 13px;
        }

        @media(max-width: 768px) {
            .main-container {
                padding: 25px 12px;
            }
        }
    </style>
</head>

<body>

<div class="main-container">

    <!-- HEADER HALAMAN -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <div class="page-title">
                <i class="bi bi-calendar-week neon me-2"></i>
                MANAJEMEN JADWAL
            </div>
            <div class="page-subtitle mt-2">
                Kelola jadwal perkuliahan dan dosen pengampu NEXORA University.
            </div>
        </div>

        <a href="dashboard_admin.php" class="btn btn-outline-info btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <!-- NOTIFIKASI -->
    <?php if ($pesan): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= e($pesan); ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <?= e($error); ?>
        </div>
    <?php endif; ?>

    <!-- RINGKASAN -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-calendar2-week"></i>
                </div>
                <div class="stat-label">Total Jadwal Kuliah</div>
                <div class="stat-number"><?= $total_jadwal; ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="stat-label">Mata Kuliah Terdaftar</div>
                <div class="stat-number"><?= $total_mk; ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="bi bi-person-video3"></i>
                </div>
                <div class="stat-label">Dosen Terdaftar</div>
                <div class="stat-number"><?= $total_dosen; ?></div>
            </div>
        </div>

    </div>

    <!-- TABEL JADWAL -->
    <div class="content-card">

        <div class="content-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="section-title">
                    <i class="bi bi-list-ul neon me-2"></i>
                    DATA JADWAL PERKULIAHAN
                </div>
                <div class="page-subtitle mt-2">
                    Daftar jadwal mata kuliah dan dosen pengampu.
                </div>
            </div>

            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-1"></i>
                Tambah Jadwal
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID</th>
                        <th>Kode MK</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Dosen Pengampu</th>
                        <th>Hari</th>
                        <th>Waktu</th>
                        <th>Ruangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($data_jadwal) > 0): ?>

                    <?php $no = 1; ?>

                    <?php foreach ($data_jadwal as $data): ?>

                        <tr>
                            <td><?= $no++; ?></td>

                            <td class="neon fw-bold">
                                #<?= e($data['id_jadwal']); ?>
                            </td>

                            <td><?= e($data['kode_mk']); ?></td>

                            <td class="fw-semibold">
                                <?= e($data['nama_mk']); ?>
                            </td>

                            <td><?= e($data['sks']); ?> SKS</td>

                            <td><?= e($data['nama_dosen']); ?></td>

                            <td>
                                <span class="badge-hari">
                                    <?= e($data['hari']); ?>
                                </span>
                            </td>

                            <td>
                                <?= date('H:i', strtotime($data['jam_mulai'])); ?>
                                -
                                <?= date('H:i', strtotime($data['jam_selesai'])); ?>
                            </td>

                            <td>
                                <span class="badge-ruangan">
                                    <?= e($data['ruangan']); ?>
                                </span>
                            </td>

                            <td class="text-nowrap">

                                <button
                                    class="btn btn-edit btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit<?= (int)$data['id_jadwal']; ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">

                                    <input type="hidden" name="id_jadwal"
                                        value="<?= (int)$data['id_jadwal']; ?>">

                                    <button type="submit" name="hapus"
                                        class="btn btn-delete btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </td>
                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade"
                            id="modalEdit<?= (int)$data['id_jadwal']; ?>"
                            tabindex="-1">

                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">

                                    <form method="POST">

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="bi bi-pencil-square neon me-2"></i>
                                                Edit Jadwal Kuliah
                                            </h5>

                                            <button type="button"
                                                class="btn-close btn-close-white"
                                                data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <input type="hidden" name="id_jadwal"
                                                value="<?= (int)$data['id_jadwal']; ?>">

                                            <div class="row g-3">

                                                <div class="col-md-6">
                                                    <label class="form-label">Mata Kuliah</label>

                                                    <select name="kode_mk" class="form-select" required>
                                                        <option value="">Pilih Mata Kuliah</option>

                                                        <?php foreach ($data_mk as $mk): ?>
                                                            <option value="<?= e($mk['kode_mk']); ?>"
                                                                <?= $mk['kode_mk'] == $data['kode_mk'] ? 'selected' : ''; ?>>
                                                                <?= e($mk['kode_mk']); ?> -
                                                                <?= e($mk['nama_mk']); ?>
                                                            </option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Dosen Pengampu</label>

                                                    <select name="nid" class="form-select" required>
                                                        <option value="">Pilih Dosen</option>

                                                        <?php foreach ($data_dosen as $dsn): ?>
                                                            <option value="<?= e($dsn['nid']); ?>"
                                                                <?= $dsn['nid'] == $data['nid'] ? 'selected' : ''; ?>>
                                                                <?= e($dsn['nid']); ?> -
                                                                <?= e($dsn['nama_lengkap']); ?>
                                                            </option>
                                                        <?php endforeach; ?>

                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Hari</label>

                                                    <select name="hari" class="form-select" required>
                                                        <?php foreach ($daftar_hari as $hari): ?>
                                                            <option value="<?= $hari; ?>"
                                                                <?= $hari == $data['hari'] ? 'selected' : ''; ?>>
                                                                <?= $hari; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Jam Mulai</label>
                                                    <input type="time" name="jam_mulai"
                                                        class="form-control"
                                                        value="<?= e($data['jam_mulai']); ?>" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Jam Selesai</label>
                                                    <input type="time" name="jam_selesai"
                                                        class="form-control"
                                                        value="<?= e($data['jam_selesai']); ?>" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label">Ruangan</label>
                                                    <input type="text" name="ruangan"
                                                        class="form-control" maxlength="5"
                                                        value="<?= e($data['ruangan']); ?>" required>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>

                                            <button type="submit" name="update" class="btn btn-add">
                                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="10" class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <div class="fw-bold">Belum Ada Jadwal Kuliah</div>
                            <div class="mt-2">
                                Silakan tambahkan jadwal perkuliahan terlebih dahulu.
                            </div>
                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-calendar-plus neon me-2"></i>
                        Tambah Jadwal Kuliah
                    </h5>

                    <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Mata Kuliah</label>

                            <select name="kode_mk" class="form-select" required>
                                <option value="">Pilih Mata Kuliah</option>

                                <?php foreach ($data_mk as $mk): ?>
                                    <option value="<?= e($mk['kode_mk']); ?>">
                                        <?= e($mk['kode_mk']); ?> -
                                        <?= e($mk['nama_mk']); ?>
                                        (<?= e($mk['sks']); ?> SKS)
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dosen Pengampu</label>

                            <select name="nid" class="form-select" required>
                                <option value="">Pilih Dosen</option>

                                <?php foreach ($data_dosen as $dsn): ?>
                                    <option value="<?= e($dsn['nid']); ?>">
                                        <?= e($dsn['nid']); ?> -
                                        <?= e($dsn['nama_lengkap']); ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Hari</label>

                            <select name="hari" class="form-select" required>
                                <option value="">Pilih Hari</option>

                                <?php foreach ($daftar_hari as $hari): ?>
                                    <option value="<?= $hari; ?>">
                                        <?= $hari; ?>
                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jam Mulai</label>
                            <input type="time" name="jam_mulai"
                                class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jam Selesai</label>
                            <input type="time" name="jam_selesai"
                                class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ruangan</label>
                            <input type="text" name="ruangan"
                                class="form-control" maxlength="5"
                                placeholder="Contoh: R301" required>
                        </div>

                    </div>

                    <?php if (count($data_mk) == 0 || count($data_dosen) == 0): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Pastikan data mata kuliah dan dosen sudah ditambahkan sebelum membuat jadwal.
                        </div>
                    <?php endif; ?>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>

                    <button type="submit" name="simpan" class="btn btn-add"
                        <?= (count($data_mk) == 0 || count($data_dosen) == 0) ? 'disabled' : ''; ?>>
                        <i class="bi bi-plus-circle me-1"></i>
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
