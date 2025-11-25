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
            <li class="active">
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
    <div class="header">
        <h2>Kelola Ketersediaan Menu</h2>
    </div>

    <?php
    if (isset($_SESSION['pesan_sukses'])) {
        echo "<p class='message sukses'>" . $_SESSION['pesan_sukses'] . "</p>";
        unset($_SESSION['pesan_sukses']);
    }
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>Status Saat Ini</th>
                    <th>Ubah Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Query untuk mengambil semua menu
                    $sql = "SELECT id_menu, nama_menu, status_menu FROM menu ORDER BY nama_menu ASC";
                    $result = mysqli_query($koneksi, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while($menu = mysqli_fetch_assoc($result)) {
                            // Tentukan status dan teks tombol berikutnya
                            $status_sekarang = $menu['status_menu'];
                            $status_berikutnya = ($status_sekarang == 'tersedia') ? 'tidak tersedia' : 'tersedia';
                            $teks_tombol = ($status_sekarang == 'tersedia') ? 'Jadikan tidak tersedia' : 'Jadikan Tersedia';
                            $kelas_tombol = ($status_sekarang == 'tersedia') ? 'btn-delete' : 'btn-selesai';
                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                                <td>
                                    <span class="status <?php echo $status_sekarang == 'tersedia' ? 'tersedia' : 'habis'; ?>">
                                        <?php echo htmlspecialchars($status_sekarang); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="update_ketersediaan.php?id=<?php echo $menu['id_menu']; ?>&status=<?php echo $status_berikutnya; ?>" class="<?php echo $kelas_tombol; ?>">
                                        <?php echo $teks_tombol; ?>
                                    </a>
                                </td>
                            </tr>
                <?php
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center;'>Belum ada data menu.</td></tr>";
                    }
                ?>
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