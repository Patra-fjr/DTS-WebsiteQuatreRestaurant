# DTS Website Quatre Restaurant

Proyek website restaurant management system yang dikembangkan sebagai tugas akhir mata kuliah pemrograman web di kampus. Website ini dibangun menggunakan PHP dengan arsitektur Spring Boot dan Docker containerization.

> **⚠️ Catatan Keamanan**: Repository ini merupakan versi publik untuk keperluan portfolio. Repository asli bersifat private untuk menjaga keamanan data dan konfigurasi sensitif.

## 👥 Tim Pengembang

Proyek ini dikerjakan oleh:
- **Patra-fjr** (Patra Fajri)
- **Taufik Dermawan** (Partner)

## 📋 Deskripsi Proyek

DTS Website Quatre Restaurant adalah sistem manajemen restaurant berbasis web yang memungkinkan pengelolaan menu, pesanan, dan operasional restaurant. Proyek ini merupakan implementasi dari konsep-konsep yang dipelajari dalam mata kuliah pemrograman web dengan fokus pada:

- Pengembangan aplikasi web menggunakan PHP
- Implementasi arsitektur Spring Boot
- Containerization menggunakan Docker
- Web server configuration dengan Nginx

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP
- **Framework**: Spring Boot architecture
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **Version Control**: Git & GitHub

## 🏗️ Struktur Proyek

```
DTS-WebsiteQuatreRestaurant/
├── .github/              # GitHub workflows dan konfigurasi
├── user_side/            # Frontend user interface
├── Dockerfile            # Docker configuration untuk aplikasi
├── Dockerfile.nginx      # Docker configuration untuk Nginx
├── default.conf          # Nginx server configuration
├── docker-compose.yml    # Docker Compose orchestration
└── .gitignore           # Git ignore rules
```

## 🚀 Cara Menjalankan Proyek

### Prerequisites

Pastikan sistem Anda telah terinstall:
- Docker
- Docker Compose
- Git

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/Patra-fjr/DTS-WebsiteQuatreRestaurant.git
   cd DTS-WebsiteQuatreRestaurant
   ```

2. **Jalankan dengan Docker Compose**
   ```bash
   docker-compose up -d
   ```

3. **Akses aplikasi**
   Buka browser dan akses:
   ```
   http://localhost
   ```

4. **Stop aplikasi**
   ```bash
   docker-compose down
   ```

## 🐳 Docker Configuration

Proyek ini menggunakan Docker multi-container setup:

- **Web Application Container**: Menjalankan aplikasi PHP
- **Nginx Container**: Sebagai reverse proxy dan web server

### Build Docker Image

```bash
# Build aplikasi
docker build -t quatre-restaurant-app -f Dockerfile .

# Build Nginx
docker build -t quatre-restaurant-nginx -f Dockerfile.nginx .
```

## 📚 Fitur Utama

- Manajemen menu restaurant
- Sistem pemesanan
- User interface yang responsif
- Containerized deployment
- Scalable architecture

## 🎓 Konteks Akademik

Proyek ini dikembangkan sebagai bagian dari:
- **Program**: Digital Talent Scholarship (DTS)
- **Mata Kuliah**: Pemrograman Web
- **Fokus Pembelajaran**: 
  - PHP Web Development
  - Spring Boot Architecture Pattern
  - Docker Containerization
  - DevOps Best Practices

## 🔒 Keamanan & Privacy

Repository ini merupakan versi demonstrasi. Beberapa file konfigurasi sensitif dan data production telah dihapus atau diganti dengan nilai default untuk keperluan keamanan. Repository asli yang berisi konfigurasi lengkap dan data production disimpan secara private.

## 📝 Lisensi

Proyek ini dikembangkan untuk keperluan akademik dan pembelajaran.

## 📞 Kontak

Untuk informasi lebih lanjut, silakan hubungi:
- **GitHub**: [@Patra-fjr](https://github.com/Patra-fjr)

---

**Developed with ❤️ by Patra Fajri & Taufik Dermawan**

*Digital Talent Scholarship - Web Programming Project*