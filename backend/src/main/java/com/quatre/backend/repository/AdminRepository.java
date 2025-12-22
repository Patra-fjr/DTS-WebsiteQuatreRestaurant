package com.quatre.backend.repository;

import com.quatre.backend.model.Admin;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

// Hapus import java.util.Optional; karena kita tidak pakai lagi

@Repository
public interface AdminRepository extends JpaRepository<Admin, String> {
    
    // UBAH DARI 'Optional<Admin>' MENJADI 'Admin' BIASA
    Admin findByUsername(String username); 
}