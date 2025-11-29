// Tunggu sampai seluruh konten HTML selesai dimuat
document.addEventListener('DOMContentLoaded', function() {

    // ==========================================================
    // BAGIAN 1: Ambil semua elemen yang dibutuhkan (Selektor)
    // ==========================================================
    const logoutBtn = document.getElementById('logout-btn');
    const logoutPopup = document.getElementById('logout-popup');
    const cancelLogoutBtn = document.getElementById('cancel-logout-btn');
    
    // Elemen Pop-up Pembayaran
    const paymentPopup = document.getElementById('payment-popup');
    const cancelPaymentBtn = document.getElementById('cancel-payment-btn');
    const openPaymentButtons = document.querySelectorAll('.open-payment-popup');
    
    // Elemen Form Pembayaran (ID dari Perbaikan HTML)
    const orderIdInput = document.getElementById('id-order-input');
    const tempMejaIdInput = document.getElementById('temp-meja-id-input');
    const submitPaymentBtn = document.getElementById('submit-payment-btn'); 
    const paymentMethodSelect = document.getElementById('metode_pembayaran');
    const adminIdInput = document.getElementById('id-admin-input');

// -------------------------------------------------------------

    // ==========================================================
    // BAGIAN 2: LOGIKA LOGOUT (Dari Kode Anda)
    // ==========================================================
    if (logoutBtn && logoutPopup && cancelLogoutBtn) {
        logoutBtn.addEventListener('click', function (event) {
            event.preventDefault(); 
            logoutPopup.style.display = 'flex';
        });

        cancelLogoutBtn.addEventListener('click', function () {
            logoutPopup.style.display = 'none';
        });

        logoutPopup.addEventListener('click', function(event) {
            if (event.target === logoutPopup) {
                logoutPopup.style.display = 'none';
            }
        });
    }

// -------------------------------------------------------------

    // ==========================================================
    // BAGIAN 3: LOGIKA POPUP PEMBAYARAN (Tampil & Isi Data)
    // ==========================================================
    if (paymentPopup && cancelPaymentBtn && openPaymentButtons.length > 0 && orderIdInput && tempMejaIdInput) {
        
        // Listener untuk Tombol "Bayar" di Tabel
        openPaymentButtons.forEach(button => {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                
                // Ambil data ID Order dan ID Meja
                const orderId = this.dataset.orderid;
                const mejaId = this.dataset.mejaid; 

                // Masukkan nilai ke hidden input di pop-up
                orderIdInput.value = orderId;
                tempMejaIdInput.value = mejaId; 
                
                paymentPopup.style.display = "flex";
            });
        });
        
        // Listener untuk Tombol "Batal"
        cancelPaymentBtn.addEventListener("click", () => {
            paymentPopup.style.display = "none";
        });

        // Listener untuk Klik Area Gelap
        paymentPopup.addEventListener('click', function(event) {
            if (event.target === paymentPopup) {
                paymentPopup.style.display = 'none';
            }
        });
    }

// -------------------------------------------------------------

    // ==========================================================
    // BAGIAN 4: LOGIKA SUBMIT PEMBAYARAN (AJAX Transaction)
    // ==========================================================
    if (submitPaymentBtn && adminIdInput && paymentMethodSelect) {
        
        // Listener pada TOMBOL KONFIRMASI BAYAR
        submitPaymentBtn.addEventListener('click', async function(event) {
            
            // Ambil semua nilai yang dibutuhkan dari pop-up
            const orderId = orderIdInput.value; 
            const mejaId = tempMejaIdInput.value; 
            const paymentMethod = paymentMethodSelect.value; 
            const adminId = adminIdInput.value; 

            if (!orderId || !mejaId || !paymentMethod || !adminId) {
                alert('Data pembayaran tidak lengkap. Coba ulangi atau refresh halaman.');
                return;
            }

            // Nonaktifkan tombol dan berikan feedback visual
            submitPaymentBtn.disabled = true;
            submitPaymentBtn.textContent = 'Memproses...';

            const dataToSend = {
                id_order: orderId,
                id_meja: mejaId,
                metode_pembayaran: paymentMethod,
                id_admin: adminId 
            };
            
            console.log('Mengirim pembayaran ke server:', dataToSend);
            
            try {
                // Target: proses_pembayaran.php (di folder admin yang sama)
                const response = await fetch('proses_pembayaran.php', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataToSend)
                });

                const responseText = await response.text();
                
                if (!response.ok) {
                    throw new Error(`Server error (${response.status}): ${responseText}`);
                }

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    console.error("Gagal parse JSON:", jsonError, "Teks mentah:", responseText);
                    throw new Error("Gagal membaca respons server.");
                }

                if (result.success) {
                    alert('Pembayaran berhasil dicatat! ' + result.message);
                    document.getElementById('payment-popup').style.display = 'none'; 
                    window.location.reload(); // Segarkan tabel
                } else {
                    alert('Gagal mencatat pembayaran: ' + result.message);
                }

            } catch (error) {
                console.error('Fetch Error:', error);
                alert('Terjadi kesalahan koneksi atau server: ' + error.message);
            } finally {
                // Aktifkan kembali tombol di segala kondisi kecuali setelah reload sukses
                submitPaymentBtn.disabled = false;
                submitPaymentBtn.textContent = 'Konfirmasi Bayar';
            }
        });
    }

});

// ==========================================================
    // BAGIAN 5: FIX BUG BACK BUTTON (METODE JEBAKAN HISTORY)
    // ==========================================================
    
    // 1. Paksa browser reload jika mendeteksi load dari cache (BFCache)
    window.addEventListener("pageshow", function (event) {
        var historyTraversal = event.persisted || 
                               (typeof window.performance != "undefined" && 
                                window.performance.navigation.type === 2);
        if (historyTraversal) {
            window.location.reload();
        }
    });

    // 2. Manipulasi History (Agar tombol Back jadi tidak berguna)
    // Ini akan menumpuk history baru setiap kali halaman dimuat
    window.history.pushState(null, "", window.location.href);
    
    // Saat tombol Back ditekan, fungsi ini akan jalan
    window.onpopstate = function () {
        // Dorong user maju lagi (mencegah mundur)
        window.history.pushState(null, "", window.location.href);
        // Paksa reload agar script PHP 'satpam' bekerja
        window.location.reload();
    };
// Akhir dari DOMContentLoaded