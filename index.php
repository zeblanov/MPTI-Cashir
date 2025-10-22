<?php
// Memasukkan file koneksi database
include 'koneksi.php'; // Pastikan file koneksi.php sudah ada

// Mengambil semua data menu dari database
$stmt_all_menu = $pdo->query("SELECT * FROM menu ORDER BY kategori_utama, sub_kategori, nama_menu");
$all_menu_items = $stmt_all_menu->fetchAll();

// Mengelompokkan data menu untuk sidebar dan tampilan grid
$menu_data_grouped = [];
foreach ($all_menu_items as $item) {
    $menu_data_grouped[$item['kategori_utama']][$item['sub_kategori']][] = $item;
}

// Tentukan sub-kategori aktif dari URL
$active_sub_category = $_GET['kategori'] ?? null;
$items_to_display = [];

// Jika tidak ada sub-kategori di URL, ambil sub-kategori pertama sebagai default
if ($active_sub_category === null && !empty($menu_data_grouped)) {
    $first_main_category = key($menu_data_grouped);
    if (!empty($menu_data_grouped[$first_main_category])) {
        $first_sub_category_list = $menu_data_grouped[$first_main_category];
        $active_sub_category = key($first_sub_category_list);
    }
}


// Cari item yang sesuai dengan sub-kategori aktif
if ($active_sub_category !== null) {
    foreach ($menu_data_grouped as $main_category => $sub_categories) {
        if (isset($sub_categories[$active_sub_category])) {
            $items_to_display = $sub_categories[$active_sub_category];
            break;
        }
    }
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
</head>
<body class="bg-light">

    <div class="container-fluid p-3">
        <div class="row g-3">

            <aside class="col-lg-3">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center pb-3 border-bottom mb-3">
                            <img src="assets/images/logo_forest_desert.png" alt="Logo" class="logo me-3">
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
                                        <?php
                                            $is_active = ($sub_kategori_name === $active_sub_category) ? 'active' : '';
                                        ?>
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

                    </div>
                </div>
            </aside>

            <main class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <input type="text" class="form-control mb-3" id="search-input" placeholder="Cari menu...">
                        <div class="menu-grid">
                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                                <?php if (!empty($items_to_display)): ?>
                                    <?php foreach ($items_to_display as $item): ?>
                                        <div class="col">
                                            <div class="card h-100 menu-item" 
                                                 data-id="<?= htmlspecialchars($item['id']) ?>" 
                                                 data-name="<?= htmlspecialchars($item['nama_menu']) ?>" 
                                                 data-price="<?= $item['harga'] ?>">
                                                <img src="<?= htmlspecialchars(file_exists($item['gambar']) ? $item['gambar'] : 'assets/images/placeholder_default.png') ?>" 
                                                     class="card-img-top" 
                                                     alt="<?= htmlspecialchars($item['nama_menu']) ?>">
                                                <div class="card-body p-2 text-center">
                                                    <h6 class="card-title fs-sm mb-1"><?= htmlspecialchars($item['nama_menu']) ?></h6>
                                                    <p class="card-text text-primary fw-bold">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted p-3">Pilih kategori atau belum ada menu untuk kategori '<?= htmlspecialchars($active_sub_category ?? 'N/A') ?>'.</p>
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
                             <span id="order-id-display" class="badge bg-secondary">Order #1</span>
                        </div>
                        <div id="order-list" class="flex-grow-1 my-3">
                            <p class="text-center text-muted mt-4">Silahkan Pilih Menu...</p>
                        </div>
                        <div class="order-summary bg-primary text-white rounded p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span id="item-count" class="d-block">0 items</span>
                                    <strong id="total-price" class="fs-5">Rp 0</strong>
                                </div>
                                <button id="btn-order" class="btn btn-light fw-bold">Order</button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body text-center p-4">
            <i class="bi bi-check-circle-fill text-success display-1 mb-3"></i>
            <h4 class="modal-title mb-2" id="orderSuccessModalLabel">Pesanan Berhasil!</h4>
            <p>Pesanan Anda telah berhasil diproses dan disimpan.</p>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>