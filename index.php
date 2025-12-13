<?php

session_start();
include 'koneksi.php'; // Membutuhkan koneksi.php yang mendefinisikan $pdo

// --- BAGIAN OTENTIKASI DAN INISIALISASI ---
// Cek apakah user sudah login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    header("Location: login.php");
    exit();
}
$nama_kasir = $_SESSION['nama_lengkap'] ?? 'Kasir Default'; 

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// --- FUNGSI UTAMA KASIR: LOGIKA KERANJANG BERBASIS POST & REFRESH ---

// 1. Logika Tambah Item (Menerima POST dari klik item menu)
if (isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['menu_id'])) {
    $menu_id = (int)$_POST['menu_id'];

    $stmt = $pdo->prepare("SELECT id, nama_menu, harga FROM menu WHERE id = ?");
    $stmt->execute([$menu_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        if (isset($_SESSION['keranjang'][$menu_id])) {
            $_SESSION['keranjang'][$menu_id]['qty']++;
        } else {
            $_SESSION['keranjang'][$menu_id] = [
                'id' => $item['id'],
                'nama' => $item['nama_menu'],
                'harga' => $item['harga'],
                'qty' => 1
            ];
        }
    }
    // Redirect untuk mencegah form resubmission dan menjaga URL tetap bersih
    header("Location: index.php" . (isset($_GET['kategori']) ? "?kategori=" . urlencode($_GET['kategori']) : ""));
    exit();
}

// 2. Logika Update Kuantitas atau Hapus (Menerima POST dari form item list)
if (isset($_POST['update_qty'])) {
    $menu_id = (int)$_POST['menu_id'];
    $new_qty = (int)$_POST['qty'];

    if (isset($_SESSION['keranjang'][$menu_id])) {
        if ($new_qty > 0) {
            $_SESSION['keranjang'][$menu_id]['qty'] = $new_qty;
        } else {
            // Hapus jika kuantitas <= 0
            unset($_SESSION['keranjang'][$menu_id]);
        }
    }
    // Redirect untuk mencegah form resubmission dan menjaga URL tetap bersih
    header("Location: index.php" . (isset($_GET['kategori']) ? "?kategori=" . urlencode($_GET['kategori']) : ""));
    exit();
}

// 3. Perhitungan Total Keranjang
$total_harga = 0;
$total_qty = 0;
foreach ($_SESSION['keranjang'] as $item) {
    $total_harga += $item['harga'] * $item['qty'];
    $total_qty += $item['qty'];
}
// ---------------------------------------------------------------------

// --- LOGIKA TAMPILAN MENU (Sama seperti sebelumnya) ---
$show_print_button = isset($_SESSION['last_transaction']) && !empty($_SESSION['last_transaction']);

$stmt_all_menu = $pdo->query("SELECT * FROM menu ORDER BY kategori_utama, sub_kategori, nama_menu");
$all_menu_items = $stmt_all_menu->fetchAll();

$menu_data_grouped = [];
foreach ($all_menu_items as $item) {
    $menu_data_grouped[$item['kategori_utama']][$item['sub_kategori']][] = $item;
}

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
        .menu-item {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .menu-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .notification-bar {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50%;
            z-index: 1050; 
        }
    </style>
    </head>
<body class="bg-light">

    <?php if ($show_print_button): ?>
        <div class="alert alert-success alert-dismissible fade show notification-bar" role="alert">
            <strong>Transaksi Berhasil!</strong> Silahkan klik tombol **Cetak Nota PDF** di sisi kanan untuk mencetak nota.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="container-fluid p-3">
        <div class="row g-3">

            <aside class="col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center pb-3 border-bottom mb-3">
                            <img src="assets/images/log_forest_dessert.jpg" alt="Logo" class="logo me-3" style="width: 100px; height: 100px;">
                            <div>
                                <h5 class="card-title mb-0">Forest Desert</h5>
                                <small class="text-muted"><?php echo date('l, j F Y'); ?></small>
                            </div>
                        </div>
                        <nav class="nav flex-column nav-pills">
                            <?php if (!empty($menu_data_grouped)): ?>
                                <?php foreach ($menu_data_grouped as $kategori => $sub_kategori_list): ?>
                                    <h6 class="nav-title mt-3"><?= htmlspecialchars($kategori) ?></h6>
                                    <?php foreach (array_keys($sub_kategori_list) as $sub_kategori_name): ?>
                                        <?php $is_active = ($sub_kategori_name === $active_sub_category) ? 'active' : ''; ?>
                                        <a class="nav-link <?= $is_active ?>" href="?kategori=<?= urlencode($sub_kategori_name) ?>">
                                            <?= htmlspecialchars($sub_kategori_name) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">Belum ada kategori menu.</p>
                            <?php endif; ?>
                        </nav>
                        
                        <a href="kelola_menu.php" class="btn btn-info w-100 mt-auto pt-2 pb-2">
                            <i class="bi bi-pencil-square"></i> Kelola Menu
                        </a>
                        
                        <a href="laporan.php" class="btn btn-outline-secondary w-100 mt-2">
                            Lihat Laporan Penjualan
                        </a>

                        <a href="logout.php" class="btn btn-danger w-100 mt-2">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </div>
                </div>
            </aside>

            <main class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <input type="text" class="form-control mb-3" placeholder="Cari menu... (Fungsionalitas ini butuh JS)">
                        <div class="menu-grid overflow-auto flex-grow-1"> 
                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                                <?php if (!empty($items_to_display)): ?>
                                    <?php foreach ($items_to_display as $item): ?>
                                        <div class="col">
                                            <form method="POST" action="index.php?kategori=<?= urlencode($active_sub_category ?? '') ?>" class="d-grid gap-1">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="menu_id" value="<?= htmlspecialchars($item['id']) ?>">

                                                <button type="submit" class="p-0 border-0 bg-transparent">
                                                    <div class="card h-100 menu-item">
                                                        <img src="<?= htmlspecialchars(file_exists($item['gambar']) ? $item['gambar'] : 'assets/images/placeholder_default.png') ?>" 
                                                            class="card-img-top" 
                                                            alt="<?= htmlspecialchars($item['nama_menu']) ?>">
                                                        <div class="card-body p-2 text-center">
                                                            <h6 class="card-title fs-sm mb-1"><?= htmlspecialchars($item['nama_menu']) ?></h6>
                                                            <p class="card-text text-primary fw-bold">Rp <?= formatRupiahPHP($item['harga']) ?></p>
                                                        </div>
                                                    </div>
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted p-3">Pilih kategori atau belum ada menu.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <aside class="col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                            <h5 class="mb-0">Order Menu</h5>
                            <span class="badge bg-secondary">Kasir: <?= htmlspecialchars($nama_kasir) ?></span>
                        </div>
                        
                        <div id="order-list" class="flex-grow-1 my-3 overflow-auto">
                            <?php if (empty($_SESSION['keranjang'])): ?>
                                <p class="text-center text-muted mt-4">Silahkan Pilih Menu...</p>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($_SESSION['keranjang'] as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                                            <div class="flex-grow-1 me-2">
                                                <strong class="d-block"><?= htmlspecialchars($item['nama']) ?></strong>
                                                <small class="text-muted">@ Rp <?= formatRupiahPHP($item['harga']) ?></small>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <form method="POST" action="index.php" class="d-flex me-2">
                                                    <input type="hidden" name="update_qty" value="1">
                                                    <input type="hidden" name="menu_id" value="<?= $item['id'] ?>">
                                                    <input type="number" name="qty" value="<?= $item['qty'] ?>" min="0" 
                                                        class="form-control form-control-sm text-center" style="width: 60px;"
                                                        onchange="this.form.submit()"> 
                                                </form>
                                                <span class="fw-bold" style="width: 80px; text-align: right;">
                                                    Rp <?= formatRupiahPHP($item['harga'] * $item['qty']) ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="payment-section mt-auto border-top pt-3">
                            <div class="d-flex justify-content-between fw-bold mb-1">
                                <span class="text-muted">Total Items:</span>
                                <span id="item-count"><?= $total_qty ?> items</span>
                            </div>
                            <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                                <span>Total Harga:</span>
                                <span id="total-price" class="text-primary">Rp <?= formatRupiahPHP($total_harga) ?></span>
                            </div>

                            <form id="payment-form" method="POST" action="proses_pesanan.php">
                                <input type="hidden" name="total_harga" value="<?= $total_harga ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold d-block mb-2">Metode Pembayaran:</label>
                                    <div class="d-flex gap-2">
                                        <input type="radio" class="btn-check" name="paymentMethod" id="pay-cash" value="Tunai" autocomplete="off" checked>
                                        <label class="btn btn-outline-success flex-fill" for="pay-cash"><i class="bi bi-cash-coin"></i> Tunai</label>

                                        <input type="radio" class="btn-check" name="paymentMethod" id="pay-qris" value="QRIS" autocomplete="off">
                                        <label class="btn btn-outline-info flex-fill" for="pay-qris"><i class="bi bi-qr-code"></i> QRIS</label>
                                    </div>
                                </div>

                                <div id="cash-payment-details" class="mb-3">
                                    <label for="cash-amount" class="form-label">Jumlah Uang Tunai (Wajib diisi jika tunai):</label>
                                    <div class="input-group mb-2"> <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="cash_amount" id="cash-amount" 
                                            placeholder="0" min="0" 
                                            <?= ($total_qty > 0) ? 'required' : '' ?> > 
                                    </div>
                                    <div class="mt-2 text-muted small">Kembalian dihitung di halaman berikutnya (proses_pesanan.php).</div>
                                </div>

                                <button type="submit" id="btn-order" class="btn btn-success w-100 fw-bold btn-lg mb-2" 
                                    <?= empty($_SESSION['keranjang']) ? 'disabled' : '' ?>>
                                    <i class="bi bi-check-circle-fill"></i> Proses Pembayaran
                                </button>
                            </form>
                        
                            <?php if ($show_print_button): ?>
                                <a href="generate_nota.php" target="_blank" class="btn btn-secondary w-100 fw-bold btn-lg">
                                    <i class="bi bi-printer-fill"></i> Cetak Nota PDF
                                </a>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
            </aside>
            </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    </body>
</html>