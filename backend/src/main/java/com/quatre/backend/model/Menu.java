package com.quatre.backend.model;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "menu")
public class Menu {

    @Id
    @Column(name = "id_menu", length = 10)
    private String idMenu;

    @Column(name = "nama_menu", nullable = false)
    private String namaMenu;

    @Column(name = "harga", nullable = false)
    private BigDecimal harga;

    // --- TAMBAHAN BARU ---
    @Column(name = "gambar")
    private String gambar;

    @Column(name = "deskripsi", columnDefinition = "TEXT")
    private String deskripsi;

    @Column(name = "id_kategori", nullable = false)
    private String idKategori;

    @Column(name = "status_menu", nullable = false)
    private String statusMenu;
    // ---------------------

    // Constructor Kosong
    public Menu() {}

    // Constructor Lengkap (Update juga constructornya kalau mau, atau pakai Getter/Setter saja)

    // GETTER & SETTER (Wajib Ditambah)
    public String getIdMenu() { return idMenu; }
    public void setIdMenu(String idMenu) { this.idMenu = idMenu; }

    public String getNamaMenu() { return namaMenu; }
    public void setNamaMenu(String namaMenu) { this.namaMenu = namaMenu; }

    public BigDecimal getHarga() { return harga; }
    public void setHarga(BigDecimal harga) { this.harga = harga; }

    // Getter Setter Tambahan
    public String getGambar() { return gambar; }
    public void setGambar(String gambar) { this.gambar = gambar; }

    public String getDeskripsi() { return deskripsi; }
    public void setDeskripsi(String deskripsi) { this.deskripsi = deskripsi; }

    public String getIdKategori() { return idKategori; }
    public void setIdKategori(String idKategori) { this.idKategori = idKategori; }

    public String getStatusMenu() { return statusMenu; }
    public void setStatusMenu(String statusMenu) { this.statusMenu = statusMenu; }
}