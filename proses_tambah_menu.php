<?php
include 'koneksi.php'; // Sertakan file koneksi

// Hanya proses jika request method adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form dan bersihkan (trim) spasi
    $id = trim($_POST['id'] ?? '');
    $nama_menu = trim($_POST['nama_menu'] ?? '');
    $harga = $_POST['harga'] ?? null;
    $kategori_dipilih = $_POST['kategori'] ?? null;
    $kategori_utama_baru = trim($_POST['kategori_utama_baru'] ?? '');
    $sub_kategori_baru = trim($_POST['sub_kategori_baru'] ?? '');
    $gambar_path = 'assets/images/placeholder_default.png'; // Path gambar default

    // --- Validasi Input Sederhana ---
    if (empty($id) || empty($nama_menu) || !is_numeric($harga) || $harga < 0) {
        // Jika validasi gagal, kembali ke halaman kelola menu dengan pesan error
        redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Kode, Nama Menu, dan Harga (angka positif) wajib diisi.');
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
         // Jika tidak memilih dropdown dan tidak mengisi kategori baru
         redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Kategori harus dipilih atau diisi (Kategori Utama & Sub Kategori Baru).');
    }

    // --- Proses Upload Gambar (jika ada file yang diupload) ---
    // Cek apakah ada file, tidak ada error upload, dan ukurannya > 0
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == UPLOAD_ERR_OK && $_FILES['gambar']['size'] > 0) {
        $target_dir = "assets/images/"; // Folder tujuan upload
        // Pastikan folder assets/images ada dan bisa ditulisi (writable)
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder jika belum ada (permission 0777 hanya untuk development)
        }
        
        $imageFileType = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        // Buat nama file unik: menu_[kode_menu]_[timestamp].[ekstensi]
        $nama_file_unik = 'menu_' . preg_replace('/[^a-zA-Z0-9]/', '_', $id) . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $nama_file_unik;
        $uploadOk = 1; // Flag status upload

        // Cek apakah file benar-benar gambar
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if ($check === false) {
            redirect_dengan_pesan('kelola_menu.php', 'gagal', 'File yang diupload bukan gambar.');
            $uploadOk = 0;
        }

        // Batasi tipe file yang diizinkan
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan.');
            $uploadOk = 0;
        }

        // Batasi ukuran file (misal: maksimum 2MB)
        $max_size = 2 * 1024 * 1024; // 2 Megabytes
        if ($_FILES["gambar"]["size"] > $max_size) {
             redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Ukuran gambar terlalu besar (maks 2MB).');
             $uploadOk = 0;
        }

        // Jika semua validasi lolos ($uploadOk == 1), coba pindahkan file
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $gambar_path = $target_file; // Update path gambar menggunakan file yang baru diupload
            } else {
                // Gagal memindahkan file, bisa jadi karena permission folder
                 // Tampilkan pesan tapi tetap lanjutkan dengan gambar default
                 echo "<script>alert('Gagal mengupload gambar, menggunakan gambar default. Periksa permission folder assets/images.');</script>";
                 // Jika ingin menghentikan proses jika upload gagal:
                 // redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Terjadi error saat mengupload gambar.');
            }
        }
    } // Akhir dari proses upload gambar

    // --- Simpan Data ke Database ---
    try {
        // Query SQL INSERT dengan prepared statement untuk keamanan
        $sql = "INSERT INTO menu (id, nama_menu, harga, kategori_utama, sub_kategori, gambar)
                VALUES (:id, :nama, :harga, :kat_utama, :sub_kat, :gambar)";
        $stmt = $pdo->prepare($sql);
        
        // Binding parameter ke placeholder
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama', $nama_menu);
        $stmt->bindParam(':harga', $harga);
        $stmt->bindParam(':kat_utama', $kategori_utama);
        $stmt->bindParam(':sub_kat', $sub_kategori);
        $stmt->bindParam(':gambar', $gambar_path);
        
        // Eksekusi query
        $stmt->execute();
        
        // Redirect kembali ke halaman kelola menu dengan pesan sukses
        redirect_dengan_pesan('kelola_menu.php', 'sukses', 'Menu baru berhasil ditambahkan.');

    } catch (PDOException $e) {
        // Tangani error jika terjadi (misalnya ID duplikat)
        if ($e->getCode() == 23000) { // Kode error SQLSTATE untuk duplicate entry (primary key/unique)
            redirect_dengan_pesan('kelola_menu.php', 'gagal', "Gagal menambahkan menu. Kode Menu '$id' sudah terdaftar. Gunakan kode lain.");
        } else {
            // Error database lainnya
            redirect_dengan_pesan('kelola_menu.php', 'gagal', 'Database error: ' . $e->getMessage());
        }
    }

} else {
    // Jika halaman ini diakses langsung tanpa POST, redirect ke form tambah
    header('Location: tambah_menu.php');
    exit;
}
?>