<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php#portal-akademik");
    exit;
}

$jml_mhs   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM mahasiswa"))['total'];
$jml_dosen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM dosen"))['total'];
$jml_mk    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM matakuliah"))['total'];
$jml_jdwl  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM jadwal"))['total'];

$users = mysqli_query($conn, "SELECT id_user, username, nama, role FROM user ORDER BY id_user DESC");
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — NEXORA University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #080715; color: #f1f5f9; }
        .card-nexora { background: #100e26; border: 1px solid rgba(255, 0, 128, 0.35); border-radius: 16px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark border-bottom border-danger border-opacity-25 px-4 py-3">
        <span class="navbar-brand fw-bold text-info"><i class="bi bi-shield-lock-fill me-2"></i>NEXORA — Panel Administrator</span>
        <div class="d-flex align-items-center gap-3">
            <span class="small text-light">Admin: <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Statistik Database -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card-nexora p-3 text-center">
                    <small class="text-secondary">Total Mahasiswa</small>
                    <h2 class="fw-bold text-info mb-0"><?= $jml_mhs; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-nexora p-3 text-center">
                    <small class="text-secondary">Total Dosen</small>
                    <h2 class="fw-bold text-primary mb-0"><?= $jml_dosen; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-nexora p-3 text-center">
                    <small class="text-secondary">Mata Kuliah</small>
                    <h2 class="fw-bold text-warning mb-0"><?= $jml_mk; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card-nexora p-3 text-center">
                    <small class="text-secondary">Jadwal Aktif</small>
                    <h2 class="fw-bold text-danger mb-0"><?= $jml_jdwl; ?></h2>
                </div>
            </div>
        </div>

        <!-- Daftar Akun Terdaftar -->
        <div class="card-nexora p-4">
            <h5 class="text-white fw-bold mb-3"><i class="bi bi-people-fill text-info me-2"></i>Daftar Pengguna Terdaftar (Tabel User)</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-info">
                            <th>ID</th>
                            <th>Username / NIM / NID</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?= $u['id_user']; ?></td>
                                <td><strong><?= htmlspecialchars($u['username']); ?></strong></td>
                                <td><?= htmlspecialchars($u['nama']); ?></td>
                                <td><span class="badge bg-secondary text-uppercase"><?= $u['role']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>