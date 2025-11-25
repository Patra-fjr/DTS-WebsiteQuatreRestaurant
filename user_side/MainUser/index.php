<?php
    // "Detektif" Error PHP
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    require_once '../config/config.php'; // Pakai $conn

    // Inisialisasi
    $tableNumber = null;
    $allMenu = [];
    $meja_invalid = false;

    // 1. Ambil & Validasi Nomor Meja (INT dari URL)
    $tableNumberParam = isset($_GET['table']) ? intval($_GET['table']) : null;

    if ($tableNumberParam !== null && $conn) {
        // Cek status meja di DB pakai NOMOR MEJA (INT)
        $stmt_check = mysqli_prepare($conn, "SELECT id_meja, status_meja FROM meja WHERE nomor_meja = ?");
        mysqli_stmt_bind_param($stmt_check, 'i', $tableNumberParam);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $meja_data = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);

        if ($meja_data && $meja_data['status_meja'] === 'tersedia') {
            $tableNumber = $tableNumberParam; // Nomor meja (angka)
            $_SESSION['id_meja_varchar'] = $meja_data['id_meja']; // Simpan ID VARCHAR ('tab00x')
            $_SESSION['nomor_meja_int'] = $tableNumberParam; 
        } else {
            $meja_invalid = true;
            unset($_SESSION['id_meja_varchar']);
            unset($_SESSION['nomor_meja_int']);
        }
    } elseif (isset($_SESSION['nomor_meja_int'])) {
        $tableNumber = $_SESSION['nomor_meja_int']; // Ambil dari Session
    }

    // 2. Query Menu (Sesuai Database Anda)
    $query = "
        SELECT m.id_menu, m.nama_menu, m.harga, m.gambar, m.deskripsi, k.nama_kategori
        FROM menu m
        JOIN kategori k ON m.id_kategori = k.id_kategori
        WHERE m.status_menu = 'tersedia' AND (k.nama_kategori = 'makanan' OR k.nama_kategori = 'minuman')
        ORDER BY k.nama_kategori, m.nama_menu ASC
    "; // Ambil 'makanan' dan 'minuman' lowercase

    if ($conn) {
        $result = $conn->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $gambar_nama = $row['gambar'] ?? null;
                    
                // ==========================================
                    // PERBAIKAN PATH GAMBAR FINAL: Gunakan URL Absolut
                    // ==========================================
                    if ($gambar_nama) {
                        // Alamat lengkap ke folder assets admin
                        $gambar_path = 'http://localhost:85/admin_side/assets/image/' . htmlspecialchars($gambar_nama);
                    } else {
                        // Alamat lengkap ke placeholder (jika ada di folder assets admin)
                        $gambar_path = 'http://localhost:85/admin_side/assets/image/placeholder.jpg';
                    }
                    $allMenu[] = [
                        'id'          => $row['id_menu'],
                        'name'        => $row['nama_menu'],
                        'price'       => $row['harga'],
                        'image'       => $gambar_path, // Path lengkap yang sudah benar
                        'description' => $row['deskripsi'] ?? '',
                        'category'    => strtolower($row['nama_kategori'])
                    ];
                }
            }
        } else { /* ... error handling ... */ }
    } else { /* ... error handling ... */ }
    ?>

    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quatre's Restaurant</title>
        <link rel="stylesheet" href="../CSSUser/index.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <style> /* Tambahan CSS untuk alert & gambar */
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 10px; border: 1px solid transparent; display: flex; align-items: center; gap: 10px; }
            .alert-error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
            .alert-warning { background-color: #fff3cd; border-color: #ffeeba; color: #856404; }
            .alert i { font-size: 1.5em; }
            .menu-image { width: 100%; height: 200px; object-fit: cover; } 
            .detail-image { width: 100%; height: 250px; object-fit: cover; border-radius: 12px; margin-bottom: 25px; background: #f0f0f0; }
        </style>
    </head>
    <body>
        <?php include 'navbar.php'; ?>
        <div class="container">
            <?php if ($meja_invalid): ?>
                <div class="alert alert-error">
                    <i class='bx bxs-error-circle'></i> Maaf, meja <strong><?php echo htmlspecialchars($_GET['table'] ?? 'ini'); ?></strong> tidak tersedia. Silakan scan QR di meja yang benar.
                </div>
            <?php elseif ($tableNumber === null): ?>
                <div class="alert alert-warning">
                    <i class='bx bxs-info-circle'></i> Silakan scan QR code di meja Anda untuk memulai pemesanan.
                </div>
            <?php endif; ?>
            
            <?php if ($tableNumber !== null): ?>
            <div class="table-info-container">
                <div class="table-info-card">
                    <span class="table-icon"><i class='bx bx-chair bx-lg'></i></span>
                    <div class="table-details">
                        <span class="table-label">Nomor Meja</span>
                        <span class="table-number" id="tableNumber"><?php echo htmlspecialchars($tableNumber); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="filter-section">
                <div class="filter-buttons">
                    <button class="filter-btn active" data-category="all">Semua Menu</button>
                    <button class="filter-btn" data-category="makanan">Makanan</button>
                    <button class="filter-btn" data-category="minuman">Minuman</button>
                </div>
            </div>

            <div class="menu-grid" id="menuGrid">
                <?php if (!empty($allMenu) && $tableNumber !== null): // Tampilkan menu hanya jika meja valid ?>
                    <?php foreach ($allMenu as $menu): ?>
                        <div class="menu-card"
                            data-category="<?php echo $menu['category']; ?>"
                            onclick="openDetail('<?php echo $menu['id']; ?>')">
                            
                            <img src="<?php echo $menu['image']; ?>" alt="<?php echo $menu['name']; ?>" class="menu-image">
                            <div class="menu-info">
                                <span class="menu-category"><?php echo ucfirst($menu['category']); ?></span>
                                <h3 class="menu-name"><?php echo $menu['name']; ?></h3>
                                <p class="menu-desc"><?php echo $menu['description']; ?></p>
                                <div class="menu-price">Rp <?php echo number_format($menu['price'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php elseif ($tableNumber !== null): ?>
                    <p style="grid-column: 1 / -1; text-align: center;">Menu belum tersedia.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="detailModal" class="modal">
        <div class="modal-content">
                <div class="modal-header">
                    <h2>Detail Menu</h2>
                    <span class="close-modal" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <img id="detailImage" src="" alt="Detail Menu" class="detail-image">
                    <h3 id="detailName" class="detail-name"></h3>
                    <p id="detailDesc" class="detail-desc"></p>
                    <div id="detailPrice" class="detail-price"></div>
                    <div class="qty-section">
                        <span class="qty-label">Jumlah:</span>
                        <div class="qty-controls">
                            <button class="qty-btn" onclick="decreaseQty()">−</button>
                            <span id="qtyDisplay" class="qty-display">1</span>
                            <button class="qty-btn" onclick="increaseQty()">+</button>
                        </div>
                    </div>
                    <button id="addToCartBtn" class="add-cart-btn" onclick="addToCart()">🛒 Tambah ke Keranjang</button>
                </div>
            </div>
        </div>

        <script>
            const allMenuData = <?php echo json_encode($allMenu); ?>;
            const tableNumber = <?php echo json_encode($tableNumber); ?>; 
        </script>
        <script src="../JSUser/index.js"></script>
        </body>
    </html>