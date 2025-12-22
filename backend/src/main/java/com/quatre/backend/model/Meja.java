package com.quatre.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "meja")
public class Meja {
    @Id
    @Column(name = "id_meja", length = 10)
    private String idMeja;

    @Column(name = "nomor_meja")
    private String nomorMeja;

    @Column(name = "status_meja")
    private String statusMeja; // "tersedia" atau "tidak tersedia"

    // --- GETTER & SETTER ---
    public String getIdMeja() { return idMeja; }
    public void setIdMeja(String idMeja) { this.idMeja = idMeja; }

    public String getNomorMeja() { return nomorMeja; }
    public void setNomorMeja(String nomorMeja) { this.nomorMeja = nomorMeja; }

    public String getStatusMeja() { return statusMeja; }
    public void setStatusMeja(String statusMeja) { this.statusMeja = statusMeja; }
}