<?php

//buat koneksi ygy

// Biasanya "localhost" jika server database ada di komputer yang sama.
$host = "mysql";

// Username default untuk XAMPP adalah "root".
$username_db = "root";

// Password default untuk XAMPP adalah kosong ("").
$password_db = "password";

// Nama database yang sudah kamu buat di phpMyAdmin.
$nama_database = "db_resto";


/**
 * Membuat Koneksi ke Database
 * ---------------------------
 * Kode di bawah ini akan mencoba terhubung ke database menggunakan
 * konfigurasi di atas.
 */
$koneksi = mysqli_connect($host, $username_db, $password_db, $nama_database);


/**
 * Memeriksa Status Koneksi
 * -----------------------
 * Jika koneksi gagal, skrip akan berhenti dan menampilkan pesan eror.
 * Ini penting untuk debugging.
 */
if (!$koneksi) {
    die("KONEKSI GAGAL: " . mysqli_connect_error());
}

?>