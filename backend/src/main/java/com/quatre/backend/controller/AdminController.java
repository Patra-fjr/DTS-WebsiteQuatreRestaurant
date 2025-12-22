package com.quatre.backend.controller;

import com.quatre.backend.config.JwtUtil;
import com.quatre.backend.model.Admin;
import com.quatre.backend.repository.AdminRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.Authentication;
import org.springframework.security.crypto.password.PasswordEncoder; 
import org.springframework.web.bind.annotation.*;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/admins")
@CrossOrigin(origins = "*") 
public class AdminController {

    @Autowired
    private AdminRepository adminRepository;

    @Autowired
    private AuthenticationManager authenticationManager; // Untuk Login JWT

    @Autowired
    private JwtUtil jwtUtil; // Untuk bikin Token

    @Autowired
    private PasswordEncoder passwordEncoder; // WAJIB ADA untuk Create/Update admin

    // ==========================================
    // 1. LOGIN ADMIN (Versi JWT - FINAL)
    // ==========================================
    @PostMapping("/login")
    public ResponseEntity<?> login(@RequestBody Map<String, String> loginData) {
        String username = loginData.get("username");
        String password = loginData.get("password");

        try {
            // 1. Cek User & Password pakai Spring Security
            Authentication authentication = authenticationManager.authenticate(
                new UsernamePasswordAuthenticationToken(username, password)
            );

            // 2. Kalau lolos, Generate Token
            if (authentication.isAuthenticated()) {
                String token = jwtUtil.generateToken(username);
                
                // Ambil data admin lengkap buat di-return ke PHP
                Admin adminData = adminRepository.findByUsername(username);
                
                Map<String, Object> response = new HashMap<>();
                response.put("status", "success");
                response.put("token", token); // Token JWT
                response.put("data", adminData);
                
                return ResponseEntity.ok(response);
            } else {
                return ResponseEntity.status(401).body("Invalid Credentials");
            }

        } catch (Exception e) {
            return ResponseEntity.status(401).body("Login Gagal: Username atau Password Salah");
        }
    }

    // ==========================================
    // 2. GET ALL ADMINS (Lihat semua admin)
    // ==========================================
    @GetMapping
    public List<Admin> getAllAdmins() {
        return adminRepository.findAll();
    }

    // ==========================================
    // 3. ADD ADMIN (Tambah admin baru + Hash Password)
    // ==========================================
    @PostMapping("/add")
    public Admin createAdmin(@RequestBody Admin admin) {
        // Generate ID otomatis jika kosong
        if (admin.getIdAdmin() == null || admin.getIdAdmin().isEmpty()) {
             String randomId = "ad" + (int)(Math.random() * 10000);
             admin.setIdAdmin(randomId);
        }

        // Default status aktif jika belum diisi
        if (admin.getStatusAdmin() == null) {
            admin.setStatusAdmin("aktif");
        }

        // HASH PASSWORD SEBELUM SIMPAN
        String hashedPassword = passwordEncoder.encode(admin.getPassword());
        admin.setPassword(hashedPassword);

        return adminRepository.save(admin);
    }

    // ==========================================
    // 4. UPDATE ADMIN (Edit admin + Cek Password)
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
        if (adminDetails.getPassword() != null && !adminDetails.getPassword().isEmpty()) {
            String hashedPassword = passwordEncoder.encode(adminDetails.getPassword());
            admin.setPassword(hashedPassword);
        }

        Admin updatedAdmin = adminRepository.save(admin);
        return ResponseEntity.ok(updatedAdmin);
    }

    // ==========================================
    // 5. DELETE ADMIN (Soft Delete)
    // ==========================================
    @DeleteMapping("/delete/{id}")
    public ResponseEntity<?> deleteAdmin(@PathVariable String id) {
        try {
            Admin admin = adminRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Admin tidak ditemukan"));
        
            // Lakukan Soft Delete (Ubah status jadi 'dihapus')
            admin.setStatusAdmin("dihapus"); 
            adminRepository.save(admin); 
        
            Map<String, Boolean> response = new HashMap<>();
            response.put("deleted", Boolean.TRUE);
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            return ResponseEntity.status(500).body("Gagal menghapus admin: " + e.getMessage());
        }
    }
}