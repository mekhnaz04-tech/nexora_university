
<?php
include 'header.php';
?>

<style>
    body {
        background: #080d1b;
        color: #e5e7eb;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .nexora-class-card {
        min-height: 380px;
        background: linear-gradient(145deg, #111b31, #0b1223);
        border: 1px solid rgba(0, 224, 255, 0.15);
        border-radius: 18px;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.25);
        position: relative;
        overflow: hidden;
    }

    .nexora-class-card::before {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        background: rgba(0, 224, 255, 0.08);
        filter: blur(65px);
        border-radius: 50%;
        top: -80px;
        right: -50px;
    }

    .class-icon {
        width: 85px;
        height: 85px;
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 224, 255, 0.10);
        border: 1px solid rgba(0, 224, 255, 0.25);
        color: #00e0ff;
        font-size: 38px;
        box-shadow: 0 0 25px rgba(0, 224, 255, 0.08);
    }

    .class-title {
        color: #ffffff;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .class-description {
        color: #94a3b8;
        max-width: 450px;
        line-height: 1.7;
    }

    .nexora-badge {
        display: inline-block;
        padding: 7px 15px;
        border-radius: 30px;
        background: rgba(124, 58, 237, 0.12);
        border: 1px solid rgba(167, 139, 250, 0.25);
        color: #c4b5fd;
        font-size: 12px;
        letter-spacing: 1px;
        font-weight: 600;
    }
</style>

<!-- HEADER HALAMAN -->
<div class="mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="bi bi-grid-1x2-fill text-info"></i>
        <span class="text-info small fw-semibold">ACADEMIC MANAGEMENT</span>
    </div>

    <h3 class="text-white fw-bold mb-1">Manajemen Kelas</h3>

    <p class="text-secondary mb-0">
        Kelola informasi dan data kelas akademik NEXORA University.
    </p>
</div>

<!-- KONTEN KELAS -->
<div class="nexora-class-card d-flex align-items-center justify-content-center p-4">
    <div class="text-center position-relative">

        <div class="class-icon mx-auto mb-4">
            <i class="bi bi-mortarboard-fill"></i>
        </div>

        <span class="nexora-badge mb-3">
            NEXORA ACADEMIC SYSTEM
        </span>

        <h4 class="class-title mt-3 mb-2">
            Halaman Kelas
        </h4>

        <p class="class-description mx-auto mb-4">
            Pusat pengelolaan kelas akademik untuk mendukung
            kegiatan perkuliahan, pengaturan mahasiswa, dan
            administrasi akademik NEXORA University.
        </p>

        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <span class="badge bg-dark border border-secondary text-secondary p-2">
                <i class="bi bi-people me-1"></i> Student Management
            </span>

            <span class="badge bg-dark border border-secondary text-secondary p-2">
                <i class="bi bi-calendar3 me-1"></i> Academic System
            </span>
        </div>

        <div class="mt-4">
            <span class="text-secondary small">
                <i class="bi bi-hourglass-split me-1"></i>
                Fitur pengelolaan kelas sedang dalam pengembangan.
            </span>
        </div>

    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
