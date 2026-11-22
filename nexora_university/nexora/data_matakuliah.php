
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
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// =====================================
// REDIRECT STATUS
// =====================================
function redirectStatus($status) {
    header("Location: data_matakuliah.php?status=" . urlencode($status));
    exit;
}

// =====================================
// PROSES TAMBAH MATA KULIAH
// =====================================
if (isset($_POST['simpan'])) {

    $kode_mk = strtoupper(trim($_POST['kode_mk'] ?? ''));
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks     = (int)($_POST['sks'] ?? 0);

    if ($kode_mk === '' || $nama_mk === '' || $sks < 1 || $sks > 6) {
        redirectStatus('data_kosong');
    }

    if (strlen($kode_mk) > 5) {
        redirectStatus('kode_invalid');
    }

    try {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO matakuliah (kode_mk, nama_mk, sks)
            VALUES (?, ?, ?)
        ");

        mysqli_stmt_bind_param($stmt, "ssi", $kode_mk, $nama_mk, $sks);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        redirectStatus('tambah');

    } catch (mysqli_sql_exception $e) {
        redirectStatus('gagal_tambah');
    }
}

// =====================================
// PROSES EDIT MATA KULIAH
// =====================================
if (isset($_POST['update'])) {

    $kode_mk = strtoupper(trim($_POST['kode_mk'] ?? ''));
    $nama_mk = trim($_POST['nama_mk'] ?? '');
    $sks     = (int)($_POST['sks'] ?? 0);

    if ($kode_mk === '' || $nama_mk === '' || $sks < 1 || $sks > 6) {
        redirectStatus('data_kosong');
    }

    try {
        $stmt = mysqli_prepare($conn, "
            UPDATE matakuliah
            SET nama_mk = ?, sks = ?
            WHERE kode_mk = ?
        ");

        mysqli_stmt_bind_param($stmt, "sis", $nama_mk, $sks, $kode_mk);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        redirectStatus('update');

    } catch (mysqli_sql_exception $e) {
        redirectStatus('gagal_update');
    }
}

// =====================================
// PROSES HAPUS MATA KULIAH
// =====================================
if (isset($_GET['hapus'])) {

    $kode_mk = trim($_GET['hapus']);

    if ($kode_mk === '') {
        redirectStatus('gagal_hapus');
    }

    try {
        $stmt = mysqli_prepare($conn, "
            DELETE FROM matakuliah
            WHERE kode_mk = ?
        ");

        mysqli_stmt_bind_param($stmt, "s", $kode_mk);
        mysqli_stmt_execute($stmt);

        $terhapus = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($terhapus < 1) {
            redirectStatus('gagal_hapus');
        }

        redirectStatus('hapus');

    } catch (mysqli_sql_exception $e) {
        // Gagal jika mata kuliah masih digunakan pada tabel jadwal
        redirectStatus('gagal_hapus');
    }
}

// =====================================
// AMBIL DATA MATA KULIAH
// =====================================
$query = mysqli_query($conn, "
    SELECT kode_mk, nama_mk, sks
    FROM matakuliah
    ORDER BY kode_mk ASC
");

if (!$query) {
    die("Gagal mengambil data: " . e(mysqli_error($conn)));
}

$total_matakuliah = mysqli_num_rows($query);

// Hitung total SKS
$resultSKS = mysqli_query($conn, "
    SELECT COALESCE(SUM(sks), 0) AS total_sks
    FROM matakuliah
");

$dataSKS = mysqli_fetch_assoc($resultSKS);
$total_sks = (int)$dataSKS['total_sks'];

// =====================================
// PESAN STATUS
// =====================================
$pesan = [
    'tambah'        => ['success', 'Mata kuliah berhasil ditambahkan.'],
    'update'        => ['success', 'Data mata kuliah berhasil diperbarui.'],
    'hapus'         => ['success', 'Mata kuliah berhasil dihapus.'],
    'gagal_tambah'  => ['danger', 'Gagal menambahkan mata kuliah. Kode mungkin sudah terdaftar.'],
    'gagal_update'  => ['danger', 'Gagal memperbarui data mata kuliah.'],
    'gagal_hapus'   => ['danger', 'Mata kuliah tidak dapat dihapus. Pastikan mata kuliah tidak sedang digunakan pada jadwal kuliah.'],
    'data_kosong'   => ['warning', 'Lengkapi seluruh data dan pastikan SKS antara 1 sampai 6.'],
    'kode_invalid'  => ['warning', 'Kode mata kuliah maksimal 5 karakter.']
];

$status = $_GET['status'] ?? '';
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Mata Kuliah - NEXORA University</title>

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
            background: rgba(16,14,38,0.97);
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

        .text-cyan {
            color: #00f2fe !important;
        }

        .page-title {
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 0.5px;
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
            border-bottom: 1px solid rgba(0,242,254,0.25);
            padding: 15px;
        }

        .table tbody td {
            padding: 14px;
            font-size: 13px;
            border-color: rgba(255,255,255,0.07);
            vertical-align: middle;
        }

        .modal-content {
            background: #100e26;
            color: #f1f5f9;
            border: 1px solid rgba(0,242,254,0.25);
            border-radius: 16px;
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(255,255,255,0.10);
        }

        .modal .form-control {
            background: #080715;
            border: 1px solid #334155;
            color: #fff;
        }

        .modal .form-control:focus {
            border-color: #00f2fe;
            box-shadow: 0 0 0 0.2rem rgba(0,242,254,0.12);
        }

        .modal .btn-close {
            filter: invert(1);
        }

        .btn-nexora {
            background: linear-gradient(90deg, #0891b2, #6366f1);
            color: #fff;
            border: none;
        }

        .btn-nexora:hover {
            color: #fff;
            opacity: 0.9;
        }

        .stat-number {
            font-family: 'Orbitron', sans-serif;
            color: #00f2fe;
        }

        .btn-back {
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

        <div class="d-flex align-items-center gap-2">

            <a href="dashboard_admin.php" class="btn btn-outline-info btn-sm btn-back">
                <i class="bi bi-arrow-left me-1"></i>
                Dashboard Admin
            </a>

            <a href="logout.php" class="btn btn-outline-danger btn-sm btn-back">
                <i class="bi bi-box-arrow-right me-1"></i>
                Logout
            </a>

        </div>
    </div>
</nav>

<div class="container py-4 py-md-5">

    <!-- JUDUL -->
    <div class="mb-4">

        <div class="text-cyan small fw-bold mb-2">
            <i class="bi bi-mortarboard-fill me-1"></i>
            NEXORA ACADEMIC SYSTEM
        </div>

        <h3 class="page-title fw-bold mb-2">
            Data Mata Kuliah
        </h3>

        <p class="text-secondary mb-0">
            Kelola daftar mata kuliah dan jumlah SKS dalam sistem akademik.
        </p>

    </div>

    <!-- PESAN STATUS -->
    <?php if (isset($pesan[$status])): ?>

        <div class="alert alert-<?= e($pesan[$status][0]); ?> alert-dismissible fade show">
            <i class="bi bi-info-circle-fill me-2"></i>
            <?= e($pesan[$status][1]); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-4">
            <div class="card-nexora p-3">
                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 p-3"
                         style="background:rgba(0,242,254,0.12);">
                        <i class="bi bi-journal-bookmark-fill text-cyan fs-3"></i>
                    </div>

                    <div>
                        <div class="text-secondary small">
                            Total Mata Kuliah
                        </div>

                        <h3 class="stat-number fw-bold mb-0">
                            <?= $total_matakuliah; ?>
                        </h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card-nexora p-3">
                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 p-3"
                         style="background:rgba(139,92,246,0.15);">
                        <i class="bi bi-award-fill fs-3" style="color:#a78bfa;"></i>
                    </div>

                    <div>
                        <div class="text-secondary small">
                            Total SKS
                        </div>

                        <h3 class="stat-number fw-bold mb-0">
                            <?= $total_sks; ?>
                        </h3>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card-nexora p-3">
                <div class="d-flex align-items-center gap-3">

                    <div class="rounded-3 p-3"
                         style="background:rgba(255,193,7,0.13);">
                        <i class="bi bi-person-gear text-warning fs-3"></i>
                    </div>

                    <div>
                        <div class="text-secondary small">
                            Hak Akses
                        </div>

                        <h6 class="fw-bold mb-0">
                            Administrator
                        </h6>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- TABEL MATA KULIAH -->
    <div class="card-nexora">

        <div class="p-3 p-md-4 border-bottom"
             style="border-color:rgba(0,242,254,0.15) !important;">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-journal-bookmark-fill text-cyan me-2"></i>
                        Course Directory
                    </h5>

                    <small class="text-secondary">
                        Daftar mata kuliah NEXORA University
                    </small>
                </div>

                <button class="btn btn-nexora fw-semibold px-3"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambah">

                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Mata Kuliah

                </button>

            </div>
        </div>

        <div class="p-3 p-md-4">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th class="text-center" width="60">No</th>
                            <th>Kode MK</th>
                            <th>Nama Mata Kuliah</th>
                            <th class="text-center">SKS</th>
                            <th class="text-center" width="190">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($total_matakuliah > 0): ?>

                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($query)):
                            $modalId = 'modalEdit' . $no;
                        ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no; ?>
                                </td>

                                <td class="fw-bold text-cyan">
                                    <?= e($data['kode_mk']); ?>
                                </td>

                                <td>
                                    <?= e($data['nama_mk']); ?>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                        <?= (int)$data['sks']; ?> SKS
                                    </span>
                                </td>

                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-sm btn-outline-warning"
                                            data-bs-toggle="modal"
                                            data-bs-target="#<?= e($modalId); ?>">

                                        <i class="bi bi-pencil-square"></i>
                                        Edit

                                    </button>

                                    <a href="data_matakuliah.php?hapus=<?= urlencode($data['kode_mk']); ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">

                                        <i class="bi bi-trash"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                            <!-- MODAL EDIT -->
                            <div class="modal fade"
                                 id="<?= e($modalId); ?>"
                                 tabindex="-1">

                                <div class="modal-dialog modal-lg modal-dialog-centered">

                                    <div class="modal-content">

                                        <form method="POST" action="data_matakuliah.php">

                                            <div class="modal-header">

                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square text-warning me-2"></i>
                                                    Edit Data Mata Kuliah
                                                </h5>

                                                <button type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal">
                                                </button>

                                            </div>

                                            <div class="modal-body">

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Kode Mata Kuliah
                                                    </label>

                                                    <input type="text"
                                                           name="kode_mk"
                                                           class="form-control"
                                                           maxlength="5"
                                                           value="<?= e($data['kode_mk']); ?>"
                                                           readonly
                                                           required>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Nama Mata Kuliah
                                                    </label>

                                                    <input type="text"
                                                           name="nama_mk"
                                                           class="form-control"
                                                           value="<?= e($data['nama_mk']); ?>"
                                                           required>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        SKS
                                                    </label>

                                                    <input type="number"
                                                           name="sks"
                                                           class="form-control"
                                                           min="1"
                                                           max="6"
                                                           value="<?= (int)$data['sks']; ?>"
                                                           required>

                                                </div>

                                            </div>

                                            <div class="modal-footer">

                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        data-bs-dismiss="modal">
                                                    Batal
                                                </button>

                                                <button type="submit"
                                                        name="update"
                                                        class="btn btn-nexora px-4">

                                                    <i class="bi bi-save me-1"></i>
                                                    Simpan Perubahan

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        <?php
                            $no++;
                        endwhile;
                        ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">

                                <i class="bi bi-journal-x fs-1 d-block mb-2"></i>

                                Belum ada data mata kuliah.

                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Administrator Academic System.
    </footer>

</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="data_matakuliah.php">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-journal-plus text-cyan me-2"></i>
                        Tambah Mata Kuliah
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Kode Mata Kuliah
                        </label>

                        <input type="text"
                               name="kode_mk"
                               class="form-control"
                               maxlength="5"
                               placeholder="Contoh: MK001"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Mata Kuliah
                        </label>

                        <input type="text"
                               name="nama_mk"
                               class="form-control"
                               placeholder="Masukkan nama mata kuliah"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            SKS
                        </label>

                        <input type="number"
                               name="sks"
                               class="form-control"
                               min="1"
                               max="6"
                               placeholder="Contoh: 3"
                               required>

                    </div>

                    <div class="alert alert-info small mb-0">

                        <i class="bi bi-info-circle-fill me-1"></i>

                        Masukkan kode mata kuliah yang unik. Jumlah SKS antara 1 sampai 6.

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            name="simpan"
                            class="btn btn-nexora px-4">

                        <i class="bi bi-save me-1"></i>
                        Simpan Mata Kuliah

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
