<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

$error = '';
$sukses = '';

// Cek apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $jabatan = $_POST['jabatan'];

    // Validasi
    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($jabatan)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // Cek apakah username atau email sudah ada
        $sql_check = "SELECT id_admin FROM admin WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($koneksi, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Username atau Email sudah terdaftar.";
        } else {
            // Buat ID unik
            $id_admin = "ad" . rand(100, 999);
            // HASH PASSWORD! Paling penting.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Siapkan query INSERT yang aman
            $sql_insert = "INSERT INTO admin (id_admin, nama, email, username, password, jabatan) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $id_admin, $nama, $email, $username, $hashed_password, $jabatan);

            if (mysqli_stmt_execute($stmt_insert)) {
                $sukses = "Admin baru berhasil ditambahkan!";
                $_POST = array(); // Kosongkan form setelah berhasil
            } else {
                $error = "Gagal menambahkan admin: " . mysqli_error($koneksi);
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
    <title>Tambah Admin Baru</title>
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
            <li><a href="kelola_ketersediaan.php"><i class='bx bxs-fridge'></i><span class="link-name">Ketersediaan</span></a></li>
            <li><a href="kelola_pesanan.php"><i class='bx bxs-receipt'></i><span class="link-name">Pesanan</span></a></li>
            <li><a href="laporan.php"><i class='bx bxs-bar-chart-alt-2'></i><span class="link-name">Laporan</span></a></li>
            <li class="active"><a href="kelola_admin.php"><i class='bx bxs-group'></i><span class="link-name">Kelola Admin</span></a></li>
            <li class="logout"><a href="#" id="logout-btn"><i class='bx bxs-log-out'></i><span class="link-name">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Tambah Admin Baru</h2>
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
                <form action="tambah_admin.php" method="POST">
                    
                    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>
                    <?php if (!empty($sukses)) echo "<p class='message sukses'>$sukses</p>"; ?>

                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <select id="jabatan" name="jabatan" required>
                            <option value="" disabled selected>-- Pilih Jabatan --</option>
                            <option value="Owner" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                            <option value="Manager" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="Staff" <?php echo (isset($_POST['jabatan']) && $_POST['jabatan'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-add">Simpan Admin</button>
                        <a href="kelola_admin.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
    
    <div class="popup-overlay" id="logout-popup">
        </div>
    <script src="../assets/script-dashboard.js"></script>
</body>
</html>