<?php
// admin/proses_pembayaran.php

// Debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Cek Login dan ambil ID Admin
if (!isset($_SESSION['login']) || !isset($_SESSION['id_admin'])) {
    // Balas dengan JSON error jika tidak login
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Anda harus login.']);
    exit;
}

require '../koneksi.php'; // Pastikan path ke koneksi.php benar

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Terjadi kesalahan tidak diketahui.'];

// Ambil ID Admin dari session
$id_admin = $_SESSION['id_admin'];

// --- 1. Ambil Data dari Input JSON ---
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] != "POST" || !$input || 
    !isset($input['id_order']) || !isset($input['id_meja']) || 
    !isset($input['metode_pembayaran'])) {
    
    $response['message'] = 'Request tidak valid atau data JSON kurang lengkap.';
    echo json_encode($response);
    exit();
}

$id_order = $input['id_order'];
$id_meja = $input['id_meja'];
$metode_pembayaran = $input['metode_pembayaran']; 

// Ambil total harga dari tabel orders
$query_total = "SELECT total_harga FROM orders WHERE id_order = ? AND status_order = 'proses'";
$stmt_total = mysqli_prepare($koneksi, $query_total);
mysqli_stmt_bind_param($stmt_total, "s", $id_order);
mysqli_stmt_execute($stmt_total);
$result_total = mysqli_stmt_get_result($stmt_total);
$order_data = mysqli_fetch_assoc($result_total);
mysqli_stmt_close($stmt_total);

if (!$order_data) {
    $response['message'] = 'ID Order tidak ditemukan atau statusnya sudah selesai/invalid.';
    echo json_encode($response);
    exit();
}

// --- 2. Siapkan Data Transaksi ---
$id_transaksi = "tr" . rand(100, 999) . time(); // Gunakan time() untuk menjamin keunikan
$tanggal_sekarang = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$status_pembayaran = 'Selesai';
$total_harga_order = $order_data['total_harga']; // Ambil harga dari DB

// Aktifkan Error Report untuk Transaction
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($koneksi); // MULAI TRANSAKSI

try {
    
    // 1. INSERT TRANSAKSI
    $sql_transaksi = "INSERT INTO transaksi (id_transaksi, id_order, id_admin, tanggal_transaksi, waktu_transaksi, metode_transaksi, status_transaksi) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_transaksi = mysqli_prepare($koneksi, $sql_transaksi);
    mysqli_stmt_bind_param($stmt_transaksi, "sssssss", $id_transaksi, $id_order, $id_admin, $tanggal_sekarang, $waktu_sekarang, $metode_pembayaran, $status_pembayaran);
    
    if (!mysqli_stmt_execute($stmt_transaksi)) {
        throw new Exception("Gagal menyimpan transaksi: " . mysqli_stmt_error($stmt_transaksi));
    }
    mysqli_stmt_close($stmt_transaksi);

    // 2. UPDATE STATUS PESANAN MENJADI 'selesai'
    $sql_order_update = "UPDATE orders SET status_order = 'selesai' WHERE id_order = ?";
    $stmt_order_update = mysqli_prepare($koneksi, $sql_order_update);
    mysqli_stmt_bind_param($stmt_order_update, "s", $id_order);
    
    if (!mysqli_stmt_execute($stmt_order_update)) {
        throw new Exception("Gagal update status order: " . mysqli_stmt_error($stmt_order_update));
    }
    mysqli_stmt_close($stmt_order_update);


    // 3. UPDATE STATUS MEJA MENJADI 'tersedia'
    $sql_meja_update = "UPDATE meja SET status_meja = 'tersedia' WHERE id_meja = ?";
    $stmt_meja_update = mysqli_prepare($koneksi, $sql_meja_update);
    mysqli_stmt_bind_param($stmt_meja_update, "s", $id_meja);
    
    if (!mysqli_stmt_execute($stmt_meja_update)) {
        throw new Exception("Gagal update status meja: " . mysqli_stmt_error($stmt_meja_update));
    }
    mysqli_stmt_close($stmt_meja_update);

    // Jika semua query berhasil, COMMIT
    mysqli_commit($koneksi); 
    
    $response['success'] = true;
    $response['message'] = "Pembayaran sukses! Order ID $id_order selesai dan Meja $id_meja tersedia.";

} catch (Exception $e) {
    // Jika ada error, ROLLBACK
    mysqli_rollback($koneksi);
    $response['message'] = "Proses pembayaran gagal. " . $e->getMessage();
    error_log("PAYMENT FAILED (Admin): " . $e->getMessage());
}

// --- 3. Kirim Respons JSON ---
echo json_encode($response);
exit();
?>