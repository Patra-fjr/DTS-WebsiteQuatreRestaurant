package com.quatre.backend.service;

import com.quatre.backend.model.Admin;
import com.quatre.backend.repository.AdminRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;

@Service
public class MyUserDetailsService implements UserDetailsService {

    @Autowired
    private AdminRepository adminRepository;

    @Override
    public UserDetails loadUserByUsername(String username) throws UsernameNotFoundException {
        // 1. Cari admin di database
        Admin admin = adminRepository.findByUsername(username);
        
        // 2. Cek apakah user ada?
        if (admin == null) {
            throw new UsernameNotFoundException("User tidak ditemukan: " + username);
        }

        // --- 3. FILTER SOFT DELETE (PENTING!) ---
        // Jika status admin adalah 'dihapus', kita tolak loginnya
        // Kita pakai equalsIgnoreCase biar aman (Dihapus/dihapus sama saja)
        if ("dihapus".equalsIgnoreCase(admin.getStatusAdmin())) {
            throw new UsernameNotFoundException("Akun ini sudah dinonaktifkan/dihapus.");
        }

        // 4. Return object User jika aman
        return User.builder()
                .username(admin.getUsername())
                .password(admin.getPassword()) 
                .roles(admin.getJabatan()) 
                .build();
    }
}