// ==========================================================
// KODE UNTUK HALAMAN pembayaran.js (TERINTEGRASI - FINAL)
// ==========================================================
document.addEventListener('DOMContentLoaded', () => {
    const payButton = document.querySelector('.pay-button'); // Cari tombol bayar
    if (payButton) {
        console.log("Menjalankan script untuk pembayaran.js");
        payButton.addEventListener('click', processPayment);
    }
    const orderTypeDiv = document.querySelector('.order-type');
    if (orderTypeDiv) {
        orderTypeDiv.addEventListener('click', toggleOrderType);
    }
});

let orderType = 'dine-in'; // Variabel global

function toggleOrderType() {
    const types = {
        'dine-in': { text: 'Makan di tempat', next: 'takeaway' },
        'takeaway': { text: 'Bungkus', next: 'dine-in' }
    };
    orderType = types[orderType].next;
    document.getElementById('orderTypeText').textContent = types[orderType].text;
}

// Fungsi Proses Pembayaran
async function processPayment(event) {
    event.preventDefault(); 
    const fullNameInput = document.getElementById('fullName');
    const phoneInput = document.getElementById('phone');
    const payButton = document.querySelector('.pay-button');

    const fullName = fullNameInput ? fullNameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';

    if (!fullName) {
        alert('Mohon isi nama lengkap Anda');
        if(fullNameInput) fullNameInput.focus();
        return;
    }

    if(payButton) payButton.disabled = true;
    if(payButton) payButton.textContent = 'Memproses...';

    const orderData = {
        name: fullName,
        phone: phone,
        orderType: orderType
    };

    console.log('Mengirim data order ke simpan_order.php:', orderData);

    try {
        // Path dari JSUser/pembayaran.js ke MainUser/simpan_order.php
        const response = await fetch('simpan_order.php', { 
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });

        const responseText = await response.text();
        console.log("Response Teks Mentah dari Server:", responseText);

        if (!response.ok) { throw new Error(`Server error (${response.status}): ${responseText}`); }

        let result = {};
        try {
             result = JSON.parse(responseText);
        } catch (jsonError) { throw new Error("Gagal membaca respons server."); }

        if (result.success) {
            console.log('Pesanan berhasil disimpan, redirecting ke closing.php...');
            window.location.href = 'closing.php'; // Arahkan ke sukses
        } else {
            alert(`Gagal menyimpan pesanan: ${result.message}`);
             if(payButton) payButton.disabled = false;
             if(payButton) payButton.textContent = 'Bayar';
        }
    } catch (error) {
        console.error('Error saat processPayment:', error);
        alert('Terjadi kesalahan saat mengirim pesanan: ' + error.message);
         if(payButton) payButton.disabled = false;
         if(payButton) payButton.textContent = 'Bayar';
    }
}