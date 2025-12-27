<?php
session_start(); // Memulai sesi

// Menghapus semua data sesi
$_SESSION = array();

// Menghapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Menghancurkan sesi secara total
session_destroy();

// Mengarahkan kembali ke halaman login
header("location: login.php");
exit;
