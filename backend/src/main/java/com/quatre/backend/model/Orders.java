package com.quatre.backend.model;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.LocalTime;

@Entity
@Table(name = "orders")
public class Orders {
    @Id
    @Column(name = "id_order", length = 20)
    private String idOrder;

    @Column(name = "id_meja")
    private String idMeja;

    @Column(name = "nama_customer")
    private String namaCustomer;

    @Column(name = "nomor_telepon")
    private String nomorTelepon;

    @Column(name = "tanggal_order")
    private LocalDate tanggalOrder;

    @Column(name = "waktu_order")
    private LocalTime waktuOrder;

    @Column(name = "total_harga")
    private Double totalHarga;

    @Column(name = "status_order")
    private String statusOrder; // 'proses', 'selesai'

    // Getters & Setters
    public String getIdOrder() { return idOrder; }
    public void setIdOrder(String idOrder) { this.idOrder = idOrder; }
    
    public String getIdMeja() { return idMeja; }
    public void setIdMeja(String idMeja) { this.idMeja = idMeja; }
    
    public String getNamaCustomer() { return namaCustomer; }
    public void setNamaCustomer(String namaCustomer) { this.namaCustomer = namaCustomer; }
    
    public String getNomorTelepon() { return nomorTelepon; }
    public void setNomorTelepon(String nomorTelepon) { this.nomorTelepon = nomorTelepon; }
    
    public LocalDate getTanggalOrder() { return tanggalOrder; }
    public void setTanggalOrder(LocalDate tanggalOrder) { this.tanggalOrder = tanggalOrder; }
    
    public LocalTime getWaktuOrder() { return waktuOrder; }
    public void setWaktuOrder(LocalTime waktuOrder) { this.waktuOrder = waktuOrder; }
    
    public Double getTotalHarga() { return totalHarga; }
    public void setTotalHarga(Double totalHarga) { this.totalHarga = totalHarga; }
    
    public String getStatusOrder() { return statusOrder; }
    public void setStatusOrder(String statusOrder) { this.statusOrder = statusOrder; }
}