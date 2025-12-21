<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

$api_url_base = "http://172.17.0.1:8080/api/admins"; 
$error = '';

if (!isset($_GET['id'])) {
    header("Location: kelola_admin.php");
    exit;
}
$id_admin = $_GET['id'];

// 1. AMBIL DATA ADMIN DARI API (GET BY ID)
// Kita perlu kirim Token saat GET juga
$token = $_SESSION['jwt_token'] ?? '';

$opts = [
    "http" => [
        "method" => "GET",
        "header" => "Authorization: Bearer " . $token . "\r\n" .
                    "Content-Type: application/json"
    ]
];
$context = stream_context_create($opts);

// Ambil semua data lalu filter ID (Sesuai logic awal Anda)
$json_data = @file_get_contents($api_url_base, false, $context);
$admins = json_decode($json_data, true);
$admin = null;

if ($admins) {
    foreach ($admins as $a) {
        if ($a['idAdmin'] == $id_admin) {
            $admin = $a;
            break;
        }
    }
}

if (!$admin) {
    // Jika admin tidak ketemu atau token expired/salah
    header("Location: kelola_admin.php"); 
    exit;
}

// 2. LOGIKA UPDATE DATA VIA API (PUT)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $jabatan = $_POST['jabatan'];
    $password = $_POST['password'];

    if (empty($nama) || empty($email) || empty($username) || empty($jabatan)) {
        $error = "Semua kolom kecuali password wajib diisi!";
    } else {
        // Siapkan data untuk dikirim ke API
        $data_update = [
            "nama" => $nama,
            "email" => $email,
            "username" => $username,
            "jabatan" => $jabatan
        ];

        if (!empty($password)) {
            $data_update["password"] = $password;
        }

        // Tembak API dengan method PUT
        $url_put = $api_url_base . "/update/" . $id_admin;
        
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
            $_SESSION['pesan_sukses'] = "Data admin berhasil diperbarui!";
            header("Location: kelola_admin.php");
            exit();
        } elseif ($httpCode == 401 || $httpCode == 403) {
            $error = "Gagal Update: Token tidak valid/kadaluarsa.";
        } else {
            $error = "Gagal memperbarui data. (Error Code: $httpCode)";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
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
                        <input type="text" value="<?php echo htmlspecialchars($admin['idAdmin']); ?>" readonly style="background:#ddd;">
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
                        <input type="text" id="password" name="password" placeholder="Kosongkan jika tidak ingin diubah">
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
    
    <script src="../assets/script-dashboard.js"></script>
</body>
</html>