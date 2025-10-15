<?php
// ▼▼▼ NEW MENU DATA STRUCTURE ACCORDING TO EXCEL ▼▼▼
$menu_data = [
    'DESSERTS & PASTRY' => [
        'Signature Desserts' => [
            ['id' => 'DB01', 'name' => 'Choco Lava Box', 'price' => 35000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'DB02', 'name' => 'Red Velvet Cheese', 'price' => 38000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'DB03', 'name' => 'Tiramisu Regal', 'price' => 32000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'DB04', 'name' => 'Matcha Green Tea', 'price' => 30000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'DB05', 'name' => 'Biscoff Lotus', 'price' => 40000, 'image' => 'assets/images/placeholder_dessert.png'],
        ],
        'Fresh & Frozen' => [
            ['id' => 'FD01', 'name' => 'Mango Sago Fusion', 'price' => 28000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'FD02', 'name' => 'Korean Strawberry', 'price' => 25000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'FD03', 'name' => 'Es Campur Kekinian', 'price' => 27000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'FD04', 'name' => 'Silky Puding Coklat', 'price' => 20000, 'image' => 'assets/images/placeholder_dessert.png'],
        ],
        'Pastry & Baked' => [
            ['id' => 'PB01', 'name' => 'Mini Fruit Tart', 'price' => 15000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'PB02', 'name' => 'Soft Cookies Chocochip', 'price' => 12000, 'image' => 'assets/images/placeholder_dessert.png'],
            ['id' => 'PB03', 'name' => 'Cinnamon Roll Cream', 'price' => 18000, 'image' => 'assets/images/placeholder_dessert.png'],
        ],
    ],
    'BEVERAGES' => [
        'Beverages (Mini BV)' => [
            ['id' => 'BV01', 'name' => 'Iced Americano', 'price' => 15000, 'image' => 'assets/images/placeholder_beverage.png'],
            ['id' => 'BV02', 'name' => 'Chocolate Hazelnut', 'price' => 22000, 'image' => 'assets/images/placeholder_beverage.png'],
            ['id' => 'BV03', 'name' => 'Lychee Tea', 'price' => 18000, 'image' => 'assets/images/placeholder_beverage.png'],
        ],
    ],
];

// Set the active category from the URL, or use 'Signature Desserts' as the default
$active_category = $_GET['kategori'] ?? 'Signature Desserts';

// Find the items that match the active category
$items_to_display = [];
foreach ($menu_data as $main_category => $sub_categories) {
    if (isset($sub_categories[$active_category])) {
        $items_to_display = $sub_categories[$active_category];
        break;
    }
}
// ▲▲▲ END OF NEW MENU DATA ▲▲▲
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
                                                 data-id="<?= htmlspecialchars($item['id']) ?>" 
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