// ==========================================================
// KODE UNTUK HALAMAN keranjang.php (TERINTEGRASI - FINAL)
// ==========================================================

// --- Fungsi Global (Didefinisikan di dalam DOMContentLoaded) ---
document.addEventListener('DOMContentLoaded', function() {
    const cartItemsContainer = document.querySelector('.cart-items');
    if (!cartItemsContainer) return; // Keluar jika bukan halaman keranjang
    console.log("Menjalankan script keranjang.js (terintegrasi)");

    // Fungsi komunikasi ke backend
    async function updateKeranjangServerJS_Keranjang(data) {
        try {
            // Path dari JSUser/keranjang.js ke MainUser/proses_cart.php
            const response = await fetch('proses_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) { /* ... error handling ... */ return null; }
             try {
                return await response.json();
            } catch (jsonError) { /* ... error handling ... */ return null; }
        } catch (error) { /* ... error handling ... */ return null; }
    }

    // Fungsi notifikasi
    function showNotification_Keranjang(message) { /* ... (kode notifikasi) ... */ }

    // Fungsi update tampilan keranjang (Versi Hapus & Gambar Ulang)
    function updateTampilanKeranjangJS(dataFromServer) {
        if (!dataFromServer || dataFromServer.keranjang === undefined) { /* ... */ return; }
        const { total_items, keranjang } = dataFromServer;
        const summaryContainer = document.querySelector('.cart-summary');
        const cartContainerElement = document.querySelector('.cart-container');
        if (!summaryContainer || !cartContainerElement) { /* ... */ return; }

        cartItemsContainer.innerHTML = ''; // Kosongkan

        if (total_items === 0) {
            cartContainerElement.innerHTML = `
                <div class="empty-cart">
                     <div class="empty-cart-icon">🛒</div>
                     <h2>Keranjang Anda Kosong</h2>
                     <p>Silakan tambahkan menu favorit Anda.</p>
                     <a href="index.php" class="back-btn">Kembali ke Menu</a>
                 </div>`;
            return;
        }

        let calculatedTotalPrice = 0;
        Object.values(keranjang).forEach((itemData, index) => {
             if (!itemData || !itemData.id /* ... */) { /* ... */ return; }

            const id_menu = itemData.id;
            const qty = parseInt(itemData.quantity) || 0;
            const harga = parseFloat(itemData.price) || 0;
            const subtotal = qty * harga;
            calculatedTotalPrice += subtotal;

            const itemHTML = `
             <div class="cart-item" data-index="${index}" data-id-menu="${id_menu}">
             <img src="${IMAGE_BASE_PATH + itemData.gambar}" alt="${itemData.name}" class="item-image">
                        <div class="item-info">
                        <div class="item-name">${itemData.name}</div>
                        <div class="item-price">Rp ${harga.toLocaleString('id-ID')}</div>
                    </div>
                    <div class="item-controls">
                        <div class="qty-control">
                            <button class="qty-btn qty-minus" data-index="${index}" ${qty <= 1 ? 'disabled' : ''}>−</button>
                            <span class="qty-display">${qty}</span>
                            <button class="qty-btn qty-plus" data-index="${index}">+</button>
                        </div>
                        <div class="subtotal">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        <button class="remove-btn" data-index="${index}">🗑️ Hapus</button>
                    </div>
                </div>
            `;
            cartItemsContainer.innerHTML += itemHTML;
        });

        // Update Total di Summary
        const tax = calculatedTotalPrice * 0.1;
        const totalWithTax = calculatedTotalPrice * 1.1;
        const subtotalSpan = summaryContainer.querySelector('#summarySubtotal');
        const taxSpan = summaryContainer.querySelector('#summaryTax');
        const totalSpan = summaryContainer.querySelector('#summaryTotal');

        if(subtotalSpan) subtotalSpan.textContent = `Rp ${calculatedTotalPrice.toLocaleString('id-ID')}`;
        if(taxSpan) taxSpan.textContent = `Rp ${tax.toLocaleString('id-ID')}`;
        if(totalSpan) totalSpan.textContent = `Rp ${totalWithTax.toLocaleString('id-ID')}`;
    }


    // --- Event Listener Utama ---
    cartItemsContainer.addEventListener('click', async (e) => {
        const target = e.target;
        const itemElement = target.closest('.cart-item');
        if (!itemElement || !itemElement.dataset.idMenu) return;
        const id_menu = itemElement.dataset.idMenu;

        // Logika tombol +/-
        if (target.classList.contains('qty-btn')) {
            const action = target.classList.contains('qty-plus') ? 'tambah' : 'kurang';
            const response = await updateKeranjangServerJS_Keranjang({ aksi: action, id_menu: id_menu });
            if (response) updateTampilanKeranjangJS(response);
        }

        // Logika tombol Hapus
        if (target.classList.contains('remove-btn')) {
             if (!confirm('Apakah Anda yakin ingin menghapus item ini?')) return;
             const response = await updateKeranjangServerJS_Keranjang({
                 aksi: 'kurang',
                 id_menu: id_menu,
                 force_delete: true
             });
             if (response) {
                 showNotification_Keranjang('Item berhasil dihapus');
                 updateTampilanKeranjangJS(response);
             }
        }
    });

}); // Akhir DOMContentLoaded

// Fungsi checkout (dari temanmu - global)
function checkout() {
    window.location.href = 'pembayaran.php';
}