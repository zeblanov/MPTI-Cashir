<?php
include 'koneksi.php'; // Sertakan file koneksi

// Hanya proses jika request method adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan bersihkan (trim)
    $id_lama = trim($_POST['id_lama'] ?? ''); // ID asli menu sebelum diedit
    $id_baru = trim($_POST['id'] ?? '');     // ID baru (bisa sama atau beda dengan id_lama)
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $harga = $_POST['harga'] ?? null;
    $kategori_dipilih = $_POST['kategori'] ?? null;
    $kategori_utama_baru = trim($_POST['kategori_utama_baru'] ?? '');
    $sub_kategori_baru = trim($_POST['sub_kategori_baru'] ?? '');
    $gambar_path = $_POST['gambar_lama'] ?? 'assets/images/placeholder_default.png'; // Defaultnya adalah gambar lama

    // --- Validasi Input Sederhana ---
    if (empty($id_lama) || empty($id_baru) || empty($nama_menu) || !is_numeric($harga) || $harga < 0) {
        redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Kode Lama, Kode Baru, Nama Menu, dan Harga (angka positif) harus diisi.');
    }
    
    // --- Tentukan Kategori ---
    $kategori_utama = '';
    $sub_kategori = '';
     if (!empty($kategori_utama_baru) && !empty($sub_kategori_baru)) {
        // Jika kategori baru diisi, gunakan itu
        $kategori_utama = $kategori_utama_baru;
        $sub_kategori = $sub_kategori_baru;
    } elseif (!empty($kategori_dipilih)) {
        // Jika memilih dari dropdown, pisahkan nilainya
        $kategori_parts = explode('|', $kategori_dipilih, 2);
         if (count($kategori_parts) == 2) {
             $kategori_utama = $kategori_parts[0];
             $sub_kategori = $kategori_parts[1];
        } else {
             // Format value dropdown salah
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Format kategori yang dipilih tidak valid.');
        }
    } else {
         // Jika tidak memilih dropdown dan tidak mengisi kategori baru (asumsi tidak ingin mengubah kategori)
         // Kita perlu mengambil kategori lama dari database jika diperlukan, tapi dalam kasus ini kita bisa membiarkannya
         // karena query UPDATE akan menggunakan nilai dari database jika tidak diubah.
         // Namun, untuk konsistensi, lebih baik jika form *mewajibkan* memilih atau mengisi.
         // Untuk saat ini, kita beri error jika keduanya kosong.
         redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Kategori harus dipilih atau diisi (Kategori Utama & Sub Kategori Baru).');
    }

    // --- Proses Upload Gambar Baru (jika ada file yang diupload) ---
    // Cek apakah ada file baru, tidak ada error, dan ukurannya > 0
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK && $_FILES['gambar']['size'] > 0) {
        $target_dir = "assets/images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        // Gunakan ID baru untuk nama file unik jika ID diubah
        $nama_file_unik = 'menu_' . preg_replace('/[^a-zA-Z0-9]/', '_', $id_baru) . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $nama_file_unik;
        $uploadOk = 1;

        // Validasi gambar (tipe, ukuran, dll.)
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if ($check === false) {
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'File baru yang diupload bukan gambar.');
             $uploadOk = 0;
        }
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan.');
             $uploadOk = 0;
        }
        $max_size = 2 * 1024 * 1024; // 2 MB
         if ($_FILES["gambar"]["size"] > $max_size) {
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Ukuran gambar baru terlalu besar (maks 2MB).');
             $uploadOk = 0;
        }

        // Jika validasi upload OK, pindahkan file baru
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                 // Hapus gambar lama JIKA:
                 // 1. Gambar lama ADA ($gambar_path tidak kosong)
                 // 2. Gambar lama BUKAN placeholder default
                 // 3. Path gambar lama BERBEDA dengan path gambar baru (jika namanya sama persis)
                 // 4. File gambar lama benar-benar ADA di server
                if (!empty($gambar_path) &&
                    $gambar_path != 'assets/images/placeholder_default.png' &&
                    $gambar_path != $target_file &&
                    file_exists($gambar_path))
                {
                    unlink($gambar_path); // Hapus file gambar lama
                }
                $gambar_path = $target_file; // Update path gambar ke yang baru diupload
            } else {
                 // Gagal memindahkan file baru
                 echo "<script>alert('Gagal mengupload gambar baru, perubahan gambar dibatalkan.');</script>";
                 // $gambar_path tetap menggunakan nilai dari $gambar_lama
            }
        }
    } // Akhir dari proses upload gambar baru

    // --- Update Data ke Database ---
    try {
        // Query SQL UPDATE dengan prepared statement
        $sql = "UPDATE menu SET
                    id = :id_baru,
                    nama_menu = :nama,
                    harga = :harga,
                    kategori_utama = :kat_utama,
                    sub_kategori = :sub_kat,
                    gambar = :gambar
                WHERE id = :id_lama"; // Kondisi WHERE menggunakan ID lama
                
        $stmt = $pdo->prepare($sql);
        
        // Binding parameter
        $stmt->bindParam(':id_baru', $id_baru);
        $stmt->bindParam(':nama', $nama_menu);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':kat_utama', $kategori_utama);
        $stmt->bindParam(':sub_kat', $sub_kategori);
        $stmt->bindParam(':gambar', $gambar_path);
        $stmt->bindParam(':id_lama', $id_lama); // ID lama untuk kondisi WHERE
        
        // Eksekusi query
        $stmt->execute();
        
        // Cek apakah ada baris yang terpengaruh (berhasil diupdate)
        if ($stmt->rowCount() > 0) {
            redirect_dengan_pesan('kelola_menu.php', 'sukses', 'Menu berhasil diperbarui.');
        } else {
             // Jika rowCount = 0, bisa jadi karena ID lama tidak ditemukan
             // atau tidak ada data yang berubah sama sekali.
             // Kita anggap saja tidak ada perubahan atau ID tidak ditemukan.
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Tidak ada perubahan data atau menu dengan kode lama tidak ditemukan.');
        }

    } catch (PDOException $e) {
        // Tangani error jika terjadi (misalnya ID Baru duplikat)
        if ($e->getCode() == 23000) { // Kode error SQLSTATE untuk duplicate entry
            redirect_dengan_pesan('kelola_menu.php', 'gagal', "Gagal memperbarui menu. Kode Menu baru '$id_baru' mungkin sudah digunakan oleh menu lain.");
        } else {
            // Error database lainnya
            redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Database error: ' . $e->getMessage());
        }
    }

} else {
    // Jika halaman ini diakses langsung tanpa POST, redirect ke halaman kelola
    header('Location: kelola_menu.php');
    exit;
}
?>