<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// --- 1. DEKLARASI VARIABEL DAN FILTER ---
$filter_type = $_GET['filter_type'] ?? 'Harian'; 
$filter_date_raw = $_GET['tanggal'] ?? ''; 
$report_title = "Laporan Penjualan";
$total_pendapatan = 0;
$transactions = [];
$best_sellers = [];

// --- 2. LOGIC PENENTUAN TANGGAL DEFAULT ---
if (empty($filter_date_raw) && $filter_type !== 'Semua') {
    if ($filter_type === 'Bulanan') {
        $filter_date_raw = date('Y-m');
    } elseif ($filter_type === 'Tahunan') {
        $filter_date_raw = date('Y');
    } else {
        $filter_date_raw = date('Y-m-d');
    }
}

$filter_date_clean = $filter_date_raw;
$input_value_display = $filter_date_raw;
$date_format_sql = '%d %M %Y %H:%i'; 
$filter_clause = '';
$input_type = 'date';

if ($filter_type === 'Harian') {
    $report_title .= " Harian (" . date('d F Y', strtotime($filter_date_raw)) . ")";
    $filter_clause = "WHERE DATE(tanggal) = :tanggal_filter";
    $input_type = 'date';
} elseif ($filter_type === 'Bulanan') {
    $report_title .= " Bulanan (" . date('F Y', strtotime($filter_date_raw . '-01')) . ")";
    $filter_clause = "WHERE DATE_FORMAT(tanggal, '%Y-%m') = :tanggal_filter";
    $input_type = 'month';
    $input_value_display = date('Y-m', strtotime($filter_date_raw));
    $filter_date_clean = $input_value_display;
} elseif ($filter_type === 'Tahunan') {
    $report_title .= " Tahunan (" . $filter_date_raw . ")";
    $filter_clause = "WHERE YEAR(tanggal) = :tanggal_filter";
    $input_type = 'number';
} elseif ($filter_type === 'Semua') {
    $report_title = "Laporan Penjualan Seluruh Waktu";
    $filter_clause = "";
    $input_type = 'date';
    $input_value_display = date('Y-m-d');
}

try {
    $sql_transaksi = "SELECT id, order_id, tanggal, total, metode_pembayaran, 
                      DATE_FORMAT(tanggal, :date_format_sql) AS formatted_tanggal
                      FROM transaksi {$filter_clause} ORDER BY tanggal DESC";
    
    $stmt_transaksi = $pdo->prepare($sql_transaksi);
    $stmt_transaksi->bindValue(':date_format_sql', $date_format_sql);
    if ($filter_type !== 'Semua') {
        $stmt_transaksi->bindValue(':tanggal_filter', $filter_date_clean);
    }
    $stmt_transaksi->execute();
    $transactions = $stmt_transaksi->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($transactions)) {
        $transaction_ids = array_column($transactions, 'id');
        $ids_str = implode(',', array_map('intval', $transaction_ids));
        $stmt_items = $pdo->query("SELECT transaksi_id, item_name, qty FROM transaksi_items WHERE transaksi_id IN ($ids_str)");
        $items_data = $stmt_items->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        foreach ($transactions as &$t) {
            $total_pendapatan += $t['total'];
            $details = "";
            if (isset($items_data[$t['id']])) {
                foreach ($items_data[$t['id']] as $item) {
                    $details .= htmlspecialchars($item['item_name']) . " (x" . $item['qty'] . ")<br>";
                }
            }
            $t['detail_items_display'] = $details;
        }

        $sql_best = "SELECT item_name, SUM(qty) AS total_qty_sold FROM transaksi_items 
                     WHERE transaksi_id IN ($ids_str) GROUP BY item_name 
                     ORDER BY total_qty_sold DESC LIMIT 5";
        $best_sellers = $pdo->query($sql_best)->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) { $error_message = "Error: " . $e->getMessage(); }

function formatRupiahPHP($angka) { return number_format($angka, 0, ',', '.'); }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Tampilan di Layar */
        .header-box { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; }
        
        /* Pengaturan KHUSUS CETAK PDF */
        @media print {
            @page { margin: 1cm; }
            .no-print, form, .btn { display: none !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .container { max-width: 100% !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; padding: 0 !important; }
            
            /* Memastikan Header di Tengah saat Cetak */
            .header-box { display: block !important; text-align: center !important; width: 100% !important; }
            .header-box .text-center { width: 100% !important; }
            .header-box img { display: inline-block !important; margin-bottom: 10px; }
            
            .bg-success { 
                background-color: #198754 !important; color: white !important; 
                print-color-adjust: exact; -webkit-print-color-adjust: exact; 
                padding: 15px !important; border-radius: 5px;
            }
            .table { width: 100% !important; }
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="card p-4 shadow-sm">
        <div class="header-box mb-4">
            <div class="no-print">
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
            <div class="text-center">
                <img src="assets/images/log_forest_dessert.jpg" alt="logo" style="width: 80px; height: 80px;" class="mb-2 rounded-circle">
                <h3 class="mb-0 fw-bold"><?= $report_title ?></h3>
                <p class="text-muted mb-0">Forest Desert</p>
                <hr class="my-3 mx-auto" style="width: 50%;">
            </div>
            <div></div> </div>

        <form action="laporan.php" method="GET" class="mb-4 no-print bg-white p-3 border rounded shadow-sm">
            <div class="row g-2 align-items-end"> 
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Tipe Laporan</label>
                    <select name="filter_type" id="filter_type" class="form-select">
                        <option value="Harian" <?= $filter_type == 'Harian' ? 'selected' : '' ?>>Harian</option>
                        <option value="Bulanan" <?= $filter_type == 'Bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="Tahunan" <?= $filter_type == 'Tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Pilih Waktu</label>
                    <input type="<?= $input_type ?>" name="tanggal" id="tanggal_input" class="form-control" value="<?= htmlspecialchars($input_value_display) ?>">
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Tampilkan</button>
                    <button type="button" class="btn btn-danger me-2" onclick="window.print()"><i class="bi bi-file-earmark-pdf"></i> Cetak PDF</button>
                    <a href="laporan.php?filter_type=Semua" class="btn btn-secondary">Tampilkan Semua</a>
                </div>
            </div>
        </form>

        <div class="bg-success text-white p-3 rounded mb-4 d-flex justify-content-between align-items-center">
            <span class="fw-bold">Total Pendapatan (Sesuai Filter):</span>
            <h4 class="mb-0 fw-bold">Rp <?= formatRupiahPHP($total_pendapatan) ?></h4>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <h5 class="fw-bold mb-3"><i class="bi bi-list-check"></i> Daftar Transaksi</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark text-center">
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
                                    <td class="small fw-bold text-primary"><?= htmlspecialchars($trans['order_id']) ?></td>
                                    <td class="small"><?= htmlspecialchars($trans['formatted_tanggal']) ?></td>
                                    <td class="small"><?= $trans['detail_items_display'] ?></td>
                                    <td class="text-center"><span class="badge bg-info text-dark"><?= htmlspecialchars($trans['metode_pembayaran']) ?></span></td>
                                    <td class="text-end fw-bold">Rp <?= formatRupiahPHP($trans['total']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada data untuk periode ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow"></i> Top 5 Produk Terlaris</h5>
                <ul class="list-group list-group-flush border rounded">
                    <?php if (!empty($best_sellers)): ?>
                        <?php $rank = 1; foreach ($best_sellers as $item): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><span class="badge bg-primary rounded-pill me-2"><?= $rank++ ?></span><?= htmlspecialchars($item['item_name']) ?></div>
                            <span class="badge bg-light text-dark border">Terjual: <?= $item['total_qty_sold'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted text-center py-3">Belum ada data penjualan.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    // Script otomatis ganti tipe input tanggal
    document.getElementById('filter_type').addEventListener('change', function() {
        const type = this.value;
        const input = document.getElementById('tanggal_input');
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');

        if (type === 'Harian') {
            input.type = 'date'; 
            input.value = `${year}-${month}-${day}`;
        } else if (type === 'Bulanan') {
            input.type = 'month'; 
            input.value = `${year}-${month}`;
        } else if (type === 'Tahunan') {
            input.type = 'number'; 
            input.value = year;
        }
    });
</script>
</body>
</html>