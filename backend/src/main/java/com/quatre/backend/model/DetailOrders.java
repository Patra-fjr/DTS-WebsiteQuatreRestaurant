package com.quatre.backend.model;

import jakarta.persistence.*;

@Entity
@Table(name = "detail_orders")
public class DetailOrders {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_detailorder")
    private Long idDetailOrder;

    @Column(name = "id_order")
    private String idOrder;

    @Column(name = "id_menu")
    private String idMenu;

    @Column(name = "quantity")
    private Integer quantity;

    @Column(name = "subtotal")
    private Double subtotal;

    // Getters & Setters
    public Long getIdDetailOrder() { return idDetailOrder; }
    public void setIdDetailOrder(Long idDetailOrder) { this.idDetailOrder = idDetailOrder; }

    public String getIdOrder() { return idOrder; }
    public void setIdOrder(String idOrder) { this.idOrder = idOrder; }

    public String getIdMenu() { return idMenu; }
    public void setIdMenu(String idMenu) { this.idMenu = idMenu; }

    public Integer getQuantity() { return quantity; }
    public void setQuantity(Integer quantity) { this.quantity = quantity; }

    public Double getSubtotal() { return subtotal; }
    public void setSubtotal(Double subtotal) { this.subtotal = subtotal; }
}