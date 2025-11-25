<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

// Ambil semua data kasir (admin) untuk dropdown filter
$kasir_result = mysqli_query($koneksi, "SELECT id_admin, nama FROM admin ORDER BY nama ASC");

// Ambil nilai filter dari URL (jika ada)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$kasir = isset($_GET['kasir']) ? $_GET['kasir'] : '';

// Bangun query SQL dinamis berdasarkan filter
$sql = "SELECT 
            transaksi.id_transaksi, 
            transaksi.id_order, 
            transaksi.tanggal_transaksi, 
            orders.total_harga, 
            admin.nama AS nama_kasir 
        FROM transaksi
        JOIN orders ON transaksi.id_order = orders.id_order
        JOIN admin ON transaksi.id_admin = admin.id_admin
        WHERE transaksi.status_transaksi = 'Selesai'";

// Tambahkan kondisi filter ke query
if (!empty($start_date)) {
    $sql .= " AND transaksi.tanggal_transaksi >= '$start_date'";
}
if (!empty($end_date)) {
    $sql .= " AND transaksi.tanggal_transaksi <= '$end_date'";
}
if (!empty($kasir)) {
    $sql .= " AND transaksi.id_admin = '$kasir'";
}

$sql .= " ORDER BY transaksi.tanggal_transaksi DESC, transaksi.waktu_transaksi DESC";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
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
            <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i><span class="link-name">Dashboard</span></a></li>
            <li><a href="kelola_menu.php"><i class='bx bxs-food-menu'></i><span class="link-name">Kelola Menu</span></a></li>
            <li><a href="kelola_ketersediaan.php"><i class='bx bxs-fridge'></i><span class="link-name">Ketersediaan Menu</span></a></li>
            <li><a href="kelola_pesanan.php"><i class='bx bxs-receipt'></i><span class="link-name">Pesanan</span></a></li>
            <li class="active"><a href="laporan.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="link-name">Laporan</span></a></li>
            <li><a href="kelola_admin.php"><i class='bx bxs-group'></i><span class="link-name">Kelola Admin</span></a></li>
            <li class="logout"><a href="#" id="logout-btn"><i class='bx bxs-log-out'></i><span class="link-name">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Laporan Penjualan</h2>
            <div class="user-wrapper">
                <i class='bx bxs-user-circle'></i>
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION['nama']); ?></h4>
                    <small><?php echo htmlspecialchars($_SESSION['jabatan']); ?></small>
                </div>
            </div>
        </header>

        <main>
            <div class="filter-container">
                <form action="laporan.php" method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="start_date">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_date">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="form-group">
                        <label for="kasir">Kasir</label>
                        <select id="kasir" name="kasir">
                            <option value="">Semua Kasir</option>
                            <?php
                            if (mysqli_num_rows($kasir_result) > 0) {
                                while($row = mysqli_fetch_assoc($kasir_result)) {
                                    $selected = ($kasir == $row['id_admin']) ? 'selected' : '';
                                    echo "<option value='" . $row['id_admin'] . "' $selected>" . htmlspecialchars($row['nama']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-buttons">
                        <button type="submit" class="btn-filter">Filter</button>
                        <a href="laporan.php" class="btn-reset">Reset</a>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>ID Pesanan</th>
                            <th>Tanggal Transaksi</th>
                            <th>Total Pendapatan</th>
                            <th>Kasir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $total_pendapatan_keseluruhan = 0;
                            if (mysqli_num_rows($result) > 0) {
                                while($laporan = mysqli_fetch_assoc($result)) {
                                    $total_pendapatan_keseluruhan += $laporan['total_harga'];
                        ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($laporan['id_transaksi']); ?></td>
                                        <td><?php echo htmlspecialchars($laporan['id_order']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($laporan['tanggal_transaksi'])); ?></td>
                                        <td>Rp <?php echo number_format($laporan['total_harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($laporan['nama_kasir']); ?></td>
                                    </tr>
                        <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center;'>Data tidak ditemukan.</td></tr>";
                            }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total Pendapatan (sesuai filter)</strong></td>
                            <td colspan="2"><strong>Rp <?php echo number_format($total_pendapatan_keseluruhan, 0, ',', '.'); ?></strong></td>
                        </tr>
                    </tfoot>
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