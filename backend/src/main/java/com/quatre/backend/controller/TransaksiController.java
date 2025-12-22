package com.quatre.backend.controller;

import com.quatre.backend.model.Meja;
import com.quatre.backend.model.Orders;
import com.quatre.backend.model.Transaksi;
import com.quatre.backend.repository.MejaRepository;
import com.quatre.backend.repository.OrdersRepository;
import com.quatre.backend.repository.TransaksiRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.*;
import java.time.LocalDate;
import java.time.LocalTime;
import java.util.*;

@RestController
@RequestMapping("/api/transaksi")
@CrossOrigin(origins = "*")
public class TransaksiController {

    @Autowired private TransaksiRepository transaksiRepository;
    @Autowired private OrdersRepository ordersRepository;
    @Autowired private MejaRepository mejaRepository;

    @PostMapping("/pay")
    @Transactional
    public ResponseEntity<?> bayarTransaksi(@RequestBody Map<String, String> request) {
        String idOrder = request.get("idOrder");
        String idAdmin = request.get("idAdmin");
        String metode = request.get("metodeTransaksi");

        try {
            Orders order = ordersRepository.findById(idOrder)
                    .orElseThrow(() -> new RuntimeException("Order tidak ditemukan"));

            Transaksi trx = new Transaksi();
            String idTrx = "TR" + (100000 + new Random().nextInt(900000));
            trx.setIdTransaksi(idTrx);
            trx.setIdOrder(idOrder);
            trx.setIdAdmin(idAdmin);
            trx.setTanggalTransaksi(LocalDate.now());
            trx.setWaktuTransaksi(LocalTime.now());
            trx.setMetodeTransaksi(metode);
            trx.setStatusTransaksi("Selesai");
            transaksiRepository.save(trx);

            order.setStatusOrder("selesai");
            ordersRepository.save(order);

            Meja meja = mejaRepository.findById(order.getIdMeja()).orElse(null);
            if (meja != null) {
                meja.setStatusMeja("tersedia");
                mejaRepository.save(meja);
            }

            Map<String, Object> response = new HashMap<>();
            response.put("status", "success");
            response.put("message", "Pembayaran berhasil!");
            response.put("idTransaksi", idTrx);
            return ResponseEntity.ok(response);

        } catch (Exception e) {
            return ResponseEntity.status(500).body("Gagal transaksi: " + e.getMessage());
        }
    }
}