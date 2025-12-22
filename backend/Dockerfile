# --- Tahap 1: Build (Compile Java) ---
FROM maven:3.9.6-eclipse-temurin-21-alpine AS build
WORKDIR /app

# Copy file konfigurasi maven
COPY pom.xml .

# Copy source code
COPY src ./src

# Lakukan build JAR (otomatis download dependency & compile)
RUN mvn clean package -DskipTests

# --- Tahap 2: Run (Jalankan Aplikasi) ---
FROM eclipse-temurin:21-jdk-alpine
WORKDIR /app

# Ambil file JAR yang sudah jadi dari Tahap 1
COPY --from=build /app/target/*.jar app.jar

EXPOSE 8080
ENTRYPOINT ["java", "-jar", "app.jar"]