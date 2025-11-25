<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

require '../koneksi.php';

$error = '';

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: kelola_admin.php");
    exit;
}
$id_admin = $_GET['id'];

// Ambil data admin yang akan diedit
$sql_select = "SELECT * FROM admin WHERE id_admin = ?";
$stmt_select = mysqli_prepare($koneksi, $sql_select);
mysqli_stmt_bind_param($stmt_select, "s", $id_admin);
mysqli_stmt_execute($stmt_select);
$result = mysqli_stmt_get_result($stmt_select);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    header("Location: kelola_admin.php");
    exit;
}

// Logika untuk UPDATE data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $jabatan = $_POST['jabatan'];
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($username) || empty($jabatan)) {
        $error = "Semua kolom kecuali password wajib diisi!";
    } else {
        // Logika Update Password: HANYA jika kolom password diisi.
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE admin SET nama = ?, email = ?, username = ?, jabatan = ?, password = ? WHERE id_admin = ?";
            $stmt_update = mysqli_prepare($koneksi, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ssssss", $nama, $email, $username, $jabatan, $hashed_password, $id_admin);
        } else {
            // Jika password tidak diisi, jangan update kolom password
            $sql_update = "UPDATE admin SET nama = ?, email = ?, username = ?, jabatan = ? WHERE id_admin = ?";
            $stmt_update = mysqli_prepare($koneksi, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "sssss", $nama, $email, $username, $jabatan, $id_admin);
        }

        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['pesan_sukses'] = "Data admin berhasil diperbarui!";
            header("Location: kelola_admin.php");
            exit();
        } else {
            $error = "Gagal memperbarui data. Mungkin username atau email sudah digunakan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="..admin_side/assets/style-dashboard.css">
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
            <h2>Edit Admin</h2>
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
                <form action="edit_admin.php?id=<?php echo htmlspecialchars($id_admin); ?>" method="POST">
                    
                    <?php if (!empty($error)) echo "<p class='message error'>$error</p>"; ?>

                    <div class="form-group">
                        <label>ID Admin</label>
                        <input type="text" value="<?php echo htmlspecialchars($admin['id_admin']); ?>" readonly style="background:#ddd;">
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required value="<?php echo htmlspecialchars($admin['nama']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($admin['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($admin['username']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">
                    </div>
                    <div class="form-group">
                        <label for="jabatan">Jabatan</label>
                        <select id="jabatan" name="jabatan" required>
                            <option value="Owner" <?php echo ($admin['jabatan'] == 'Owner') ? 'selected' : ''; ?>>Owner</option>
                            <option value="Manager" <?php echo ($admin['jabatan'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                            <option value="Staff" <?php echo ($admin['jabatan'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                        </select>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" class="btn-add">Update Admin</button>
                        <a href="kelola_admin.php" class="btn-cancel">Batal</a>
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