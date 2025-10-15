<?php
// Mengatur header agar browser tahu bahwa responsnya adalah format JSON
header('Content-Type: application/json');

// Menentukan nama file yang akan kita gunakan sebagai database
$database_file = 'transaksi.json';

// 1. Mengambil data mentah (raw data) yang dikirim oleh JavaScript
$json_data = file_get_contents('php://input');
// 2. Mengubah string JSON menjadi array PHP
$order_data = json_decode($json_data, true);

// 3. Validasi sederhana: pastikan ada data yang dikirim
if (empty($order_data)) {
    // Jika tidak ada data, kirim pesan error
    echo json_encode(['success' => false, 'message' => 'Tidak ada data pesanan yang diterima.']);
    exit; // Hentikan eksekusi script
}

// 4. Membaca data transaksi yang sudah ada (jika file sudah ada)
$all_transactions = [];
if (file_exists($database_file)) {
    $transactions_json = file_get_contents($database_file);
    $all_transactions = json_decode($transactions_json, true);
}

// 5. Menyiapkan data transaksi baru
$total_harga = 0;
foreach ($order_data as $item) {
    $total_harga += $item['price'] * $item['qty'];
}

$new_transaction = [
    'order_id' => 'TRX-' . time(), // Membuat ID unik berdasarkan waktu
    'tanggal' => date('Y-m-d H:i:s'),
    'total' => $total_harga,
    'items' => $order_data,
];

// 6. Menambahkan transaksi baru ke dalam daftar semua transaksi
$all_transactions[] = $new_transaction;

// 7. Menyimpan kembali semua data ke dalam file transaksi.json
// JSON_PRETTY_PRINT membuat format file JSON lebih rapi dan mudah dibaca
file_put_contents($database_file, json_encode($all_transactions, JSON_PRETTY_PRINT));

// 8. Mengirim respons berhasil kembali ke JavaScript
echo json_encode(['success' => true, 'message' => 'Pesanan berhasil disimpan!']);

?>