package com.quatre.backend.controller;

import com.quatre.backend.model.Meja;
import com.quatre.backend.repository.MejaRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/meja")
@CrossOrigin(origins = "*") // Penting! Supaya PHP bisa akses tanpa diblokir
public class MejaController {

    @Autowired
    private MejaRepository mejaRepository;

    // 1. Ambil Semua Data Meja
    @GetMapping
    public List<Meja> getAllMeja() {
        return mejaRepository.findAll();
    }

    // 2. Ambil Meja Berdasarkan Status (Misal: cari yang 'tersedia' saja)
    @GetMapping("/status/{status}")
    public List<Meja> getMejaByStatus(@PathVariable String status) {
        return mejaRepository.findByStatusMeja(status);
    }

    // 3. Tambah Meja Baru (Buat Admin nanti)
    @PostMapping
    public Meja createMeja(@RequestBody Meja meja) {
        return mejaRepository.save(meja);
    }
}