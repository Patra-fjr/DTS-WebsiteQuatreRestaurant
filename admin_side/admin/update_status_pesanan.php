<?php
session_start();
// Pastikan hanya admin yang login yang bisa mengakses file ini
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// Ambil ID pesanan dari URL
if (isset($_GET['id'])) {
    $id_order = $_GET['id'];

    // Siapkan query UPDATE yang aman
    $sql = "UPDATE orders SET status_order = 'selesai' WHERE id_order = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "s", $id_order);

    // Eksekusi query
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil, buat pesan sukses
        $_SESSION['pesan_sukses'] = "Status pesanan berhasil diubah menjadi Selesai.";
    } else {
        // Jika gagal, buat pesan error
        $_SESSION['pesan_error'] = "Gagal mengubah status pesanan.";
    }

    // Kembali ke halaman kelola pesanan
    header("Location: kelola_pesanan.php");
    exit();

} else {
    // Jika tidak ada ID di URL, langsung tendang kembali
    header("Location: kelola_pesanan.php");
    exit();
}
?>