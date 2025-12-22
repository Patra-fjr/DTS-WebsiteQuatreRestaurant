package com.quatre.backend.repository;
import com.quatre.backend.model.Transaksi;
import org.springframework.data.jpa.repository.JpaRepository;
public interface TransaksiRepository extends JpaRepository<Transaksi, String> {}