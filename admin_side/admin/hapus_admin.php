<?php
session_start();

// 1. Proteksi Halaman (Satpam)
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 2. Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id_admin_to_delete = $_GET['id'];
    $id_admin_logged_in = $_SESSION['id_admin'];

    // 3. Pengecekan agar tidak menghapus akun sendiri
    if ($id_admin_to_delete == $id_admin_logged_in) {
        $_SESSION['pesan_error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    } else {
        
        // --- PROSES DELETE VIA API SPRING BOOT ---
        
        // Sesuaikan IP-nya dengan IP gateway yang berhasil kamu pakai sebelumnya
        $url = "http://172.17.0.1:8080/api/admins/delete/" . $id_admin_to_delete;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Menggunakan method DELETE
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Cek status code dari API (200 artinya OK/Berhasil)
        if ($httpCode == 200) {
            $_SESSION['pesan_sukses'] = "Admin berhasil dihapus";
        } else {
            $_SESSION['pesan_error'] = "Gagal menghapus admin. Kode Error API: " . $httpCode;
        }
    }

    // Kembali ke halaman kelola admin
    header("Location: kelola_admin.php");
    exit();

} else {
    // Jika tidak ada ID, kembali ke daftar
    header("Location: kelola_admin.php");
    exit();
}