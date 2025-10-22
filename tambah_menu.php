<?php
include 'koneksi.php'; // Sertakan file koneksi

// Ambil daftar kategori unik untuk dropdown pilihan
$stmt_cat = $pdo->query("SELECT DISTINCT kategori_utama, sub_kategori FROM menu ORDER BY kategori_utama, sub_kategori");
$kategori_list = $stmt_cat->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu Baru - Forest Desert</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container my-4" style="max-width: 600px;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="bi bi-plus-circle-fill"></i> Tambah Menu Baru</h3>
            </div>
            <div class="card-body">
                <form action="proses_tambah_menu.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="id" class="form-label">Kode Menu (ID Unik) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="id" name="id" required placeholder="Contoh: DB06, BV04">
                        <small class="text-muted">Kode ini tidak bisa diubah nanti.</small>
                    </div>
                    <div class="mb-3">
                        <label for="nama_menu" class="form-label">Nama Menu <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_menu" name="nama_menu" required>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga (Angka saja) <span class="text-danger">*</span></label>
                        <div class="input-group">
                           <span class="input-group-text">Rp</span>
                           <input type="number" class="form-control" id="harga" name="harga" required placeholder="Contoh: 35000" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                         <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                         <select class="form-select" id="kategori" name="kategori">
                             <option value="">-- Pilih Kategori yang Sudah Ada --</option>
                             <?php
                                // Membuat dropdown kategori dengan optgroup
                                $current_main_cat = '';
                                foreach ($kategori_list as $kat) {
                                    // Jika kategori utama berbeda, buat optgroup baru
                                    if ($kat['kategori_utama'] != $current_main_cat) {
                                        if ($current_main_cat != '') echo '</optgroup>'; // Tutup optgroup sebelumnya
                                        echo '<optgroup label="' . htmlspecialchars($kat['kategori_utama']) . '">';
                                        $current_main_cat = $kat['kategori_utama'];
                                    }
                                    // Tampilkan sub kategori sebagai option
                                    echo '<option value="' . htmlspecialchars($kat['kategori_utama']) . '|' . htmlspecialchars($kat['sub_kategori']) . '">'
                                        . htmlspecialchars($kat['sub_kategori']) . '</option>';
                                }
                                if ($current_main_cat != '') echo '</optgroup>'; // Tutup optgroup terakhir
                             ?>
                         </select>
                         <small class="text-muted">Atau isi kategori baru di bawah jika belum ada.</small>
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
                        <label for="gambar" class="form-label">Gambar Menu (Opsional)</label>
                        <input class="form-control" type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/gif">
                        <small>Jika dikosongkan, akan menggunakan gambar default.</small>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="kelola_menu.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save-fill"></i> Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>