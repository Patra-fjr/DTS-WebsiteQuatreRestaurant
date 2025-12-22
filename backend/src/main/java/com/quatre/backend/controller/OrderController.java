package com.quatre.backend.controller;

import com.quatre.backend.dto.OrderRequest;
import com.quatre.backend.model.DetailOrders;
import com.quatre.backend.model.Meja;
import com.quatre.backend.model.Orders;
import com.quatre.backend.repository.DetailOrdersRepository;
import com.quatre.backend.repository.MejaRepository;
import com.quatre.backend.repository.OrdersRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.*;

import java.math.BigDecimal; // <--- WAJIB IMPORT
import java.time.LocalDate;
import java.time.LocalTime;
import java.util.*;

@RestController
@RequestMapping("/api/orders")
@CrossOrigin(origins = "*")
public class OrderController {

    @Autowired private OrdersRepository ordersRepository;
    @Autowired private DetailOrdersRepository detailOrdersRepository;
    @Autowired private MejaRepository mejaRepository;

    @PostMapping("/create")
    @Transactional
    public ResponseEntity<?> createOrder(@RequestBody OrderRequest request) {
        try {
            String idOrder = "ORD" + (100000 + new Random().nextInt(900000));
            
            // 1. Inisialisasi BigDecimal (Bukan 0 biasa)
            BigDecimal totalHarga = BigDecimal.ZERO;
            
            // 2. Hitung Total Harga (Looping)
            for (OrderRequest.OrderItem item : request.getItems()) {
                // Konversi harga (double) dan quantity (int) ke BigDecimal dulu
                BigDecimal hargaSatuan = BigDecimal.valueOf(item.getHargaSatuan());
                BigDecimal qty = BigDecimal.valueOf(item.getQuantity());
                
                // Rumus: totalHarga = totalHarga + (harga * qty)
                totalHarga = totalHarga.add(hargaSatuan.multiply(qty));
            }

            // 3. Hitung Pajak (Total * 1.1)
            // Kita pakai string "1.1" agar presisi desimalnya akurat
            BigDecimal pajak = new BigDecimal("1.1");
            BigDecimal totalFinal = totalHarga.multiply(pajak);

            // 4. Simpan ke Orders
            Orders order = new Orders();
            order.setIdOrder(idOrder);
            order.setNamaCustomer(request.getNamaCustomer());
            order.setNomorTelepon(request.getNomorTelepon());
            order.setIdMeja(request.getIdMeja());
            order.setTanggalOrder(LocalDate.now());
            order.setWaktuOrder(LocalTime.now());
            order.setTotalHarga(totalFinal); // <--- Sekarang sudah cocok (BigDecimal)
            order.setStatusOrder("proses");
            ordersRepository.save(order);

            // 5. Simpan Detail Orders
            for (OrderRequest.OrderItem item : request.getItems()) {
                DetailOrders detail = new DetailOrders();
                detail.setIdOrder(idOrder);
                detail.setIdMenu(item.getIdMenu());
                detail.setQuantity(item.getQuantity());
                
                // Hitung Subtotal per Item (Harga * Qty)
                BigDecimal hargaSatuan = BigDecimal.valueOf(item.getHargaSatuan());
                BigDecimal qty = BigDecimal.valueOf(item.getQuantity());
                BigDecimal subtotal = hargaSatuan.multiply(qty);

                detail.setSubtotal(subtotal); // <--- Sekarang sudah cocok (BigDecimal)
                detailOrdersRepository.save(detail);
            }

            // 6. Update Status Meja
            Meja meja = mejaRepository.findById(request.getIdMeja()).orElse(null);
            if (meja != null) {
                meja.setStatusMeja("tidak tersedia");
                mejaRepository.save(meja);
            }

            Map<String, Object> response = new HashMap<>();
            response.put("status", "success");
            response.put("message", "Pesanan berhasil dibuat");
            response.put("idOrder", idOrder);
            return ResponseEntity.ok(response);

        } catch (Exception e) {
            e.printStackTrace(); // Biar errornya kelihatan di console log
            return ResponseEntity.status(500).body("Gagal order: " + e.getMessage());
        }
    }
    
    @GetMapping
    public List<Orders> getAllOrders() {
        return ordersRepository.findAll();
    }
}