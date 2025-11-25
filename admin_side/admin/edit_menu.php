<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php'; // Menggunakan $koneksi

$error = '';
$sukses = '';

// 1. Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: kelola_menu.php");
    exit;
}
$id_menu = $_GET['id'];

// 2. Ambil data menu yang akan diedit
$sql_select = "SELECT * FROM menu WHERE id_menu = ?";
$stmt_select = mysqli_prepare($koneksi, $sql_select);
mysqli_stmt_bind_param($stmt_select, "s", $id_menu);
mysqli_stmt_execute($stmt_select);
$result = mysqli_stmt_get_result($stmt_select);
$menu = mysqli_fetch_assoc($result);

if (!$menu) {
    header("Location: kelola_menu.php");
    exit;
}

// Ambil data kategori untuk dropdown
$kategori_result = mysqli_query($koneksi, "SELECT * FROM kategori WHERE status_kategori = 'tersedia'");

// 3. Logika untuk memproses UPDATE
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_menu = $_POST['nama_menu'];
    $id_kategori = $_POST['id_kategori'];
    $harga = $_POST['harga'];
    $status_menu = $_POST['status_menu'];
    $deskripsi = $_POST['deskripsi'];
    
    // Ambil nama gambar lama dari hidden input
    $nama_gambar_lama = $_POST['gambar_lama'];
    $nama_gambar_baru = $nama_gambar_lama; // Defaultnya adalah gambar lama

    // Cek apakah ada file gambar baru yang di-upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0 && !empty($_FILES['gambar']['name'])) {
        $target_dir = "../assets/image/";
        $nama_gambar_baru = uniqid() . '-' . basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $nama_gambar_baru;
        
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Maaf, hanya file JPG, JPEG, & PNG yang diizinkan.";
        } else {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                // Hapus gambar lama jika ada (dan bukan placeholder)
                if (!empty($nama_gambar_lama) && $nama_gambar_lama != 'placeholder.jpg') {
                    if (file_exists($target_dir . $nama_gambar_lama)) {
                        unlink($target_dir . $nama_gambar_lama);
                    }
                }
            } else {
                $error = "Maaf, terjadi kesalahan saat meng-upload file gambar baru.";
                $nama_gambar_baru = $nama_gambar_lama; // Kembalikan ke gambar lama jika gagal upload
            }
        }
    }

    if (empty($error) && (empty($nama_menu) || empty($id_kategori) || empty($harga) || empty($status_menu))) {
        $error = "Kolom Nama, Kategori, Harga, dan Status wajib diisi!";
    } elseif (empty($error)) {
        // Update query untuk menyertakan deskripsi dan gambar
        $sql_update = "UPDATE menu SET id_kategori = ?, nama_menu = ?, harga = ?, status_menu = ?, deskripsi = ?, gambar = ? WHERE id_menu = ?";
        $stmt_update = mysqli_prepare($koneksi, $sql_update);
        // Sesuaikan bind_param (ssissss)
        mysqli_stmt_bind_param($stmt_update, "ssissss", $id_kategori, $nama_menu, $harga, $status_menu, $deskripsi, $nama_gambar_baru, $id_menu);

        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['pesan_sukses'] = "Data menu berhasil diperbarui!";
            header("Location: kelola_menu.php");
            exit();
        } else {
            $error = "Gagal memperbarui menu: " . mysqli_error($koneksi);
        }
    }
    
    // Muat ulang data $menu jika terjadi error agar form menampilkan data yang baru diinput
    if (!empty($error)) {
        $menu['nama_menu'] = $nama_menu;
        $menu['id_kategori'] = $id_kategori;
        $menu['harga'] = $harga;
        $menu['status_menu'] = $status_menu;
        $menu['deskripsi'] = $deskripsi;
        // $menu['gambar'] biarkan gambar lama jika upload baru gagal
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
        </div>

    <div class="main-content">
        <header>
            <h2>Edit Menu</h2>
            </header>

        <main>
            <div class="form-container">
                <form action="edit_menu.php?id=<?php echo htmlspecialchars($id_menu); ?>" method="POST" enctype="multipart/form-data">
                    
                    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

                    <div class="form-group">
                        <label for="id_menu">ID Menu</label>
                        <input type="text" id="id_menu" name="id_menu" value="<?php echo htmlspecialchars($menu['id_menu']); ?>" readonly style="background:#ddd;">
                    </div>
                    <div class="form-group">
                        <label for="nama_menu">Nama Menu</label>
                        <input type="text" id="nama_menu" name="nama_menu" value="<?php echo htmlspecialchars($menu['nama_menu']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_kategori">Kategori</label>
                        <select id="id_kategori" name="id_kategori" required>
                            <option value="" disabled>-- Pilih Kategori --</option>
                            <?php
                            // Reset pointer result set kategori
                            mysqli_data_seek($kategori_result, 0); 
                            while($kategori = mysqli_fetch_assoc($kategori_result)) {
                                $selected = ($kategori['id_kategori'] == $menu['id_kategori']) ? 'selected' : '';
                                echo "<option value='" . $kategori['id_kategori'] . "' $selected>" . htmlspecialchars($kategori['nama_kategori']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="harga">Harga</label>
                        <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($menu['harga']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" placeholder="Tulis deskripsi singkat menu..."><?php echo htmlspecialchars($menu['deskripsi']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="gambar">Gambar Menu (Kosongkan jika tidak ingin diubah)</label>
                        <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg, image/jpg">
                        <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($menu['gambar']); ?>">
                        
                        <?php if (!empty($menu['gambar'])): ?>
                            <div class="image-preview">
                                <p>Gambar Saat Ini:</p>
                                <img src="../assets/image/<?php echo htmlspecialchars($menu['gambar']); ?>" alt="Preview">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_menu">Status Menu</label>
                        <select id="status_menu" name="status_menu" required>
                            <option value="tersedia" <?php echo ($menu['status_menu'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="tidak tersedia" <?php echo ($menu['status_menu'] == 'tidak tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
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