# Full Database Setup (Admin & User)

Berikut adalah struktur database lengkap untuk memulai dari awal. Script ini membuat tabel `admin` dan `user` beserta data dummy untuk login.

**Catatan:** Password default di bawah ini belum di-hash (plain text) untuk kemudahan tes awal. Sistem login Anda (`Login.php`) mendukung password plain text sementara (`$password === $hash`). Untuk produksi, pastikan password di-hash.

```sql
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

-- 4. Insert Data Dummy (Default Login)

-- Admin (Email: admin@kik.com, Pass: admin123)
INSERT INTO `admin` (`nama`, `email`, `password`) VALUES
('Administrator', 'admin@gmail.com', 'admin123');
```

### Jika Tabel Sudah Ada (Update Only)
Jika Anda tidak ingin menghapus tabel yang sudah ada, gunakan perintah ini untuk menambahkan kolom baru ke tabel `user`:

```sql
ALTER TABLE user ADD COLUMN divisi VARCHAR(50) DEFAULT 'Staff' AFTER email;
ALTER TABLE user ADD COLUMN jam_masuk TIME DEFAULT '08:00:00' AFTER divisi;
ALTER TABLE user ADD COLUMN jam_keluar TIME DEFAULT '17:00:00' AFTER jam_masuk;
```