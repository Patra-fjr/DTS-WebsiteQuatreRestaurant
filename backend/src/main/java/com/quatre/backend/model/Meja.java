package com.quatre.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "meja")
public class Meja {
    @Id
    @Column(name = "id_meja", length = 10)
    private String idMeja;

    @Column(name = "nomor_meja")
    private Integer nomorMeja;

    @Column(name = "status_meja", columnDefinition = "ENUM('tersedia','tidak tersedia')")
    private String statusMeja;

    // --- GETTER & SETTER ---
    public String getIdMeja() { return idMeja; }
    public void setIdMeja(String idMeja) { this.idMeja = idMeja; }

    public Integer getNomorMeja() { return nomorMeja; }
    public void setNomorMeja(Integer nomorMeja) { this.nomorMeja = nomorMeja; }

    public String getStatusMeja() { return statusMeja; }
    public void setStatusMeja(String statusMeja) { this.statusMeja = statusMeja; }
}