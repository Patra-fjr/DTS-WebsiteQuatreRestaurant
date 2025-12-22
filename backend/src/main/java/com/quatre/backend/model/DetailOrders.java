package com.quatre.backend.model;

import jakarta.persistence.*;
import java.math.BigDecimal; // <--- WAJIB IMPORT INI

@Entity
@Table(name = "detail_orders")
public class DetailOrders {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "id_detailorder")
    private Integer idDetailOrder;

    @Column(name = "id_order")
    private String idOrder;

    @Column(name = "id_menu")
    private String idMenu;

    @Column(name = "quantity")
    private Integer quantity;

    @Column(name = "subtotal")
    private BigDecimal subtotal; // <--- UBAH JADI BigDecimal

    // --- GETTER SETTER ---
    public Integer getIdDetailOrder() { return idDetailOrder; }
    public void setIdDetailOrder(Integer idDetailOrder) { this.idDetailOrder = idDetailOrder; }

    public String getIdOrder() { return idOrder; }
    public void setIdOrder(String idOrder) { this.idOrder = idOrder; }

    public String getIdMenu() { return idMenu; }
    public void setIdMenu(String idMenu) { this.idMenu = idMenu; }

    public Integer getQuantity() { return quantity; }
    public void setQuantity(Integer quantity) { this.quantity = quantity; }

    // Update Getter Setter Subtotal
    public BigDecimal getSubtotal() { return subtotal; }
    public void setSubtotal(BigDecimal subtotal) { this.subtotal = subtotal; }
}