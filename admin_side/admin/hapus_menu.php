<?php
session_start();

// 1. Proteksi Halaman (Satpam)
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Cek apakah ada ID di URL
if (isset($_GET['id'])) {
    $id_menu = $_GET['id'];

    // --- PROSES DELETE VIA API SPRING BOOT DENGAN JWT ---
    
    // Ambil token dari session
    $token = $_SESSION['jwt_token'] ?? '';

    // Gunakan IP gateway yang sesuai
    $url = "http://172.17.0.1:8080/api/menus/delete/" . $id_menu;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Menggunakan HTTP DELETE
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // --- TAMBAHKAN HEADER AUTHORIZATION ---
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token // Token dikirim di sini
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Cek status code dari API
    if ($httpCode == 200) {
        $_SESSION['pesan_sukses'] = "Menu berhasil dihapus (Soft Delete) via API.";
    } elseif ($httpCode == 401 || $httpCode == 403) {
        $_SESSION['pesan_error'] = "Gagal menghapus: Token tidak valid atau sesi habis.";
    } else {
        // Jika gagal (misal error server atau constraint lain)
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
?>