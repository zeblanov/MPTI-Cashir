<?php
// Pastikan koneksi.php ada dan berfungsi
include 'koneksi.php';

$transactions = [];
$total_pendapatan = 0;
$error_message = '';
$tanggal_filter = $_GET['tanggal'] ?? null; 

try {
    // PERBAIKAN: Mengganti transaksi_id menjadi id di query SELECT transaksi
    $sql_base = "SELECT id, order_id, tanggal, total, metode_pembayaran FROM transaksi";
    $params = [];
    
    if ($tanggal_filter) {
        $sql_base .= " WHERE DATE(tanggal) = ?";
        $params[] = $tanggal_filter;
    }
    
    $sql_base .= " ORDER BY tanggal DESC"; 
    
    $stmt = $pdo->prepare($sql_base);
    $stmt->execute($params);
    $raw_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk item, menggunakan transaksi_id yang benar
    $sql_items = "SELECT item_name, qty FROM transaksi_items WHERE transaksi_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    
    foreach ($raw_transactions as $trans) {
        // PERBAIKAN: Mengambil ID transaksi dari kolom 'id'
        $transaksi_id = $trans['id']; 
        
        $stmt_items->execute([$transaksi_id]);
        $items_data = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        $trans['items'] = $items_data;
        $transactions[] = $trans;
        
        $total_pendapatan += (float)($trans['total']);
    }

} catch (PDOException $e) {
    // Tambahkan log dan tampilkan pesan umum ke user
    error_log("Database Error Laporan: " . $e->getMessage());
    $transactions = [];
    $error_message = 'Gagal mengambil data dari database. Pastikan koneksi dan semua nama kolom sudah benar.';
}

function formatTanggal($timestamp) {
    if (!$timestamp) {
        return 'N/A';
    }
    return date('d/m/Y H:i', @strtotime($timestamp)); 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-container { max-width: 900px; margin: 30px auto; }
        .items-list { list-style: none; padding-left: 0; margin: 0; font-size: 0.9rem; }
        .report-container th:nth-child(1), .report-container td:nth-child(1) { width: 15%; }
        .report-container th:nth-child(2), .report-container td:nth-child(2) { width: 25%; }
        .report-container th:nth-child(4), .report-container td:nth-child(4) { width: 20%; }
    </style>
</head>
<body class="bg-light">

    <div class="report-container card p-4 shadow-sm">
        <div class="text-center mb-4">
            <img src="assets/images/logo_forest_desert.png" alt="Logo Forest Desert" style="width: 50px; height: 50px;" class="mb-2">
            <h2 class="card-title">Laporan Penjualan</h2>
            <h6 class="card-subtitle mb-2 text-muted">Forest Desert</h6>
        </div>
        
        <form action="laporan.php" method="GET" class="row g-3 align-items-center mb-4 p-3 bg-light border rounded">
            <div class="col-md-auto">
                <label for="tanggal" class="form-label fw-bold">Pilih Tanggal:</label>
            </div>
            <div class="col-md-4">
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal_filter ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Cari Transaksi</button>
            </div>
            <div class="col-md-auto">
                <a href="laporan.php" class="btn btn-secondary">Tampilkan Semua</a>
            </div>
        </form>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="alert alert-success">
            <div class="d-flex justify-content-between fw-bold">
                <span>Total Pendapatan (Sesuai Filter):</span>
                <span>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tanggal & Waktu</th>
                        <th>Detail Item</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Tidak ada data transaksi untuk 
                                <?= $tanggal_filter ? 'tanggal ' . htmlspecialchars(date('d/m/Y', strtotime($tanggal_filter))) : 'filter ini' ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td><?= htmlspecialchars($trans['order_id'] ?? 'N/A') ?></td>
                                <td><?= formatTanggal($trans['tanggal'] ?? 'N/A') ?></td>
                                <td>
                                    <?php 
                                    $items_data = $trans['items'] ?? [];
                                    if (is_array($items_data) && !empty($items_data)): ?>
                                        <ul class="items-list">
                                            <?php foreach ($items_data as $item): ?>
                                                <li>
                                                    <?= htmlspecialchars($item['item_name'] ?? 'Item Tanpa Nama') ?> 
                                                    (x<?= htmlspecialchars($item['qty'] ?? 1) ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-danger">Item tidak tercatat.</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">Rp <?= number_format($trans['total'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="index.php" class="btn btn-outline-secondary mt-3 text-decoration-none">&larr; Kembali ke Halaman Kasir</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>