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
    <?php
        // 1. Ambil ID dari URL dan pastikan ada
        if (!isset($_GET['id'])) {
            // Jika tidak ada ID, tendang kembali
            header("Location: kelola_pesanan.php");
            exit;
        }
        $id_order = $_GET['id'];

        // 2. Query untuk info pesanan utama (JOIN dengan meja)
        $sql_order = "SELECT orders.*, meja.nomor_meja 
                      FROM orders 
                      JOIN meja ON orders.id_meja = meja.id_meja
                      WHERE orders.id_order = ?";
        $stmt_order = mysqli_prepare($koneksi, $sql_order);
        mysqli_stmt_bind_param($stmt_order, "s", $id_order);
        mysqli_stmt_execute($stmt_order);
        $result_order = mysqli_stmt_get_result($stmt_order);
        $order = mysqli_fetch_assoc($result_order);

        // Jika data pesanan tidak ditemukan, tendang kembali
        if (!$order) {
            header("Location: kelola_pesanan.php");
            exit;
        }

        // 3. Query untuk daftar item pesanan (JOIN dengan menu)
        $sql_details = "SELECT detail_orders.*, menu.nama_menu, menu.harga 
                        FROM detail_orders 
                        JOIN menu ON detail_orders.id_menu = menu.id_menu
                        WHERE detail_orders.id_order = ?";
        $stmt_details = mysqli_prepare($koneksi, $sql_details);
        mysqli_stmt_bind_param($stmt_details, "s", $id_order);
        mysqli_stmt_execute($stmt_details);
        $result_details = mysqli_stmt_get_result($stmt_details);
    ?>

    <div class="header">
        <h2>Detail Pesanan: <?php echo htmlspecialchars($order['id_order']); ?></h2>
        <a href="kelola_pesanan.php" class="btn-cancel" style="padding: 10px 15px;">Kembali</a>
    </div>

    <div class="summary-container">
        <div class="summary-item">
            <strong>Nama Customer:</strong>
            <span><?php echo htmlspecialchars($order['nama_customer']); ?></span>
        </div>
        <div class="summary-item">
            <strong>Nomor Meja:</strong>
            <span><?php echo htmlspecialchars($order['nomor_meja']); ?></span>
        </div>
        <div class="summary-item">
            <strong>Waktu Pesan:</strong>
            <span><?php echo date('d M Y, H:i', strtotime($order['tanggal_order'] . ' ' . $order['waktu_order'])); ?></span>
        </div>
         <div class="summary-item">
            <strong>Status Pesanan:</strong>
            <span><?php echo htmlspecialchars($order['status_order']); ?></span>
        </div>
    </div>

    <div class="table-container">
        <h3>Item yang Dipesan</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Harga Satuan</th>
                    <th>Jumlah (Qty)</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result_details) > 0) {
                    while($detail = mysqli_fetch_assoc($result_details)) {
                ?>
                        <tr>
                            <td><?php echo htmlspecialchars($detail['nama_menu']); ?></td>
                            <td>Rp <?php echo number_format($detail['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($detail['quantity']); ?></td>
                            <td>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
                <tr class="total-row">
                    <td colspan="3"><strong>Total Harga</strong></td>
                    <td><strong>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>


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