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
        // 1. Cari admin di database kamu
        Admin admin = adminRepository.findByUsername(username);
        
        if (admin == null) {
            throw new UsernameNotFoundException("User tidak ditemukan: " + username);
        }

        // 2. Return object User milik Spring Security (bukan Admin model kamu)
        return User.builder()
                .username(admin.getUsername())
                .password(admin.getPassword()) // Password harus sudah ter-hash BCrypt di DB
                .roles(admin.getJabatan()) // Jabatan jadi Role
                .build();
    }
}