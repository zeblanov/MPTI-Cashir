<?php
// register.php

session_start();

// Cek jika user sudah login, arahkan ke index.php
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE) {
    header("Location: index.php");
    exit();
}

// Memasukkan koneksi database
include 'koneksi.php'; 

$error_message = '';
$success_message = '';
$nama_lengkap = ''; // Inisialisasi variabel untuk mempertahankan nilai
$username = ''; // Inisialisasi variabel untuk mempertahankan nilai

// Proses Pendaftaran jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form dan sanitasi
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    // Variabel $role DIHAPUS karena tidak ada di tabel database.

    // 1. Validasi Input
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($konfirmasi_password)) {
        $error_message = "Semua kolom harus diisi.";
    } elseif ($password !== $konfirmasi_password) {
        $error_message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password harus minimal 6 karakter.";
    } else {
        // 2. Cek apakah username sudah ada
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            
            if ($stmt->fetchColumn() > 0) {
                $error_message = "Username sudah terdaftar. Gunakan username lain.";
            } else {
                
                // 3. Hash Password (Wajib untuk Keamanan!)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // 4. Masukkan data ke Database
                // QUERY DIUBAH: kolom 'role' dan parameternya DIHILANGKAN
                $sql = "INSERT INTO users (nama_lengkap, username, password) VALUES (:nama, :user, :pass)";
                $stmt = $pdo->prepare($sql);
                
                $result = $stmt->execute([
                    ':nama' => $nama_lengkap,
                    ':user' => $username,
                    ':pass' => $hashed_password
                    // Parameter ':role' DIHILANGKAN
                ]);

                if ($result) {
                    $success_message = "Pendaftaran berhasil! Silakan <a href='login.php'>Login di sini</a>.";
                    
                    // Kosongkan variabel setelah sukses agar form bersih
                    $nama_lengkap = '';
                    $username = '';

                } else {
                    $error_message = "Terjadi kesalahan saat menyimpan data ke database.";
                }
            }

        } catch (PDOException $e) {
            // Tangani error database
            $error_message = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Forest Desert Kasir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .register-card {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="card register-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="assets/images/log_forest_dessert.jpg" alt="Logo" class="mb-3" style="width: 80px; height: 80px; border-radius: 50%;">
                <h4 class="card-title">Daftar Akun Baru</h4>
                <p class="text-muted">Untuk mengakses sistem Kasir Forest Desert</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?= $success_message ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($nama_lengkap) ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="konfirmasi_password" class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-check-lg"></i></span>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold">Daftar Akun</button>
            </form>
            
            <p class="mt-3 text-center">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>