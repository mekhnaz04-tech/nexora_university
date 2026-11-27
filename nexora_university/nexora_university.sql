-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Nov 2026 pada 01.52
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nexora_university`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `dosen`
--

CREATE TABLE `dosen` (
  `nid` char(5) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dosen`
--

INSERT INTO `dosen` (`nid`, `nama_lengkap`) VALUES
('12345', 'Ariyanto');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `kode_mk` char(5) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `ruangan` char(5) NOT NULL,
  `nid` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `kode_mk`, `hari`, `jam_mulai`, `jam_selesai`, `ruangan`, `nid`) VALUES
(1, 'GD103', 'Selasa', '08:30:00', '10:00:00', 'L-205', '12345');

-- --------------------------------------------------------

--
-- Struktur dari tabel `krs`
--

CREATE TABLE `krs` (
  `id_krs` int(11) NOT NULL,
  `nim` char(10) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `pertemuan1` time DEFAULT NULL,
  `pertemuan2` time DEFAULT NULL,
  `pertemuan3` time DEFAULT NULL,
  `pertemuan4` time DEFAULT NULL,
  `pertemuan5` time DEFAULT NULL,
  `nilai_tm` int(3) DEFAULT NULL,
  `nilai_uts` int(3) DEFAULT NULL,
  `nilai_uas` int(3) DEFAULT NULL,
  `nilai_akhir` decimal(5,2) DEFAULT NULL,
  `grade` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `krs`
--

INSERT INTO `krs` (`id_krs`, `nim`, `id_jadwal`, `pertemuan1`, `pertemuan2`, `pertemuan3`, `pertemuan4`, `pertemuan5`, `nilai_tm`, `nilai_uts`, `nilai_uas`, `nilai_akhir`, `grade`) VALUES
(1, '2026042356', 1, '07:49:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `nim` char(10) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `jurusan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `mahasiswa`
--

INSERT INTO `mahasiswa` (`nim`, `nama_lengkap`, `jurusan`) VALUES
('2026042356', 'Ananda Gina', 'Digital Content & Broadcasting');

-- --------------------------------------------------------

--
-- Struktur dari tabel `matakuliah`
--

CREATE TABLE `matakuliah` (
  `kode_mk` char(5) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `matakuliah`
--

INSERT INTO `matakuliah` (`kode_mk`, `nama_mk`, `sks`) VALUES
('DC101', 'Live Tournament Production & OB Van', 4),
('DC102', 'Shoutcasting, Analyst Desk & Public Speaking', 3),
('DC103', 'Motion Graphics, Virtual Studio & AR/HUD', 3),
('DC104', 'Storytelling & Produksi Konten Kreator', 3),
('DC105', 'Manajemen Siaran Digital & Hak Siar', 2),
('EM101', 'Manajemen Ekosistem & Bisnis Esports', 3),
('EM102', 'Data Science & In-Game Telemetry', 4),
('EM103', 'Event & Tournament Operations', 3),
('EM104', 'Sports Law, Kontrak & Brand Partnership', 3),
('EM105', 'Psikologi Kompetitif & Pembinaan Atlet', 2),
('GD101', 'Unreal Engine 5, C++ & Vulkan Graphics', 4),
('GD102', 'Multiplayer Netcode & Anti-Cheat Security', 4),
('GD103', 'Game Design, Level Balancing & UI/UX', 3),
('GD104', 'Kecerdasan Buatan (AI) untuk Gim', 3),
('GD105', 'Arsitektur Server & XR/VR Programming', 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id_user`, `username`, `password`, `nama`, `role`) VALUES
(3, 'admin01', 'admin94', 'Administrator NEXORA', 'admin'),
(4, '2026042356', 'gina20', 'Ananda Gina', 'mahasiswa'),
(5, '12345', '123456', 'Ariyanto', 'dosen');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`nid`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `fk_jadwal_mk` (`kode_mk`),
  ADD KEY `fk_jadwal_dosen` (`nid`);

--
-- Indeks untuk tabel `krs`
--
ALTER TABLE `krs`
  ADD PRIMARY KEY (`id_krs`),
  ADD KEY `fk_krs_mahasiswa` (`nim`),
  ADD KEY `fk_krs_jadwal` (`id_jadwal`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`);

--
-- Indeks untuk tabel `matakuliah`
--
ALTER TABLE `matakuliah`
  ADD PRIMARY KEY (`kode_mk`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `krs`
--
ALTER TABLE `krs`
  MODIFY `id_krs` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD CONSTRAINT `fk_jadwal_dosen` FOREIGN KEY (`nid`) REFERENCES `dosen` (`nid`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jadwal_mk` FOREIGN KEY (`kode_mk`) REFERENCES `matakuliah` (`kode_mk`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `krs`
--
ALTER TABLE `krs`
  ADD CONSTRAINT `fk_krs_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_krs_mahasiswa` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
