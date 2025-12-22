package com.quatre.backend.repository;
import com.quatre.backend.model.DetailOrders;
import org.springframework.data.jpa.repository.JpaRepository;
public interface DetailOrdersRepository extends JpaRepository<DetailOrders, Long> {}