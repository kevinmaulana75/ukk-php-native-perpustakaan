-- Database schema untuk Aplikasi Perpustakaan Bookavy
-- Buat database 'perpus' sebelum mengimpor file ini atau biarkan script ini membuatnya secara otomatis jika diizinkan.

CREATE DATABASE IF NOT EXISTS `perpus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `perpus`;

-- --------------------------------------------------------
-- 1. Struktur Tabel `admin`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_admin` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 2. Struktur Tabel `anggota`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `anggota` (
  `id_anggota` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `kelas` VARCHAR(50) NOT NULL,
  `alamat` TEXT NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `tanggal_daftar` DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 3. Struktur Tabel `buku`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `buku` (
  `id_buku` INT AUTO_INCREMENT PRIMARY KEY,
  `judul_buku` VARCHAR(255) NOT NULL,
  `kategori` VARCHAR(100) DEFAULT NULL,
  `pengarang` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100) NOT NULL,
  `tahun_terbit` INT NOT NULL,
  `stok` INT NOT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 4. Struktur Tabel `peminjaman`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id_peminjaman` INT AUTO_INCREMENT PRIMARY KEY,
  `id_anggota` INT NOT NULL,
  `id_buku` INT NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `tanggal_kembali` DATE NOT NULL,
  `status` ENUM('menunggu', 'dipinjam', 'pengajuan_kembali', 'dikembalikan') NOT NULL DEFAULT 'menunggu',
  `catatan` TEXT DEFAULT NULL,
  `denda` INT DEFAULT 0,
  FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`) ON DELETE CASCADE,
  FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data Awal (Seed Data)
-- --------------------------------------------------------

-- Default Admin (Password: admin123)
INSERT INTO `admin` (`nama_admin`, `username`, `password`) VALUES
('Administrator Perpus', 'admin', '$2y$10$10Bnlosw9wW6wkuGn1mpZOGe4muBOAsxKhaa0/v/VjSr.WH1vy7Em')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Default Anggota (Password: anggota123)
INSERT INTO `anggota` (`nama`, `kelas`, `alamat`, `username`, `password`, `tanggal_daftar`) VALUES
('Kevin Maulana', 'XII RPL 1', 'Jl. Kebon Jeruk No. 25, Jakarta', 'anggota', '$2y$10$rG6mN/uk9AQ0ONUA9Jst2OaH.bpa/lMelKujya0oldgBUk06u390u', '2026-06-21')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Default Buku
INSERT INTO `buku` (`judul_buku`, `kategori`, `pengarang`, `penerbit`, `tahun_terbit`, `stok`, `gambar`) VALUES
('Laskar Pelangi', 'Novel', 'Andrea Hirata', 'Bentang Pustaka', 2005, 5, NULL),
('Bumi Manusia', 'Novel', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 3, NULL),
('Algoritma dan Struktur Data', 'Teknologi', 'Dr. Eng. Rinaldi Munir', 'Informatika Bandung', 2011, 4, NULL),
('Filosofi Teras', 'Filsafat', 'Henry Manampiring', 'Kompas Penerbit Buku', 2018, 6, NULL);
