
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

$id_admin = (int) $_SESSION['id_user'];
$nama_admin = $_SESSION['nama'] ?? 'Administrator';

// =====================================
// FUNGSI
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES, 'UTF-8');
}

function kembali($status) {
    header("Location: data_user.php?status=" . urlencode($status));
    exit;
}

$role_valid = ['admin', 'dosen', 'mahasiswa'];

// =====================================
// TAMBAH USER
// =====================================
if (isset($_POST['tambah'])) {

    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if (
        $username === '' ||
        $nama === '' ||
        $password === '' ||
        !in_array($role, $role_valid, true)
    ) {
        kembali('input_kosong');
    }

    if (strlen($password) < 6) {
        kembali('password_pendek');
    }

    // Cek username sudah digunakan
    $cek = mysqli_prepare($conn, "
        SELECT id_user
        FROM `user`
        WHERE username = ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    $username_ada = mysqli_stmt_num_rows($cek) > 0;

    mysqli_stmt_close($cek);

    if ($username_ada) {
        kembali('username_ada');
    }

    // Password di-hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO `user` (username, password, nama, role)
        VALUES (?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $username,
        $password_hash,
        $nama,
        $role
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        kembali('tambah');
    }

    mysqli_stmt_close($stmt);
    kembali('gagal');
}

// =====================================
// EDIT USER
// =====================================
if (isset($_POST['edit'])) {

    $id_user  = (int) ($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $nama     = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if (
        $id_user <= 0 ||
        $username === '' ||
        $nama === '' ||
        !in_array($role, $role_valid, true)
    ) {
        kembali('input_kosong');
    }

    // Admin tidak boleh mengubah role akunnya sendiri
    if ($id_user === $id_admin && $role !== 'admin') {
        kembali('role_diri');
    }

    if ($password !== '' && strlen($password) < 6) {
        kembali('password_pendek');
    }

    // Cek username digunakan akun lain
    $cek = mysqli_prepare($conn, "
        SELECT id_user
        FROM `user`
        WHERE username = ?
        AND id_user != ?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($cek, "si", $username, $id_user);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    $username_ada = mysqli_stmt_num_rows($cek) > 0;

    mysqli_stmt_close($cek);

    if ($username_ada) {
        kembali('username_ada');
    }

    if ($password !== '') {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "
            UPDATE `user`
            SET username = ?, nama = ?, password = ?, role = ?
            WHERE id_user = ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $username,
            $nama,
            $password_hash,
            $role,
            $id_user
        );

    } else {

        $stmt = mysqli_prepare($conn, "
            UPDATE `user`
            SET username = ?, nama = ?, role = ?
            WHERE id_user = ?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $username,
            $nama,
            $role,
            $id_user
        );
    }

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        kembali('edit');
    }

    mysqli_stmt_close($stmt);
    kembali('gagal');
}

// =====================================
// HAPUS USER
// =====================================
if (isset($_POST['hapus'])) {

    $id_user = (int) ($_POST['id_user'] ?? 0);

    if ($id_user <= 0) {
        kembali('gagal_hapus');
    }

    // Tidak boleh menghapus akun sendiri
    if ($id_user === $id_admin) {
        kembali('hapus_diri');
    }

    $stmt = mysqli_prepare($conn, "
        DELETE FROM `user`
        WHERE id_user = ?
    ");

    mysqli_stmt_bind_param($stmt, "i", $id_user);

    if (mysqli_stmt_execute($stmt)) {

        $terhapus = mysqli_stmt_affected_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        kembali($terhapus ? 'hapus' : 'gagal_hapus');
    }

    mysqli_stmt_close($stmt);
    kembali('gagal_hapus');
}

// =====================================
// AMBIL DATA USER
// =====================================
$query_user = mysqli_query($conn, "
    SELECT id_user, username, nama, role
    FROM `user`
    ORDER BY
        FIELD(role, 'admin', 'dosen', 'mahasiswa'),
        nama ASC
");

if (!$query_user) {
    die("Gagal mengambil data user: " . e(mysqli_error($conn)));
}

// =====================================
// HITUNG JUMLAH USER
// =====================================
$query_statistik = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total_user,
        SUM(role = 'admin') AS total_admin,
        SUM(role = 'dosen') AS total_dosen,
        SUM(role = 'mahasiswa') AS total_mahasiswa
    FROM `user`
");

$statistik = mysqli_fetch_assoc($query_statistik);

$total_user = (int) $statistik['total_user'];
$total_admin = (int) $statistik['total_admin'];
$total_dosen = (int) $statistik['total_dosen'];
$total_mahasiswa = (int) $statistik['total_mahasiswa'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen User | NEXORA University</title>

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
            max-width: 1300px;
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

        .neon-icon {
            color: var(--cyan);
            font-size: 20px;
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
            font-size: 13px;
            vertical-align: middle;
        }

        .table tbody tr:hover td {
            background: rgba(0,242,254,.035);
        }

        .user-name {
            font-weight: 700;
        }

        .user-username {
            color: var(--cyan);
            font-size: 12px;
        }

        .role-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .role-admin {
            background: rgba(155,92,255,.13);
            color: #c5a4ff;
            border: 1px solid rgba(155,92,255,.25);
        }

        .role-dosen {
            background: rgba(0,242,254,.10);
            color: var(--cyan);
            border: 1px solid rgba(0,242,254,.22);
        }

        .role-mahasiswa {
            background: rgba(25,210,130,.10);
            color: #54e6a5;
            border: 1px solid rgba(25,210,130,.22);
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
                <i class="bi bi-people-fill neon-icon me-2"></i>
                MANAJEMEN USER
            </div>

            <div class="page-subtitle mt-2">
                Pengelolaan akun pengguna NEXORA University
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
        'tambah' => ['success', 'Akun pengguna berhasil ditambahkan.'],
        'edit' => ['success', 'Data akun pengguna berhasil diperbarui.'],
        'hapus' => ['success', 'Akun pengguna berhasil dihapus.'],
        'input_kosong' => ['warning', 'Username, nama, dan role wajib diisi.'],
        'password_pendek' => ['warning', 'Password minimal harus 6 karakter.'],
        'username_ada' => ['warning', 'Username sudah digunakan oleh akun lain.'],
        'role_diri' => ['danger', 'Role akun admin yang sedang digunakan tidak boleh diubah.'],
        'hapus_diri' => ['danger', 'Anda tidak dapat menghapus akun yang sedang digunakan.'],
        'gagal' => ['danger', 'Proses penyimpanan akun gagal. Periksa kembali data.'],
        'gagal_hapus' => ['danger', 'Akun gagal dihapus.']
    ];
    ?>

    <?php if (isset($notifikasi[$status])): ?>
        <div class="alert alert-<?= e($notifikasi[$status][0]); ?> alert-dismissible fade show">
            <i class="bi bi-info-circle me-2"></i>
            <?= e($notifikasi[$status][1]); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- STATISTIK -->
    <div class="row g-3 mb-4">

        <div class="col-md-3 col-6">
            <div class="nex-card p-4 h-100">
                <i class="bi bi-people neon-icon fs-3"></i>
                <div class="stat-number mt-2"><?= $total_user; ?></div>
                <div class="stat-label">Total Pengguna</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="nex-card p-4 h-100">
                <i class="bi bi-shield-lock neon-icon fs-3"></i>
                <div class="stat-number mt-2"><?= $total_admin; ?></div>
                <div class="stat-label">Administrator</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="nex-card p-4 h-100">
                <i class="bi bi-person-workspace neon-icon fs-3"></i>
                <div class="stat-number mt-2"><?= $total_dosen; ?></div>
                <div class="stat-label">Dosen</div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="nex-card p-4 h-100">
                <i class="bi bi-mortarboard neon-icon fs-3"></i>
                <div class="stat-number mt-2"><?= $total_mahasiswa; ?></div>
                <div class="stat-label">Mahasiswa</div>
            </div>
        </div>

    </div>

    <!-- TABEL USER -->
    <div class="nex-card">

        <div class="card-heading d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h5>
                    <i class="bi bi-person-lines-fill neon-icon me-2"></i>
                    Data Akun Pengguna
                </h5>

                <small class="text-secondary">
                    Daftar akun yang dapat mengakses sistem NEXORA
                </small>
            </div>

            <button type="button"
                    class="btn btn-nexora"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah">

                <i class="bi bi-person-plus-fill me-2"></i>
                Tambah User

            </button>

        </div>

        <div class="p-3 p-md-4">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Nama Pengguna</th>
                            <th>Username</th>
                            <th>Role / Hak Akses</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($query_user) > 0): ?>

                        <?php $no = 1; ?>

                        <?php while ($row = mysqli_fetch_assoc($query_user)): ?>

                            <?php
                            $kelas_role = 'role-' . $row['role'];
                            ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <div class="user-name">
                                        <?= e($row['nama']); ?>
                                    </div>

                                    <?php if ((int)$row['id_user'] === $id_admin): ?>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle-fill me-1"></i>
                                            Akun Anda
                                        </small>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="user-username">
                                        <?= e($row['username']); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="role-badge <?= e($kelas_role); ?>">
                                        <?= e($row['role']); ?>
                                    </span>
                                </td>

                                <td class="text-center text-nowrap">

                                    <!-- EDIT -->
                                    <button type="button"
                                            class="btn btn-edit btn-sm btn-action tombol-edit"

                                            data-id="<?= (int)$row['id_user']; ?>"
                                            data-username="<?= e($row['username']); ?>"
                                            data-nama="<?= e($row['nama']); ?>"
                                            data-role="<?= e($row['role']); ?>"

                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit">

                                        <i class="bi bi-pencil-square me-1"></i>
                                        Edit

                                    </button>

                                    <!-- HAPUS -->
                                    <?php if ((int)$row['id_user'] !== $id_admin): ?>

                                        <form method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus akun <?= e($row['username']); ?>?')">

                                            <input type="hidden"
                                                   name="id_user"
                                                   value="<?= (int)$row['id_user']; ?>">

                                            <button type="submit"
                                                    name="hapus"
                                                    class="btn btn-delete btn-sm btn-action">

                                                <i class="bi bi-trash me-1"></i>
                                                Hapus

                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="bi bi-people fs-1 d-block mb-3"></i>
                                <strong>Belum Ada Data Pengguna</strong>
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

<!-- =====================================
     MODAL TAMBAH USER
===================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-person-plus neon-icon me-2"></i>
                        Tambah Akun Pengguna
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body p-4">

                    <div class="mb-3">

                        <label class="form-label">Nama Lengkap</label>

                        <input type="text"
                               name="nama"
                               class="form-control"
                               placeholder="Masukkan nama lengkap"
                               maxlength="100"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Username</label>

                        <input type="text"
                               name="username"
                               class="form-control"
                               placeholder="Masukkan username"
                               maxlength="10"
                               required>

                        <small class="text-secondary">
                            Untuk akun dosen gunakan NID, mahasiswa gunakan NIM.
                        </small>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Password</label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Minimal 6 karakter"
                               minlength="6"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Role / Hak Akses</label>

                        <select name="role" class="form-select" required>

                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Admin</option>
                            <option value="dosen">Dosen</option>
                            <option value="mahasiswa">Mahasiswa</option>

                        </select>

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
                        Simpan User

                    </button>

                </div>

            </form>

        </div>

    </div>
</div>

<!-- =====================================
     MODAL EDIT USER
===================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square neon-icon me-2"></i>
                        Edit Akun Pengguna
                    </h5>

                    <button type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body p-4">

                    <input type="hidden" name="id_user" id="edit_id_user">

                    <div class="mb-3">

                        <label class="form-label">Nama Lengkap</label>

                        <input type="text"
                               name="nama"
                               id="edit_nama"
                               class="form-control"
                               maxlength="100"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Username</label>

                        <input type="text"
                               name="username"
                               id="edit_username"
                               class="form-control"
                               maxlength="10"
                               required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Password Baru</label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               minlength="6"
                               placeholder="Kosongkan jika tidak diganti">

                        <small class="text-secondary">
                            Password lama tetap digunakan jika kolom ini dikosongkan.
                        </small>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">Role / Hak Akses</label>

                        <select name="role"
                                id="edit_role"
                                class="form-select"
                                required>

                            <option value="admin">Admin</option>
                            <option value="dosen">Dosen</option>
                            <option value="mahasiswa">Mahasiswa</option>

                        </select>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.querySelectorAll('.tombol-edit').forEach(function(button) {

    button.addEventListener('click', function() {

        document.getElementById('edit_id_user').value =
            this.dataset.id;

        document.getElementById('edit_username').value =
            this.dataset.username;

        document.getElementById('edit_nama').value =
            this.dataset.nama;

        document.getElementById('edit_role').value =
            this.dataset.role;

    });

});
</script>

</body>
</html>
