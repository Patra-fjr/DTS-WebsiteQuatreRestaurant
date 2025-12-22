package com.quatre.backend.dto;

import java.util.List;

public class OrderRequest {
    private String namaCustomer;
    private String nomorTelepon;
    private String idMeja;
    private List<OrderItem> items;

    // Getters & Setters
    public String getNamaCustomer() { return namaCustomer; }
    public void setNamaCustomer(String namaCustomer) { this.namaCustomer = namaCustomer; }

    public String getNomorTelepon() { return nomorTelepon; }
    public void setNomorTelepon(String nomorTelepon) { this.nomorTelepon = nomorTelepon; }

    public String getIdMeja() { return idMeja; }
    public void setIdMeja(String idMeja) { this.idMeja = idMeja; }

    public List<OrderItem> getItems() { return items; }
    public void setItems(List<OrderItem> items) { this.items = items; }

    // Inner Class
    public static class OrderItem {
        private String idMenu;
        private int quantity;
        private Double hargaSatuan;

        public String getIdMenu() { return idMenu; }
        public void setIdMenu(String idMenu) { this.idMenu = idMenu; }

        public int getQuantity() { return quantity; }
        public void setQuantity(int quantity) { this.quantity = quantity; }
        
        public Double getHargaSatuan() { return hargaSatuan; }
        public void setHargaSatuan(Double hargaSatuan) { this.hargaSatuan = hargaSatuan; }
    }
}