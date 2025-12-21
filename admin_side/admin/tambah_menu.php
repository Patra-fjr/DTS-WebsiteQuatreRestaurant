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

$error = '';
$sukses = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_menu = $_POST['id_menu'];
    $nama_menu = $_POST['nama_menu'];
    $id_kategori = $_POST['id_kategori'];
    $harga = $_POST['harga'];
    $status_menu = $_POST['status_menu'];
    $deskripsi = $_POST['deskripsi'];
    $nama_gambar = NULL;

    // --- 2. LOGIKA UPLOAD GAMBAR LOKAL ---
    if (isset($_FILES['gambar']) && !empty($_FILES['gambar']['name'])) {
        
        if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
            $error = "Upload Gagal! Kode Error PHP: " . $_FILES['gambar']['error'];
        } else {
            // Gunakan __DIR__ untuk path absolut yang aman
            // Gunakan /../ untuk mundur satu folder ke 'admin_side/assets/image'
            $target_dir = __DIR__ . "/../assets/image/";
            
            // Cek dan buat folder jika belum ada
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $error = "Gagal membuat folder assets/image. Cek permission!";
                }
            }

            if (empty($error)) {
                $nama_gambar = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
                $target_file = $target_dir . $nama_gambar;
                
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
                    $error = "Maaf, hanya file JPG, JPEG, & PNG yang diizinkan.";
                } else {
                    if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                        // Upload Berhasil!
                    } else {
                        $error = "Gagal memindahkan file gambar. Cek permission folder assets/image!";
                    }
                }
            }
        }
    }

    if (empty($error)) {
        // --- 3. KIRIM DATA KE API SPRING BOOT DENGAN JWT ---
        
        $data_api = [
            "idMenu" => $id_menu,
            "namaMenu" => $nama_menu,
            "idKategori" => $id_kategori,
            "harga" => (int)$harga,
            "statusMenu" => $status_menu,
            "deskripsi" => $deskripsi,
            "gambar" => $nama_gambar
        ];

        // Ambil Token dari Session
        $token = $_SESSION['jwt_token'] ?? '';

        $url_add = "http://172.17.0.1:8080/api/menus/add";
        
        $ch = curl_init($url_add);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_api));
        
        // --- TAMBAHKAN HEADER AUTHORIZATION ---
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200 || $httpCode == 201) {
            $sukses = "Menu baru berhasil ditambahkan via API!";
            $_POST = array(); // Reset form
        } elseif ($httpCode == 401 || $httpCode == 403) {
            $error = "Gagal Tambah Menu: Token tidak valid atau sesi habis.";
            // Hapus gambar jika gagal agar tidak nyampah
            if ($nama_gambar && file_exists($target_dir . $nama_gambar)) unlink($target_dir . $nama_gambar);
        } else {
            $error = "Gagal menambahkan menu ke API. (Error Code: $httpCode)";
             // Hapus gambar jika gagal agar tidak nyampah
             if ($nama_gambar && file_exists($target_dir . $nama_gambar)) unlink($target_dir . $nama_gambar);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu Baru</title>
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
            <li><a href="kelola_ketersediaan.php"><i class='bx bxs-fridge'></i><span class="link-name">Ketersediaan Menu</span></a></li>
            <li><a href="kelola_pesanan.php"><i class='bx bxs-receipt'></i><span class="link-name">Pesanan</span></a></li>
            <li><a href="laporan.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="link-name">Laporan</span></a></li>
            <li><a href="kelola_admin.php"><i class='bx bxs-group'></i><span class="link-name">Kelola Admin</span></a></li>
            <li class="logout"><a href="#" id="logout-btn"><i class='bx bxs-log-out'></i><span class="link-name">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Tambah Menu Baru</h2>
            <div class="user-wrapper">
                <i class='bx bxs-user-circle'></i>
                <div>
                    <h4><?php echo htmlspecialchars($_SESSION['nama']); ?></h4>
                    <small><?php echo htmlspecialchars($_SESSION['jabatan']); ?></small>
                </div>
            </div>
        </header>

        <main>
            <div class="form-container">
                <form action="tambah_menu.php" method="POST" enctype="multipart/form-data">
                    
                    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>
                    <?php if (!empty($sukses)) echo "<p class='message sukses'>$sukses</p>"; ?>

                    <div class="form-group">
                        <label for="id_menu">ID Menu</label>
                        <input type="text" id="id_menu" name="id_menu" placeholder="Contoh: menu011" required value="<?php echo isset($_POST['id_menu']) ? htmlspecialchars($_POST['id_menu']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="nama_menu">Nama Menu</label>
                        <input type="text" id="nama_menu" name="nama_menu" placeholder="Contoh: Nasi Goreng Spesial" required value="<?php echo isset($_POST['nama_menu']) ? htmlspecialchars($_POST['nama_menu']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="id_kategori">Kategori</label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <option value="kat001" <?php echo (isset($_POST['id_kategori']) && $_POST['id_kategori'] == 'kat001') ? 'selected' : ''; ?>>Makanan</option>
                            <option value="kat002" <?php echo (isset($_POST['id_kategori']) && $_POST['id_kategori'] == 'kat002') ? 'selected' : ''; ?>>Minuman</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" id="harga" name="harga" placeholder="Contoh: 25000" required value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" placeholder="Tulis deskripsi singkat menu..."><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Menu</label>
                        <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg">
                    </div>

                    <div class="form-group">
                        <label for="status_menu">Status Menu</label>
                        <select id="status_menu" name="status_menu" required>
                            <option value="tersedia" <?php echo (isset($_POST['status_menu']) && $_POST['status_menu'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak tersedia" <?php echo (isset($_POST['status_menu']) && $_POST['status_menu'] == 'tidak tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-add">Simpan Menu</button>
                        <a href="kelola_menu.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
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