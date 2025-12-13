<?php
// login.php

session_start();

// 1. Memasukkan file koneksi database
include 'koneksi.php'; 

// Cek jika user sudah login, jika ya, arahkan ke index.php
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === TRUE) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

// Cek pesan status dari logout.php (Tinggalkan ini di atas agar pesan muncul)
if (isset($_GET['status']) && $_GET['status'] == 'logged_out') {
    $success_message = "Anda telah berhasil keluar (Logout).";
}


// Cek apakah form login disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- LOGIKA OTENTIKASI MENGGUNAKAN DATABASE (PDO) ---
    try {
        // 1. Siapkan Query SQL
        // QUERY DIUBAH: kolom 'role' DIHAPUS karena tidak ada di tabel users Anda.
        $stmt = $pdo->prepare("SELECT id, username, password, nama_lengkap FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // 2. Periksa apakah pengguna ditemukan
        if ($user) {
            
            // 3. Verifikasi Password dengan password_verify()
            if (password_verify($password, $user['password'])) {
                
                // Kredensial valid
                $_SESSION['logged_in'] = TRUE;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap']; 
                // $_SESSION['role'] DIHAPUS karena tidak ada data 'role' yang ditarik dari DB.
                
                // Arahkan ke halaman utama (index.php)
                header("Location: index.php");
                exit();
                
            } else {
                // Password salah
                $error_message = "Username atau password salah. Silakan coba lagi.";
            }
        } else {
            // Username tidak ditemukan
            $error_message = "Username atau password salah. Silakan coba lagi.";
        }
        
    } catch (\PDOException $e) {
        // Tangani error database
        $error_message = "Kesalahan Database saat Login: " . $e->getMessage();
    }
    // --- AKHIR LOGIKA OTENTIKASI ---
}

// HTML (Tidak ada perubahan di bagian ini)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Forest Desert Kasir</title>
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
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <div class="card login-card">
        <div class="card-body">
            <div class="text-center mb-4">
                <img src="assets/images/log_forest_dessert.jpg" alt="Logo" class="mb-3" style="width: 80px; height: 80px; border-radius: 50%;">
                <h4 class="card-title">Login Kasir</h4>
                <p class="text-muted">Forest Desert</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold">Login</button>
            </form>
            
            <p class="mt-3 text-center">
                Belum punya akun? <a href="register.php" class="text-decoration-none fw-bold">Daftar di sini</a>
            </p>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>