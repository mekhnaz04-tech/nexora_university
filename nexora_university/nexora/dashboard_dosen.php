<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'dosen') {
    header("Location: index.php#portal-akademik");
    exit;
}

$nid  = $_SESSION['username'];
$nama = $_SESSION['nama'];

// Ambil jadwal mengajar dosen
$query_jadwal = "SELECT jadwal.*, matakuliah.nama_mk, matakuliah.sks 
                 FROM jadwal 
                 JOIN matakuliah ON jadwal.kode_mk = matakuliah.kode_mk 
                 WHERE jadwal.nid = '$nid'";
$result_jadwal = mysqli_query($conn, $query_jadwal);
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dosen — NEXORA University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #080715; color: #f1f5f9; }
        .card-nexora { background: #100e26; border: 1px solid rgba(121, 40, 202, 0.45); border-radius: 16px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark border-bottom border-primary border-opacity-25 px-4 py-3">
        <span class="navbar-brand fw-bold text-info"><i class="bi bi-person-workspace me-2"></i>NEXORA — Portal Dosen</span>
        <div class="d-flex align-items-center gap-3">
            <span class="small text-light">NID: <strong><?= htmlspecialchars($nid); ?></strong> | <?= htmlspecialchars($nama); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="card-nexora p-4 mb-4">
            <h4 class="text-white fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($nama); ?>!</h4>
            <p class="text-secondary mb-0">NID Dosen: <strong><?= htmlspecialchars($nid); ?></strong></p>
        </div>

        <div class="card-nexora p-4">
            <h5 class="text-white fw-bold mb-3"><i class="bi bi-calendar-week text-info me-2"></i>Jadwal Mengajar Anda</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-info">
                            <th>ID Jadwal</th>
                            <th>Kode MK</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_jadwal) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_jadwal)): ?>
                                <tr>
                                    <td>#<?= $row['id_jadwal']; ?></td>
                                    <td><?= $row['kode_mk']; ?></td>
                                    <td><?= $row['nama_mk']; ?></td>
                                    <td><?= $row['sks']; ?></td>
                                    <td><?= $row['hari']; ?></td>
                                    <td><?= substr($row['jam_mulai'],0,5); ?> - <?= substr($row['jam_selesai'],0,5); ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $row['ruangan']; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">Belum ada jadwal mengajar yang ditetapkan oleh Admin.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>