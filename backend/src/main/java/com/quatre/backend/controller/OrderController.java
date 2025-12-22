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
            double totalHarga = 0;
            
            for (OrderRequest.OrderItem item : request.getItems()) {
                totalHarga += item.getHargaSatuan() * item.getQuantity();
            }
            double totalFinal = totalHarga * 1.1; // Pajak

            Orders order = new Orders();
            order.setIdOrder(idOrder);
            order.setNamaCustomer(request.getNamaCustomer());
            order.setNomorTelepon(request.getNomorTelepon());
            order.setIdMeja(request.getIdMeja());
            order.setTanggalOrder(LocalDate.now());
            order.setWaktuOrder(LocalTime.now());
            order.setTotalHarga(totalFinal);
            order.setStatusOrder("proses");
            ordersRepository.save(order);

            for (OrderRequest.OrderItem item : request.getItems()) {
                DetailOrders detail = new DetailOrders();
                detail.setIdOrder(idOrder);
                detail.setIdMenu(item.getIdMenu());
                detail.setQuantity(item.getQuantity());
                detail.setSubtotal(item.getHargaSatuan() * item.getQuantity());
                detailOrdersRepository.save(detail);
            }

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
            return ResponseEntity.status(500).body("Gagal order: " + e.getMessage());
        }
    }
    
    @GetMapping
    public List<Orders> getAllOrders() {
        return ordersRepository.findAll();
    }
}