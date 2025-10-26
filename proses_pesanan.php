<?php
// Memasukkan file koneksi database
include 'koneksi.php';

header('Content-Type: application/json');

// --- Peningkatan 1: Validasi Input dan Pengamanan ---
// Pastikan input method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Metode permintaan tidak valid.']);
    exit;
}

// Mengambil data JSON yang dikirim oleh JavaScript
$payload = json_decode(file_get_contents('php://input'), true);

// Ekstrak data dari payload dengan nilai default yang aman
$order_data = $payload['items'] ?? [];
$payment_method = $payload['payment_method'] ?? null;
$total_price = $payload['total_price'] ?? 0.00; // Gunakan float untuk harga
$cash_paid = $payload['cash_paid'] ?? 0.00; 
$change_amount = $payload['change_amount'] ?? 0.00;

// Validasi kritis
if (empty($order_data) || empty($payment_method) || $total_price <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Data pesanan, total harga, atau metode pembayaran tidak valid.']);
    exit;
}

// --- Akhir Validasi Input ---

// Memulai Database Transaction
// Pengecekan status pdo harus dilakukan sebelum beginTransaction jika koneksi.php rentan error
try {
    $pdo->beginTransaction();

    // 1. Siapkan data transaksi utama
    // Order ID yang unik dan readable
    $order_id_str = 'TRX-' . date('YmdHis') . '-' . rand(100, 999);
    $tanggal = date('Y-m-d H:i:s');

    // 2. Simpan ke tabel `transaksi`
    // Gunakan parameter terikat untuk semua nilai untuk keamanan (terhindar dari SQL Injection)
    $sql_transaksi = "INSERT INTO transaksi (order_id, tanggal, total, metode_pembayaran, jumlah_bayar, kembalian)
                     VALUES (:order_id, :tanggal, :total, :metode, :bayar, :kembalian)";
    $stmt_transaksi = $pdo->prepare($sql_transaksi);
    $stmt_transaksi->execute([
        ':order_id' => $order_id_str,
        ':tanggal' => $tanggal,
        ':total' => $total_price,
        ':metode' => $payment_method,
        ':bayar' => $cash_paid, 
        ':kembalian' => $change_amount
    ]);

    // 3. Ambil ID dari transaksi yang baru saja disimpan
    $transaksi_id_baru = $pdo->lastInsertId();

    // 4. Siapkan query untuk menyimpan item-item
    $sql_items = "INSERT INTO transaksi_items (transaksi_id, item_id_menu, item_name, item_price, qty)
                  VALUES (:transaksi_id, :id_menu, :name, :price, :qty)";
    $stmt_items = $pdo->prepare($sql_items);

    // 5. Loop dan simpan setiap item ke tabel `transaksi_items`
    foreach ($order_data as $item) {
        // Pastikan semua kunci yang diakses ada
        $stmt_items->execute([
            ':transaksi_id' => $transaksi_id_baru,
            ':id_menu' => $item['id'] ?? null,
            ':name' => $item['name'] ?? 'Unknown Item',
            ':price' => $item['price'] ?? 0.00,
            ':qty' => $item['qty'] ?? 1
        ]);
    }

    // 6. Jika semua berhasil, commit transaksi
    $pdo->commit();

    http_response_code(201); // Created
    echo json_encode([
        'success' => true, 
        'message' => 'Pesanan berhasil disimpan ke database!',
        'order_id' => $order_id_str // Kembalikan ID untuk konfirmasi di frontend
    ]);

} catch (PDOException $e) {
    // 7. Jika ada kegagalan, batalkan semua (rollback)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Kirim pesan error yang lebih spesifik jika memungkinkan
    http_response_code(500); // Internal Server Error
    error_log("Database Error (PDO): " . $e->getMessage() . " in file " . __FILE__ . " on line " . $e->getLine()); 
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan pada database saat menyimpan transaksi.', 
        'error_details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    // Tangani error non-PDO (misalnya, masalah JSON parsing)
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500);
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan umum pada server.', 
        'error_details' => $e->getMessage()
    ]);
}

?>