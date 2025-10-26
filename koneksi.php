<?php
$db_host = 'localhost';
$db_name = 'db_forest_desert';
$db_user = 'root';
$db_pass = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}

function redirect_dengan_pesan($url, $status, $pesan) {
    header("Location: $url?status=$status&pesan=" . urlencode($pesan));
    exit;
}
?>