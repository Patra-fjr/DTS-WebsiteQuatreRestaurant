<?php
// "Detektif" Error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['aksi'])) {
    if ($data['aksi'] != 'get_status') {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']); exit;
    }
}

$aksi = $data['aksi'] ?? 'get_status';
$id_menu = $data['id_menu'] ?? null;
$force_delete = $data['force_delete'] ?? false;

// Logika disesuaikan dengan struktur session temanmu (Array [{id:..., qty:...}])
if ($aksi == 'tambah' && $id_menu) {
    $found_index = -1;
    foreach ($_SESSION['cart'] as $index => &$item) {
        if (isset($item['id']) && $item['id'] == $id_menu) {
            $item['quantity'] += $data['qty'] ?? 1;
            $found_index = $index;
            break;
        }
    }
    unset($item);

    if ($found_index === -1) { // Item baru
        if (isset($data['nama']) && isset($data['harga']) && isset($data['gambar'])) {
            $_SESSION['cart'][] = [
                'id'       => $id_menu,
                'name'     => $data['nama'],
                'price'    => (float)$data['harga'],
                'quantity' => (int)($data['qty'] ?? 1),
                'gambar'   => $data['gambar']
            ];
        } else { exit(json_encode(['success' => false, 'message' => 'Data item baru tidak lengkap'])); }
    }
} elseif ($aksi == 'kurang' && $id_menu) {
    $found_index = -1;
    foreach ($_SESSION['cart'] as $index => $item) {
         if (isset($item['id']) && $item['id'] == $id_menu) {
            $found_index = $index;
            break;
        }
    }
    if ($found_index !== -1) {
        if ($force_delete || $_SESSION['cart'][$found_index]['quantity'] <= 1) {
            array_splice($_SESSION['cart'], $found_index, 1);
        } else {
            $_SESSION['cart'][$found_index]['quantity']--;
        }
    }
}

// Hitung total dan siapkan respons
$totalItems = 0;
$totalHarga = 0;
$responseKeranjang = [];
foreach ($_SESSION['cart'] as $index => $cartItemData) {
    $qty = (int)($cartItemData['quantity'] ?? 0);
    $harga = (float)($cartItemData['price'] ?? 0);
    $totalItems += $qty;
    $totalHarga += $qty * $harga;
    $responseKeranjang[] = [ // Kirim sebagai array
        'id'       => $cartItemData['id'] ?? null,
        'name'     => $cartItemData['name'] ?? 'Error',
        'price'    => $harga,
        'quantity' => $qty,
        'gambar'   => $cartItemData['gambar'] ?? 'placeholder.jpg',
        'index'    => $index
    ];
}

echo json_encode([
    'success'     => true,
    'total_items' => $totalItems,
    'total_harga' => $totalHarga,
    'keranjang'   => $responseKeranjang
]);
?>