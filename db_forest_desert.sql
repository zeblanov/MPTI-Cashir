-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Okt 2025 pada 14.04
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_forest_desert`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` varchar(20) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` decimal(10,0) NOT NULL,
  `kategori_utama` varchar(50) NOT NULL,
  `sub_kategori` varchar(50) NOT NULL,
  `gambar` varchar(255) DEFAULT 'assets/images/placeholder_default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id`, `nama_menu`, `harga`, `kategori_utama`, `sub_kategori`, `gambar`) VALUES
('BV01', 'Iced Americano', 15000, 'BEVERAGES', 'Beverages (Mini BV)', 'assets/images/placeholder_beverage.png'),
('BV02', 'Chocolate Hazelnut', 22000, 'BEVERAGES', 'Beverages (Mini BV)', 'assets/images/placeholder_beverage.png'),
('BV03', 'Lychee Tea', 18000, 'BEVERAGES', 'Beverages (Mini BV)', 'assets/images/placeholder_beverage.png'),
('DB01', 'Choco Lava Box', 35000, 'DESSERTS & PASTRY', 'Signature Desserts', 'assets/images/placeholder_dessert.png'),
('DB02', 'Red Velvet Cheese', 38000, 'DESSERTS & PASTRY', 'Signature Desserts', 'assets/images/placeholder_dessert.png'),
('DB04', 'Matcha Green Tea', 30000, 'DESSERTS & PASTRY', 'Signature Desserts', 'assets/images/placeholder_dessert.png'),
('DB05', 'Biscoff Lotus', 40000, 'DESSERTS & PASTRY', 'Signature Desserts', 'assets/images/placeholder_dessert.png'),
('FD01', 'Mango Sago Fusion', 28000, 'DESSERTS & PASTRY', 'Fresh & Frozen', 'assets/images/placeholder_dessert.png'),
('FD02', 'Korean Strawberry', 25000, 'DESSERTS & PASTRY', 'Fresh & Frozen', 'assets/images/placeholder_dessert.png'),
('FD03', 'Es Campur Kekinian', 27000, 'DESSERTS & PASTRY', 'Fresh & Frozen', 'assets/images/placeholder_dessert.png'),
('FD04', 'Silky Puding Coklat', 20000, 'DESSERTS & PASTRY', 'Fresh & Frozen', 'assets/images/placeholder_dessert.png'),
('PB01', 'Mini Fruit Tart', 15000, 'DESSERTS & PASTRY', 'Pastry & Baked', 'assets/images/placeholder_dessert.png'),
('PB02', 'Soft Cookies Chocochip', 12000, 'DESSERTS & PASTRY', 'Pastry & Baked', 'assets/images/placeholder_dessert.png'),
('PB03', 'Cinnamon Roll Cream', 18000, 'DESSERTS & PASTRY', 'Pastry & Baked', 'assets/images/placeholder_dessert.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `tanggal` datetime NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `metode_pembayaran` varchar(20) DEFAULT NULL,
  `jumlah_bayar` decimal(12,2) DEFAULT NULL,
  `kembalian` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `order_id`, `tanggal`, `total`, `metode_pembayaran`, `jumlah_bayar`, `kembalian`) VALUES
(1, 'TRX-1761113570-348', '2025-10-22 08:12:50', 12000.00, 'Tunai', 20000.00, 8000.00),
(2, 'TRX-1761113589-280', '2025-10-22 08:13:09', 12000.00, 'Tunai', 20000.00, 8000.00),
(3, 'TRX-1761477569-444', '2025-10-26 12:19:29', 54000.00, 'Tunai', 60000.00, 6000.00),
(4, 'TRX-1761477696-605', '2025-10-26 12:21:36', 18000.00, 'QRIS', 18000.00, 0.00),
(5, 'TRX-1761477883-577', '2025-10-26 12:24:43', 15000.00, 'QRIS', 15000.00, 0.00),
(6, 'TRX-1761477899-344', '2025-10-26 12:24:59', 15000.00, 'QRIS', 15000.00, 0.00),
(7, 'TRX-20251026122940-716', '2025-10-26 12:29:40', 18000.00, 'QRIS', 18000.00, 0.00),
(8, 'TRX-20251026124700-223', '2025-10-26 12:47:00', 18000.00, 'QRIS', 18000.00, 0.00),
(9, 'TRX-20251026125328-537', '2025-10-26 12:53:28', 22000.00, 'Tunai', 25000.00, 3000.00),
(10, 'TRX-20251026125438-720', '2025-10-26 12:54:38', 18000.00, 'QRIS', 18000.00, 0.00),
(11, 'TRX-20251026125856-882', '2025-10-26 12:58:56', 18000.00, 'QRIS', 18000.00, 0.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_items`
--

CREATE TABLE `transaksi_items` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) NOT NULL,
  `item_id_menu` varchar(20) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `item_price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi_items`
--

INSERT INTO `transaksi_items` (`id`, `transaksi_id`, `item_id_menu`, `item_name`, `item_price`, `qty`) VALUES
(1, 1, 'PB02', 'Soft Cookies Chocochip', 12000.00, 1),
(2, 2, 'PB02', 'Soft Cookies Chocochip', 12000.00, 1),
(3, 3, 'FD03', 'Es Campur Kekinian', 27000.00, 2),
(4, 4, 'BV03', 'Lychee Tea', 18000.00, 1),
(5, 5, 'BV01', 'Iced Americano', 15000.00, 1),
(6, 6, 'BV01', 'Iced Americano', 15000.00, 1),
(7, 7, 'BV03', 'Lychee Tea', 18000.00, 1),
(8, 8, 'BV03', 'Lychee Tea', 18000.00, 1),
(9, 9, 'BV02', 'Chocolate Hazelnut', 22000.00, 1),
(10, 10, 'BV03', 'Lychee Tea', 18000.00, 1),
(11, 11, 'BV03', 'Lychee Tea', 18000.00, 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transaksi_id` (`transaksi_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  ADD CONSTRAINT `fk_transaksi_id` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
