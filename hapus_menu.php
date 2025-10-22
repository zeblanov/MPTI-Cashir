<?php
// 1. Sertakan file koneksi database
include 'koneksi.php'; // Pastikan file koneksi.php ada dan berisi fungsi redirect_dengan_pesan

// 2. Ambil ID menu dari parameter URL (?id=...)
$id_menu = $_GET['id'] ?? null;

// 3. Validasi ID
if (!$id_menu) {
    // Jika tidak ada ID di URL, kembali ke halaman kelola menu dengan pesan error
    redirect_dengan_pesan('kelola_menu.php', 'gagal', 'ID Menu tidak valid atau tidak ditemukan untuk dihapus.');
}

try {
    // --- Langkah 4: Ambil path gambar SEBELUM data dihapus dari DB ---
    $stmt_select = $pdo->prepare("SELECT gambar FROM menu WHERE id = :id");
    $stmt_select->bindParam(':id', $id_menu);
    $stmt_select->execute();
    $menu_item = $stmt_select->fetch(); // Ambil data menu (terutama path gambar)

    // Simpan path gambar jika item ditemukan
    $gambar_path_untuk_dihapus = null;
    if ($menu_item) {
        $gambar_path_untuk_dihapus = $menu_item['gambar'];
    } else {
        // Jika menu tidak ditemukan sama sekali
        redirect_dengan_pesan('kelola_menu.php', 'gagal', "Menu dengan Kode '$id_menu' tidak ditemukan.");
    }

    // --- Langkah 5: Hapus data menu dari database ---
    $sql = "DELETE FROM menu WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id_menu);
    $stmt->execute();

    // --- Langkah 6: Cek apakah penghapusan dari DB berhasil ---
    // rowCount() mengembalikan jumlah baris yang terpengaruh oleh query DELETE
    if ($stmt->rowCount() > 0) {
        // Jika data berhasil dihapus dari DB (rowCount > 0)

        // --- Langkah 7: Hapus file gambar terkait (jika perlu) ---
        if ($gambar_path_untuk_dihapus &&                     // Pastikan path gambar ada
            $gambar_path_untuk_dihapus != 'assets/images/placeholder_default.png' && // Jangan hapus placeholder
            strpos($gambar_path_untuk_dihapus, 'placeholder_') === false && // Cek juga placeholder lain (opsional)
            file_exists($gambar_path_untuk_dihapus))         // Pastikan file benar-benar ada di server
        {
            // Coba hapus file gambar dari folder assets/images/
            // @unlink digunakan untuk menekan error jika file tidak bisa dihapus (misal karena permission)
            @unlink($gambar_path_untuk_dihapus);
        }

        // --- Langkah 8: Redirect dengan pesan sukses ---
        redirect_dengan_pesan('kelola_menu.php', 'sukses', 'Menu berhasil dihapus.');

    } else {
        // Jika rowCount = 0, berarti ID tidak ditemukan saat mencoba menghapus
        // (Meskipun seharusnya sudah dicek di langkah 4, ini sebagai pengaman tambahan)
        redirect_dengan_pesan('kelola_menu.php', 'gagal', "Gagal menghapus. Menu dengan Kode '$id_menu' mungkin sudah dihapus sebelumnya.");
    }

} catch (PDOException $e) {
    // --- Langkah 9: Tangani jika terjadi error database saat proses ---
    redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Database error saat mencoba menghapus: ' . $e->getMessage());
}
?>