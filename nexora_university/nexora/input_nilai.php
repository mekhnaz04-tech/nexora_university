
<?php
session_start();
require_once 'koneksi.php';

// =====================================
// CEK LOGIN DOSEN
// =====================================
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'dosen'
) {
    header("Location: index.php#portal-akademik");
    exit;
}

$nid = $_SESSION['username'];

// =====================================
// FUNGSI OUTPUT AMAN
// =====================================
function e($data) {
    return htmlspecialchars((string)($data ?? '-'), ENT_QUOTES, 'UTF-8');
}

// =====================================
// VALIDASI ID JADWAL
// =====================================
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: dashboard_dosen.php");
    exit;
}

$id_jadwal = (int) $_GET['id'];

// =====================================
// TOKEN KEAMANAN
// =====================================
if (empty($_SESSION['csrf_nilai'])) {
    $_SESSION['csrf_nilai'] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION['csrf_nilai'];

// =====================================
// AMBIL JADWAL MILIK DOSEN LOGIN
// =====================================
$stmt_jadwal = mysqli_prepare($conn, "
    SELECT
        jadwal.id_jadwal,
        jadwal.kode_mk,
        jadwal.hari,
        jadwal.jam_mulai,
        jadwal.jam_selesai,
        jadwal.ruangan,
        matakuliah.nama_mk,
        matakuliah.sks,
        dosen.nama_lengkap AS nama_dosen
    FROM jadwal
    INNER JOIN matakuliah
        ON jadwal.kode_mk = matakuliah.kode_mk
    INNER JOIN dosen
        ON jadwal.nid = dosen.nid
    WHERE jadwal.id_jadwal = ?
      AND jadwal.nid = ?
");

mysqli_stmt_bind_param($stmt_jadwal, "is", $id_jadwal, $nid);
mysqli_stmt_execute($stmt_jadwal);

$result_jadwal = mysqli_stmt_get_result($stmt_jadwal);
$jadwal = mysqli_fetch_assoc($result_jadwal);

if (!$jadwal) {
    http_response_code(403);
    exit("Jadwal tidak ditemukan atau bukan milik akun dosen ini.");
}

// =====================================
// PROSES SIMPAN NILAI
// =====================================
$pesan = '';
$jenis_pesan = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {

    if (
        !hash_equals(
            $_SESSION['csrf_nilai'],
            $_POST['csrf_token'] ?? ''
        )
    ) {
        exit("Permintaan tidak valid. Silakan muat ulang halaman.");
    }

    $ids = $_POST['id_krs'] ?? [];
    $nilai_tm = $_POST['nilai_tm'] ?? [];
    $nilai_uts = $_POST['nilai_uts'] ?? [];
    $nilai_uas = $_POST['nilai_uas'] ?? [];

    if (
        !is_array($ids) ||
        !is_array($nilai_tm) ||
        !is_array($nilai_uts) ||
        !is_array($nilai_uas) ||
        count($ids) !== count($nilai_tm) ||
        count($ids) !== count($nilai_uts) ||
        count($ids) !== count($nilai_uas)
    ) {
        $pesan = "Format data nilai tidak valid.";
        $jenis_pesan = "danger";
    } else {

        $data_simpan = [];
        $error = '';

        foreach ($ids as $i => $id) {

            if (!ctype_digit((string)$id)) {
                $error = "ID KRS tidak valid.";
                break;
            }

            $tm = trim((string)$nilai_tm[$i]);
            $uts = trim((string)$nilai_uts[$i]);
            $uas = trim((string)$nilai_uas[$i]);

            // Jika ketiga nilai kosong, lewati baris.
            if ($tm === '' && $uts === '' && $uas === '') {
                continue;
            }

            // Ketiga komponen nilai wajib diisi.
            if ($tm === '' || $uts === '' || $uas === '') {
                $error = "Nilai TM, UTS, dan UAS harus diisi semuanya.";
                break;
            }

            // Validasi nilai antara 0 sampai 100.
            foreach ([$tm, $uts, $uas] as $nilai) {
                if (
                    !is_numeric($nilai) ||
                    (float)$nilai < 0 ||
                    (float)$nilai > 100
                ) {
                    $error = "Nilai harus berupa angka antara 0 sampai 100.";
                    break 2;
                }
            }

            $tm = (float)$tm;
            $uts = (float)$uts;
            $uas = (float)$uas;

            // Hitung nilai akhir.
            $akhir = ($tm * 0.30) + ($uts * 0.30) + ($uas * 0.40);

            // Tentukan grade.
            if ($akhir >= 85) {
                $grade = 'A';
            } elseif ($akhir >= 75) {
                $grade = 'B';
            } elseif ($akhir >= 65) {
                $grade = 'C';
            } elseif ($akhir >= 55) {
                $grade = 'D';
            } else {
                $grade = 'E';
            }

            $data_simpan[] = [
                'id_krs' => (int)$id,
                'tm' => $tm,
                'uts' => $uts,
                'uas' => $uas,
                'akhir' => $akhir,
                'grade' => $grade
            ];
        }

        if ($error !== '') {

            $pesan = $error;
            $jenis_pesan = 'danger';

        } elseif (count($data_simpan) === 0) {

            $pesan = "Tidak ada nilai yang diubah.";
            $jenis_pesan = 'warning';

        } else {

            // Simpan seluruh nilai dalam satu transaksi.
            mysqli_begin_transaction($conn);

            try {

                $stmt_update = mysqli_prepare($conn, "
                    UPDATE krs
                    SET
                        nilai_tm = ?,
                        nilai_uts = ?,
                        nilai_uas = ?,
                        nilai_akhir = ?,
                        grade = ?
                    WHERE id_krs = ?
                      AND id_jadwal = ?
                ");

                foreach ($data_simpan as $data) {

                    mysqli_stmt_bind_param(
                        $stmt_update,
                        "ddddsii",
                        $data['tm'],
                        $data['uts'],
                        $data['uas'],
                        $data['akhir'],
                        $data['grade'],
                        $data['id_krs'],
                        $id_jadwal
                    );

                    if (!mysqli_stmt_execute($stmt_update)) {
                        throw new Exception(mysqli_stmt_error($stmt_update));
                    }
                }

                mysqli_commit($conn);

                // Redirect untuk menghindari pengiriman ulang form.
                header("Location: input_nilai.php?id=" . $id_jadwal . "&status=berhasil");
                exit;

            } catch (Throwable $e) {

                mysqli_rollback($conn);

                $pesan = "Gagal menyimpan nilai. Silakan periksa database.";
                $jenis_pesan = 'danger';
            }
        }
    }
}

// =====================================
// NOTIFIKASI SETELAH REDIRECT
// =====================================
if (isset($_GET['status']) && $_GET['status'] === 'berhasil') {
    $pesan = "Seluruh nilai berhasil disimpan dan dihitung.";
    $jenis_pesan = 'success';
}

// =====================================
// AMBIL DATA MAHASISWA DAN NILAI
// =====================================
$stmt_mhs = mysqli_prepare($conn, "
    SELECT
        krs.id_krs,
        krs.nim,
        krs.nilai_tm,
        krs.nilai_uts,
        krs.nilai_uas,
        krs.nilai_akhir,
        krs.grade,
        mahasiswa.nama_lengkap,
        mahasiswa.jurusan
    FROM krs
    INNER JOIN mahasiswa
        ON krs.nim = mahasiswa.nim
    WHERE krs.id_jadwal = ?
    ORDER BY mahasiswa.nim ASC
");

mysqli_stmt_bind_param($stmt_mhs, "i", $id_jadwal);
mysqli_stmt_execute($stmt_mhs);

$result_mhs = mysqli_stmt_get_result($stmt_mhs);

$data_mahasiswa = [];

while ($row = mysqli_fetch_assoc($result_mhs)) {
    $data_mahasiswa[] = $row;
}

$total_mhs = count($data_mahasiswa);

?>

<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Input Nilai — NEXORA University</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>

        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(139,92,246,0.08), transparent 30%),
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
            color: #00f2fe !important;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .card-nexora {
            background: #100e26;
            border: 1px solid rgba(0,242,254,0.23);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .welcome-card {
            background: linear-gradient(
                120deg,
                rgba(0,242,254,0.12),
                rgba(139,92,246,0.14),
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
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            font-size: 21px;
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
            padding: 13px;
            border-color: rgba(255,255,255,0.07);
            font-size: 13px;
        }

        .form-control {
            background: #080715;
            border: 1px solid rgba(0,242,254,0.25);
            color: #fff;
            min-width: 90px;
        }

        .form-control:focus {
            background: #080715;
            color: #fff;
            border-color: #00f2fe;
            box-shadow: 0 0 0 0.2rem rgba(0,242,254,0.12);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .grade-badge {
            background: rgba(0,242,254,0.12);
            color: #00f2fe;
            border: 1px solid rgba(0,242,254,0.3);
            font-size: 14px;
            padding: 7px 13px;
        }

        .btn-save {
            background: linear-gradient(90deg, #00c6d7, #4776e6);
            color: #080715;
            border: none;
            border-radius: 9px;
            font-weight: 700;
        }

        .btn-save:hover {
            color: #080715;
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

        <a class="navbar-brand brand" href="dashboard_dosen.php">
            <i class="bi bi-mortarboard-fill me-2"></i>
            NEXORA
        </a>

        <div class="d-flex align-items-center gap-3">

            <div class="text-end d-none d-sm-block">

                <div class="small fw-bold">
                    <?= e($_SESSION['nama'] ?? 'Dosen'); ?>
                </div>

                <div class="small text-secondary">
                    Portal Dosen
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

        <a href="dashboard_dosen.php" class="btn btn-outline-info btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i>
            Kembali ke Dashboard
        </a>

    </div>


    <!-- INFORMASI KELAS -->
    <div class="card-nexora welcome-card p-4 mb-4">

        <div class="text-cyan small fw-bold mb-2">
            LECTURER ACADEMIC PORTAL
        </div>

        <h4 class="fw-bold mb-3">
            <i class="bi bi-award-fill text-cyan me-2"></i>
            Form Input Nilai Mahasiswa
        </h4>

        <div class="row g-3">

            <div class="col-md-6">

                <div class="small text-secondary">Mata Kuliah</div>

                <div class="fw-bold">
                    <?= e($jadwal['kode_mk']); ?> -
                    <?= e($jadwal['nama_mk']); ?>
                </div>

                <div class="small text-cyan">
                    <?= e($jadwal['sks']); ?> SKS
                </div>

                <div class="small text-secondary mt-3">
                    Dosen Pengampu
                </div>

                <div class="fw-semibold">
                    <?= e($jadwal['nama_dosen']); ?>
                </div>

            </div>

            <div class="col-md-6">

                <div class="small text-secondary">Hari / Waktu</div>

                <div class="fw-bold">
                    <?= e($jadwal['hari']); ?>,
                    <?= e(date('H:i', strtotime($jadwal['jam_mulai']))); ?>
                    -
                    <?= e(date('H:i', strtotime($jadwal['jam_selesai']))); ?>
                    WIB
                </div>

                <div class="small text-secondary mt-3">
                    Ruangan
                </div>

                <span class="badge bg-info text-dark">
                    <?= e($jadwal['ruangan']); ?>
                </span>

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
    <div class="row g-3 mb-4">

        <div class="col-md-6">

            <div class="card-nexora p-3 h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>

                        <div class="text-secondary small">
                            Mahasiswa Terdaftar
                        </div>

                        <h3 class="fw-bold mb-0">
                            <?= $total_mhs; ?>
                        </h3>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="card-nexora p-3 h-100">

                <div class="d-flex align-items-center gap-3">

                    <div class="stat-icon">
                        <i class="bi bi-calculator-fill"></i>
                    </div>

                    <div>

                        <div class="text-secondary small">
                            Komponen Penilaian
                        </div>

                        <h5 class="fw-bold mb-0 text-cyan">
                            TM 30% | UTS 30% | UAS 40%
                        </h5>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- FORM NILAI -->
    <div class="card-nexora p-3 p-md-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">

            <div>

                <h5 class="section-title text-white mb-1">
                    <i class="bi bi-journal-check text-cyan me-2"></i>
                    DAFTAR NILAI MAHASISWA
                </h5>

                <div class="small text-secondary">
                    Masukkan nilai setiap mahasiswa pada kelas ini.
                </div>

            </div>

            <span class="badge bg-info text-dark">
                <?= $total_mhs; ?> Mahasiswa
            </span>

        </div>


        <!-- KETERANGAN BOBOT -->
        <div class="alert alert-dark border border-info border-opacity-25 small mb-4">

            <i class="bi bi-info-circle text-cyan me-2"></i>

            Nilai akhir dihitung otomatis menggunakan rumus:

            <div class="fw-bold text-cyan mt-2">
                Nilai Akhir = (TM × 30%) + (UTS × 30%) + (UAS × 40%)
            </div>

            <div class="text-secondary mt-2">
                Grade: A (85-100), B (75-84,99), C (65-74,99),
                D (55-64,99), E (di bawah 55).
            </div>

        </div>


        <form method="POST" action="input_nilai.php?id=<?= $id_jadwal; ?>">

            <input type="hidden" name="csrf_token" value="<?= e($csrf); ?>">

            <div class="table-responsive">

                <table class="table table-dark table-hover align-middle mb-0">

                    <thead>

                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Nilai TM (30%)</th>
                            <th>Nilai UTS (30%)</th>
                            <th>Nilai UAS (40%)</th>
                            <th>Nilai Akhir</th>
                            <th>Grade</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($total_mhs > 0): ?>

                            <?php $no = 1; ?>

                            <?php foreach ($data_mahasiswa as $mhs): ?>

                                <tr>

                                    <td>
                                        <?= $no++; ?>
                                    </td>

                                    <td class="text-cyan fw-bold">

                                        <?= e($mhs['nim']); ?>

                                        <input type="hidden"
                                               name="id_krs[]"
                                               value="<?= (int)$mhs['id_krs']; ?>">

                                    </td>

                                    <td>

                                        <div class="fw-semibold">
                                            <?= e($mhs['nama_lengkap']); ?>
                                        </div>

                                        <small class="text-secondary">
                                            <?= e($mhs['jurusan']); ?>
                                        </small>

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="nilai_tm[]"
                                            class="form-control text-center"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            placeholder="0-100"
                                            value="<?= e($mhs['nilai_tm'] === null ? '' : $mhs['nilai_tm']); ?>"
                                            required
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="nilai_uts[]"
                                            class="form-control text-center"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            placeholder="0-100"
                                            value="<?= e($mhs['nilai_uts'] === null ? '' : $mhs['nilai_uts']); ?>"
                                            required
                                        >

                                    </td>

                                    <td>

                                        <input
                                            type="number"
                                            name="nilai_uas[]"
                                            class="form-control text-center"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            placeholder="0-100"
                                            value="<?= e($mhs['nilai_uas'] === null ? '' : $mhs['nilai_uas']); ?>"
                                            required
                                        >

                                    </td>

                                    <td class="text-center fw-bold text-cyan">

                                        <?= $mhs['nilai_akhir'] !== null
                                            ? number_format((float)$mhs['nilai_akhir'], 2)
                                            : '-'; ?>

                                    </td>

                                    <td class="text-center">

                                        <?php if (!empty($mhs['grade'])): ?>

                                            <span class="badge grade-badge">
                                                <?= e($mhs['grade']); ?>
                                            </span>

                                        <?php else: ?>

                                            <span class="text-secondary">-</span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="8" class="text-center py-5">

                                    <i class="bi bi-journal-x text-secondary"
                                       style="font-size: 40px;"></i>

                                    <div class="text-secondary mt-2">
                                        Belum ada mahasiswa yang terdaftar pada kelas ini.
                                    </div>

                                    <small class="text-secondary">
                                        Tambahkan KRS mahasiswa terlebih dahulu.
                                    </small>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>


            <?php if ($total_mhs > 0): ?>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 pt-3 border-top border-secondary">

                    <div class="small text-secondary">
                        <i class="bi bi-shield-check text-cyan me-1"></i>
                        Pastikan nilai sudah benar sebelum disimpan.
                    </div>

                    <button type="submit" name="simpan" class="btn btn-save px-4 py-2">

                        <i class="bi bi-save me-2"></i>
                        Simpan Semua Nilai

                    </button>

                </div>

            <?php endif; ?>

        </form>

    </div>


    <footer class="text-center mt-5">
        &copy; <?= date('Y'); ?> NEXORA University.
        Lecturer Academic Portal.
    </footer>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
