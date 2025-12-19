<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// Alamat API Spring Boot
$api_url = 'http://172.17.0.1:8080/api/menus';
$json_data = @file_get_contents($api_url);
$menus = json_decode($json_data, true);

// Mapping Kategori manual agar tampilan lebih manusiawi
$kategori_nama = [
    'kat001' => 'Makanan',
    'kat002' => 'Minuman'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu</title> <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
            <li class="active">
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
            <h2>Kelola Menu</h2> <div class="user-wrapper">
                <i class='bx bxs-user-circle'></i>
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION['nama']); ?></h4>
                    <small><?php echo htmlspecialchars($_SESSION['jabatan']); ?></small>
                </div>
            </div>
        </header>

        <main>
            <div class="header">
                <h2>Daftar Menu</h2> <a href="tambah_menu.php" class="btn-add">
                    <i class='bx bx-plus'></i> Tambah Menu
                </a>
            </div>

            <?php
            if (isset($_SESSION['pesan_sukses'])) {
                echo "<p class='message sukses'>" . $_SESSION['pesan_sukses'] . "</p>";
                unset($_SESSION['pesan_sukses']); // Hapus pesan setelah ditampilkan
            }
            if (isset($_SESSION['pesan_error'])) {
                echo "<p class='message error'>" . $_SESSION['pesan_error'] . "</p>";
                unset($_SESSION['pesan_error']); // Hapus pesan setelah ditampilkan
            }
            ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Menu</th>
                            <th>Gambar</th> <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Deskripsi</th> <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query untuk mengambil data menu beserta nama kategorinya
                        // Query Anda sudah benar karena menggunakan menu.*
                        $sql = "SELECT menu.*, kategori.nama_kategori
                                FROM menu
                                JOIN kategori ON menu.id_kategori = kategori.id_kategori
                                ORDER BY menu.id_menu ASC";
                        $result = mysqli_query($koneksi, $sql);

                        if (!$result) {
                            die("Query Error: " . mysqli_error($koneksi));
                        }

                        if (mysqli_num_rows($result) > 0) {
                            while($menu = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($menu['id_menu']); ?></td>
                                    <td>
                                        <?php if (!empty($menu['gambar'])): ?>
                                            <img src="../assets/image/<?php echo htmlspecialchars($menu['gambar']); ?>" alt="Gambar Menu" class="menu-table-img">
                                        <?php else: ?>
                                            <span>(No Image)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                                    <td><?php echo htmlspecialchars($menu['nama_kategori']); ?></td>
                                    <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="menu-desc-short" title="<?php echo htmlspecialchars($menu['deskripsi']); ?>">
                                            <?php echo htmlspecialchars($menu['deskripsi']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $menu['status_menu'] == 'tersedia' ? 'tersedia' : 'habis'; ?>">
                                            <?php echo htmlspecialchars($menu['status_menu']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_menu.php?id=<?php echo $menu['id_menu']; ?>" class="btn-edit">Edit</a>
                                        <a href="hapus_menu.php?id=<?php echo $menu['id_menu']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">Hapus</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            // Jika tidak ada data, tampilkan pesan
                            echo "<tr><td colspan='8' style='text-align:center;'>Belum ada data menu.</td></tr>"; // DIUBAH: colspan jadi 8
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