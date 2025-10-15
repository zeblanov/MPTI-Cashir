<?php
// STRUKTUR DATA MENU
$menu_data = [
    'MAKANAN' => [
        'Spesial Nasi Goreng' => [
            ['id' => 1, 'name' => 'Nasgor Biasa', 'price' => 10000, 'image' => 'assets/images/nasgor.jpg'],
            ['id' => 3, 'name' => 'Nasgor Ayam', 'price' => 13000, 'image' => 'assets/images/nasgor.jpg'],
            ['id' => 6, 'name' => 'Nasgor Spesial', 'price' => 16000, 'image' => 'assets/images/nasgor.jpg'],
            ['id' => 7, 'name' => 'Nasgor Setan', 'price' => 17000, 'image' => 'assets/images/nasgor.jpg'],
        ],
        'Spesial Omelet' => [
            ['id' => 8, 'name' => 'Omelet Original', 'price' => 8000, 'image' => 'assets/images/omelet.jpg'],
        ],
    ],
    'MINUMAN' => [
        'Minuman Dingin' => [
            ['id' => 10, 'name' => 'Teh Es', 'price' => 3000, 'image' => 'assets/images/teh_es.jpg'],
            ['id' => 11, 'name' => 'Es Jeruk', 'price' => 4000, 'image' => 'assets/images/es_jeruk.jpg'],
        ],
    ],
];

// ▼▼▼ LOGIKA BARU UNTUK MENAMPILKAN MENU SECARA DINAMIS ▼▼▼

// Tentukan kategori aktif dari URL, jika tidak ada, gunakan 'Spesial Nasi Goreng' sebagai default
$active_category = $_GET['kategori'] ?? 'Spesial Nasi Goreng';

// Cari item yang sesuai dengan kategori aktif
$items_to_display = [];
foreach ($menu_data as $main_category => $sub_categories) {
    if (isset($sub_categories[$active_category])) {
        $items_to_display = $sub_categories[$active_category];
        break; // Hentikan pencarian jika sudah ditemukan
    }
}
// ▲▲▲ BATAS AKHIR LOGIKA BARU ▲▲▲

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir (Bootstrap) - Forest Desert</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
                            <?php foreach ($menu_data as $kategori => $sub_kategori_list): ?>
                                <h6 class="nav-title mt-3"><?= $kategori ?></h6>
                                <?php foreach (array_keys($sub_kategori_list) as $sub_kategori_name): ?>
                                    
                                    <?php
                                        // Cek apakah sub-kategori saat ini adalah yang sedang aktif
                                        $is_active = ($sub_kategori_name === $active_category) ? 'active' : '';
                                    ?>
                                    <a class="nav-link <?= $is_active ?>" href="?kategori=<?= urlencode($sub_kategori_name) ?>">
                                        <?= $sub_kategori_name ?>
                                    </a>
                                    <?php endforeach; ?>
                            <?php endforeach; ?>
                        </nav>
                        
                        <a href="laporan.php" class="btn btn-outline-secondary w-100 mt-auto">
                            Lihat Laporan Penjualan
                        </a>

                    </div>
                </div>
            </aside>

            <main class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <input type="text" class="form-control mb-3" placeholder="Search menu...">
                        <div class="menu-grid">
                            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                                
                                <?php if (!empty($items_to_display)): ?>
                                    <?php foreach ($items_to_display as $item): ?>
                                        <div class="col">
                                            <div class="card h-100 menu-item" 
                                                 data-id="<?= $item['id'] ?>" 
                                                 data-name="<?= htmlspecialchars($item['name']) ?>" 
                                                 data-price="<?= $item['price'] ?>">
                                                <img src="<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                                <div class="card-body p-2 text-center">
                                                    <h6 class="card-title fs-sm mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                    <p class="card-text text-primary fw-bold">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Menu untuk kategori ini tidak ditemukan.</p>
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
                             <span class="badge bg-secondary">Order No. 18</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>