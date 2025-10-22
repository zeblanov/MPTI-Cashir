<?php
// Pengaturan Database
$db_host = 'localhost';
$db_name = 'db_forest_desert'; // Sesuaikan jika nama database berbeda
$db_user = 'root';
$db_pass = ''; // Sesuaikan jika Anda menggunakan password

// Opsi untuk koneksi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Tampilkan error sebagai exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Hasil query sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false, // Gunakan prepared statements asli
];

try {
    // Membuat koneksi PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // Jika koneksi gagal, hentikan script dan tampilkan pesan error
    // Sebaiknya log error ini di production, bukan menampilkannya langsung
    die("Koneksi Database Gagal: " . $e->getMessage());
}

// Menampilkan error PHP (berguna saat development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fungsi helper untuk redirect dengan pesan status
function redirect_dengan_pesan($url, $status, $pesan) {
    header("Location: $url?status=$status&pesan=" . urlencode($pesan));
    exit; // Pastikan script berhenti setelah redirect
}
?>