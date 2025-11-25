<?php
session_start();
// Pastikan hanya admin yang login yang bisa mengakses file ini
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// Ambil ID admin dari URL dan pastikan ID itu ada
if (isset($_GET['id'])) {
    $id_admin_to_delete = $_GET['id'];
    $id_admin_logged_in = $_SESSION['id_admin'];

    // PENTING: Tambahkan pengecekan agar pengguna tidak bisa menghapus akunnya sendiri
    if ($id_admin_to_delete == $id_admin_logged_in) {
        // Jika mencoba menghapus diri sendiri, kirim pesan error
        $_SESSION['pesan_error'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    } else {
        // Siapkan query DELETE yang aman
        $sql = "DELETE FROM admin WHERE id_admin = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $id_admin_to_delete);

        // Eksekusi query
        if (mysqli_stmt_execute($stmt)) {
            // Jika berhasil, buat pesan sukses
            $_SESSION['pesan_sukses'] = "Admin berhasil dihapus.";
        } else {
            // Jika gagal, buat pesan error
            $_SESSION['pesan_error'] = "Gagal menghapus admin.";
        }
    }

    // Kembali ke halaman kelola admin
    header("Location: kelola_admin.php");
    exit();

} else {
    // Jika tidak ada ID di URL, langsung tendang kembali
    header("Location: kelola_admin.php");
    exit();
}
?>