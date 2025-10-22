<?php
include 'koneksi.php'; // Sertakan file koneksi

// Ambil semua data menu dari database, diurutkan
$stmt = $pdo->query("SELECT * FROM menu ORDER BY kategori_utama, sub_kategori, nama_menu");
$menu_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="mb-0">📋 Kelola Daftar Menu</h3>
                <a href="tambah_menu.php" class="btn btn-light">
                    <i class="bi bi-plus-circle-fill"></i> Tambah Menu Baru
                </a>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['status'])): ?>
                    <div class="alert alert-<?= $_GET['status'] == 'sukses' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_GET['pesan']) // Tampilkan pesan ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Menu</th>
                                <th class="text-end">Harga</th>
                                <th>Kategori</th>
                                <th>Sub Kategori</th>
                                <th>Gambar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($menu_items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data menu. Silakan tambahkan menu baru.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($menu_items as $item): ?>
                                    <tr>
                                        <td class="text-center"><code><?= htmlspecialchars($item['id']) ?></code></td>
                                        <td><?= htmlspecialchars($item['nama_menu']) ?></td>
                                        <td class="text-end">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                        <td><?= htmlspecialchars($item['kategori_utama']) ?></td>
                                        <td><?= htmlspecialchars($item['sub_kategori']) ?></td>
                                        <td class="text-center">
                                            <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['nama_menu']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        </td>
                                        <td class="text-center">
                                            <a href="edit_menu.php?id=<?= htmlspecialchars($item['id']) ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="hapus_menu.php?id=<?= htmlspecialchars($item['id']) ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('⚠️ Apakah Anda yakin ingin menghapus menu \'<?= htmlspecialchars(addslashes($item['nama_menu'])) // Escape ' untuk JavaScript ?>\' secara permanen?')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="index.php" class="btn btn-outline-secondary mt-3">&larr; Kembali ke Halaman Kasir</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>