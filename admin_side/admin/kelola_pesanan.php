<?php
// Langkah 1: Mulai session dan pasang "satpam"
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <link rel="stylesheet" href="../assets/style-dashboard.css">
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <i class='bx bxs-store-alt'></i>
            <span>Admin Resto</span>
        </div>
        <ul class="nav-links">
            <li> 
                <a href="dashboard.php">
                    <i class='bx bxs-dashboard'></i>
                    <span class="link-name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="kelola_menu.php">
                    <i class='bx bxs-food-menu'></i>
                    <span class="link-name">Kelola Menu</span>
                </a>
            </li>
            <li>
                <a href="kelola_ketersediaan.php">
                    <i class='bx bxs-fridge'></i>
                    <span class="link-name">Ketersediaan Menu</span>
                </a>
            </li>
            <li class="active">
                <a href="kelola_pesanan.php">
                    <i class='bx bxs-receipt'></i>
                    <span class="link-name">Pesanan</span>
                </a>
            </li>
            <li>
                <a href="laporan.php">
                    <i class='bx bxs-bar-chart-alt-2'></i>
                    <span class="link-name">Laporan</span>
                </a>
            </li>
            <li>
                <a href="kelola_admin.php">
                    <i class='bx bxs-group'></i>
                    <span class="link-name">Kelola Admin</span>
                </a>
            </li>
            <li class="logout">
                <a href="#" id="logout-btn">
                    <i class='bx bxs-log-out'></i>
                    <span class="link-name">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Dashboard</h2>
            <div class="user-wrapper">
    <i class='bx bxs-user-circle'></i>
    <div>
        <h4><?php echo htmlspecialchars($_SESSION['nama']); ?></h4>
        <small><?php echo htmlspecialchars($_SESSION['jabatan']); ?></small>
    </div>
</div>
        </header>

        <main>
    <div class="header">
        <h2>Kelola Pesanan</h2>
        </div>
    
        <?php
    if (isset($_SESSION['pesan_sukses'])) {
        echo "<p class='message sukses'>" . $_SESSION['pesan_sukses'] . "</p>";
        unset($_SESSION['pesan_sukses']);
    }
    if (isset($_SESSION['pesan_error'])) {
        echo "<p class='message error'>" . $_SESSION['pesan_error'] . "</p>";
        unset($_SESSION['pesan_error']);
    }
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Customer</th>
                    <th>No. Meja</th>
                    <th>Waktu Pesan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Query untuk mengambil data pesanan, diurutkan dari yang terbaru
                    // Kita JOIN dengan tabel 'meja' untuk mendapatkan nomor mejanya
                    $sql = "SELECT orders.*, meja.nomor_meja 
                            FROM orders 
                            JOIN meja ON orders.id_meja = meja.id_meja
                            ORDER BY orders.tanggal_order DESC, orders.waktu_order DESC";
                    $result = mysqli_query($koneksi, $sql);

                    if (!$result) {
                        die("Query Error: " . mysqli_error($koneksi));
                    }

                    if (mysqli_num_rows($result) > 0) {
                        while($order = mysqli_fetch_assoc($result)) {
                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['id_order']); ?></td>
                                <td><?php echo htmlspecialchars($order['nama_customer']); ?></td>
                                <td><?php echo htmlspecialchars($order['nomor_meja']); ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['tanggal_order'] . ' ' . $order['waktu_order'])); ?></td>
                                <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                        $status_class = '';
                                        if ($order['status_order'] == 'proses') {
                                            $status_class = 'proses';
                                        } elseif ($order['status_order'] == 'selesai') {
                                            $status_class = 'selesai';
                                        } else {
                                            $status_class = 'habis'; // atau 'dibatalkan'
                                        }
                                    ?>
                                    <span class="status <?php echo $status_class; ?>">
                                        <?php echo htmlspecialchars($order['status_order']); ?>
                                    </span>
                                </td>
                                <td>
    <a href="detail_pesanan.php?id=<?php echo $order['id_order']; ?>" class="btn-edit">Detail</a>
    
    <?php if ($order['status_order'] == 'proses'): ?>
        <a href="#" class="btn-bayar open-payment-popup" 
   data-orderid="<?php echo $order['id_order']; ?>" 
   data-mejaid="<?php echo $order['id_meja']; ?>">Bayar</a>
    <?php endif; ?>
</td>
                            </tr>
                <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>Belum ada pesanan.</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </div>
</main>

    <div class="popup-overlay" id="payment-popup">
    <div class="popup-box">
        <h2>Proses Pembayaran</h2>
        <form id="payment-form"> 
            <input type="hidden" name="id_order" id="id-order-input">
            <input type="hidden" name="id_meja" id="temp-meja-id-input">
            <input type="hidden" name="id_admin" id="id-admin-input" value="<?php echo htmlspecialchars($_SESSION['id_admin'] ?? ''); ?>"> 

            <div class="form-group">
                <label for="metode_pembayaran">Pilih Metode Pembayaran</label>
                <select name="metode_pembayaran" id="metode_pembayaran" required>
                    <option value="Tunai">Tunai</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>
            <div class="popup-buttons">
                <button type="button" class="btn-cancel" id="cancel-payment-btn">Batal</button>
                <button type="button" id="submit-payment-btn" class="btn-confirm">Konfirmasi Bayar</button> 
            </div>
        </form>
    </div>
</div>

    </div>
    <div class="popup-overlay" id="logout-popup">
    <div class="popup-box">
        <h2>Konfirmasi Logout</h2>
        <p>Apakah Anda yakin ingin keluar?</p>
        <div class="popup-buttons">
            <button class="btn-cancel" id="cancel-logout-btn">Batal</button>
            <a href="../logout.php" class="btn-confirm">Yakin</a>
        </div>
    </div>
</div>

<script src="../assets/script-dashboard.js"></script>

</body>
</html>