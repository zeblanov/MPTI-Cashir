<?php
include 'koneksi.php'; // Sertakan file koneksi

// Ambil ID menu dari parameter URL (?id=...)
$id_menu = $_GET['id'] ?? null;
if (!$id_menu) {
    // Jika tidak ada ID di URL, kembali ke halaman kelola menu
    redirect_dengan_pesan('kelola_menu.php', 'gagal', 'ID Menu tidak valid atau tidak ditemukan.');
}

// Ambil data menu yang akan diedit dari database berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM menu WHERE id = :id");
    $stmt->bindParam(':id', $id_menu);
    $stmt->execute();
    $menu_item = $stmt->fetch(); // Ambil satu baris data

    // Jika menu dengan ID tersebut tidak ditemukan di database
    if (!$menu_item) {
        redirect_dengan_pesan('kelola_menu.php', 'gagal', "Menu dengan Kode '$id_menu' tidak ditemukan.");
    }
} catch (PDOException $e) {
    // Jika terjadi error saat mengambil data
    redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Database error: ' . $e->getMessage());
}


// Ambil daftar kategori unik untuk dropdown pilihan
$stmt_cat = $pdo->query("SELECT DISTINCT kategori_utama, sub_kategori FROM menu ORDER BY kategori_utama, sub_kategori");
$kategori_list = $stmt_cat->fetchAll();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu - <?= htmlspecialchars($menu_item['nama_menu']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-4" style="max-width: 600px;">
        <div class="card shadow-sm">
             <div class="card-header bg-warning">
                <h3 class="mb-0"><i class="bi bi-pencil-fill"></i> Edit Menu: <?= htmlspecialchars($menu_item['nama_menu']) ?></h3>
            </div>
            <div class="card-body">
                <form action="proses_edit_menu.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_lama" value="<?= htmlspecialchars($menu_item['id']) ?>">
                    
                    <div class="mb-3">
                        <label for="id" class="form-label">Kode Menu (ID Unik) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="id" name="id" required value="<?= htmlspecialchars($menu_item['id']) ?>">
                        <small class="text-muted">Hati-hati mengubah kode menu jika sudah ada transaksi terkait.</small>
                    </div>
                    <div class="mb-3">
                        <label for="nama_menu" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_menu" name="nama_menu" required value="<?= htmlspecialchars($menu_item['nama_menu']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga (Angka saja) <span class="text-danger">*</span></label>
                         <div class="input-group">
                           <span class="input-group-text">Rp</span>
                           <input type="number" class="form-control" id="harga" name="harga" required value="<?= htmlspecialchars($menu_item['harga']) ?>" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                         <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                         <select class="form-select" id="kategori" name="kategori">
                             <option value="">-- Pilih Kategori yang Sudah Ada --</option>
                             <?php
                                // Membuat dropdown kategori dengan optgroup dan menandai yang terpilih
                                $current_main_cat = '';
                                foreach ($kategori_list as $kat) {
                                    if ($kat['kategori_utama'] != $current_main_cat) {
                                        if ($current_main_cat != '') echo '</optgroup>';
                                        echo '<optgroup label="' . htmlspecialchars($kat['kategori_utama']) . '">';
                                        $current_main_cat = $kat['kategori_utama'];
                                    }
                                    $value_kat = htmlspecialchars($kat['kategori_utama']) . '|' . htmlspecialchars($kat['sub_kategori']);
                                    // Cek apakah kategori ini yang sedang dipilih oleh item menu yang diedit
                                    $selected = ($kat['kategori_utama'] == $menu_item['kategori_utama'] && $kat['sub_kategori'] == $menu_item['sub_kategori']) ? 'selected' : '';
                                    echo '<option value="' . $value_kat . '" ' . $selected . '>' . htmlspecialchars($kat['sub_kategori']) . '</option>';
                                }
                                if ($current_main_cat != '') echo '</optgroup>';
                             ?>
                         </select>
                         <small class="text-muted">Atau isi kategori baru di bawah jika ingin mengubah/membuat baru.</small>
                    </div>
                     <div class="row mb-3">
                         <div class="col-md-6">
                            <label for="kategori_utama_baru" class="form-label">Kategori Utama Baru</label>
                            <input type="text" class="form-control" id="kategori_utama_baru" name="kategori_utama_baru" placeholder="Contoh: SNACKS">
                         </div>
                         <div class="col-md-6">
                            <label for="sub_kategori_baru" class="form-label">Sub Kategori Baru</label>
                            <input type="text" class="form-control" id="sub_kategori_baru" name="sub_kategori_baru" placeholder="Contoh: Keripik">
                         </div>
                     </div>
                     <div class="mb-3">
                        <label for="gambar" class="form-label">Ganti Gambar Menu (Opsional)</label>
                        <input class="form-control" type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/gif">
                        <small>Kosongkan jika tidak ingin mengganti gambar.</small>
                        <div class="mt-2">
                             <p class="mb-1">Gambar saat ini:</p>
                             <img src="<?= htmlspecialchars($menu_item['gambar']) ?>" alt="Gambar saat ini" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                             <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($menu_item['gambar']) ?>">
                        </div>
                    </div>
                     <hr>
                    <div class="d-flex justify-content-between">
                        <a href="kelola_menu.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Batal</a>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-save-fill"></i> Update Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>