<?php
// 1. CEK AUTENTIKASI
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    header("Location: login.php");
    exit();
}

// 2. KONEKSI DATABASE
include 'koneksi.php';

// 3. AMBIL DATA MENU
try {
    $stmt_all_menu = $pdo->query("SELECT * FROM menu ORDER BY kategori_utama, sub_kategori, nama_menu");
    $all_menu_items = $stmt_all_menu->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data menu: " . $e->getMessage());
}

// 4. PENGELOMPOKKAN DATA MENU
$menu_data_grouped = [];
foreach ($all_menu_items as $item) {
    $menu_data_grouped[$item['kategori_utama']][$item['sub_kategori']][] = $item;
}

// 5. LOGIKA FILTER KATEGORI
$active_sub_category = $_GET['kategori'] ?? null;
$items_to_display = [];

if ($active_sub_category === null && !empty($menu_data_grouped)) {
    $first_main_category = key($menu_data_grouped);
    if (!empty($menu_data_grouped[$first_main_category])) {
        $first_sub_category_list = $menu_data_grouped[$first_main_category];
        $active_sub_category = key($first_sub_category_list);
    }
}

if ($active_sub_category !== null) {
    foreach ($menu_data_grouped as $main_category => $sub_categories) {
        if (isset($sub_categories[$active_sub_category])) {
            $items_to_display = $sub_categories[$active_sub_category];
            break;
        }
    }
}

function formatRupiahPHP($angka) {
    return number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .menu-item { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
        .menu-item:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .sidebar-logo { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; }
        .nav-link.active { background-color: #0d6efd !important; color: white !important; }
        .menu-grid { max-height: 75vh; overflow-y: auto; overflow-x: hidden; }
        .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid p-3">
        <div class="row g-3">
            <aside class="col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="text-center pb-3 border-bottom mb-3">
                            <img src="assets/images/log_forest_dessert.jpg" alt="Logo" class="sidebar-logo mb-2 border">
                            <h5 class="mb-0 fw-bold">Forest Desert</h5>
                            <p class="small text-primary mb-1"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
                            <small class="text-muted"><?php echo date('l, j F Y'); ?></small>
                        </div>
                        <nav class="nav flex-column nav-pills overflow-auto mb-4" style="max-height: 400px;">
                            <?php foreach ($menu_data_grouped as $kategori => $sub_list): ?>
                                <h6 class="nav-title mt-3 text-uppercase small fw-bold text-secondary"><?= htmlspecialchars($kategori) ?></h6>
                                <?php foreach (array_keys($sub_list) as $sub_name): ?>
                                    <a class="nav-link <?= ($sub_name === $active_sub_category) ? 'active' : 'text-dark' ?>" 
                                       href="?kategori=<?= urlencode($sub_name) ?>"><?= htmlspecialchars($sub_name) ?></a>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </nav>
                        <div class="mt-auto">
                            <a href="kelola_menu.php" class="btn btn-info w-100 mb-2 text-white fw-bold"><i class="bi bi-pencil-square"></i> Kelola Menu</a>
                            <a href="laporan.php" class="btn btn-outline-secondary w-100 mb-2"><i class="bi bi-graph-up"></i> Laporan</a>
                            <hr>
                            <button type="button" class="btn btn-danger w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal"><i class="bi bi-box-arrow-right"></i> Logout</button>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="search-input" placeholder="Cari nama menu...">
                        </div>
                        <div class="menu-grid flex-grow-1">
                            <div class="row row-cols-2 row-cols-md-3 g-3" id="menu-container">
                                <?php foreach ($items_to_display as $item): ?>
                                    <div class="col menu-card-item" data-name="<?= strtolower(htmlspecialchars($item['nama_menu'])) ?>">
                                        <div class="card h-100 menu-item border-0 shadow-sm" 
                                             data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['nama_menu']) ?>" data-price="<?= $item['harga'] ?>">
                                            <img src="<?= htmlspecialchars(file_exists($item['gambar']) && !empty($item['gambar']) ? $item['gambar'] : 'assets/images/placeholder_default.png') ?>" class="card-img-top" style="height: 120px; object-fit: cover;">
                                            <div class="card-body p-2 text-center">
                                                <h6 class="card-title small mb-1"><?= htmlspecialchars($item['nama_menu']) ?></h6>
                                                <p class="card-text text-primary fw-bold mb-0">Rp <?= formatRupiahPHP($item['harga']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <aside class="col-lg-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h5 class="pb-3 border-bottom">Order Menu</h5>
                        <div id="order-list" class="flex-grow-1 my-3 overflow-auto">
                            <p class="text-center text-muted mt-5">Silahkan Pilih Menu...</p>
                        </div>
                        <div class="payment-section mt-auto border-top pt-3">
                            <div class="d-flex justify-content-between mb-1 text-muted small">
                                <span>Total Item:</span> <span id="item-count">0 items</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total Harga:</span> <span id="total-price" class="text-primary fw-bold fs-5">Rp 0</span>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Metode Pembayaran:</label>
                                <div class="d-flex gap-2">
                                    <input type="radio" class="btn-check" name="paymentMethod" id="pay-cash" value="Tunai" checked>
                                    <label class="btn btn-outline-success flex-fill btn-sm" for="pay-cash">Tunai</label>
                                    <input type="radio" class="btn-check" name="paymentMethod" id="pay-qris" value="QRIS">
                                    <label class="btn btn-outline-info flex-fill btn-sm" for="pay-qris">QRIS</label>
                                </div>
                            </div>
                            <div id="cash-payment-details">
                                <label class="small fw-bold">Uang Tunai:</label>
                                <input type="number" class="form-control mb-2" id="cash-amount" placeholder="0">
                                <div id="cash-error" class="text-danger small fw-bold mb-2" style="display: none;"><i class="bi bi-exclamation-triangle-fill"></i> Uang kurang!</div>
                                <div id="change-display" class="alert alert-info py-2 mb-2" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold">Kembalian:</span> <span id="change-amount" class="fw-bold fs-5">Rp 0</span>
                                    </div>
                                </div>
                                <div class="quick-cash-buttons d-flex flex-wrap gap-1 mb-2">
                                    <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-amount="20000">20rb</button>
                                    <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-amount="50000">50rb</button>
                                    <button class="btn btn-xs btn-outline-secondary quick-cash-btn" data-amount="100000">100rb</button>
                                </div>
                            </div>
                            <button id="btn-order" class="btn btn-success w-100 fw-bold py-2 mt-2" disabled>
                                <i class="bi bi-check-circle-fill"></i> Selesaikan Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="logoutConfirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <i class="bi bi-question-circle text-warning display-4 mb-3"></i>
                <h4 class="fw-bold">Logout?</h4>
                <p class="text-muted">Anda yakin ingin keluar?</p>
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-danger px-4">Ya, Keluar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmOrderModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-cart-check"></i> Validasi Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-3">
                        <i class="bi bi-question-circle text-primary display-5"></i>
                        <h5 class="fw-bold mt-2">Apakah pesanan sudah benar?</h5>
                    </div>
                    <div class="bg-light p-3 rounded-3 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Metode Bayar:</span>
                            <span id="confirm-method" class="fw-bold text-dark">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Bayar:</span>
                            <span id="confirm-total" class="fw-bold text-primary fs-5">-</span>
                        </div>
                        <div id="confirm-cash-section" class="pt-2 border-top mt-2" style="display: none;">
                            <div class="d-flex justify-content-between text-success">
                                <span class="small">Info Tunai:</span>
                                <span id="confirm-cash-info" class="fw-bold small">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 d-flex justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cek Lagi</button>
                    <button type="button" id="confirm-final-submit" class="btn btn-primary px-4 fw-bold">Ya, Proses & Cetak</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderSuccessModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center p-4">
                <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
                <h4 class="fw-bold">Pesanan Berhasil!</h4>
                <p>Transaksi telah berhasil disimpan ke database.</p>
                <button type="button" class="btn btn-primary px-5" onclick="location.reload()">Selesai</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>