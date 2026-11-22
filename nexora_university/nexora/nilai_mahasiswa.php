
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'koneksi.php';

// =====================================
// CEK LOGIN MAHASISWA
// =====================================
if (
    !isset($_SESSION['username']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'mahasiswa'
) {
    header("Location: login.php");
    exit;
}

$nim = trim($_SESSION['username']);

// =====================================
// AMBIL DATA NILAI MAHASISWA
// =====================================
$stmt = mysqli_prepare($conn, "
    SELECT
        krs.*,
        jadwal.kode_mk,
        matakuliah.nama_mk,
        matakuliah.sks,
        dosen.nama_lengkap AS nama_dosen
    FROM krs
    INNER JOIN jadwal
        ON krs.id_jadwal = jadwal.id_jadwal
    INNER JOIN matakuliah
        ON jadwal.kode_mk = matakuliah.kode_mk
    INNER JOIN dosen
        ON jadwal.nid = dosen.nid
    WHERE krs.nim = ?
    ORDER BY matakuliah.kode_mk ASC
");

if (!$stmt) {
    die("Query gagal disiapkan: " . htmlspecialchars(mysqli_error($conn)));
}

mysqli_stmt_bind_param($stmt, "s", $nim);
mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

if (!$query) {
    die("Gagal mengambil data nilai.");
}

// =====================================
// INISIALISASI PERHITUNGAN
// =====================================
$no = 1;
$total_sks = 0;
$total_sks_dinilai = 0;
$total_mutu = 0;

$data_nilai = mysqli_fetch_all($query, MYSQLI_ASSOC);

// =====================================
// HITUNG TOTAL SKS DAN IPS
// =====================================
foreach ($data_nilai as $data) {

    $sks = (int)$data['sks'];
    $total_sks += $sks;

    if ($data['grade'] !== null && $data['grade'] !== '') {

        $bobot = 0;

        switch (strtoupper($data['grade'])) {
            case 'A':
                $bobot = 4;
                break;
            case 'B':
                $bobot = 3;
                break;
            case 'C':
                $bobot = 2;
                break;
            case 'D':
                $bobot = 1;
                break;
            case 'E':
                $bobot = 0;
                break;
        }

        $total_sks_dinilai += $sks;
        $total_mutu += ($bobot * $sks);
    }
}

$ips = ($total_sks_dinilai > 0)
    ? $total_mutu / $total_sks_dinilai
    : 0;

include 'header.php';
?>

<style>
    .nexora-card {
        background: linear-gradient(145deg, #111b31, #0b1223);
        border: 1px solid rgba(0, 224, 255, .15);
        border-radius: 16px;
        color: #e5e7eb;
    }

    .nexora-card .card-header {
        background: rgba(8, 15, 30, .7);
        border-bottom: 1px solid rgba(0, 224, 255, .15);
        color: #fff;
        border-radius: 16px 16px 0 0;
    }

    .nexora-table {
        --bs-table-bg: transparent;
        --bs-table-color: #dbeafe;
        --bs-table-border-color: rgba(148, 163, 184, .15);
        --bs-table-hover-bg: rgba(0, 224, 255, .06);
        --bs-table-hover-color: #fff;
    }

    .nexora-table thead th {
        background: #17243b;
        color: #67e8f9;
        text-align: center;
        white-space: nowrap;
        font-size: 13px;
        vertical-align: middle;
    }

    .nexora-table td {
        vertical-align: middle;
        font-size: 13px;
    }

    .nexora-table tfoot td,
    .nexora-table tfoot th {
        background: #17243b;
        color: #e5e7eb;
        border-color: rgba(148, 163, 184, .15);
    }

    .nexora-muted {
        color: #94a3b8;
    }

    .ips-box {
        background: rgba(16, 185, 129, .08);
        border: 1px solid rgba(16, 185, 129, .25);
        border-radius: 12px;
        padding: 10px 18px;
    }
</style>

<!-- =====================================
     HEADER HALAMAN
===================================== -->
<div class="mb-4">

    <div class="text-info small fw-semibold mb-2">
        <i class="bi bi-mortarboard-fill me-1"></i>
        NEXORA STUDENT PORTAL
    </div>

    <h3 class="text-white fw-bold mb-1">
        Kartu Hasil Studi
    </h3>

    <p class="nexora-muted mb-0">
        Informasi nilai akademik dan indeks prestasi mahasiswa.
    </p>

</div>

<!-- =====================================
     CARD HASIL STUDI
===================================== -->
<div class="card nexora-card shadow-lg">

    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">

        <div>
            <h5 class="fw-bold mb-1">
                <i class="bi bi-award-fill text-info me-2"></i>
                Academic Result
            </h5>

            <small class="nexora-muted">
                Rekapitulasi hasil studi mahasiswa
            </small>
        </div>

        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
            NIM: <?= htmlspecialchars($nim, ENT_QUOTES, 'UTF-8'); ?>
        </span>

    </div>

    <div class="card-body p-3 p-md-4">

        <div class="table-responsive">

            <table class="table table-hover nexora-table align-middle mb-0">

                <thead>
                    <tr>
                        <th width="50">No</th>
                        <th width="100">Kode MK</th>
                        <th class="text-start">Mata Kuliah & Dosen</th>
                        <th width="65">SKS</th>
                        <th>Nilai TM<br><small>(30%)</small></th>
                        <th>Nilai UTS<br><small>(30%)</small></th>
                        <th>Nilai UAS<br><small>(40%)</small></th>
                        <th>Nilai Akhir</th>
                        <th>Grade</th>
                    </tr>
                </thead>

                <tbody>

                <?php if (count($data_nilai) > 0): ?>

                    <?php foreach ($data_nilai as $data): ?>

                        <?php
                        $grade = strtoupper(trim((string)$data['grade']));

                        $badge_grade = 'bg-secondary';

                        switch ($grade) {
                            case 'A':
                                $badge_grade = 'bg-success';
                                break;

                            case 'B':
                                $badge_grade = 'bg-primary';
                                break;

                            case 'C':
                                $badge_grade = 'bg-info text-dark';
                                break;

                            case 'D':
                                $badge_grade = 'bg-warning text-dark';
                                break;

                            case 'E':
                                $badge_grade = 'bg-danger';
                                break;
                        }
                        ?>

                        <tr>

                            <td class="text-center">
                                <?= $no++; ?>
                            </td>

                            <td class="text-center fw-semibold text-info">
                                <?= htmlspecialchars($data['kode_mk'], ENT_QUOTES, 'UTF-8'); ?>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($data['nama_mk'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>

                                <small class="nexora-muted">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?= htmlspecialchars($data['nama_dosen'], ENT_QUOTES, 'UTF-8'); ?>
                                </small>
                            </td>

                            <td class="text-center">
                                <?= (int)$data['sks']; ?>
                            </td>

                            <td class="text-center">
                                <?= $data['nilai_tm'] !== null
                                    ? number_format((float)$data['nilai_tm'], 2)
                                    : '-'; ?>
                            </td>

                            <td class="text-center">
                                <?= $data['nilai_uts'] !== null
                                    ? number_format((float)$data['nilai_uts'], 2)
                                    : '-'; ?>
                            </td>

                            <td class="text-center">
                                <?= $data['nilai_uas'] !== null
                                    ? number_format((float)$data['nilai_uas'], 2)
                                    : '-'; ?>
                            </td>

                            <td class="text-center fw-bold text-info">
                                <?= $data['nilai_akhir'] !== null
                                    ? number_format((float)$data['nilai_akhir'], 2)
                                    : '-'; ?>
                            </td>

                            <td class="text-center">

                                <?php if ($grade !== ''): ?>

                                    <span class="badge <?= $badge_grade; ?> px-3 py-2">
                                        <?= htmlspecialchars($grade, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>

                                <?php else: ?>

                                    <span class="nexora-muted">Belum ada</span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="9" class="text-center py-5 nexora-muted">

                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>

                            Belum ada data nilai untuk NIM Anda.

                        </td>
                    </tr>

                <?php endif; ?>

                </tbody>

                <?php if (count($data_nilai) > 0): ?>

                    <tfoot>
                        <tr>

                            <th colspan="3" class="text-end">
                                Total SKS
                            </th>

                            <th class="text-center text-info">
                                <?= $total_sks; ?>
                            </th>

                            <th colspan="3" class="text-end">
                                Indeks Prestasi
                            </th>

                            <th colspan="2" class="text-center">

                                <div class="ips-box">

                                    <span class="text-success fw-bold fs-5">
                                        <?= number_format($ips, 2); ?>
                                    </span>

                                    <div class="nexora-muted small">
                                        IPS
                                    </div>

                                </div>

                            </th>

                        </tr>
                    </tfoot>

                <?php endif; ?>

            </table>

        </div>

        <!-- KETERANGAN -->
        <div class="mt-3 nexora-muted small">
            <i class="bi bi-info-circle me-1 text-info"></i>
            IPS dihitung berdasarkan mata kuliah yang sudah memiliki grade.
            Mata kuliah tanpa grade belum masuk perhitungan IPS.
        </div>

        <div class="mt-2 nexora-muted small">
            <i class="bi bi-calculator me-1 text-info"></i>
            Bobot nilai: TM 30%, UTS 30%, dan UAS 40%.
        </div>

    </div>
</div>

<?php
mysqli_stmt_close($stmt);
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
