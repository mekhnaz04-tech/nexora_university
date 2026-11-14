<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role          = strtolower(mysqli_real_escape_string($conn, trim($_POST['role'] ?? '')));
    $username      = mysqli_real_escape_string($conn, trim($_POST['username'] ?? ''));
    $nama_lengkap  = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap'] ?? ''));
    $info_tambahan = mysqli_real_escape_string($conn, trim($_POST['info_tambahan'] ?? ''));
    $password      = $_POST['password'] ?? '';
    $confirm       = $_POST['confirm_password'] ?? '';

    // 1. Validasi konfirmasi password
    if ($password !== $confirm) {
        $_SESSION['auth_type'] = 'danger';
        $_SESSION['auth_msg']  = 'Registrasi Gagal: Konfirmasi kata sandi tidak cocok!';
        header("Location: index.php");
        exit;
    }

    // 2. Validasi panjang karakter NIM (10), NID (5), Admin (maks 10)
    if ($role === 'mahasiswa' && strlen($username) !== 10) {
        $_SESSION['auth_type'] = 'warning';
        $_SESSION['auth_msg']  = "Registrasi Gagal: NIM Mahasiswa harus tepat <strong>10 karakter</strong> (Anda memasukkan " . strlen($username) . " karakter: <code>$username</code>).";
        header("Location: index.php");
        exit;
    }
    if ($role === 'dosen' && strlen($username) !== 5) {
        $_SESSION['auth_type'] = 'warning';
        $_SESSION['auth_msg']  = "Registrasi Gagal: NID Dosen harus tepat <strong>5 karakter</strong> (Anda memasukkan " . strlen($username) . " karakter: <code>$username</code>).";
        header("Location: index.php");
        exit;
    }
    if (strlen($username) > 10 || empty($username)) {
        $_SESSION['auth_type'] = 'warning';
        $_SESSION['auth_msg']  = 'Registrasi Gagal: Username tidak boleh kosong dan maksimal 10 karakter!';
        header("Location: index.php");
        exit;
    }

    // 3. Cek apakah username sudah terdaftar di tabel user
    $cek = mysqli_query($conn, "SELECT id_user FROM user WHERE username = '$username'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        $_SESSION['auth_type'] = 'warning';
        $_SESSION['auth_msg']  = "Username/ID <strong>$username</strong> sudah terdaftar di database! Silakan langsung login.";
        header("Location: index.php");
        exit;
    }

    // Simpan kata sandi teks biasa agar sinkron dengan data phpMyAdmin
    $plain_password = mysqli_real_escape_string($conn, $password);

    // 4. Simpan ke tabel utama: user
    $q_user = "INSERT INTO user (username, password, nama, role) 
               VALUES ('$username', '$plain_password', '$nama_lengkap', '$role')";

    if (!mysqli_query($conn, $q_user)) {
        $_SESSION['auth_type'] = 'danger';
        $_SESSION['auth_msg']  = "Gagal menyimpan ke tabel <strong>user</strong>: " . mysqli_error($conn);
        header("Location: index.php");
        exit;
    }

    // 5. Simpan otomatis ke tabel detail sesuai role (mahasiswa / dosen)
    if ($role === 'mahasiswa') {
        // Cek apakah NIM sudah ada di tabel mahasiswa
        $cek_mhs = mysqli_query($conn, "SELECT nim FROM mahasiswa WHERE nim = '$username'");
        if ($cek_mhs && mysqli_num_rows($cek_mhs) === 0) {
            $q_mhs = "INSERT INTO mahasiswa (nim, nama_lengkap, jurusan) 
                      VALUES ('$username', '$nama_lengkap', '$info_tambahan')";
            if (!mysqli_query($conn, $q_mhs)) {
                // Hapus dari tabel user jika tabel mahasiswa gagal agar data tetap sinkron
                mysqli_query($conn, "DELETE FROM user WHERE username = '$username'");
                $_SESSION['auth_type'] = 'danger';
                $_SESSION['auth_msg']  = "Gagal menyimpan ke tabel <strong>mahasiswa</strong>: " . mysqli_error($conn);
                header("Location: index.php");
                exit;
            }
        }
    } elseif ($role === 'dosen') {
        // Cek apakah NID sudah ada di tabel dosen
        $cek_dsn = mysqli_query($conn, "SELECT nid FROM dosen WHERE nid = '$username'");
        if ($cek_dsn && mysqli_num_rows($cek_dsn) === 0) {
            $q_dosen = "INSERT INTO dosen (nid, nama_lengkap) 
                        VALUES ('$username', '$nama_lengkap')";
            if (!mysqli_query($conn, $q_dosen)) {
                mysqli_query($conn, "DELETE FROM user WHERE username = '$username'");
                $_SESSION['auth_type'] = 'danger';
                $_SESSION['auth_msg']  = "Gagal menyimpan ke tabel <strong>dosen</strong>: " . mysqli_error($conn);
                header("Location: index.php");
                exit;
            }
        }
    }

    // Jika semua berhasil tersimpan ke database
    $_SESSION['auth_type'] = 'success';
    $_SESSION['auth_msg']  = "Registrasi <strong>" . ucfirst($role) . "</strong> (<code>$username</code>) berhasil tersimpan ke database! Silakan login sekarang.";
    header("Location: index.php");
    exit;
}
?>