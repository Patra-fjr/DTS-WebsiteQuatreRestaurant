package com.quatre.backend.model;

import jakarta.persistence.*;
import java.time.LocalDate;
import java.time.LocalTime;

@Entity
@Table(name = "transaksi")
public class Transaksi {
    @Id
    @Column(name = "id_transaksi", length = 20)
    private String idTransaksi;

    @Column(name = "id_order")
    private String idOrder;

    @Column(name = "id_admin")
    private String idAdmin;

    @Column(name = "tanggal_transaksi")
    private LocalDate tanggalTransaksi;

    @Column(name = "waktu_transaksi")
    private LocalTime waktuTransaksi;

    @Column(name = "metode_transaksi")
    private String metodeTransaksi;

    @Column(name = "status_transaksi")
    private String statusTransaksi;

    // Getters & Setters
    public String getIdTransaksi() { return idTransaksi; }
    public void setIdTransaksi(String idTransaksi) { this.idTransaksi = idTransaksi; }

    public String getIdOrder() { return idOrder; }
    public void setIdOrder(String idOrder) { this.idOrder = idOrder; }

    public String getIdAdmin() { return idAdmin; }
    public void setIdAdmin(String idAdmin) { this.idAdmin = idAdmin; }

    public LocalDate getTanggalTransaksi() { return tanggalTransaksi; }
    public void setTanggalTransaksi(LocalDate tanggalTransaksi) { this.tanggalTransaksi = tanggalTransaksi; }

    public LocalTime getWaktuTransaksi() { return waktuTransaksi; }
    public void setWaktuTransaksi(LocalTime waktuTransaksi) { this.waktuTransaksi = waktuTransaksi; }

    public String getMetodeTransaksi() { return metodeTransaksi; }
    public void setMetodeTransaksi(String metodeTransaksi) { this.metodeTransaksi = metodeTransaksi; }

    public String getStatusTransaksi() { return statusTransaksi; }
    public void setStatusTransaksi(String statusTransaksi) { this.statusTransaksi = statusTransaksi; }
}