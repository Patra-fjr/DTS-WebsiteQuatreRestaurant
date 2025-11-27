<?php
// Mulai session di baris paling atas, wajib!
session_start();
require 'koneksi.php';


//buat login guys

// Inisialisasi variabel untuk menampung pesan
$error_login = '';
$error_register = '';
$sukses = '';

// Cek apakah ada pesan sukses dari proses registrasi
if (isset($_SESSION['pesan_sukses'])) {
    $sukses = $_SESSION['pesan_sukses'];
    unset($_SESSION['pesan_sukses']); // Hapus pesan agar tidak muncul lagi
}

// ===============================================
// PROSES LOGIN (JIKA TOMBOL LOGIN DITEKAN)
// ===============================================
if (isset($_POST['login_btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_login = "Username dan password wajib diisi!";
    } else {
        $sql = "SELECT id_admin, username, password, nama, jabatan FROM admin WHERE username = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Verifikasi password yang di-hash
            if (password_verify($password, $user['password'])) {
                // Login berhasil, simpan data ke session
                $_SESSION['login'] = true;
                $_SESSION['id_admin'] = $user['id_admin'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['jabatan'] = $user['jabatan'];

                // Alihkan ke halaman dashboard
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $error_login = "Username atau password salah.";
            }
        } else {
            $error_login = "Username atau password salah.";
        }
    }
}

// ===============================================
// PROSES REGISTRASI (JIKA TOMBOL REGISTER DITEKAN)
// ===============================================
if (isset($_POST['register_btn'])) {
    $nama = $_POST['nama_lengkap'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $jabatan = $_POST['jabatan'];

    if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($jabatan)) {
        $error_register = "Semua kolom wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_register = "Format email tidak valid!";
    } else {
        $sql_check = "SELECT id_admin FROM admin WHERE username = ? OR email = ?";
        $stmt_check = mysqli_prepare($koneksi, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error_register = "Username atau Email sudah terdaftar.";
        } else {
            $id_admin = "ad" . rand(100, 999);
            // HASH PASSWORD! Paling penting.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO admin (id_admin, nama, email, username, password, jabatan) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($koneksi, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $id_admin, $nama, $email, $username, $hashed_password, $jabatan);

            if (mysqli_stmt_execute($stmt_insert)) {
                // Kirim pesan sukses ke halaman ini via session
                $_SESSION['pesan_sukses'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                $error_register = "Registrasi gagal, silakan coba lagi.";
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
    <title>Sign In</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/style-login.css">
</head>
<body>

<div class="container <?php if (!empty($error_register)) echo 'active'; ?>">
    <div class="form-box login">
        <form action="login.php" method="POST">
            <h1>Sign In</h1>

            <?php if (!empty($error_login)) echo "<p style='color:red; text-align:center;'>$error_login</p>"; ?>
            <?php if (!empty($sukses)) echo "<p style='color:green; text-align:center;'>$sukses</p>"; ?>

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

            <?php if (!empty($error_register)) echo "<p style='color:red; text-align:center;'>$error_register</p>"; ?>

            <div class="input-box">
                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required>
                <i class='bx bxs-user-detail'></i>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
                <i class='bx bxs-envelope'></i>
            </div>
              <div class="input-box">
                <input type="text" name="jabatan" placeholder="Jabatan" required>
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