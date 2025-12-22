package com.quatre.backend.repository;
import com.quatre.backend.model.Orders;
import org.springframework.data.jpa.repository.JpaRepository;
public interface OrdersRepository extends JpaRepository<Orders, String> {}