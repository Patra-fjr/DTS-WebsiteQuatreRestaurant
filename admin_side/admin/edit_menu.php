<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// 1. KONFIGURASI API
$api_url_base = "http://172.17.0.1:8080/api/menus"; 
$error = '';
$sukses = '';

if (!isset($_GET['id'])) {
    header("Location: kelola_menu.php");
    exit;
}
$id_menu = $_GET['id'];

// 2. AMBIL TOKEN & DATA MENU LAMA DARI API
$token = $_SESSION['jwt_token'] ?? '';

// Context Header untuk GET
$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: Bearer " . $token . "\r\n" .
                    "Content-Type: application/json"
    ]
];
$context = stream_context_create($opts);

$json_all = @file_get_contents($api_url_base, false, $context);
$all_menus = json_decode($json_all, true);
$menu = null;

if ($all_menus) {
    foreach ($all_menus as $m) {
        if ($m['idMenu'] == $id_menu) {
            $menu = $m;
            break;
        }
    }
}

if (!$menu) {
    // Jika tidak ketemu atau token expired
    header("Location: kelola_menu.php");
    exit;
}

// 3. LOGIKA UNTUK MEMPROSES UPDATE (PUT)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_menu = $_POST['nama_menu'];
    $id_kategori = $_POST['id_kategori'];
    $harga = $_POST['harga'];
    $status_menu = $_POST['status_menu'];
    $deskripsi = $_POST['deskripsi'];
    
    // Default: gunakan gambar lama
    $nama_gambar_lama = $_POST['gambar_lama'];
    $nama_gambar_baru = $nama_gambar_lama; 

    // Cek upload gambar baru
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = __DIR__ . "/../assets/image/";
        $nama_gambar_baru = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_gambar_baru;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Hanya file JPG, JPEG, & PNG yang diizinkan.";
        } else {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                // Hapus file lama jika sukses
                if (!empty($nama_gambar_lama) && $nama_gambar_lama != 'placeholder.jpg') {
                    if (file_exists($target_dir . $nama_gambar_lama)) {
                        unlink($target_dir . $nama_gambar_lama);
                    }
                }
            } else {
                $error = "Gagal meng-upload gambar baru.";
                $nama_gambar_baru = $nama_gambar_lama;
            }
        }
    }

    if (empty($error)) {
        // --- KIRIM DATA KE API SPRING BOOT (PUT METHOD) ---
        $data_update = [
            "namaMenu" => $nama_menu,
            "idKategori" => $id_kategori,
            "harga" => (int)$harga,
            "statusMenu" => $status_menu,
            "deskripsi" => $deskripsi,
            "gambar" => $nama_gambar_baru
        ];

        $url_put = $api_url_base . "/update/" . $id_menu;
        $ch = curl_init($url_put);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_update));
        
        // --- HEADER AUTHORIZATION ---
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $_SESSION['pesan_sukses'] = "Data menu berhasil diperbarui via API!";
            header("Location: kelola_menu.php");
            exit();
        } elseif ($httpCode == 401 || $httpCode == 403) {
            $error = "Gagal Update: Token tidak valid atau sesi habis.";
        } else {
            $error = "Gagal memperbarui menu di API. (Error Code: $httpCode)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu</title>
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
            <h2>Edit Menu</h2>
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
                <form action="edit_menu.php?id=<?php echo htmlspecialchars($id_menu); ?>" method="POST" enctype="multipart/form-data">
                    
                    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

                    <div class="form-group">
                        <label>ID Menu</label>
                        <input type="text" value="<?php echo htmlspecialchars($menu['idMenu']); ?>" readonly style="background:#ddd;">
                    </div>

                    <div class="form-group">
                        <label for="nama_menu">Nama Menu</label>
                        <input type="text" id="nama_menu" name="nama_menu" value="<?php echo htmlspecialchars($menu['namaMenu']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_kategori">Kategori</label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="kat001" <?php echo ($menu['idKategori'] == 'kat001') ? 'selected' : ''; ?>>Makanan</option>
                            <option value="kat002" <?php echo ($menu['idKategori'] == 'kat002') ? 'selected' : ''; ?>>Minuman</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($menu['harga']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($menu['deskripsi']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Menu (Kosongkan jika tidak ingin diubah)</label>
                        <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($menu['gambar']); ?>">
                        
                        <?php if (!empty($menu['gambar'])): ?>
                            <div class="image-preview" style="margin-top: 10px;">
                                <p>Gambar Saat Ini:</p>
                                <img src="../assets/image/<?php echo htmlspecialchars($menu['gambar']); ?>" alt="Preview" style="max-width: 150px; border-radius: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_menu">Status Menu</label>
                        <select id="status_menu" name="status_menu" required>
                            <option value="tersedia" <?php echo ($menu['statusMenu'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="habis" <?php echo ($menu['statusMenu'] == 'habis') ? 'selected' : ''; ?>>Habis</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-add">Update Menu</button>
                        <a href="kelola_menu.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/script-dashboard.js"></script>
</body>
</html>