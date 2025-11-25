// ==========================================================
// KODE UNTUK HALAMAN index.js (TERINTEGRASI - FINAL)
// ==========================================================

// Variabel global modal (dari temanmu)
let currentQty = 1;
let currentItemId = null; // Ini akan menyimpan id_menu

// --- Fungsi Komunikasi Global ---
async function updateKeranjangServer(data) {
    try {
        // Path dari JSUser/index.js ke MainUser/proses_cart.php
        // (Asumsi JSUser dan MainUser ada di dalam view-user)
        const response = await fetch('../MainUser/proses_cart.php', { // Targetkan proses_cart.php
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
             const errorText = await response.text();
             console.error("Server error:", response.status, errorText);
             alert(`Error: ${errorText}`);
             return null; 
        }
        return await response.json();
    } catch (error) {
        console.error('Error saat menghubungi server:', error);
        return null;
    }
}

// --- Fungsi Update Badge Keranjang ---
function updateCartCount(count) {
    // Selector ini dari file navbar.php temanmu
    const cartBadge = document.querySelector('.cart-count'); 
    const cartIcon = document.querySelector('.nav-cart-btn'); 

    if (count > 0) {
        if (cartBadge) { 
            cartBadge.textContent = count; 
        } else if (cartIcon) {
            // Buat badge jika belum ada
            const badge = document.createElement('span');
            badge.className = 'cart-count';
            badge.textContent = count;
            const link = cartIcon.querySelector('a'); // Cari link di dalam cart-icon
            if (link) { 
                link.appendChild(badge); 
            } else { 
                cartIcon.appendChild(badge); // Fallback
            }
        }
    } else {
        if (cartBadge) { 
            cartBadge.remove(); 
        }
    }
}

// --- Logika yang berjalan saat halaman dimuat ---
document.addEventListener('DOMContentLoaded', () => {
    console.log("Menjalankan script index.js (terintegrasi)");

    // Filter menu (dari temanmu)
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = this.getAttribute('data-category');
            filterMenu(category); // Panggil fungsi global
        });
    });

    // Sinkronisasi keranjang saat halaman load
    async function sinkronkanKeranjangSaatMuat() {
        // Cek jika nomor meja valid sebelum sinkronisasi
        // 'tableNumber' didapat dari <script> di index.php
        if (typeof tableNumber !== 'undefined' && tableNumber !== null) { 
             console.log("Sinkronisasi keranjang...");
             const response = await updateKeranjangServer({ aksi: 'get_status' });
             if(response && response.total_items !== undefined) {
                 updateCartCount(response.total_items);
             }
         }
    }
    sinkronkanKeranjangSaatMuat();
});

// --- Fungsi Global (Dipanggil dari HTML onclick) ---

// Fungsi filterMenu (dari temanmu)
function filterMenu(category) {
    const menuCards = document.querySelectorAll('.menu-card');
    menuCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        const displayStyle = (category === 'all' || cardCategory === category) ? 'block' : 'none';
        card.style.display = displayStyle;
    });
}

// Fungsi Modal (dari temanmu - disesuaikan)
function openDetail(id_menu) {
    currentItemId = id_menu; // Simpan id_menu
    currentQty = 1;
    
    // Gunakan allMenuData dari PHP (bukan allMenu)
    const item = allMenuData.find(m => m.id == id_menu); 
    if (!item) { console.error("Item tidak ditemukan:", id_menu); return; }

    const detailImageEl = document.getElementById('detailImage');
    if (detailImageEl) {
        detailImageEl.src = item.image; // Set 'src' untuk tag <img>
        detailImageEl.alt = item.name;
    }
    const detailNameEl = document.getElementById('detailName');
    if(detailNameEl) detailNameEl.textContent = item.name;
    
    const detailDescEl = document.getElementById('detailDesc');
    if (detailDescEl) detailDescEl.textContent = item.description || ''; // Tampilkan deskripsi
    
    const detailPriceEl = document.getElementById('detailPrice');
    if(detailPriceEl) detailPriceEl.textContent = 'Rp ' + (parseFloat(item.price) || 0).toLocaleString('id-ID');
    
    const qtyDisplayEl = document.getElementById('qtyDisplay');
    if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;
    
    const detailModalEl = document.getElementById('detailModal');
    if(detailModalEl) detailModalEl.style.display = 'block';
    
    document.body.style.overflow = 'hidden';
}

// Fungsi Modal (dari temanmu - tidak diubah)
function closeModal() {
    const detailModalEl = document.getElementById('detailModal');
     if(detailModalEl) detailModalEl.style.display = 'none';
    document.body.style.overflow = 'auto';
}
function decreaseQty() {
    if (currentQty > 1) {
        currentQty--;
        const qtyDisplayEl = document.getElementById('qtyDisplay');
         if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;
    }
}
function increaseQty() {
    currentQty++;
    const qtyDisplayEl = document.getElementById('qtyDisplay');
    if(qtyDisplayEl) qtyDisplayEl.textContent = currentQty;
}

// --- FUNGSI Add to Cart (INTEGRASI UTAMA) ---
async function addToCart() {
    // Cek nomor meja dari <script> PHP
    if (typeof tableNumber === 'undefined' || tableNumber === null) { 
        alert("Silakan scan QR code meja yang valid untuk memesan.");
        return;
    }
    
    const item = allMenuData.find(m => m.id == currentItemId);
    if (!item) return;

    const btn = document.getElementById('addToCartBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Menambahkan...';
    
    // Ekstrak nama file saja dari path lengkap
    const gambarFilename = item.image.split('/').pop(); 

    try {
        // Panggil proses_cart.php kita dengan data JSON
        const response = await updateKeranjangServer({
            aksi: 'tambah',
            id_menu: item.id, // Kirim id_menu (dari key 'id')
            nama: item.name,   // Kirim nama
            harga: parseFloat(item.price) || 0, // Kirim harga
            gambar: gambarFilename, // Kirim NAMA FILE gambar
            qty: currentQty    // Kirim kuantitas dari modal
        });

        if (response && response.success) {
            updateCartCount(response.total_items); // Update badge
            closeModal();
            showNotification(item.name, currentQty); // Panggil notifikasi temanmu
        } else {
            alert(`Gagal menambahkan: ${response ? response.message : 'Server error'}`);
        }
    } catch (error) { 
        console.error('Error saat addToCart:', error);
        alert('Terjadi kesalahan saat menambahkan');
    }
    finally {
        btn.disabled = false;
        btn.innerHTML = '🛒 Tambah ke Keranjang';
    }
}

// Notifikasi (dari temanmu)
function showNotification(itemName, qty) {
     const notification = document.createElement('div');
     notification.className = 'toast-notification';
     notification.style.animation = 'slideInRight 0.5s ease forwards';
     notification.innerHTML =
        `<span style="font-size: 24px;">✓</span>
         <div>
             <div style="font-weight: 700; margin-bottom: 5px;">${itemName}</div>
             <div style="font-size: 14px; opacity: 0.8;">${qty} item ditambahkan</div>
         </div>`;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.5s ease forwards';
        setTimeout(() => notification.remove(), 500);
    }, 2500);
}

// Close modal event listeners (dari temanmu)
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modal = document.getElementById('detailModal');
        if (modal && modal.style.display === 'block') {
           closeModal();
        }
    }
});
window.onclick = function(event) {
    const modal = document.getElementById('detailModal');
    if (modal && event.target == modal) {
        closeModal();
    }
}