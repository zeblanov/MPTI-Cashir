<?php
// Memasukkan file koneksi database
include 'koneksi.php';

header('Content-Type: application/json');

// Mengambil data JSON yang dikirim oleh JavaScript
$payload = json_decode(file_get_contents('php://input'), true);

// Ekstrak data dari payload
$order_data = $payload['items'] ?? [];
$payment_method = $payload['payment_method'] ?? null;
$total_price = $payload['total_price'] ?? 0;
$cash_paid = $payload['cash_paid'] ?? null; // Bisa null jika bukan tunai
$change_amount = $payload['change_amount'] ?? null; // Bisa null jika bukan tunai

if (empty($order_data) || empty($payment_method)) {
    echo json_encode(['success' => false, 'message' => 'Data pesanan atau metode pembayaran tidak lengkap.']);
    exit;
}

// Mulai Database Transaction
$pdo->beginTransaction();

try {
    // 1. Siapkan data transaksi utama
    $order_id_str = 'TRX-' . time() . '-' . rand(100, 999); // Tambahkan random agar lebih unik
    $tanggal = date('Y-m-d H:i:s');

    // 2. Simpan ke tabel `transaksi` (termasuk data pembayaran baru)
    $sql_transaksi = "INSERT INTO transaksi (order_id, tanggal, total, metode_pembayaran, jumlah_bayar, kembalian)
                      VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_transaksi = $pdo->prepare($sql_transaksi);
    $stmt_transaksi->execute([
        $order_id_str,
        $tanggal,
        $total_price,
        $payment_method,
        $cash_paid,  // Simpan jumlah bayar
        $change_amount // Simpan kembalian
    ]);

    // 3. Ambil ID dari transaksi yang baru saja disimpan
    $transaksi_id_baru = $pdo->lastInsertId();

    // 4. Siapkan query untuk menyimpan item-item
    $sql_items = "INSERT INTO transaksi_items (transaksi_id, item_id_menu, item_name, item_price, qty)
                  VALUES (?, ?, ?, ?, ?)";
    $stmt_items = $pdo->prepare($sql_items);

    // 5. Loop dan simpan setiap item ke tabel `transaksi_items`
    foreach ($order_data as $item) {
        $stmt_items->execute([
            $transaksi_id_baru,
            $item['id'],
            $item['name'],
            $item['price'],
            $item['qty']
        ]);
    }

    // 6. Jika semua berhasil, commit transaksi
    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Pesanan berhasil disimpan ke database!']);

} catch (Exception $e) {
    // 7. Jika ada kegagalan, batalkan semua (rollback)
    $pdo->rollBack();
    // Kirim pesan error yang lebih spesifik jika memungkinkan
    error_log("Database Error: " . $e->getMessage()); // Catat error di log server
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada database. Silakan coba lagi.']);
}
?>