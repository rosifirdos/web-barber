# Product Requirements Document (PRD)
**Nama Produk:** IF Barber (Sistem Reservasi & Manajemen Barbershop)
**Status:** Draft / Perencanaan
**Versi:** 1.0

## 1. Ringkasan Eksekutif
IF Barber adalah platform aplikasi web berbasis PHP yang dirancang untuk mendigitalisasi proses bisnis barbershop. Sistem ini menggabungkan fungsionalitas *Company Profile*, Sistem Reservasi (Booking), Manajemen Operasional Admin, serta integrasi asisten virtual cerdas. Tujuannya adalah menghilangkan antrean fisik yang tidak teratur, mempermudah admin dalam melacak pendapatan, dan meningkatkan retensi pelanggan.

## 2. Target Pengguna
1. **Pelanggan (End-User):** Orang yang ingin melihat profil barbershop, mencari informasi layanan, dan melakukan booking jadwal secara online.
2. **Admin/Pemilik (Internal):** Staf barbershop yang bertugas mengelola antrean, memperbarui layanan, dan memantau performa harian.

---

## 3. Cakupan Fitur (Feature Requirements)

### 3.1. Fitur Utama (Core Booking System)
Fitur dasar yang memastikan alur utama pemesanan berjalan lancar.
* **Frontend Responsive:** Tampilan UI/UX yang optimal dan menyesuaikan ukuran layar secara otomatis (Mobile, Tablet, Desktop).
* **Create (Booking Jadwal):** Formulir pemesanan bagi pelanggan untuk memilih layanan, barber, tanggal, dan jam operasional.
* **Read (Daftar Antrean):** Halaman bagi admin untuk melihat daftar pelanggan yang sudah melakukan reservasi.
* **Update (Ubah Status):** Tombol aksi bagi admin untuk mengubah status antrean (misal: "Pending" menjadi "Selesai").
* **Delete (Batalkan Booking):** Fitur bagi admin untuk membatalkan antrean jika pelanggan tidak hadir atau ada kesalahan data.

### 3.2. Fitur Admin & Manajemen (Dashboard & Operasional)
Sistem *backend* untuk mengontrol seluruh operasional bisnis IF Barber.
* **Admin Authentication:** Sistem login yang aman menggunakan *password hashing* untuk membatasi akses ke dashboard.
* **Dashboard Statistik Terintegrasi:** Panel ringkasan data real-time yang memuat:
  1. Total antrean hari ini.
  2. Layanan yang paling populer (Top Service).
  3. Estimasi total pendapatan harian.
* **Manajemen Layanan (Full CRUD):** Modul bagi admin untuk menambah layanan baru, mengubah deskripsi/harga, dan menghapus layanan yang tidak aktif.
* **Sistem Filter & Pencarian:** Fitur pencarian riwayat booking berdasarkan nama pelanggan, rentang tanggal, atau status antrean.
* **Cetak Struk (PDF):** Kemampuan untuk menghasilkan dan mengunduh invoice/struk digital dalam format PDF setelah status booking "Selesai".

### 3.3. Halaman Profil Web (Company Profile)
Fasad digital untuk membangun kepercayaan pelanggan.
* **Profile Barbershop:** Halaman *Landing Page* yang menyajikan identitas visual, sejarah singkat, visi-misi, dan keunggulan IF Barber.
* **Profile Barber (Staff Showcase):** Katalog interaktif yang menampilkan tenaga pemangkas profesional, meliputi:
  * Foto potret masing-masing barber.
  * Profil dan bio singkat.
  * *Skillset* dan spesialisasi keahlian (contoh: *Fade*, *Hair Tattoo*, *Beard Trimming*).

### 3.4. Fitur Ekstensi & Modernisasi (Advanced Features)
Fitur tambahan untuk meningkatkan nilai kompetitif aplikasi.
* **AI Chatbot Assistance (Gemini API Integration):** 
  * Asisten virtual cerdas di halaman depan yang dapat menjawab pertanyaan pelanggan seputar jam buka, harga layanan, atau merekomendasikan gaya rambut.
  * *Teknis:* Terintegrasi menggunakan REST API dari Google Gemini.
* **Sistem Membership (Loyalty Program):**
  * Registrasi khusus untuk pelanggan tetap.
  * Fitur pelacakan riwayat cukur bagi pelanggan yang sudah login.
  * *Benefit:* Pelanggan *member* tidak perlu mengisi ulang nama dan nomor HP saat booking jadwal baru.

---

## 4. Kebutuhan Non-Fungsional (Tech Stack)
* **Bahasa Pemrograman:** PHP (Backend), HTML5, CSS3, JavaScript (Frontend).
* **Database:** MySQL / MariaDB (Relational Database).
* **Arsitektur:** Native / Prosedural terstruktur (dengan pemisahan file koneksi dan *logic*).
* **Integrasi Eksternal:** API Google Gemini (Chatbot), Library PDF Generator (misal: FPDF / Dompdf).