<?php
// Mulai session di baris paling atas, wajib!
session_start();

// HAPUS KONEKSI DATABASE LAMA
// require 'koneksi.php'; 

// Inisialisasi variabel pesan
$error_login = '';
$error_register = '';
$sukses = '';

// Cek pesan sukses dari redirect sebelumnya
if (isset($_SESSION['pesan_sukses'])) {
    $sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']);
}

// Konfigurasi IP Backend (Sesuaikan jika pakai Docker/Localhost)
// Jika PHP di XAMPP & Java di VSCode -> "http://localhost:8080"
// Jika di dalam Docker -> "http://172.17.0.1:8080"
$api_base_url = "http://172.17.0.1:8080/api/admins";

// ===============================================
// PROSES LOGIN (VIA API SPRING BOOT)
// ===============================================
if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_login = "Username dan password wajib diisi!";
    } else {
        // 1. Siapkan Data Login
        $data_login = [
            "username" => $username,
            "password" => $password
        ];

        // 2. Kirim Request ke API Login
        $ch = curl_init($api_base_url . "/login");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_login));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        // 3. Cek Hasil Login
        if ($httpCode == 200 && isset($result['token'])) {
            // LOGIN SUKSES!
            $_SESSION['login'] = true;
            
            // --- SIMPAN TOKEN JWT (Sangat Penting!) ---
            $_SESSION['jwt_token'] = $result['token']; 
            
            // Simpan data user ke session
            $_SESSION['id_admin'] = $result['data']['idAdmin'];
            $_SESSION['username'] = $result['data']['username'];
            $_SESSION['nama']     = $result['data']['nama'];
            $_SESSION['jabatan']  = $result['data']['jabatan'];

            // Redirect ke Dashboard
            header("Location: admin/dashboard.php");
            exit();
        } elseif ($httpCode == 401) {
            $error_login = "Username atau password salah.";
        } else {
            $error_login = "Gagal login. Server Backend mungkin mati.";
        }
    }
}

// ===============================================
// PROSES REGISTRASI (VIA API SPRING BOOT)
// ===============================================
if (isset($_POST['register_btn'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Kirim raw, Java yang akan hash
    $jabatan = $_POST['jabatan'];

    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($jabatan)) {
        $error_register = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_register = "Format email tidak valid!";
    } else {
        // 1. Siapkan Data Registrasi
        // Perhatikan key array harus sama dengan field di Admin.java
        $data_reg = [
            "nama" => $nama,
            "email" => $email,
            "username" => $username,
            "password" => $password, 
            "jabatan" => $jabatan,
            "statusAdmin" => "aktif"
        ];

        // 2. Kirim Request ke API Add Admin
        // Catatan: Pastikan endpoint /add di SecurityConfig.java diizinkan (permitAll) 
        // atau kamu login dulu baru bisa add. Jika ini registrasi publik, /add harus permitAll.
        $ch = curl_init($api_base_url . "/add");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_reg));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // 3. Cek Hasil Registrasi
        if ($httpCode == 200 || $httpCode == 201) {
            $_SESSION['pesan_sukses'] = "Registrasi berhasil! Silakan login.";
            header("Location: login.php"); // Refresh halaman
            exit();
        } else {
            // Jika gagal (misal username duplicate), API Java biasanya return 500/400
            $error_register = "Registrasi gagal. Username/Email mungkin sudah dipakai.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Admin Resto</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/style-login.css">
</head>
<body>

<div class="container <?php if (!empty($error_register)) echo 'active'; ?>">
    <div class="form-box login">
        <form action="login.php" method="POST">
            <h1>Sign In</h1>

            <?php if (!empty($error_login)) echo "<p style='color:red; text-align:center; font-size:14px;'>$error_login</p>"; ?>
            <?php if (!empty($sukses)) echo "<p style='color:green; text-align:center; font-size:14px;'>$sukses</p>"; ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <div class="forgot-link">
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit" name="login_btn" class="btn">Login</button>
        </form>
    </div>

    <div class="form-box register">
        <form action="login.php" method="POST">
            <h1>Registration</h1>

            <?php if (!empty($error_register)) echo "<p style='color:red; text-align:center; font-size:14px;'>$error_register</p>"; ?>

            <div class="input-box">
                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                <i class='bx bxs-user-detail'></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class='bx bxs-envelope'></i>
            </div>
              <div class="input-box">
                <input type="text" name="jabatan" placeholder="Jabatan (Owner/Staff)" required>
              <i class='bx bxs-briefcase-alt-2'></i>  
            </div>
            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <button type="submit" name="register_btn" class="btn">Register</button>
        </form>
    </div>

    <div class="toggle-box">
        <div class="toggle-panel toggle-left">
            <h1>Hello, Welcome</h1>
            <p>Don't have an account?</p>
            <button class="btn register-btn">Register</button>
        </div> 
        <div class="toggle-panel toggle-right">
            <h1>Welcome back!</h1>
            <p>Already have an account?</p>
            <button class="btn login-btn">Login</button>
        </div>
    </div>
</div>

<script src="assets/script-login.js"></script>
</body>
</html>