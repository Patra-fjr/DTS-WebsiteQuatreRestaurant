package com.quatre.backend.repository;

import com.quatre.backend.model.Admin;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface AdminRepository extends JpaRepository<Admin, String> {
    // Cari berdasarkan username (untuk login)
    Optional<Admin> findByUsername(String username);
}