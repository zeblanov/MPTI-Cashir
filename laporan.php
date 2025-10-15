<?php
// Bagian PHP untuk membaca dan mengolah data
$database_file = 'transaksi.json';
$transactions = [];
$total_pendapatan = 0;

// === LOGIKA BARU UNTUK FILTER TANGGAL ===
// 1. Cek apakah ada tanggal yang dikirim dari form
$tanggal_filter = null;
if (isset($_GET['tanggal']) && !empty($_GET['tanggal'])) {
    $tanggal_filter = $_GET['tanggal'];
}

// Cek apakah file database ada
if (file_exists($database_file)) {
    // Baca isi file dan ubah dari JSON menjadi array PHP
    $all_transactions = json_decode(file_get_contents($database_file), true) ?: [];

    // 2. Filter transaksi jika ada tanggal yang dipilih
    if ($tanggal_filter) {
        foreach ($all_transactions as $trans) {
            // Pastikan data transaksi lengkap sebelum diakses
            if (isset($trans['tanggal'])) {
                // Ambil hanya bagian tanggal (YYYY-MM-DD) dari timestamp transaksi
                $tanggal_transaksi = substr($trans['tanggal'], 0, 10);
                if ($tanggal_transaksi == $tanggal_filter) {
                    // Jika tanggalnya cocok, masukkan ke array hasil
                    $transactions[] = $trans;
                }
            }
        }
    } else {
        // Jika tidak ada filter, tampilkan semua transaksi
        $transactions = $all_transactions;
    }
    
    // Balik urutan array agar transaksi terbaru ada di paling atas
    if (is_array($transactions)) {
        $transactions = array_reverse($transactions);
    }

    // 3. Hitung total pendapatan HANYA dari transaksi yang sudah difilter
    if (is_array($transactions)) {
        foreach ($transactions as $trans) {
             if (isset($trans['total'])) {
                $total_pendapatan += $trans['total'];
            }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .report-container { max-width: 900px; margin: 30px auto; }
        .items-list { list-style: none; padding-left: 0; margin: 0; font-size: 0.9rem; }
    </style>
</head>
<body class="bg-light">

    <div class="report-container card p-4 shadow-sm">
        <div class="text-center mb-4">
            <img src="assets/images/logo_forest_desert.png" alt="Logo" style="width: 50px; height: 50px;" class="mb-2">
            <h2 class="card-title">Laporan Penjualan</h2>
            <h6 class="card-subtitle mb-2 text-muted">Forest Desert</h6>
        </div>
        
        <form action="laporan.php" method="GET" class="row g-3 align-items-center mb-4 p-3 bg-light border rounded">
            <div class="col-md-auto">
                <label for="tanggal" class="form-label fw-bold">Pilih Tanggal:</label>
            </div>
            <div class="col-md-4">
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal_filter ?? '') ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">Cari Transaksi</button>
            </div>
            <div class="col-md-auto">
                <a href="laporan.php" class="btn btn-secondary">Tampilkan Semua</a>
            </div>
        </form>
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
                            <td colspan="4" class="text-center text-muted py-4">Tidak ada data transaksi untuk tanggal yang dipilih.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $trans): ?>
                            <tr>
                                <td><?= htmlspecialchars($trans['order_id'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($trans['tanggal'] ?? 'N/A') ?></td>
                                <td>
                                    <?php if (!empty($trans['items'])): ?>
                                        <ul class="items-list">
                                            <?php foreach ($trans['items'] as $item): ?>
                                                <li><?= htmlspecialchars($item['name']) ?> (x<?= $item['qty'] ?>)</li>
                                            <?php endforeach; ?>
                                        </ul>
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

</body>
</html>