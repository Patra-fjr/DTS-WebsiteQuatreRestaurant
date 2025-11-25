<?php
session_start();
require_once '../config/config.php';

// Validasi keranjang & nomor meja
if (empty($_SESSION['cart']) || !isset($_SESSION['nomor_meja_int'])) {
    $redirect_url = isset($_SESSION['nomor_meja_int']) ? 'index.php?table=' . $_SESSION['nomor_meja_int'] : 'index.php';
    header('Location: ' . $redirect_url);
    exit();
}

$cart = $_SESSION['cart'];
$nomor_meja = $_SESSION['nomor_meja_int']; // Ambil nomor meja (angka)
$totalPrice = 0;

// Hitung total dari session
foreach ($cart as $item) {
    if (isset($item['price']) && isset($item['quantity'])) {
        $totalPrice += (float)$item['price'] * (int)$item['quantity'];
    }
}
$totalWithTax = $totalPrice * 1.1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Quatre Restaurant</title>
    <link rel="stylesheet" href="../CSSUser/pembayaran.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="container">
        <div class="header">
            <a href="keranjang.php" class="back-arrow"><i class='bx bx-chevron-left'></i></a>
            <h1>Pemesanan</h1>
            <div style="width: 24px;"></div>
        </div>

        <div class="order-type" onclick="toggleOrderType()">
             <span class="order-type-label">Tipe Pemesanan</span>
             <span class="order-type-value">
                 <strong id="orderTypeText">Makan di tempat</strong>
                 <span>✓</span>
             </span>
        </div>

        <div class="form-section" id="paymentForm">
            <h2 class="section-title">Informasi Pemesan</h2>
            
            <div class="form-group">
                <label class="form-label">Nama Lengkap<span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type="text" id="fullName" placeholder="Nama Lengkap" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Ponsel (Opsional)</label>
                <div class="input-wrapper">
                    <input type="tel" id="phone" placeholder="Nomor Ponsel">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Meja<span class="required">*</span></label>
                <div class="input-wrapper">
                    <input type-="number" id="tableNumber" value="<?php echo htmlspecialchars($nomor_meja); ?>" readonly style="background-color: #eee;">
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="total-section">
                <div class="total-label"> Total Pembayaran </div>
                <div class="total-amount" id="totalAmount">Rp <?php echo number_format($totalWithTax, 0, ',', '.'); ?></div>
            </div>
            <button type="button" id="payButton" class="pay-button">Bayar</button>
        </div>
    </div>

    <script>
        // Kirim total ke JS (jika JS temanmu membutuhkannya)
        const totalPayment = <?php echo $totalWithTax; ?>;
    </script>
    <script src="../JSUser/pembayaran.js"></script>
</body>
</html>