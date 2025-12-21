package com.quatre.backend.repository;

import com.quatre.backend.model.Admin;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface AdminRepository extends JpaRepository<Admin, String> {
    // Hapus 'Optional<>' agar langsung mengembalikan objek Admin
    // Ini supaya cocok dengan AdminController baris: Admin adminData = ...
    Admin findByUsername(String username); 
}