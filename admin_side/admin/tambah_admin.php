<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

// require '../koneksi.php'; // Sudah tidak perlu koneksi DB langsung

$error = '';
$sukses = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $jabatan = $_POST['jabatan'];

    // 1. Validasi Input Dasar
    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($jabatan)) {
        $error = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } else {
        // 2. Siapkan Data untuk API
        $data = [
            "nama" => $nama,
            "email" => $email,
            "username" => $username,
            "password" => $password, // Akan di-hash otomatis oleh Spring Boot
            "jabatan" => $jabatan
        ];

        // 3. Kirim ke API Spring Boot menggunakan cURL
        // Gunakan IP gateway yang berhasil Anda pakai sebelumnya
        $url = "http://172.17.0.1:8080/api/admins/add";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 4. Cek Respon API
        if ($httpCode == 200 || $httpCode == 201) {
            $sukses = "Admin baru berhasil ditambahkan melalui API!";
            $_POST = array(); // Kosongkan form
        } else {
            // Jika gagal (misal username sudah ada), API biasanya melempar error
            $error = "Gagal menambahkan admin. Periksa apakah Username sudah terdaftar di sistem.";
        }
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
                        <input type="text" id="password" name="password" required>
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
    
    <script src="../assets/script-dashboard.js"></script>
</body>
</html>