<?php
session_start();
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role       = strtolower(mysqli_real_escape_string($conn, trim($_POST['role'] ?? '')));
    $identifier = mysqli_real_escape_string($conn, trim($_POST['identifier'] ?? ''));
    $password   = $_POST['password'] ?? '';

    $query  = "SELECT * FROM user WHERE username = '$identifier' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        // Cocokkan role
        if (strtolower($user['role']) !== $role) {
            $_SESSION['auth_type'] = 'warning';
            $_SESSION['auth_msg']  = "Akun <strong>$identifier</strong> terdaftar sebagai <strong>" . ucfirst($user['role']) . "</strong>, bukan " . ucfirst($role) . ".";
            header("Location: index.php");
            exit;
        }

        // Cocokkan password
        if ($password === $user['password']) {
            $_SESSION['id_user']  = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = strtolower($user['role']);

            if ($_SESSION['role'] === 'mahasiswa') {
                header("Location: dashboard_mahasiswa.php");
            } elseif ($_SESSION['role'] === 'dosen') {
                header("Location: dashboard_dosen.php");
            } elseif ($_SESSION['role'] === 'admin') {
                header("Location: dashboard_admin.php");
            }
            exit;
        } else {
            $_SESSION['auth_type'] = 'danger';
            $_SESSION['auth_msg']  = 'Login Gagal: Kata sandi salah!';
            header("Location: index.php");
            exit;
        }
    } else {
        $_SESSION['auth_type'] = 'danger';
        $_SESSION['auth_msg']  = "Login Gagal: Username <strong>$identifier</strong> belum terdaftar. Silakan klik tab <strong>Registrasi Akun</strong> terlebih dahulu.";
        header("Location: index.php");
        exit;
    }
}
?>