<?php
session_start();
// Pastikan Anda sudah include 'koneksi.php' dan library FPDF

require('fpdf/fpdf.php');
include 'koneksi.php'; // Digunakan untuk PDO

// Ambil Nota ID dari URL
$nota_id = $_GET['nota_id'] ?? null;

if (!$nota_id) {
    die("Error: Nota ID tidak ditemukan.");
}

try {
    // 1. Ambil data transaksi utama
    $stmt_transaksi = $pdo->prepare("SELECT * FROM transaksi WHERE id = ?");
    $stmt_transaksi->execute([$nota_id]);
    $transaksi = $stmt_transaksi->fetch(PDO::FETCH_ASSOC);

    if (!$transaksi) {
        die("Error: Transaksi tidak ditemukan.");
    }
    
    // 2. Ambil data item transaksi
    $stmt_items = $pdo->prepare("SELECT nama_menu, harga, qty FROM transaksi_items WHERE transaksi_id = ?");
    $stmt_items->execute([$nota_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // Siapkan data untuk FPDF
    $data_cetak = [
        'nota_id' => $transaksi['id'],
        'tanggal' => $transaksi['tanggal'],
        // Anda perlu mengambil nama kasir jika tidak disimpan di tabel transaksi
        'nama_kasir' => $_SESSION['nama_lengkap'] ?? 'Kasir', 
        'metode_pembayaran' => $transaksi['metode_pembayaran'],
        'bayar_tunai' => $transaksi['uang_bayar'], // Sesuaikan nama kolom di DB Anda
        'total_harga' => $transaksi['total_harga'],
        'items' => $items
    ];

    // --- KODE FPDF DIMULAI DI SINI ---
    // (Gunakan kode FPDF dari jawaban saya sebelumnya, tetapi gunakan $data_cetak
    // alih-alih $_SESSION['last_transaction'])

    // Contoh FPDF initialization (sama seperti sebelumnya)
    class PDF extends FPDF
    {
        function formatRupiahPDF($angka) {
            return 'Rp ' . number_format($angka, 0, ',', '.');
        }
    }
    
    $pdf = new PDF('P','mm',array(80,200)); 
    // ... Tambahkan kode pencetakan detail nota ...
    
    $pdf->Output('I', 'Nota_' . $data_cetak['nota_id'] . '.pdf');

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>