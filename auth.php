<?php
session_start();

// Cek apakah variabel session 'logged_in' ada dan bernilai TRUE
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== TRUE) {
    // Jika tidak/belum login, paksa arahkan ke halaman login
    header("Location: login.php");
    exit();
}
?>