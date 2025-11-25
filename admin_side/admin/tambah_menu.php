<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php'; // Menggunakan $koneksi

$error = '';
$sukses = '';

// Ambil data kategori untuk dropdown
$kategori_result = mysqli_query($koneksi, "SELECT * FROM kategori WHERE status_kategori = 'tersedia'");

// Cek apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_menu = $_POST['id_menu'];
    $nama_menu = $_POST['nama_menu'];
    $id_kategori = $_POST['id_kategori'];
    $harga = $_POST['harga'];
    $status_menu = $_POST['status_menu'];
    $deskripsi = $_POST['deskripsi']; // Ambil deskripsi

    // Variabel untuk nama file gambar
    $nama_gambar = NULL; // Default NULL jika tidak ada gambar

    // --- LOGIKA UPLOAD GAMBAR BARU ---
    // Cek apakah ada file gambar yang di-upload dan tidak ada error
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        // Path dari folder 'admin' naik 1 level, lalu masuk ke 'assets/image/'
        $target_dir = "../assets/image/"; 
        
        // Buat nama file unik (prefix unik + nama asli)
        $nama_gambar = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_gambar;
        
        // Cek tipe file
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Maaf, hanya file JPG, JPEG, & PNG yang diizinkan.";
        } else {
            // Pindahkan file
            if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                $error = "Maaf, terjadi kesalahan saat meng-upload file gambar.";
                $nama_gambar = NULL; // Kembalikan ke NULL jika upload gagal
            }
        }
    }
    // --- AKHIR LOGIKA UPLOAD ---

    if (empty($error) && (empty($id_menu) || empty($nama_menu) || empty($id_kategori) || empty($harga) || empty($status_menu))) {
        $error = "Kolom ID Menu, Nama, Kategori, Harga, dan Status wajib diisi!";
    } elseif (empty($error)) {
        // Cek ID Menu
        $sql_check = "SELECT id_menu FROM menu WHERE id_menu = ?";
        $stmt_check = mysqli_prepare($koneksi, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $id_menu);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "ID Menu sudah digunakan, harap gunakan ID lain.";
        } else {
            // Query diubah untuk memasukkan 'deskripsi' dan 'gambar'
            $sql_insert = "INSERT INTO menu (id_menu, id_kategori, nama_menu, harga, status_menu, deskripsi, gambar) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
            // Tipe data bind_param diubah (sssisss)
            mysqli_stmt_bind_param($stmt_insert, "sssisss", $id_menu, $id_kategori, $nama_menu, $harga, $status_menu, $deskripsi, $nama_gambar);

            if (mysqli_stmt_execute($stmt_insert)) {
                $sukses = "Menu baru berhasil ditambahkan!";
                $_POST = array(); // Kosongkan form setelah berhasil
            } else {
                $error = "Gagal menambahkan menu: " . mysqli_error($koneksi);
            }
        }
        mysqli_stmt_close($stmt_check);
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
                            <?php
                            // Reset pointer result set kategori
                            mysqli_data_seek($kategori_result, 0); 
                            if (mysqli_num_rows($kategori_result) > 0) {
                                while($kategori = mysqli_fetch_assoc($kategori_result)) {
                                    $selected = (isset($_POST['id_kategori']) && $_POST['id_kategori'] == $kategori['id_kategori']) ? 'selected' : '';
                                    echo "<option value='" . $kategori['id_kategori'] . "' $selected>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                                }
                            }
                            ?>
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