
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
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
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
// PROSES TAMBAH, EDIT, HAPUS
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($csrf, $_POST['csrf_token'] ?? '')) {
        exit("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $aksi = $_POST['aksi'] ?? '';

    // ---------------------------------
    // TAMBAH DOSEN
    // ---------------------------------
    if ($aksi === 'simpan') {

        $nid = trim($_POST['nid'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');

        if ($nid === '' || $nama === '') {
            $pesan = "NID dan nama dosen wajib diisi.";
            $jenis_pesan = 'danger';

        } elseif (strlen($nid) > 5) {
            $pesan = "NID maksimal 5 karakter.";
            $jenis_pesan = 'danger';

        } else {

            // Cek apakah NID sudah digunakan
            $cek = mysqli_prepare($conn, "
                SELECT nid FROM dosen WHERE nid = ?
            ");

            mysqli_stmt_bind_param($cek, "s", $nid);
            mysqli_stmt_execute($cek);

            $hasil_cek = mysqli_stmt_get_result($cek);

            // Cek apakah username sudah digunakan
            $cek_user = mysqli_prepare($conn, "
                SELECT id_user FROM user WHERE username = ?
            ");

            mysqli_stmt_bind_param($cek_user, "s", $nid);
            mysqli_stmt_execute($cek_user);

            $hasil_user = mysqli_stmt_get_result($cek_user);

            if (mysqli_num_rows($hasil_cek) > 0 ||
                mysqli_num_rows($hasil_user) > 0) {

                $pesan = "NID atau username sudah terdaftar.";
                $jenis_pesan = 'warning';

            } else {

                mysqli_begin_transaction($conn);

                try {

                    // Simpan data dosen
                    $stmt = mysqli_prepare($conn, "
                        INSERT INTO dosen (nid, nama_lengkap)
                        VALUES (?, ?)
                    ");

                    mysqli_stmt_bind_param($stmt, "ss", $nid, $nama);
                    mysqli_stmt_execute($stmt);

                    // Buat akun login dosen
                    // Password awal mengikuti pola Mini Project: NID
                    $password = $nid;
                    $role = 'dosen';

                    $stmt_user = mysqli_prepare($conn, "
                        INSERT INTO user (username, password, nama, role)
                        VALUES (?, ?, ?, ?)
                    ");

                    mysqli_stmt_bind_param(
                        $stmt_user,
                        "ssss",
                        $nid,
                        $password,
                        $nama,
                        $role
                    );

                    mysqli_stmt_execute($stmt_user);

                    mysqli_commit($conn);

                    $_SESSION['flash_dosen'] = [
                        'pesan' => 'Data dosen dan akun login berhasil ditambahkan.',
                        'jenis' => 'success'
                    ];

                    header("Location: dosen.php");
                    exit;

                } catch (Throwable $e) {

                    mysqli_rollback($conn);

                    $pesan = "Gagal menambahkan data dosen. Periksa kembali database.";
                    $jenis_pesan = 'danger';
                }
            }
        }
    }

    // ---------------------------------
    // UPDATE DOSEN
    // ---------------------------------
    elseif ($aksi === 'update') {

        $nid = trim($_POST['nid'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');

        if ($nid === '' || $nama === '') {

            $pesan = "Data dosen tidak boleh kosong.";
            $jenis_pesan = 'danger';

        } else {

            mysqli_begin_transaction($conn);

            try {

                $stmt = mysqli_prepare($conn, "
                    UPDATE dosen
                    SET nama_lengkap = ?
                    WHERE nid = ?
                ");

                mysqli_stmt_bind_param($stmt, "ss", $nama, $nid);
                mysqli_stmt_execute($stmt);

                // Perbarui nama akun dosen
                $stmt_user = mysqli_prepare($conn, "
                    UPDATE user
                    SET nama = ?
                    WHERE username = ?
                      AND role = 'dosen'
                ");

                mysqli_stmt_bind_param($stmt_user, "ss", $nama, $nid);
                mysqli_stmt_execute($stmt_user);

                mysqli_commit($conn);

                $_SESSION['flash_dosen'] = [
                    'pesan' => 'Data dosen berhasil diperbarui.',
                    'jenis' => 'success'
                ];

                header("Location: dosen.php");
                exit;

            } catch (Throwable $e) {

                mysqli_rollback($conn);

                $pesan = "Gagal memperbarui data dosen.";
                $jenis_pesan = 'danger';
            }
        }
    }

    // ---------------------------------
    // HAPUS DOSEN
    // ---------------------------------
    elseif ($aksi === 'hapus') {

        $nid = trim($_POST['nid'] ?? '');

        // Periksa apakah dosen sudah memiliki jadwal
        $cek_jadwal = mysqli_prepare($conn, "
            SELECT COUNT(*) AS total
            FROM jadwal
            WHERE nid = ?
        ");

        mysqli_stmt_bind_param($cek_jadwal, "s", $nid);
        mysqli_stmt_execute($cek_jadwal);

        $hasil_jadwal = mysqli_stmt_get_result($cek_jadwal);
        $data_jadwal = mysqli_fetch_assoc($hasil_jadwal);

        if ((int)$data_jadwal['total'] > 0) {

            $pesan = "Dosen tidak dapat dihapus karena masih memiliki jadwal mengajar. Hapus atau pindahkan jadwalnya terlebih dahulu.";
            $jenis_pesan = 'warning';

        } else {

            mysqli_begin_transaction($conn);

            try {

                // Hapus data dosen
                $stmt = mysqli_prepare($conn, "
                    DELETE FROM dosen WHERE nid = ?
                ");

                mysqli_stmt_bind_param($stmt, "s", $nid);
                mysqli_stmt_execute($stmt);

                // Hapus akun login khusus role dosen
                $stmt_user = mysqli_prepare($conn, "
                    DELETE FROM user
                    WHERE username = ?
                      AND role = 'dosen'
                ");

                mysqli_stmt_bind_param($stmt_user, "s", $nid);
                mysqli_stmt_execute($stmt_user);

                mysqli_commit($conn);

                $_SESSION['flash_dosen'] = [
                    'pesan' => 'Data dosen dan akun login berhasil dihapus.',
                    'jenis' => 'success'
                ];

                header("Location: dosen.php");
                exit;

            } catch (Throwable $e) {

                mysqli_rollback($conn);

                $pesan = "Gagal menghapus data dosen. Periksa relasi database.";
                $jenis_pesan = 'danger';
            }
        }
    }
}

// =====================================
// NOTIFIKASI
// =====================================
if (isset($_SESSION['flash_dosen'])) {
    $pesan = $_SESSION['flash_dosen']['pesan'];
    $jenis_pesan = $_SESSION['flash_dosen']['jenis'];

    unset($_SESSION['flash_dosen']);
}

// =====================================
// AMBIL DATA DOSEN
// =====================================
$query = mysqli_query($conn, "
    SELECT
        dosen.nid,
        dosen.nama_lengkap,
        user.username
    FROM dosen
    LEFT JOIN user
        ON dosen.nid = user.username
        AND user.role = 'dosen'
    ORDER BY dosen.nid ASC
");

if (!$query) {
    die("Gagal mengambil data dosen: " . e(mysqli_error($conn)));
}

$total_dosen = mysqli_num_rows($query);

$data_dosen = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data_dosen[] = $row;
}

$no = 1;
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
            border: 1px solid rgba(0,242,254,0.23);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
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
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            font-size: 23px;
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
            padding: 14px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
        }

        .form-control {
            background: #080715;
            border: 1px solid rgba(0,242,254,0.25);
            color: #fff;
            padding: 11px 13px;
        }

        .form-control:focus {
            background: #080715;
            color: #fff;
            border-color: #00f2fe;
            box-shadow: 0 0 0 0.2rem rgba(0,242,254,0.1);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .modal-content {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.25);
            border-radius: 16px;
        }

        .modal-header,
        .modal-footer {
            border-color: rgba(255,255,255,0.08);
        }

        .btn-add {
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            border: none;
            color: #080715;
            font-weight: 700;
            border-radius: 9px;
        }

        .btn-add:hover {
            color: white;
            opacity: 0.9;
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

    <!-- KEMBALI -->
    <div class="mb-3">

        <a href="dashboard_admin.php" class="btn btn-outline-info btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>
            Dashboard Admin
        </a>

    </div>


    <!-- HEADER -->
    <div class="card-nexora p-4 mb-4">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">

            <div>

                <div class="text-cyan small fw-bold mb-2">
                    ACADEMIC MANAGEMENT SYSTEM
                </div>

                <h4 class="fw-bold mb-1">
                    <i class="bi bi-person-workspace text-cyan me-2"></i>
                    Data Dosen
                </h4>

                <p class="text-secondary small mb-0">
                    Kelola data dosen dan akun akses portal pengajar NEXORA.
                </p>

            </div>

            <div class="stat-icon">
                <i class="bi bi-person-badge-fill"></i>
            </div>

        </div>

    </div>


    <!-- NOTIFIKASI -->
    <?php if ($pesan !== ''): ?>

        <div class="alert alert-<?= e($jenis_pesan); ?> alert-dismissible fade show">

            <i class="bi bi-info-circle-fill me-2"></i>
            <?= e($pesan); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>


    <!-- STATISTIK -->
    <div class="card-nexora p-3 mb-4">

        <div class="d-flex align-items-center gap-3">

            <div class="stat-icon">
                <i class="bi bi-people-fill"></i>
            </div>

            <div>
                <div class="text-secondary small">
                    Total Dosen Terdaftar
                </div>

                <h3 class="fw-bold text-cyan mb-0">
                    <?= $total_dosen; ?>
                </h3>
            </div>

        </div>

    </div>


    <!-- TABEL DATA DOSEN -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

            <div>

                <h5 class="section-title mb-1">
                    <i class="bi bi-person-lines-fill text-cyan me-2"></i>
                    DAFTAR DOSEN
                </h5>

                <div class="small text-secondary">
                    Data dosen yang tersimpan di database.
                </div>

            </div>

            <button type="button"
                    class="btn btn-add btn-sm px-3 py-2"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah">

                <i class="bi bi-plus-circle me-1"></i>
                Tambah Dosen

            </button>

        </div>


        <!-- PENCARIAN -->
        <div class="mb-3">

            <div class="input-group">

                <span class="input-group-text bg-dark border-secondary text-cyan">
                    <i class="bi bi-search"></i>
                </span>

                <input type="text"
                       id="cariDosen"
                       class="form-control"
                       placeholder="Cari NID atau nama dosen...">

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-dark table-hover align-middle mb-0" id="tabelDosen">

                <thead>

                    <tr>
                        <th width="60">No</th>
                        <th>NID</th>
                        <th>Nama Lengkap</th>
                        <th>Status Akun</th>
                        <th class="text-center">Aksi</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if ($total_dosen > 0): ?>

                        <?php foreach ($data_dosen as $data): ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td class="text-cyan fw-bold">
                                    <?= e($data['nid']); ?>
                                </td>

                                <td class="fw-semibold">
                                    <?= e($data['nama_lengkap']); ?>
                                </td>

                                <td>

                                    <?php if (!empty($data['username'])): ?>

                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Akun Aktif
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Akun Belum Ada
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td class="text-center">

                                    <button type="button"
                                            class="btn btn-warning btn-sm text-dark"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit<?= e($data['nid']); ?>">

                                        <i class="bi bi-pencil-square"></i>
                                        Edit

                                    </button>

                                    <form method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus dosen <?= e($data['nama_lengkap']); ?>?');">

                                        <input type="hidden" name="csrf_token"
                                               value="<?= e($csrf); ?>">

                                        <input type="hidden" name="aksi"
                                               value="hapus">

                                        <input type="hidden" name="nid"
                                               value="<?= e($data['nid']); ?>">

                                        <button type="submit"
                                                class="btn btn-danger btn-sm">

                                            <i class="bi bi-trash"></i>
                                            Hapus

                                        </button>

                                    </form>

                                </td>

                            </tr>


                            <!-- MODAL EDIT -->
                            <div class="modal fade"
                                 id="modalEdit<?= e($data['nid']); ?>"
                                 tabindex="-1">

                                <div class="modal-dialog modal-dialog-centered">

                                    <div class="modal-content">

                                        <form method="POST">

                                            <div class="modal-header">

                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square text-cyan me-2"></i>
                                                    Edit Data Dosen
                                                </h5>

                                                <button type="button"
                                                        class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal"></button>

                                            </div>

                                            <div class="modal-body">

                                                <input type="hidden" name="csrf_token"
                                                       value="<?= e($csrf); ?>">

                                                <input type="hidden" name="aksi"
                                                       value="update">

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        NID
                                                    </label>

                                                    <input type="text"
                                                           name="nid"
                                                           class="form-control"
                                                           value="<?= e($data['nid']); ?>"
                                                           readonly>

                                                </div>

                                                <div class="mb-3">

                                                    <label class="form-label">
                                                        Nama Lengkap
                                                    </label>

                                                    <input type="text"
                                                           name="nama_lengkap"
                                                           class="form-control"
                                                           value="<?= e($data['nama_lengkap']); ?>"
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
                                                        class="btn btn-info">
                                                    <i class="bi bi-save me-1"></i>
                                                    Simpan Perubahan
                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="5" class="text-center py-5">

                                <i class="bi bi-person-x text-secondary"
                                   style="font-size: 40px;"></i>

                                <div class="text-secondary mt-2">
                                    Belum ada data dosen.
                                </div>

                                <small class="text-secondary">
                                    Klik tombol Tambah Dosen untuk memasukkan data baru.
                                </small>

                            </td>

                        </tr>

                    <?php endif; ?>

                    <tr id="tidakDitemukan" style="display:none;">
                        <td colspan="5" class="text-center text-secondary py-4">
                            Data dosen tidak ditemukan.
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>


    <!-- MODAL TAMBAH DOSEN -->
    <div class="modal fade" id="modalTambah" tabindex="-1">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <form method="POST">

                    <div class="modal-header">

                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-person-plus-fill text-cyan me-2"></i>
                            Tambah Data Dosen
                        </h5>

                        <button type="button"
                                class="btn-close btn-close-white"
                                data-bs-dismiss="modal"></button>

                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="csrf_token"
                               value="<?= e($csrf); ?>">

                        <input type="hidden" name="aksi"
                               value="simpan">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Nomor Induk Dosen (NID)
                            </label>

                            <input type="text"
                                   name="nid"
                                   class="form-control"
                                   maxlength="5"
                                   placeholder="Contoh: 25001"
                                   required>

                            <small class="text-secondary">
                                Maksimal 5 karakter.
                            </small>

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Nama Lengkap
                            </label>

                            <input type="text"
                                   name="nama_lengkap"
                                   class="form-control"
                                   placeholder="Masukkan nama lengkap dosen"
                                   maxlength="100"
                                   required>

                        </div>

                        <div class="alert alert-info small mb-0">

                            <i class="bi bi-info-circle-fill me-1"></i>

                            Akun login dosen akan dibuat otomatis dengan username dan password awal menggunakan NID.

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-add px-4">

                            <i class="bi bi-plus-circle me-1"></i>
                            Simpan Dosen

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Academic Management System.
    </footer>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// PENCARIAN DATA DOSEN
document.getElementById('cariDosen').addEventListener('input', function() {

    const keyword = this.value.toLowerCase();

    const rows = document.querySelectorAll('#tabelDosen tbody tr:not(#tidakDitemukan)');

    let ditemukan = 0;

    rows.forEach(function(row) {

        const cocok = row.innerText.toLowerCase().includes(keyword);

        row.style.display = cocok ? '' : 'none';

        if (cocok) {
            ditemukan++;
        }

    });

    const pesan = document.getElementById('tidakDitemukan');

    pesan.style.display = ditemukan === 0 ? '' : 'none';

});

</script>

</body>
</html>
