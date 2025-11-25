<?php
// Konfigurasi Database
$host = 'mysql';
$user = 'root';
$pass = 'password'; // Sesuaikan password MySQL Anda
$dbname = 'db_resto';

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset UTF-8
$conn->set_charset("utf8mb4");

define('IMAGE_PATH', 'http://localhost/website_quatre/admin_side/assets/image/');
?>