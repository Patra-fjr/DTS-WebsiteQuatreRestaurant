<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// Ambil ID dan status baru dari URL
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id_menu = $_GET['id'];
    $status_baru = $_GET['status'];

    // Validasi status untuk keamanan
    if ($status_baru == 'tersedia' || $status_baru == 'tidak tersedia') {
        
        $sql = "UPDATE menu SET status_menu = ? WHERE id_menu = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $status_baru, $id_menu);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['pesan_sukses'] = "Status ketersediaan menu berhasil diubah.";
        } else {
            $_SESSION['pesan_error'] = "Gagal mengubah status."; // (Opsional, jika butuh pesan error)
        }
    }
}

// Kembali ke halaman ketersediaan menu
header("Location: kelola_ketersediaan.php");
exit();
?>