package com.quatre.backend.controller;

import com.quatre.backend.model.Admin;
import com.quatre.backend.repository.AdminRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder; // Import untuk Hashing
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/admins")
@CrossOrigin(origins = "*") // Agar bisa diakses dari PHP/Frontend manapun
public class AdminController {

    @Autowired
    private AdminRepository adminRepository;

    // Alat untuk enkripsi/hashing password
    private BCryptPasswordEncoder passwordEncoder = new BCryptPasswordEncoder();

    // ==========================================
    // 1. GET ALL ADMINS (Lihat semua admin) - INI YANG TADI HILANG
    // ==========================================
    @GetMapping
    public List<Admin> getAllAdmins() {
        return adminRepository.findAll();
    }

    // ==========================================
    // 2. ADD ADMIN (Tambah admin baru dengan Hashing)
    // ==========================================
    @PostMapping("/add")
    public Admin createAdmin(@RequestBody Admin admin) {
        // Generate ID otomatis jika kosong (misal: ad123)
        if (admin.getIdAdmin() == null || admin.getIdAdmin().isEmpty()) {
             String randomId = "ad" + (int)(Math.random() * 1000);
             admin.setIdAdmin(randomId);
        }

        // ENKRIPSI PASSWORD SEBELUM SIMPAN
        // Mengubah "rahasia123" menjadi "$2a$10$xd....."
        String hashedPassword = passwordEncoder.encode(admin.getPassword());
        admin.setPassword(hashedPassword);

        return adminRepository.save(admin);
    }

    // ==========================================
    // 3. UPDATE ADMIN (Edit admin dengan cek password)
    // ==========================================
    @PutMapping("/update/{id}")
    public ResponseEntity<Admin> updateAdmin(@PathVariable String id, @RequestBody Admin adminDetails) {
        Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan dengan id: " + id));

        admin.setNama(adminDetails.getNama());
        admin.setEmail(adminDetails.getEmail());
        admin.setUsername(adminDetails.getUsername());
        admin.setJabatan(adminDetails.getJabatan());

        // Cek: Apakah user mengirim password baru?
        // Kalau field password di JSON tidak kosong, berarti dia mau ganti password
        if (adminDetails.getPassword() != null && !adminDetails.getPassword().isEmpty()) {
            String hashedPassword = passwordEncoder.encode(adminDetails.getPassword());
            admin.setPassword(hashedPassword);
        }

        Admin updatedAdmin = adminRepository.save(admin);
        return ResponseEntity.ok(updatedAdmin);
    }

    // ==========================================
    // 4. DELETE ADMIN (Hapus admin)
    // ==========================================
    // Ubah method DELETE menjadi SOFT DELETE
    @DeleteMapping("/delete/{id}")
    public ResponseEntity<?> deleteAdmin(@PathVariable String id) {
        try {
            Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan"));
        
        // Cek jika admin mencoba menghapus dirinya sendiri (Logic tambahan opsional)
        // ...

        // Lakukan Soft Delete
            admin.setStatusAdmin("dihapus"); 
            adminRepository.save(admin); // Simpan perubahan status
        
            Map<String, Boolean> response = new HashMap<>();
            response.put("deleted", Boolean.TRUE);
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            return ResponseEntity.status(500).body("Gagal menghapus admin: " + e.getMessage());
        }
    }

    // ==========================================
    // 5. LOGIN CHECK (Cek password hash)
    // ==========================================
    @PostMapping("/login")
    public ResponseEntity<Map<String, Object>> loginAdmin(@RequestBody Admin loginData) {
        Admin admin = adminRepository.findByUsername(loginData.getUsername())
                .orElseThrow(() -> new RuntimeException("Username tidak ditemukan"));

        Map<String, Object> response = new HashMap<>();
        
        // Cek kecocokan password mentah (inputan user) dengan password hash (di database)
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