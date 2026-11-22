
<?php
require_once 'koneksi.php';

// ===============================
// PROSES TAMBAH MAHASISWA
// ===============================
if (isset($_POST['simpan'])) {
    $nim     = trim($_POST['nim']);
    $nama    = trim($_POST['nama']);
    $jurusan = trim($_POST['jurusan']);

    mysqli_begin_transaction($conn);

    try {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO mahasiswa (nim, nama_lengkap, jurusan)
            VALUES (?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "sss", $nim, $nama, $jurusan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Membuat akun login mahasiswa
        $stmtUser = mysqli_prepare($conn, "
            INSERT INTO user (username, password, nama, role)
            VALUES (?, ?, ?, 'mahasiswa')
        ");
        mysqli_stmt_bind_param($stmtUser, "sss", $nim, $nim, $nama);
        mysqli_stmt_execute($stmtUser);
        mysqli_stmt_close($stmtUser);

        mysqli_commit($conn);
        header("Location: mahasiswa.php?status=tambah");
        exit;

    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        header("Location: mahasiswa.php?status=gagal");
        exit;
    }
}

// ===============================
// PROSES EDIT MAHASISWA
// ===============================
if (isset($_POST['update'])) {
    $nim     = trim($_POST['nim']);
    $nama    = trim($_POST['nama']);
    $jurusan = trim($_POST['jurusan']);

    $stmt = mysqli_prepare($conn, "
        UPDATE mahasiswa
        SET nama_lengkap = ?, jurusan = ?
        WHERE nim = ?
    ");
    mysqli_stmt_bind_param($stmt, "sss", $nama, $jurusan, $nim);
    $berhasil = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($berhasil) {
        $stmtUser = mysqli_prepare($conn, "
            UPDATE user SET nama = ? WHERE username = ?
        ");
        mysqli_stmt_bind_param($stmtUser, "ss", $nama, $nim);
        mysqli_stmt_execute($stmtUser);
        mysqli_stmt_close($stmtUser);

        header("Location: mahasiswa.php?status=update");
        exit;
    }

    header("Location: mahasiswa.php?status=gagal");
    exit;
}

// ===============================
// PROSES HAPUS MAHASISWA
// ===============================
if (isset($_GET['hapus'])) {
    $nim = trim($_GET['hapus']);

    mysqli_begin_transaction($conn);

    try {
        $stmt = mysqli_prepare($conn, "
            DELETE FROM mahasiswa WHERE nim = ?
        ");
        mysqli_stmt_bind_param($stmt, "s", $nim);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmtUser = mysqli_prepare($conn, "
            DELETE FROM user WHERE username = ? AND role = 'mahasiswa'
        ");
        mysqli_stmt_bind_param($stmtUser, "s", $nim);
        mysqli_stmt_execute($stmtUser);
        mysqli_stmt_close($stmtUser);

        mysqli_commit($conn);
        header("Location: mahasiswa.php?status=hapus");
        exit;

    } catch (mysqli_sql_exception $e) {
        mysqli_rollback($conn);
        header("Location: mahasiswa.php?status=gagal_hapus");
        exit;
    }
}

// Ambil data mahasiswa
$query = mysqli_query($conn, "
    SELECT nim, nama_lengkap, jurusan
    FROM mahasiswa
    ORDER BY nama_lengkap ASC
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

    .nexora-modal .form-control,
    .nexora-modal .form-select {
        background: #0b1223;
        border: 1px solid #334155;
        color: #fff;
    }

    .nexora-modal .form-control:focus,
    .nexora-modal .form-select:focus {
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

<div class="mb-4">
    <div class="text-info small fw-semibold mb-2">
        <i class="bi bi-mortarboard-fill me-1"></i>
        NEXORA ACADEMIC SYSTEM
    </div>
    <h3 class="text-white fw-bold mb-1">Data Mahasiswa</h3>
    <p class="nexora-muted mb-0">
        Kelola data mahasiswa dan akun akses perkuliahan.
    </p>
</div>

<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-info alert-dismissible fade show">
        <?php
        $pesan = [
            'tambah'      => 'Mahasiswa berhasil ditambahkan.',
            'update'      => 'Data mahasiswa berhasil diperbarui.',
            'hapus'       => 'Data mahasiswa berhasil dihapus.',
            'gagal'       => 'Gagal menyimpan data. Periksa NIM atau kemungkinan data sudah terdaftar.',
            'gagal_hapus' => 'Data tidak dapat dihapus. Mahasiswa mungkin masih memiliki data KRS.',
        ];
        echo htmlspecialchars($pesan[$_GET['status']] ?? 'Status tidak diketahui.');
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card nexora-card shadow-lg">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-1">
                <i class="bi bi-people-fill text-info me-2"></i>
                Student Directory
            </h5>
            <small class="nexora-muted">Daftar mahasiswa NEXORA University</small>
        </div>

        <button class="btn btn-nexora fw-semibold px-3"
                data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle me-1"></i> Tambah Mahasiswa
        </button>
    </div>

    <div class="card-body p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover nexora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">No</th>
                        <th>NIM</th>
                        <th>Nama Lengkap</th>
                        <th>Program Studi</th>
                        <th class="text-center" width="190">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($query && mysqli_num_rows($query) > 0): ?>
                    <?php while ($data = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="fw-semibold text-info">
                            <?= htmlspecialchars($data['nim']); ?>
                        </td>
                        <td><?= htmlspecialchars($data['nama_lengkap']); ?></td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                <?= htmlspecialchars($data['jurusan']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit<?= $no; ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>

                            <a href="mahasiswa.php?hapus=<?= urlencode($data['nim']); ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin ingin menghapus mahasiswa ini?')">
                                <i class="bi bi-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>

                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="modalEdit<?= $no; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content nexora-modal">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">
                                            <i class="bi bi-pencil-square text-warning me-2"></i>
                                            Edit Data Mahasiswa
                                        </h5>
                                        <button type="button" class="btn-close"
                                                data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label">NIM</label>
                                            <input type="text" name="nim"
                                                   class="form-control"
                                                   value="<?= htmlspecialchars($data['nim']); ?>"
                                                   maxlength="10" readonly>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama"
                                                   class="form-control"
                                                   value="<?= htmlspecialchars($data['nama_lengkap']); ?>"
                                                   required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Program Studi</label>
                                            <select name="jurusan" class="form-select" required>
                                                <option value="">-- Pilih Program Studi --</option>
                                                <?php
                                                $jurusanList = [
                                                    'Teknik Informatika',
                                                    'Sistem Informasi',
                                                    'Sistem Komputer',
                                                    'Manajemen Informatika',
                                                    'Digital Content & Broadcasting'
                                                ];
                                                foreach ($jurusanList as $jurusan):
                                                ?>
                                                    <option value="<?= htmlspecialchars($jurusan); ?>"
                                                        <?= $data['jurusan'] === $jurusan ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($jurusan); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" name="update"
                                                class="btn btn-nexora px-4">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 nexora-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Belum ada data mahasiswa.
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
                        <i class="bi bi-person-plus-fill text-info me-2"></i>
                        Tambah Mahasiswa
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIM</label>
                        <input type="text" name="nim" class="form-control"
                               maxlength="10" placeholder="Contoh: 2026042356"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control"
                               placeholder="Masukkan nama lengkap" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Program Studi</label>
                        <select name="jurusan" class="form-select" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <option value="Teknik Informatika">Teknik Informatika</option>
                            <option value="Sistem Informasi">Sistem Informasi</option>
                            <option value="Sistem Komputer">Sistem Komputer</option>
                            <option value="Manajemen Informatika">Manajemen Informatika</option>
                            <option value="Digital Content & Broadcasting">Digital Content & Broadcasting</option>
                        </select>
                    </div>

                    <small class="nexora-muted">
                        Akun mahasiswa akan dibuat otomatis dengan username dan password awal berupa NIM.
                    </small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan"
                            class="btn btn-nexora px-4">
                        <i class="bi bi-save me-1"></i> Simpan Mahasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
