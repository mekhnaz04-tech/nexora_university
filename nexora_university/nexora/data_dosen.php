
<?php
session_start();
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
// TOKEN KEAMANAN
// =====================================
if (empty($_SESSION['csrf_dosen'])) {
    $_SESSION['csrf_dosen'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_dosen'];

$pesan = '';
$jenis_pesan = 'success';

// =====================================
// PROSES TAMBAH DOSEN
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        exit('Permintaan tidak valid. Silakan muat ulang halaman.');
    }

    $aksi = $_POST['aksi'] ?? '';

    // TAMBAH DOSEN
    if ($aksi === 'tambah') {

        $nid = trim($_POST['nid'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');

        if ($nid === '' || $nama === '') {
            $pesan = 'NID dan nama dosen wajib diisi.';
            $jenis_pesan = 'danger';

        } else {

            $stmt = mysqli_prepare($conn, "
                INSERT INTO dosen (nid, nama_lengkap)
                VALUES (?, ?)
            ");

            mysqli_stmt_bind_param($stmt, "ss", $nid, $nama);

            if (mysqli_stmt_execute($stmt)) {
                $pesan = 'Data dosen berhasil ditambahkan.';
            } else {
                $pesan = 'Gagal menambahkan dosen. Pastikan NID belum digunakan.';
                $jenis_pesan = 'danger';
            }
        }
    }

    // EDIT DOSEN
    elseif ($aksi === 'edit') {

        $nid = trim($_POST['nid'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');

        if ($nid === '' || $nama === '') {
            $pesan = 'Data dosen tidak boleh kosong.';
            $jenis_pesan = 'danger';

        } else {

            $stmt = mysqli_prepare($conn, "
                UPDATE dosen
                SET nama_lengkap = ?
                WHERE nid = ?
            ");

            mysqli_stmt_bind_param($stmt, "ss", $nama, $nid);

            if (mysqli_stmt_execute($stmt)) {
                $pesan = 'Data dosen berhasil diperbarui.';
            } else {
                $pesan = 'Gagal memperbarui data dosen.';
                $jenis_pesan = 'danger';
            }
        }
    }

    // HAPUS DOSEN
    elseif ($aksi === 'hapus') {

        $nid = trim($_POST['nid'] ?? '');

        $stmt = mysqli_prepare($conn, "
            DELETE FROM dosen
            WHERE nid = ?
        ");

        mysqli_stmt_bind_param($stmt, "s", $nid);

        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $pesan = 'Data dosen berhasil dihapus.';
            } else {
                $pesan = 'Data dosen tidak ditemukan.';
                $jenis_pesan = 'warning';
            }
        } else {
            $pesan = 'Dosen tidak dapat dihapus karena masih digunakan pada jadwal kuliah.';
            $jenis_pesan = 'danger';
        }
    }
}

// =====================================
// AMBIL DATA DOSEN
// =====================================
$query = mysqli_query($conn, "
    SELECT
        dosen.nid,
        dosen.nama_lengkap,
        COUNT(jadwal.id_jadwal) AS total_jadwal
    FROM dosen
    LEFT JOIN jadwal
        ON dosen.nid = jadwal.nid
    GROUP BY dosen.nid, dosen.nama_lengkap
    ORDER BY dosen.nid ASC
");

if (!$query) {
    die("Gagal mengambil data dosen: " . e(mysqli_error($conn)));
}

$total_dosen = mysqli_num_rows($query);

// =====================================
// AMBIL DATA DOSEN UNTUK EDIT
// =====================================
$data_edit = null;

if (isset($_GET['edit'])) {

    $nid_edit = trim($_GET['edit']);

    $stmt_edit = mysqli_prepare($conn, "
        SELECT nid, nama_lengkap
        FROM dosen
        WHERE nid = ?
    ");

    mysqli_stmt_bind_param($stmt_edit, "s", $nid_edit);
    mysqli_stmt_execute($stmt_edit);

    $result_edit = mysqli_stmt_get_result($stmt_edit);
    $data_edit = mysqli_fetch_assoc($result_edit);
}
?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Dosen — NEXORA University</title>

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
            border: 1px solid rgba(0,242,254,0.22);
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
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            font-size: 22px;
            color: #00f2fe;
            background: rgba(0,242,254,0.12);
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
            border-bottom: 1px solid rgba(0,242,254,0.25);
            padding: 15px;
        }

        .table tbody td {
            padding: 14px;
            font-size: 13px;
            border-color: rgba(255,255,255,0.07);
        }

        .form-control {
            background: #080715;
            border: 1px solid rgba(0,242,254,0.25);
            color: white;
            border-radius: 10px;
            padding: 11px 13px;
        }

        .form-control:focus {
            background: #080715;
            color: white;
            border-color: #00f2fe;
            box-shadow: 0 0 0 3px rgba(0,242,254,0.1);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .btn-cyan {
            background: #00f2fe;
            color: #080715;
            border: none;
            font-weight: 700;
            border-radius: 9px;
        }

        .btn-cyan:hover {
            background: #8ffaff;
            color: #080715;
        }

        .btn-outline-cyan {
            border: 1px solid #00f2fe;
            color: #00f2fe;
            border-radius: 9px;
        }

        .btn-outline-cyan:hover {
            background: #00f2fe;
            color: #080715;
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

        <a class="navbar-brand brand" href="dashboard_admin.php">
            <i class="bi bi-shield-lock-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-3">

            <div class="text-end d-none d-sm-block">
                <div class="small fw-bold">
                    <?= e($_SESSION['nama'] ?? 'Administrator'); ?>
                </div>

                <div class="small text-secondary">
                    Administrator
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

        <a href="dashboard_admin.php" class="btn btn-outline-info btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Dashboard
        </a>

    </div>


    <!-- HEADER -->
    <div class="card-nexora welcome-card p-4 mb-4">

        <div class="text-cyan small fw-bold mb-2">
            ACADEMIC MANAGEMENT SYSTEM
        </div>

        <h3 class="fw-bold mb-2">
            <i class="bi bi-person-badge-fill text-cyan me-2"></i>
            Manajemen Data Dosen
        </h3>

        <p class="text-secondary mb-0">
            Kelola informasi dosen yang terdaftar di NEXORA University.
        </p>

    </div>


    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>

        <div class="alert alert-<?= e($jenis_pesan); ?> alert-dismissible fade show">

            <i class="bi bi-info-circle-fill me-2"></i>
            <?= e($pesan); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>


    <div class="row g-4">

        <!-- FORM TAMBAH / EDIT -->
        <div class="col-lg-4">

            <div class="card-nexora p-4">

                <h5 class="section-title mb-4">
                    <i class="bi bi-person-plus-fill text-cyan me-2"></i>

                    <?= $data_edit ? 'EDIT DATA DOSEN' : 'TAMBAH DOSEN'; ?>
                </h5>

                <form method="POST" action="data_dosen.php<?= $data_edit ? '?edit=' . urlencode($data_edit['nid']) : ''; ?>">

                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">

                    <input type="hidden" name="aksi" value="<?= $data_edit ? 'edit' : 'tambah'; ?>">

                    <div class="mb-3">

                        <label class="form-label small text-secondary">
                            Nomor Induk Dosen (NID)
                        </label>

                        <input
                            type="text"
                            name="nid"
                            class="form-control"
                            placeholder="Masukkan NID dosen"
                            value="<?= e($data_edit['nid'] ?? ''); ?>"
                            <?= $data_edit ? 'readonly' : 'required'; ?>
                            maxlength="5"
                        >

                    </div>


                    <div class="mb-4">

                        <label class="form-label small text-secondary">
                            Nama Lengkap Dosen
                        </label>

                        <input
                            type="text"
                            name="nama_lengkap"
                            class="form-control"
                            placeholder="Masukkan nama lengkap"
                            value="<?= e($data_edit['nama_lengkap'] ?? ''); ?>"
                            required
                            maxlength="100"
                        >

                    </div>


                    <button type="submit" class="btn btn-cyan w-100 py-2">

                        <i class="bi <?= $data_edit ? 'bi-save-fill' : 'bi-plus-circle-fill'; ?> me-2"></i>

                        <?= $data_edit ? 'Simpan Perubahan' : 'Tambah Dosen'; ?>

                    </button>


                    <?php if ($data_edit): ?>

                        <a href="data_dosen.php" class="btn btn-outline-secondary w-100 mt-2">
                            Batal Edit
                        </a>

                    <?php endif; ?>

                </form>

            </div>

        </div>


        <!-- DAFTAR DOSEN -->
        <div class="col-lg-8">

            <div class="card-nexora p-4">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <h5 class="section-title mb-1">
                            <i class="bi bi-people-fill text-cyan me-2"></i>
                            DAFTAR DOSEN
                        </h5>

                        <small class="text-secondary">
                            Data dosen yang tersimpan dalam database.
                        </small>
                    </div>

                    <span class="badge bg-info text-dark">
                        <?= $total_dosen; ?> Dosen
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table table-dark table-hover align-middle mb-0">

                        <thead>

                            <tr>
                                <th>No</th>
                                <th>NID</th>
                                <th>Nama Lengkap</th>
                                <th>Jadwal</th>
                                <th>Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if ($total_dosen > 0): ?>

                                <?php
                                $no = 1;
                                mysqli_data_seek($query, 0);
                                ?>

                                <?php while ($dosen = mysqli_fetch_assoc($query)): ?>

                                    <tr>

                                        <td><?= $no++; ?></td>

                                        <td class="text-cyan fw-bold">
                                            <?= e($dosen['nid']); ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= e($dosen['nama_lengkap']); ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-primary">
                                                <?= (int)$dosen['total_jadwal']; ?> Jadwal
                                            </span>
                                        </td>

                                        <td>

                                            <div class="d-flex gap-2">

                                                <a
                                                    href="data_dosen.php?edit=<?= urlencode($dosen['nid']); ?>"
                                                    class="btn btn-outline-warning btn-sm"
                                                    title="Edit"
                                                >
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>


                                                <form method="POST" action="data_dosen.php"
                                                      onsubmit="return confirm('Yakin ingin menghapus dosen <?= e($dosen['nama_lengkap']); ?>?');">

                                                    <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">

                                                    <input type="hidden" name="aksi" value="hapus">

                                                    <input type="hidden" name="nid" value="<?= e($dosen['nid']); ?>">

                                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Hapus">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="5" class="text-center py-5">

                                        <i class="bi bi-person-x text-secondary" style="font-size: 40px;"></i>

                                        <div class="text-secondary mt-2">
                                            Belum ada data dosen.
                                        </div>

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University. Academic Management System.
    </footer>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
