<?php
// "Detektif" Error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../config/config.php'; // Koneksi temanmu ($conn)

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Kesalahan tidak diketahui.'];

// 1. Validasi
$input = json_decode(file_get_contents('php://input'), true);
if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_SESSION['cart']) || !$input || !isset($_SESSION['id_meja_varchar'])) { // Cek id_meja_varchar
    $response['message'] = 'Request tidak valid, keranjang kosong, atau nomor meja tidak diset.';
    echo json_encode($response);
    exit();
}

// 2. Siapkan Data
$nama_customer = $input['name'] ?? '';
$nomor_telepon = $input['phone'] ?? null; // Ambil nomor telepon
$id_meja = $_SESSION['id_meja_varchar']; // Ambil ID Meja VARCHAR dari session
$keranjang = $_SESSION['cart'];

if (empty($nama_customer)) { /* ... (validasi nama) ... */ exit(json_encode($response)); }
if (empty($id_meja)) { /* ... (validasi meja) ... */ exit(json_encode($response)); }

// 3. Hitung Total Harga
$total_harga = 0;
foreach ($keranjang as $item) {
    $total_harga += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
}
// Tambahkan pajak 10% ke total_harga
$total_harga_final = $total_harga * 1.1; 


// 4. Buat ID & Tanggal (Sesuai tabel orders-mu)
$id_order_baru = 'ORD' . uniqid('', true); // Buat ID Order VARCHAR
$tanggal_sekarang = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$status_order = 'proses';

// 5. Proses Transaksi Database
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($conn);

try {
    // 5a. INSERT ke `orders` (termasuk nomor_telepon)
    $sql_order = "INSERT INTO `orders` (id_order, id_meja, nama_customer, nomor_telepon, tanggal_order, waktu_order, total_harga, status_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = mysqli_prepare($conn, $sql_order);
    mysqli_stmt_bind_param($stmt_order, 'ssssssds',
        $id_order_baru, $id_meja, $nama_customer, $nomor_telepon, $tanggal_sekarang, $waktu_sekarang, $total_harga_final, $status_order
    );
    if (!mysqli_stmt_execute($stmt_order)) { throw new Exception("Gagal order utama: " . mysqli_stmt_error($stmt_order)); }
    mysqli_stmt_close($stmt_order);

    // 5b. INSERT ke `detail_orders`
    $sql_detail = "INSERT INTO detail_orders (id_order, id_menu, quantity, subtotal) VALUES (?, ?, ?, ?)"; // id_detailorder adalah AUTO_INCREMENT
    $stmt_detail = mysqli_prepare($conn, $sql_detail); 

    foreach ($keranjang as $item) {
        $id_menu = $item['id'] ?? null;
        $quantity = (int)($item['quantity'] ?? 0);
        $harga_saat_pesan = (float)($item['price'] ?? 0);
        $subtotal = $quantity * $harga_saat_pesan;

        if (empty($id_menu) || $quantity <= 0) { throw new Exception("Item keranjang tidak valid."); }

        mysqli_stmt_bind_param($stmt_detail, 'ssid',
            $id_order_baru, $id_menu, $quantity, $subtotal
        );
        if (!mysqli_stmt_execute($stmt_detail)) { throw new Exception("Gagal detail pesanan: " . mysqli_stmt_error($stmt_detail)); }
    }
    mysqli_stmt_close($stmt_detail);

    // 5c. Update status meja
    $sql_update_meja = "UPDATE meja SET status_meja = 'tidak tersedia' WHERE id_meja = ?";
    $stmt_meja = mysqli_prepare($conn, $sql_update_meja);
    mysqli_stmt_bind_param($stmt_meja, 's', $id_meja);
    if (!mysqli_stmt_execute($stmt_meja)) { throw new Exception("Gagal mengunci meja: " . mysqli_stmt_error($stmt_meja)); }
    mysqli_stmt_close($stmt_meja);

    // 5d. COMMIT
    mysqli_commit($conn);
    unset($_SESSION['cart']); 
    // unset($_SESSION['id_meja_varchar']); // Hapus juga session meja
    // unset($_SESSION['nomor_meja_int']); // Hapus juga session meja
    $response['success'] = true;
    $response['message'] = 'Pesanan berhasil disimpan!';

} catch (Exception $e) {
    // 5e. ROLLBACK
    mysqli_rollback($conn);
    $response['message'] = "Pesanan gagal: " . $e->getMessage();
}

// 6. Kirim Respons JSON
echo json_encode($response);
exit();
?>