<?php
// Mulai session
session_start();

// 1. Hapus semua variabel session (Termasuk Token JWT & Data Login)
session_unset();

// 2. Hancurkan session sepenuhnya
session_destroy();

// 3. (PENTING) Paksa Browser Lupakan Cache
// Ini supaya pas user klik tombol "Back", halaman tidak muncul lagi dari memori browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 4. Tendang ke Login
header("Location: login.php");
exit(); 
?>