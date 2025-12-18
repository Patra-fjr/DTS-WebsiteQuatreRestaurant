package com.quatre.backend.controller;

import com.quatre.backend.model.Menu;
import com.quatre.backend.repository.MenuRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/api/menus")
public class MenuController {

    @Autowired
    private MenuRepository menuRepository;

    // Endpoint 1: Ambil semua menu
    // Akses: GET http://localhost:8080/api/menus
    @GetMapping
    public List<Menu> getAllMenus() {
        return menuRepository.findAll();
    }

    // Endpoint 2: Ambil menu berdasarkan ID
    // Akses: GET http://localhost:8080/api/menus/menu002
    @GetMapping("/{id}")
    public Optional<Menu> getMenuById(@PathVariable String id) {
        return menuRepository.findById(id);
    }
}