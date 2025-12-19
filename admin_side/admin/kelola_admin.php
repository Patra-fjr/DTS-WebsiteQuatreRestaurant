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
            <li class="active">
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
        <h2>Kelola Admin</h2>
        <a href="tambah_admin.php" class="btn-add">
            <i class='bx bx-plus'></i> Tambah Admin
        </a>
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
                    <th>ID Admin</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Jabatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    // --- KODE BARU: CONSUME API SPRING BOOT ---
    
                    // Gunakan IP gateway yang berhasil kamu gunakan di index user tadi
                $api_url = 'http://172.17.0.1:8080/api/admins'; 
    
                // Ambil data JSON
                $json_data = @file_get_contents($api_url);

                if ($json_data !== false) {
                    $admins = json_decode($json_data, true);

                        if (!empty($admins)) {
                        // Kita urutkan manual berdasarkan nama (karena di SQL ada ORDER BY nama ASC)
                        usort($admins, function($a, $b) {
                            return strcmp($a['nama'], $b['nama']);
                         });

                        foreach ($admins as $admin) {
                ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['idAdmin']); ?></td>
                                <td><?php echo htmlspecialchars($admin['nama']); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['jabatan']); ?></td>
                                <td>
                                    <a href="edit_admin.php?id=<?php echo $admin['idAdmin']; ?>" class="btn-edit">Edit</a>
                                    
                                    <?php if ($admin['idAdmin'] != $_SESSION['id_admin']): ?>
                                        <a href="hapus_admin.php?id=<?php echo $admin['idAdmin']; ?>" 
                                        class="btn-delete" 
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus admin ini?');">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data admin di API.</td></tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center; color:red;'>Gagal terhubung ke API Spring Boot. Pastikan server Backend menyala!</td></tr>";
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