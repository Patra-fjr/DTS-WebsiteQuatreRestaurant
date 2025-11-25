<?php
session_start();
require_once '../config/config.php';

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalPrice = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Quatre Restaurant</title>
    <link rel="stylesheet" href="../CSSUser/keranjang.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .item-image { width: 100px; height: 100px; object-fit: cover; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Keranjang Belanja</h1>
            <a href="index.php" class="back-btn">← Kembali ke Menu</a>
        </div>

        <div class="cart-container">
            <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon"><i class='bx bx-cart-alt bx-lg'></i></div>
                    <h2>Keranjang Belanja Kosong</h2>
                    <p>Silakan tambahkan menu favorit Anda.</p>
                    <a href="index.php" class="back-btn">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <div class="cart-items" id="cart-items-dynamic"> 
                    <?php foreach ($cart as $index => $item): ?>
                        <?php
                            $id_menu = $item['id'] ?? null;
                            $nama_menu = $item['name'] ?? 'Nama Error';
                            $harga = isset($item['price']) ? (float)$item['price'] : 0;
                            $gambar_nama = $item['gambar'] ?? 'placeholder.jpg';
                            $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
                            $gambar_path_lengkap = IMAGE_PATH . htmlspecialchars($gambar_nama);
                            $subtotal = $harga * $quantity;
                            $totalPrice += $subtotal;
                            if (!$id_menu || $quantity <= 0) continue;
                        ?>
                        <div class="cart-item" data-index="<?php echo $index; ?>" data-id-menu="<?php echo $id_menu; ?>">
                            <img src="<?php echo $gambar_path_lengkap; ?>" alt="<?php echo htmlspecialchars($nama_menu); ?>" class="item-image">
                            
                            <div class="item-info">
                                <div class="item-name"><?php echo htmlspecialchars($nama_menu); ?></div>
                                <div class="item-price">Rp <?php echo number_format($harga, 0, ',', '.'); ?></div>
                            </div>
                            <div class="item-controls">
                                <div class="qty-control">
                                    <button class="qty-btn qty-minus" data-index="<?php echo $index; ?>" <?php echo $quantity <= 1 ? 'disabled' : ''; ?>>−</button>
                                    <span class="qty-display"><?php echo $quantity; ?></span>
                                    <button class="qty-btn qty-plus" data-index="<?php echo $index; ?>">+</button>
                                </div>
                                <div class="subtotal">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></div>
                                <button class="remove-btn" data-index="<?php echo $index; ?>">🗑️ Hapus</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php
                    $tax = $totalPrice * 0.1; // Pajak 10%
                    $totalWithTax = $totalPrice * 1.1;
                ?>
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="summarySubtotal">Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Pajak (10%):</span>
                        <span id="summaryTax">Rp <?php echo number_format($tax, 0, ',', '.'); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="summaryTotal">Rp <?php echo number_format($totalWithTax, 0, ',', '.'); ?></span>
                    </div>
                    <button class="checkout-btn" onclick="checkout()">Bayar Sekarang</button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Mendefinisikan variabel JavaScript dari konstanta PHP
        const IMAGE_BASE_PATH = '<?php echo IMAGE_PATH; ?>';
    </script>

    <script src="../JSUser/keranjang.js"></script> 
</body>
</html>