<?php

// Langkah 1: Mulai session
session_start();

// --- BAGIAN TAMBAHAN (ANTI CACHE / ANTI BACK BUTTON) ---
// Ini memerintahkan browser: "JANGAN SIMPAN halaman ini di memori!"
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
// -------------------------------------------------------

// Langkah 2: Cek apakah user login
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// 1. Query untuk PENDAPATAN HARI INI
// Mengambil jumlah total harga dari transaksi yang statusnya 'Selesai' DAN tanggalnya hari ini.
$query_pendapatan = "SELECT SUM(orders.total_harga) AS total_pendapatan 
                     FROM transaksi 
                     JOIN orders ON transaksi.id_order = orders.id_order 
                     WHERE transaksi.status_transaksi = 'Selesai' AND transaksi.tanggal_transaksi = CURDATE()";
$result_pendapatan = mysqli_query($koneksi, $query_pendapatan);
$data_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$pendapatan_hari_ini = $data_pendapatan['total_pendapatan'] ? $data_pendapatan['total_pendapatan'] : 0;

// 2. Query untuk PESANAN BARU
// Menghitung jumlah pesanan yang statusnya masih 'proses'.
$query_pesanan = "SELECT COUNT(id_order) AS jumlah_pesanan FROM orders WHERE status_order = 'proses'";
$result_pesanan = mysqli_query($koneksi, $query_pesanan);
$data_pesanan = mysqli_fetch_assoc($result_pesanan);
$pesanan_baru = $data_pesanan['jumlah_pesanan'];

// 3. Query untuk MEJA TERISI
// Menghitung jumlah meja yang statusnya 'tidak tersedia'.
$query_meja = "SELECT COUNT(id_meja) AS jumlah_meja FROM meja WHERE status_meja = 'tidak tersedia'";
$result_meja = mysqli_query($koneksi, $query_meja);
$data_meja = mysqli_fetch_assoc($result_meja);
$meja_terisi = $data_meja['jumlah_meja'];
// ==================================
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
            <li class="active"> 
                <a href="#">
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
            <li>
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
    <div class="cards-container">
        <div class="card">
            <div>
                <h3>Rp <?php echo number_format($pendapatan_hari_ini, 0, ',', '.'); ?></h3>
                <span>Pendapatan Hari Ini</span>
            </div>
            <div><i class='bx bxs-wallet'></i></div>
        </div>
        <div class="card">
            <div>
                <h3><?php echo $pesanan_baru; ?></h3>
                <span>Pesanan Baru</span>
            </div>
            <div><i class='bx bxs-bell'></i></div>
        </div>
        <div class="card">
            <div>
                <h3><?php echo $meja_terisi; ?></h3>
                <span>Meja Terisi</span>
            </div>
            <div><i class='bx bxs-group'></i></div>
        </div>
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