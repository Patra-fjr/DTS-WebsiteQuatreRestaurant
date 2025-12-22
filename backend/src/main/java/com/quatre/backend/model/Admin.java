package com.quatre.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "admin") // Sesuai nama tabel di db_resto.sql
public class Admin {

    @Id
    @Column(name = "id_admin", length = 10) // Primary Key String (bukan Auto Increment)
    private String idAdmin;

    @Column(name = "nama", nullable = false, length = 100)
    private String nama;

    @Column(name = "email", nullable = false, length = 100)
    private String email;

    @Column(name = "username", nullable = false, unique = true, length = 50)
    private String username;

    @Column(name = "password", nullable = false)
    private String password;

    @Column(name = "jabatan", nullable = false, length = 50)
    private String jabatan;

    @Column(name = "status_admin", nullable = false, length = 50)
    private String statusAdmin = "aktif";

    // Constructor Kosong (Wajib)
    public Admin() {
    }

    // Constructor Lengkap
    public Admin(String idAdmin, String nama, String email, String username, String password, String jabatan, String statusAdmin) {
        this.idAdmin = idAdmin;
        this.nama = nama;
        this.email = email;
        this.username = username;
        this.password = password;
        this.jabatan = jabatan;
        this.statusAdmin = statusAdmin;
    }

    // Getter dan Setter
    public String getIdAdmin() { return idAdmin; }
    public void setIdAdmin(String idAdmin) { this.idAdmin = idAdmin; }

    public String getNama() { return nama; }
    public void setNama(String nama) { this.nama = nama; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getUsername() { return username; }
    public void setUsername(String username) { this.username = username; }

    public String getPassword() { return password; }
    public void setPassword(String password) { this.password = password; }

    public String getJabatan() { return jabatan; }
    public void setJabatan(String jabatan) { this.jabatan = jabatan; }

    public String getStatusAdmin () { return statusAdmin; }
    public void setStatusAdmin (String statusAdmin) {this.statusAdmin = statusAdmin; }
    
}