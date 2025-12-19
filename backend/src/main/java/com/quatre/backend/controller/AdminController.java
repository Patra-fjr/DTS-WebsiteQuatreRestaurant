package com.quatre.backend.controller;

import com.quatre.backend.model.Admin;
import com.quatre.backend.repository.AdminRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;

import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.UUID;

@RestController
@RequestMapping("/api/admins")
@CrossOrigin(origins = "*")
public class AdminController {

    @Autowired
    private AdminRepository adminRepository;

    // Buat alat untuk hashing
    private BCryptPasswordEncoder passwordEncoder = new BCryptPasswordEncoder();

    // ... (kode GET ALL tetap sama) ...

    // ADD NEW ADMIN (Dengan Hashing)
    @PostMapping("/add")
    public Admin createAdmin(@RequestBody Admin admin) {
        // 1. Generate ID jika kosong
        if (admin.getIdAdmin() == null || admin.getIdAdmin().isEmpty()) {
             String randomId = "ad" + (int)(Math.random() * 1000);
             admin.setIdAdmin(randomId);
        }

        // 2. HASHING PASSWORD (PENTING!)
        // Password asli "rahasia123" diubah jadi "$2a$10$xd....."
        String hashedPassword = passwordEncoder.encode(admin.getPassword());
        admin.setPassword(hashedPassword);

        return adminRepository.save(admin);
    }

    // UPDATE (Dengan Hashing, jika password diganti)
    @PutMapping("/update/{id}")
    public ResponseEntity<Admin> updateAdmin(@PathVariable String id, @RequestBody Admin adminDetails) {
        Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan dengan id: " + id));

        admin.setNama(adminDetails.getNama());
        admin.setEmail(adminDetails.getEmail());
        admin.setUsername(adminDetails.getUsername());
        admin.setJabatan(adminDetails.getJabatan());

        // Cek: User mau ganti password gak?
        // Kalau field password di JSON tidak kosong, berarti dia mau ganti
        if (adminDetails.getPassword() != null && !adminDetails.getPassword().isEmpty()) {
            String hashedPassword = passwordEncoder.encode(adminDetails.getPassword());
            admin.setPassword(hashedPassword);
        }

        Admin updatedAdmin = adminRepository.save(admin);
        return ResponseEntity.ok(updatedAdmin);
    }
    
    // ... (kode DELETE tetap sama) ...

    // LOGIN CHECK (Update Logic Verifikasi)
    @PostMapping("/login")
    public ResponseEntity<Map<String, Object>> loginAdmin(@RequestBody Admin loginData) {
        Admin admin = adminRepository.findByUsername(loginData.getUsername())
                .orElseThrow(() -> new RuntimeException("Username tidak ditemukan"));

        Map<String, Object> response = new HashMap<>();
        
        // Verifikasi Password Hash
        // passwordEncoder.matches(password_mentah, password_database_hash)
        if(passwordEncoder.matches(loginData.getPassword(), admin.getPassword())) {
            response.put("status", "success");
            response.put("message", "Login Berhasil");
            response.put("data", admin);
            return ResponseEntity.ok(response);
        } else {
            response.put("status", "error");
            response.put("message", "Password Salah");
            return ResponseEntity.status(401).body(response);
        }
    }
}