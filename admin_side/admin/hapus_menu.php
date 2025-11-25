<?php
session_start();
// Pastikan hanya admin yang login yang bisa mengakses file ini
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// Ambil ID menu dari URL
if (isset($_GET['id'])) {
    $id_menu = $_GET['id'];

    // Siapkan query DELETE yang aman
    $sql = "DELETE FROM menu WHERE id_menu = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $id_menu);

    // Eksekusi query
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, buat pesan sukses
        $_SESSION['pesan_sukses'] = "Menu berhasil dihapus.";
    } else {
        // Jika gagal, buat pesan error
        $_SESSION['pesan_error'] = "Gagal menghapus menu.";
    }

    // Kembali ke halaman kelola menu
    header("Location: kelola_menu.php");
    exit();

} else {
    // Jika tidak ada ID di URL, langsung tendang kembali
    header("Location: kelola_menu.php");
    exit();
}
?>