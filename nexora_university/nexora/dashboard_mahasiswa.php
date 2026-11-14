<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php#portal-akademik");
    exit;
}

$nim = $_SESSION['username'];
$nama = $_SESSION['nama'];
$jurusan = $_SESSION['jurusan'] ?? '-';

// Ambil data KRS & Nilai Mahasiswa dari database
$query_krs = "SELECT krs.*, matakuliah.kode_mk, matakuliah.nama_mk, matakuliah.sks, 
                     jadwal.hari, jadwal.jam_mulai, jadwal.jam_selesai, jadwal.ruangan, 
                     dosen.nama_lengkap AS nama_dosen
              FROM krs
              JOIN jadwal ON krs.id_jadwal = jadwal.id_jadwal
              JOIN matakuliah ON jadwal.kode_mk = matakuliah.kode_mk
              JOIN dosen ON jadwal.nid = dosen.nid
              WHERE krs.nim = '$nim'";
$result_krs = mysqli_query($conn, $query_krs);
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa — NEXORA University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #080715; color: #f1f5f9; }
        .card-nexora { background: #100e26; border: 1px solid rgba(0, 242, 254, 0.3); border-radius: 16px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark border-bottom border-info border-opacity-25 px-4 py-3">
        <span class="navbar-brand fw-bold text-info"><i class="bi bi-mortarboard-fill me-2"></i>NEXORA — Portal Mahasiswa</span>
        <div class="d-flex align-items-center gap-3">
            <span class="small text-light">NIM: <strong><?= htmlspecialchars($nim); ?></strong> | <?= htmlspecialchars($nama); ?></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">Logout</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="card-nexora p-4 mb-4">
            <h4 class="text-white fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($nama); ?>!</h4>
            <p class="text-info mb-0">Program Studi: <strong><?= htmlspecialchars($jurusan); ?></strong></p>
        </div>

        <div class="card-nexora p-4">
            <h5 class="text-white fw-bold mb-3"><i class="bi bi-journal-check text-info me-2"></i>Kartu Rencana Studi (KRS) & Nilai</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-info">
                            <th>Kode MK</th>
                            <th>Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Jadwal & Ruang</th>
                            <th>Dosen</th>
                            <th>TM</th>
                            <th>UTS</th>
                            <th>UAS</th>
                            <th>Akhir</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_krs) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result_krs)): ?>
                                <tr>
                                    <td><?= $row['kode_mk']; ?></td>
                                    <td><?= $row['nama_mk']; ?></td>
                                    <td><?= $row['sks']; ?></td>
                                    <td><?= $row['hari']; ?>, <?= substr($row['jam_mulai'],0,5); ?>-<?= substr($row['jam_selesai'],0,5); ?> (<?= $row['ruangan']; ?>)</td>
                                    <td><?= $row['nama_dosen']; ?></td>
                                    <td><?= $row['nilai_tm'] ?? '-'; ?></td>
                                    <td><?= $row['nilai_uts'] ?? '-'; ?></td>
                                    <td><?= $row['nilai_uas'] ?? '-'; ?></td>
                                    <td class="fw-bold text-info"><?= $row['nilai_akhir'] ?? '-'; ?></td>
                                    <td><span class="badge bg-primary"><?= $row['grade'] ?? '-'; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-secondary py-4">Belum ada data KRS yang diambil.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>