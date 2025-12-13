<?php
// logout.php

// 1. Mulai sesi PHP
// Ini diperlukan untuk mengakses data sesi yang akan dihapus.
session_start();

// 2. Hapus semua variabel sesi
// Menghilangkan data pengguna yang tersimpan (misalnya $_SESSION['logged_in'])
session_unset();

// 3. Hancurkan sesi
// Menghapus file sesi dari server.
session_destroy();

// 4. Lakukan pengalihan (redirect) ke halaman login.php
// header("Location: login.php");
// Gunakan exit() setelah header() untuk menghentikan eksekusi skrip lebih lanjut.
header("Location: login.php?status=logged_out");
exit(); 
?>