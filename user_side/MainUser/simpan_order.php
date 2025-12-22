<?php
// "Detektif" Error PHP (Bisa dimatikan saat production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
// Pastikan path ke config database benar
require_once '../config/config.php'; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Kesalahan tidak diketahui.'];

// ==========================================
// 1. VALIDASI REQUEST & SESSION
// ==========================================
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] != 'POST' || empty($_SESSION['cart']) || !$input || !isset($_SESSION['id_meja_varchar'])) {
    $response['message'] = 'Request tidak valid, keranjang kosong, atau nomor meja tidak diset.';
    echo json_encode($response);
    exit();
}

// ==========================================
// 2. SIAPKAN DATA
// ==========================================
$nama_customer = $input['name'] ?? '';
$nomor_telepon = $input['phone'] ?? null;
$id_meja = $_SESSION['id_meja_varchar']; // ID Meja dari Session
$keranjang = $_SESSION['cart'];

// Validasi tambahan
if (empty($nama_customer)) {
    $response['message'] = 'Nama customer wajib diisi.';
    echo json_encode($response);
    exit();
}

// ==========================================
// 3. HITUNG TOTAL HARGA
// ==========================================
$total_harga = 0;
foreach ($keranjang as $item) {
    $total_harga += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 0);
}
// Tambahkan pajak 10%
$total_harga_final = $total_harga * 1.1; 


// ==========================================
// 4. GENERATE ID ORDER (DENGAN PREFIX 'ORD')
// ==========================================
// Format: ORD + 6 digit angka acak (Contoh: ORD123456)
$id_order_baru = "ORD" . rand(100000, 999999);

$tanggal_sekarang = date("Y-m-d");
$waktu_sekarang = date("H:i:s");
$status_order = 'proses'; // Default status

// ==========================================
// 5. PROSES TRANSAKSI DATABASE
// ==========================================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
mysqli_begin_transaction($conn);

try {
    // 5a. INSERT ke tabel `orders`
    // Pastikan urutan bind_param sesuai dengan kolom:
    // id_order(s), id_meja(s), nama_customer(s), nomor_telepon(s), tanggal_order(s), waktu_order(s), total_harga(d), status_order(s)
    $sql_order = "INSERT INTO `orders` (id_order, id_meja, nama_customer, nomor_telepon, tanggal_order, waktu_order, total_harga, status_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = mysqli_prepare($conn, $sql_order);
    
    mysqli_stmt_bind_param($stmt_order, 'ssssssds',
        $id_order_baru, 
        $id_meja, 
        $nama_customer, 
        $nomor_telepon, 
        $tanggal_sekarang, 
        $waktu_sekarang, 
        $total_harga_final, 
        $status_order
    );
    
    if (!mysqli_stmt_execute($stmt_order)) { 
        throw new Exception("Gagal membuat order utama."); 
    }
    mysqli_stmt_close($stmt_order);

    // 5b. INSERT ke tabel `detail_orders`
    $sql_detail = "INSERT INTO detail_orders (id_order, id_menu, quantity, subtotal) VALUES (?, ?, ?, ?)";
    $stmt_detail = mysqli_prepare($conn, $sql_detail); 

    foreach ($keranjang as $item) {
        $id_menu = $item['id'] ?? null;
        $quantity = (int)($item['quantity'] ?? 0);
        $harga_saat_pesan = (float)($item['price'] ?? 0);
        $subtotal = $quantity * $harga_saat_pesan;

        if (empty($id_menu) || $quantity <= 0) { 
            continue; // Skip item yang tidak valid
        }

        // Bind params: id_order(s), id_menu(s), quantity(i), subtotal(d)
        mysqli_stmt_bind_param($stmt_detail, 'ssid',
            $id_order_baru, 
            $id_menu, 
            $quantity, 
            $subtotal
        );
        
        if (!mysqli_stmt_execute($stmt_detail)) { 
            throw new Exception("Gagal menyimpan detail menu: " . $id_menu); 
        }
    }
    mysqli_stmt_close($stmt_detail);

    // 5c. Update status meja menjadi 'tidak tersedia'
    $sql_update_meja = "UPDATE meja SET status_meja = 'tidak tersedia' WHERE id_meja = ?";
    $stmt_meja = mysqli_prepare($conn, $sql_update_meja);
    mysqli_stmt_bind_param($stmt_meja, 's', $id_meja);
    
    if (!mysqli_stmt_execute($stmt_meja)) { 
        throw new Exception("Gagal update status meja."); 
    }
    mysqli_stmt_close($stmt_meja);

    // 5d. COMMIT TRANSAKSI (Simpan Permanen)
    mysqli_commit($conn);

    // Bersihkan keranjang belanja setelah sukses
    unset($_SESSION['cart']); 
    
    $response['success'] = true;
    $response['message'] = 'Pesanan berhasil dibuat! ID Order: ' . $id_order_baru;

} catch (Exception $e) {
    // 5e. ROLLBACK (Batalkan semua jika ada error)
    mysqli_rollback($conn);
    $response['message'] = "Pesanan gagal: " . $e->getMessage();
}

// 6. Kirim Respons JSON
echo json_encode($response);
exit();
?>