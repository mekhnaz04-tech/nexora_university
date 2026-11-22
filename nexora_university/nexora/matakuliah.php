
<?php
require_once 'koneksi.php';

// ===============================
// PROSES TAMBAH MATA KULIAH
// ===============================
if (isset($_POST['simpan'])) {
    $kode_mk = strtoupper(trim($_POST['kode_mk']));
    $nama_mk = trim($_POST['nama_mk']);
    $sks     = (int) $_POST['sks'];

    $stmt = mysqli_prepare($conn, "
        INSERT INTO matakuliah (kode_mk, nama_mk, sks)
        VALUES (?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmt, "ssi", $kode_mk, $nama_mk, $sks);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: matakuliah.php?status=" . ($berhasil ? "tambah" : "gagal"));
    exit;
}

// ===============================
// PROSES EDIT MATA KULIAH
// ===============================
if (isset($_POST['update'])) {
    $kode_mk = trim($_POST['kode_mk']);
    $nama_mk = trim($_POST['nama_mk']);
    $sks     = (int) $_POST['sks'];

    $stmt = mysqli_prepare($conn, "
        UPDATE matakuliah
        SET nama_mk = ?, sks = ?
        WHERE kode_mk = ?
    ");
    mysqli_stmt_bind_param($stmt, "sis", $nama_mk, $sks, $kode_mk);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: matakuliah.php?status=" . ($berhasil ? "update" : "gagal"));
    exit;
}

// ===============================
// PROSES HAPUS MATA KULIAH
// ===============================
if (isset($_GET['hapus'])) {
    $kode_mk = trim($_GET['hapus']);

    $stmt = mysqli_prepare($conn, "
        DELETE FROM matakuliah WHERE kode_mk = ?
    ");
    mysqli_stmt_bind_param($stmt, "s", $kode_mk);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: matakuliah.php?status=" . ($berhasil ? "hapus" : "gagal_hapus"));
    exit;
}

// Ambil data mata kuliah
$query = mysqli_query($conn, "
    SELECT kode_mk, nama_mk, sks
    FROM matakuliah
    ORDER BY kode_mk ASC
");

$no = 1;

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
        white-space: nowrap;
    }

    .nexora-table td {
        vertical-align: middle;
    }

    .nexora-modal {
        background: #111b31;
        color: #e5e7eb;
        border: 1px solid rgba(0, 224, 255, .2);
        border-radius: 16px;
    }

    .nexora-modal .modal-header,
    .nexora-modal .modal-footer {
        border-color: rgba(148, 163, 184, .2);
    }

    .nexora-modal .form-control {
        background: #0b1223;
        border: 1px solid #334155;
        color: #fff;
    }

    .nexora-modal .form-control:focus {
        border-color: #00e0ff;
        box-shadow: 0 0 0 .2rem rgba(0, 224, 255, .12);
    }

    .nexora-modal .btn-close {
        filter: invert(1);
    }

    .btn-nexora {
        background: linear-gradient(90deg, #0891b2, #6366f1);
        color: white;
        border: 0;
    }

    .btn-nexora:hover {
        color: white;
        opacity: .9;
    }

    .nexora-muted {
        color: #94a3b8;
    }
</style>

<!-- HEADER HALAMAN -->
<div class="mb-4">
    <div class="text-info small fw-semibold mb-2">
        <i class="bi bi-mortarboard-fill me-1"></i>
        NEXORA ACADEMIC SYSTEM
    </div>

    <h3 class="text-white fw-bold mb-1">Data Mata Kuliah</h3>

    <p class="nexora-muted mb-0">
        Kelola daftar mata kuliah dan jumlah SKS dalam program akademik.
    </p>
</div>

<!-- NOTIFIKASI -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php
        $pesan = [
            'tambah'      => 'Mata kuliah berhasil ditambahkan.',
            'update'      => 'Data mata kuliah berhasil diperbarui.',
            'hapus'       => 'Mata kuliah berhasil dihapus.',
            'gagal'       => 'Gagal menyimpan data. Pastikan kode mata kuliah belum digunakan.',
            'gagal_hapus' => 'Mata kuliah tidak dapat dihapus karena mungkin masih digunakan pada jadwal kuliah.'
        ];

        echo htmlspecialchars($pesan[$_GET['status']] ?? 'Status tidak diketahui.');
        ?>

        <button type="button" class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- CARD DATA MATA KULIAH -->
<div class="card nexora-card shadow-lg">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="bi bi-journal-bookmark-fill text-info me-2"></i>
                Course Management
            </h5>
            <small class="nexora-muted">
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

    <div class="card-body p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover nexora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">No</th>
                        <th width="150">Kode MK</th>
                        <th>Nama Mata Kuliah</th>
                        <th class="text-center" width="110">SKS</th>
                        <th class="text-center" width="190">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($query && mysqli_num_rows($query) > 0): ?>
                    <?php while ($data = mysqli_fetch_assoc($query)): ?>
                        <?php $idModal = 'modalEdit' . $no; ?>

                        <tr>
                            <td class="text-center"><?= $no; ?></td>

                            <td class="fw-bold text-info">
                                <?= htmlspecialchars($data['kode_mk']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['nama_mk']); ?>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                    <?= (int) $data['sks']; ?> SKS
                                </span>
                            </td>

                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#<?= $idModal; ?>">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>

                                <a href="matakuliah.php?hapus=<?= urlencode($data['kode_mk']); ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="<?= $idModal; ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content nexora-modal">
                                    <form method="POST">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-pencil-square text-warning me-2"></i>
                                                Edit Mata Kuliah
                                            </h5>

                                            <button type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Kode Mata Kuliah</label>
                                                <input type="text"
                                                       name="kode_mk"
                                                       class="form-control"
                                                       maxlength="5"
                                                       value="<?= htmlspecialchars($data['kode_mk']); ?>"
                                                       readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nama Mata Kuliah</label>
                                                <input type="text"
                                                       name="nama_mk"
                                                       class="form-control"
                                                       value="<?= htmlspecialchars($data['nama_mk']); ?>"
                                                       required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Jumlah SKS</label>
                                                <input type="number"
                                                       name="sks"
                                                       class="form-control"
                                                       min="1"
                                                       max="6"
                                                       value="<?= (int) $data['sks']; ?>"
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
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php $no++; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 nexora-muted">
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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content nexora-modal">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle-fill text-info me-2"></i>
                        Tambah Mata Kuliah
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Kuliah</label>
                        <input type="text"
                               name="kode_mk"
                               class="form-control"
                               maxlength="5"
                               placeholder="Contoh: MK001"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Mata Kuliah</label>
                        <input type="text"
                               name="nama_mk"
                               class="form-control"
                               placeholder="Masukkan nama mata kuliah"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah SKS</label>
                        <input type="number"
                               name="sks"
                               class="form-control"
                               min="1"
                               max="6"
                               placeholder="Contoh: 3"
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
