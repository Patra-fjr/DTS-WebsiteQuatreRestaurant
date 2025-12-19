package com.quatre.backend.controller;

import com.quatre.backend.model.Menu;
import com.quatre.backend.repository.MenuRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.http.ResponseEntity;

import java.util.Map;
import java.util.HashMap;
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

    // CREATE: Tambah Menu Baru
    @PostMapping("/add")
    public Menu addMenu(@RequestBody Menu menu) {
        return menuRepository.save(menu);
    }

    // UPDATE: Edit Menu berdasarkan ID
    @PutMapping("/update/{id}")
    public ResponseEntity<Menu> updateMenu(@PathVariable String id, @RequestBody Menu menuDetails) {
        Menu menu = menuRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Menu tidak ditemukan"));
    // Update data
    menu.setNamaMenu(menuDetails.getNamaMenu());
    menu.setHarga(menuDetails.getHarga());
    Menu updatedMenu = menuRepository.save(menu);
        return ResponseEntity.ok(updatedMenu);
    }

    // DELETE: Hapus Menu
    @DeleteMapping("/delete/{id}")
    public ResponseEntity<Map<String, Boolean>> deleteMenu(@PathVariable String id) {
        Menu menu = menuRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Menu tidak ditemukan"));
    menuRepository.delete(menu);
    
    Map<String, Boolean> response = new HashMap<>();
    response.put("deleted", Boolean.TRUE);
        return ResponseEntity.ok(response);
    }
}