package com.quatre.backend.repository;

import com.quatre.backend.model.Meja;
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface MejaRepository extends JpaRepository<Meja, String> {
    // Fitur tambahan: Cari meja yang "tersedia" saja (Opsional, buat filter di frontend)
    List<Meja> findByStatusMeja(String statusMeja);
}