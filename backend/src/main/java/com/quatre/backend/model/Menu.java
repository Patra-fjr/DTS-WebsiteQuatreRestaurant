package com.quatre.backend.model;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "menu")
public class Menu {

    @Id
    @Column(name = "id_menu")
    private String idMenu; // Sesuai tipe varchar(10) di database Anda

    @Column(name = "id_kategori")
    private String idKategori;

    @Column(name = "nama_menu")
    private String namaMenu;

    private BigDecimal harga;

    @Column(name = "status_menu")
    private String statusMenu; // enum di database bisa dianggap String di Java sederhana

    private String gambar;
    private String deskripsi;

    // Getter dan Setter (Wajib ada, bisa generate otomatis di IDE)
    public String getIdMenu() { return idMenu; }
    public void setIdMenu(String idMenu) { this.idMenu = idMenu; }
    
    public String getNamaMenu() { return namaMenu; }
    public void setNamaMenu(String namaMenu) { this.namaMenu = namaMenu; }

    public BigDecimal getHarga() { return harga; }
    public void setHarga(BigDecimal harga) { this.harga = harga; }

    // ... (Generate getter/setter untuk field lain jika perlu)
}