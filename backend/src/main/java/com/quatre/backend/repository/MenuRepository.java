package com.quatre.backend.repository;

import com.quatre.backend.model.Menu;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface MenuRepository extends JpaRepository<Menu, String> {
    // Kosong saja, Spring Boot otomatis menyediakan fungsi findAll(), findById(), save(), dll.
}