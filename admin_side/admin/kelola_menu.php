<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 1. KATEGORI MAPPING (Statis)
$kategori_nama = [
    'kat001' => 'Makanan',
    'kat002' => 'Minuman'
];

// 2. CONSUME API SPRING BOOT DENGAN JWT
$api_url = 'http://172.17.0.1:8080/api/menus'; 

// Ambil Token dari Session
$token = $_SESSION['jwt_token'] ?? '';

// Siapkan Context Header (Authorization)
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: Bearer " . $token . "\r\n" .
                    "Content-Type: application/json"
    ]
];
$context = stream_context_create($opts);

// Ambil data dengan context
$json_data = @file_get_contents($api_url, false, $context);
$menus = json_decode($json_data, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu</title> 
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
            <li class="active"><a href="kelola_menu.php"><i class='bx bxs-food-menu'></i><span class="link-name">Kelola Menu</span></a></li>
            <li><a href="kelola_ketersediaan.php"><i class='bx bxs-fridge'></i><span class="link-name">Ketersediaan</span></a></li>
            <li><a href="kelola_pesanan.php"><i class='bx bxs-receipt'></i><span class="link-name">Pesanan</span></a></li>
            <li><a href="laporan.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="link-name">Laporan</span></a></li>
            <li><a href="kelola_admin.php"><i class='bx bxs-group'></i><span class="link-name">Kelola Admin</span></a></li>
            <li class="logout"><a href="#" id="logout-btn"><i class='bx bxs-log-out'></i><span class="link-name">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Kelola Menu</h2> 
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
                <h2>Daftar Menu</h2> 
                <a href="tambah_menu.php" class="btn-add">
                    <i class='bx bx-plus'></i> Tambah Menu
                </a>
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
                            <th>ID Menu</th>
                            <th>Gambar</th> 
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Deskripsi</th> 
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($json_data !== false && !empty($menus)) {
                            // Sort by ID
                            usort($menus, function($a, $b) {
                                return strcmp($a['idMenu'], $b['idMenu']);
                            });

                            foreach($menus as $menu) {
                                // --- LOGIKA SOFT DELETE ---
                                if (isset($menu['statusMenu']) && $menu['statusMenu'] == 'dihapus') {
                                    continue;
                                }
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($menu['idMenu']); ?></td>
                                    <td>
                                        <?php if (!empty($menu['gambar'])): ?>
                                            <img src="assets/image/<?php echo htmlspecialchars($menu['gambar']); ?>" alt="Gambar Menu" class="menu-table-img">
                                        <?php else: ?>
                                            <span>(No Image)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($menu['namaMenu']); ?></td>
                                    <td><?php echo htmlspecialchars($kategori_nama[$menu['idKategori']] ?? 'Lainnya'); ?></td>
                                    <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                                    <td>
                                        <div class="menu-desc-short" title="<?php echo htmlspecialchars($menu['deskripsi']); ?>">
                                            <?php echo htmlspecialchars($menu['deskripsi']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $menu['statusMenu'] == 'tersedia' ? 'tersedia' : 'habis'; ?>">
                                            <?php echo htmlspecialchars($menu['statusMenu']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_menu.php?id=<?php echo $menu['idMenu']; ?>" class="btn-edit">Edit</a>
                                        <a href="hapus_menu.php?id=<?php echo $menu['idMenu']; ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?');">Hapus</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            // Jika $json_data false atau kosong, kemungkinan server mati atau token expired
                            echo "<tr><td colspan='8' style='text-align:center;'>Gagal memuat data menu. Token JWT mungkin kadaluarsa atau Server mati.</td></tr>";
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