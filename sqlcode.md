-- 1. Buat Database (Jika belum ada)
CREATE DATABASE IF NOT EXISTS kik_karyawan;
USE kik_karyawan;

-- 2. Buat Tabel Admin
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Buat Tabel User (Pegawai) dengan kolom Divisi & Jadwal
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `divisi` varchar(50) DEFAULT 'Staff',
  `jam_masuk` time DEFAULT '08:00:00',
  `jam_keluar` time DEFAULT '17:00:00',
  `role` enum('admin','pegawai') DEFAULT 'pegawai',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Buat Tabel Presensi
CREATE TABLE IF NOT EXISTS `presensi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_keluar` time DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_presensi_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Insert Data Dummy Admin (Email: admin@gmail.com, Pass: admin123)
INSERT INTO `admin` (`nama`, `email`, `password`) VALUES
('Administrator', 'admin@gmail.com', 'admin123');