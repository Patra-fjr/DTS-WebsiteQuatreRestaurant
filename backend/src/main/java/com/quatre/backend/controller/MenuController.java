package com.quatre.backend.controller;

import com.quatre.backend.model.Menu;
import com.quatre.backend.repository.MenuRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.http.ResponseEntity;
import org.springframework.http.HttpStatus;

import java.util.Map;
import java.util.HashMap;
import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/api/menus")
@CrossOrigin(origins = "*") // WAJIB: Agar PHP bisa akses method DELETE/PUT
public class MenuController {

    @Autowired
    private MenuRepository menuRepository;

    @GetMapping
    public List<Menu> getAllMenus() {
        return menuRepository.findAll();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Menu> getMenuById(@PathVariable String id) {
        return menuRepository.findById(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }

    @PostMapping("/add")
    public Menu addMenu(@RequestBody Menu menu) {
        // Generate ID Otomatis jika kosong
        if (menu.getIdMenu() == null || menu.getIdMenu().isEmpty()) {
            menu.setIdMenu("men" + (int)(Math.random() * 1000));
        }
        return menuRepository.save(menu);
    }

    @PutMapping("/update/{id}")
    public ResponseEntity<Menu> updateMenu(@PathVariable String id, @RequestBody Menu menuDetails) {
        Menu menu = menuRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Menu tidak ditemukan"));
        
        menu.setNamaMenu(menuDetails.getNamaMenu());
        menu.setHarga(menuDetails.getHarga());
        menu.setGambar(menuDetails.getGambar());
        menu.setDeskripsi(menuDetails.getDeskripsi());
        menu.setIdKategori(menuDetails.getIdKategori());
        menu.setStatusMenu(menuDetails.getStatusMenu());
        
        Menu updatedMenu = menuRepository.save(menu);
        return ResponseEntity.ok(updatedMenu);
    }

    // DELETE: Sekarang menjadi Soft Delete
    @DeleteMapping("/delete/{id}")
    public ResponseEntity<?> deleteMenu(@PathVariable String id) {
        try {
            Menu menu = menuRepository.findById(id)
                .orElseThrow(() -> new RuntimeException("Menu tidak ditemukan"));
        
            // Alih-alih menghapus, kita ubah statusnya
            // Pastikan di database/model kamu mendukung status 'dihapus' atau semacamnya
            menu.setStatusMenu("dihapus"); 
            menuRepository.save(menu);
        
            Map<String, Boolean> response = new HashMap<>();
            response.put("soft_deleted", Boolean.TRUE);
            return ResponseEntity.ok(response);
        } catch (Exception e) {
            return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR).body("Gagal menonaktifkan menu");
        }
    }   
}