<?php
// Bagian PHP untuk membaca dan mengolah data
$database_file = 'transaksi.json';
$transactions = [];
$total_pendapatan = 0;

// Cek apakah file database ada
if (file_exists($database_file)) {
    // Baca isi file dan ubah dari JSON menjadi array PHP
    $transactions = json_decode(file_get_contents($database_file), true);
    
    // Balik urutan array agar transaksi terbaru ada di paling atas
    $transactions = array_reverse($transactions);

    // Hitung total pendapatan dari semua transaksi
    if (is_array($transactions)) {
        foreach ($transactions as $trans) {
            $total_pendapatan += $trans['total'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Forest Desert</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Sedikit tambahan CSS khusus untuk halaman laporan */
        body { display: block; } /* Override display flex dari style.css */
        .report-container { max-width: 900px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f7f9; }
        .total-row { font-weight: bold; background-color: #f0f0f0; }
        .items-list { list-style: none; padding-left: 0; margin: 0; }
    </style>
</head>
<body>

    <div class="report-container">
        <div style="text-align:center;">
            <img src="assets/images/logo_forest_desert.png" alt="Logo" style="width: 50px; height: 50px;">
            <h2>Laporan Penjualan - Forest Desert</h2>
        </div>
        
        <div class="order-total total-row" style="padding: 15px;">
            <span>Total Seluruh Pendapatan:</span>
            <span>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Tanggal</th>
                    <th>Detail Item</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Belum ada data transaksi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $trans): ?>
                        <tr>
                            <td><?= htmlspecialchars($trans['order_id']) ?></td>
                            <td><?= htmlspecialchars($trans['tanggal']) ?></td>
                            <td>
                                <ul class="items-list">
                                    <?php foreach ($trans['items'] as $item): ?>
                                        <li><?= htmlspecialchars($item['name']) ?> (x<?= $item['qty'] ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>Rp <?= number_format($trans['total'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
         <a href="index.php" style="display:inline-block; margin-top: 20px;">&larr; Kembali ke Kasir</a>
    </div>

</body>
</html>