<?php
session_start();

// 1. Proteksi Halaman (Satpam)
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Ambil ID menu dari URL
if (isset($_GET['id'])) {
    $id_menu = $_GET['id'];

    // --- PROSES DELETE VIA API SPRING BOOT ---
    
    // Gunakan IP gateway yang berhasil digunakan sebelumnya
    $url = "http://172.17.0.1:8080/api/menus/delete/" . $id_menu;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Menggunakan HTTP DELETE
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Cek status code dari API (200 artinya OK/Berhasil)
    if ($httpCode == 200) {
        $_SESSION['pesan_sukses'] = "Menu berhasil dihapus melalui API.";
    } else {
        // Jika gagal, bisa jadi karena menu sedang terikat di tabel transaksi
        $_SESSION['pesan_error'] = "Gagal menghapus menu. (Error API: " . $httpCode . ")";
    }

    // Kembali ke halaman kelola menu
    header("Location: kelola_menu.php");
    exit();

} else {
    // Jika tidak ada ID di URL, kembali ke halaman utama
    header("Location: kelola_menu.php");
    exit();
}