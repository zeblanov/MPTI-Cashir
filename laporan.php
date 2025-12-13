<?php

// Bagian paling atas dari index.php, kelola_menu.php, dll.

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit();
}
// Tambahkan nama pengguna yang sedang aktif (opsional, tapi bagus untuk UI)
$nama_kasir = $_SESSION['nama_lengkap']; 

// Jika sudah login, lanjutkan loading halaman



include 'koneksi.php'; // Pastikan koneksi.php mendefinisikan $pdo

// --- 1. DEKLARASI VARIABEL DAN FILTER ---
$filter_type = $_GET['filter_type'] ?? 'Harian'; 
$filter_date_raw = $_GET['tanggal'] ?? date('Y-m-d'); 
$report_title = "Laporan Penjualan";
$total_pendapatan = 0;
$transactions = [];
$best_sellers = [];
$error_message = '';
$date_format_sql = '';
$filter_clause = '';
$date_placeholder = 'YYYY-MM-DD'; 
$current_time = time();
$input_type = 'text'; // Default input type

// --- 2. LOGIC PENENTUAN QUERY DAN TANGGAL BERSIH ---
$filter_date_clean = $filter_date_raw; 
$input_value_display = $filter_date_raw; 

if ($filter_type === 'Harian') {
    $report_title .= " Harian (" . date('d F Y', strtotime($filter_date_raw)) . ")";
    $filter_clause = "WHERE DATE(tanggal) = :tanggal_filter";
    $date_format_sql = '%d %M %Y %H:%i'; 
    $date_placeholder = 'YYYY-MM-DD';
    $input_type = 'date'; // Gunakan type date untuk filter Harian agar ada kalender bawaan browser
    if (!isset($_GET['tanggal']) || $filter_date_raw === "") {
        $input_value_display = date('Y-m-d', $current_time);
        $filter_date_clean = date('Y-m-d', $current_time);
    }

} elseif ($filter_type === 'Bulanan') {
    $filter_date_clean = date('Y-m', strtotime($filter_date_raw));
    $report_title .= " Bulanan (" . date('F Y', strtotime($filter_date_clean . '-01')) . ")";
    $filter_clause = "WHERE DATE_FORMAT(tanggal, '%Y-%m') = :tanggal_filter";
    $date_format_sql = '%d %M %Y %H:%i';
    $date_placeholder = 'YYYY-MM';
    $input_type = 'month'; // Gunakan type month untuk filter Bulanan
    if (!isset($_GET['tanggal']) || $filter_date_raw === "") {
        $input_value_display = date('Y-m', $current_time);
        $filter_date_clean = date('Y-m', $current_time);
    }
    
} elseif ($filter_type === 'Tahunan') {
    $filter_date_clean = date('Y', strtotime($filter_date_raw));
    $report_title .= " Tahunan (" . $filter_date_clean . ")";
    $filter_clause = "WHERE YEAR(tanggal) = :tanggal_filter";
    $date_format_sql = '%d %M %Y %H:%i';
    $date_placeholder = 'YYYY';
    $input_type = 'number'; // Gunakan type number untuk tahun
    if (!isset($_GET['tanggal']) || $filter_date_raw === "") {
        $input_value_display = date('Y', $current_time);
        $filter_date_clean = date('Y', $current_time);
    }

} elseif ($filter_type === 'Semua') {
    $report_title = "Laporan Penjualan Seluruh Waktu";
    $filter_clause = "";
    $date_format_sql = '%d %M %Y %H:%i';
    $date_placeholder = 'Tidak Perlu Diisi';
    $input_value_display = ''; 
}

// ... (Sisa logic query transaksi dan best seller SAMA) ...
try {
    // --- 3. QUERY TRANSAKSI UTAMA ---
    $sql_transaksi = "
        SELECT
            id, order_id, tanggal, total, metode_pembayaran, jumlah_bayar, kembalian,
            DATE_FORMAT(tanggal, :date_format_sql) AS formatted_tanggal
        FROM transaksi
        {$filter_clause}
        ORDER BY tanggal DESC
    ";
    
    $stmt_transaksi = $pdo->prepare($sql_transaksi);
    $stmt_transaksi->bindValue(':date_format_sql', $date_format_sql);

    if ($filter_type !== 'Semua' && $filter_date_clean) {
        $stmt_transaksi->bindValue(':tanggal_filter', $filter_date_clean);
    }
    
    $stmt_transaksi->execute();
    $transactions = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

    // --- 4. PERHITUNGAN TOTAL PENDAPATAN & DETAIL ITEM & BEST SELLER ---
    $transaction_ids = [];
    foreach ($transactions as $t) {
        $transaction_ids[] = $t['id'];
        $total_pendapatan += $t['total'];
    }

    if (!empty($transaction_ids)) {
        $ids_str = implode(',', array_map('intval', $transaction_ids));

        // Query Detail Item
        $sql_items = "SELECT transaksi_id, item_name, qty FROM transaksi_items WHERE transaksi_id IN ({$ids_str})";
        $stmt_items = $pdo->query($sql_items);
        $items_data = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        $items_by_transaksi = [];
        foreach ($items_data as $item) {
            $items_by_transaksi[$item['transaksi_id']][] = $item;
        }

        foreach ($transactions as &$t) {
            $t['detail_items'] = $items_by_transaksi[$t['id']] ?? [];
            
            $item_details_str = '';
            foreach ($t['detail_items'] as $item) {
                $item_details_str .= htmlspecialchars($item['item_name']) . ' (x' . $item['qty'] . ')<br>';
            }
            $t['detail_items_display'] = $item_details_str;
        }

        // Query Best Seller
        $sql_best_seller = "
            SELECT 
                item_name, SUM(qty) AS total_qty_sold
            FROM transaksi_items
            WHERE transaksi_id IN ({$ids_str})
            GROUP BY item_name
            ORDER BY total_qty_sold DESC
            LIMIT 5
        ";
        $stmt_best_seller = $pdo->query($sql_best_seller);
        $best_sellers = $stmt_best_seller->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Gagal mengambil data dari database: " . $e->getMessage();
    $transactions = [];
}

function formatRupiahPHP($angka) {
    if ($angka === null || $angka === "") return '0';
    return number_format($angka, 0, ',', '.');
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </head>
<body class="bg-light">

    <div class="container py-5">
        <div class="card p-4 shadow-sm">
            <div class="text-center mb-4">
                <img src="assets/images/log_forest_dessert.jpg"logo" class="logo me-3" style="width: 100px; height: 100px;">
                <h3 class="mb-0"><?= $report_title ?></h3>
                <p class="text-muted">Forest Desert</p>
            </div>

            <form action="laporan.php" method="GET" class="mb-4">
                <div class="row g-2 align-items-end"> 
                    <div class="col-md-3">
                        <label for="filter_type" class="form-label">Tipe Laporan</label>
                        <select name="filter_type" id="filter_type" class="form-select">
                            <option value="Harian" <?= $filter_type == 'Harian' ? 'selected' : '' ?>>Harian</option>
                            <option value="Bulanan" <?= $filter_type == 'Bulanan' ? 'selected' : '' ?>>Bulanan</option>
                            <option value="Tahunan" <?= $filter_type == 'Tahunan' ? 'selected' : '' ?>>Tahunan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="tanggal_input" class="form-label">Pilih Tanggal/Bulan/Tahun</label>
                        <input type="<?= $input_type ?>" name="tanggal" id="tanggal_input" class="form-control" 
                            value="<?= htmlspecialchars($input_value_display) ?>" 
                            placeholder="<?= $date_placeholder ?>"
                            <?= ($filter_type === 'Tahunan') ? 'maxlength="4"' : '' ?>>
                        <small class="form-text text-muted" id="format_info">
                            Format: <?= $date_placeholder ?>
                        </small>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end align-self-end">
                        <button type="submit" class="btn btn-primary me-2">Tampilkan Laporan</button>
                        <a href="laporan.php?filter_type=Semua" class="btn btn-secondary">Tampilkan Semua</a>
                    </div>
                </div>
            </form>
            
            <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_message ?>
            </div>
            <?php endif; ?>

            <div class="bg-success text-white p-3 rounded mb-4 d-flex justify-content-between">
                <strong>Total Pendapatan (Sesuai Filter):</strong>
                <strong>Rp <?= formatRupiahPHP($total_pendapatan) ?></strong>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <h5>Daftar Transaksi</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Detail Item</th>
                                    <th>Metode</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($transactions)): ?>
                                    <?php foreach ($transactions as $trans): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($trans['order_id']) ?></td>
                                        <td><?= htmlspecialchars($trans['formatted_tanggal']) ?></td>
                                        <td><?= $trans['detail_items_display'] ?></td>
                                        <td><?= htmlspecialchars($trans['metode_pembayaran']) ?></td>
                                        <td class="text-end fw-bold">Rp <?= formatRupiahPHP($trans['total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data transaksi untuk filter ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4">
                    <h5>Top 5 Produk Terlaris</h5>
                    <ul class="list-group">
                        <?php if (!empty($best_sellers)): ?>
                            <?php $rank = 1; ?>
                            <?php foreach ($best_sellers as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary rounded-pill me-2"><?= $rank++ ?></span>
                                    <?= htmlspecialchars($item['item_name']) ?>
                                    <span class="badge bg-secondary">Terjual: <?= $item['total_qty_sold'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center">Tidak ada data penjualan item.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div> <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary">
                    &larr; Kembali ke Halaman Kasir
                </a>
            </div>

        </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('filter_type').addEventListener('change', function() {
            const type = this.value;
            const input = document.getElementById('tanggal_input');
            const formatInfo = document.getElementById('format_info');
            let placeholder = '';
            let newValue = '';
            let inputType = 'text';

            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');

            if (type === 'Harian') {
                placeholder = 'YYYY-MM-DD';
                newValue = `${year}-${month}-${day}`;
                inputType = 'date'; // Set type date untuk kalender
            } else if (type === 'Bulanan') {
                placeholder = 'YYYY-MM';
                newValue = `${year}-${month}`;
                inputType = 'month'; // Set type month
            } else if (type === 'Tahunan') {
                placeholder = 'YYYY';
                newValue = `${year}`;
                inputType = 'number'; // Set type number
            } else {
                placeholder = 'Tidak Perlu Diisi';
                newValue = '';
                inputType = 'text';
            }

            input.setAttribute('type', inputType); // Ubah jenis input
            input.setAttribute('placeholder', placeholder);
            input.value = newValue;
            formatInfo.innerHTML = 'Format: ' + placeholder;
        });
    </script>
</body>
</html>