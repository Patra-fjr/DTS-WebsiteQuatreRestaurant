package com.quatre.backend.controller;

import com.quatre.backend.model.Admin;
import com.quatre.backend.repository.AdminRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

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

    // GET ALL
    @GetMapping
    public List<Admin> getAllAdmins() {
        return adminRepository.findAll();
    }

    // ADD NEW ADMIN
    @PostMapping("/add")
    public Admin createAdmin(@RequestBody Admin admin) {
        // Karena ID bukan auto-increment, kita harus cek:
        // Apakah user mengirim ID? Jika tidak, kita buatkan ID otomatis sederhana
        if (admin.getIdAdmin() == null || admin.getIdAdmin().isEmpty()) {
             // Contoh generate ID: "ad" + 3 angka random (misal: ad592)
             String randomId = "ad" + (int)(Math.random() * 1000);
             admin.setIdAdmin(randomId);
        }
        return adminRepository.save(admin);
    }

    // UPDATE
    @PutMapping("/update/{id}")
    public ResponseEntity<Admin> updateAdmin(@PathVariable String id, @RequestBody Admin adminDetails) {
        Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan dengan id: " + id));

        admin.setNama(adminDetails.getNama());
        admin.setEmail(adminDetails.getEmail());
        admin.setUsername(adminDetails.getUsername());
        admin.setPassword(adminDetails.getPassword());
        admin.setJabatan(adminDetails.getJabatan());

        Admin updatedAdmin = adminRepository.save(admin);
        return ResponseEntity.ok(updatedAdmin);
    }

    // DELETE
    @DeleteMapping("/delete/{id}")
    public ResponseEntity<Map<String, Boolean>> deleteAdmin(@PathVariable String id) {
        Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan dengan id: " + id));

        adminRepository.delete(admin);
        Map<String, Boolean> response = new HashMap<>();
        response.put("deleted", Boolean.TRUE);
        return ResponseEntity.ok(response);
    }

    // LOGIN
    @PostMapping("/login")
    public ResponseEntity<Map<String, Object>> loginAdmin(@RequestBody Admin loginData) {
        Admin admin = adminRepository.findByUsername(loginData.getUsername())
                .orElseThrow(() -> new RuntimeException("Username tidak ditemukan"));

        Map<String, Object> response = new HashMap<>();
        
        // PENTING: Di database kamu password terenkripsi ($2y$10$...).
        // Untuk tahap development ini, login mungkin GAGAL jika kamu input password polos 
        // karena string polos tidak sama dengan hash di database.
        // Nanti kita bahas cara verifikasi password hash ya.
        
        // Sementara kita return datanya dulu untuk cek koneksi:
        response.put("status", "success");
        response.put("data", admin);
        return ResponseEntity.ok(response);
    }
}