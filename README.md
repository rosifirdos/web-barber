# IF Barber — Premium Grooming Experience

Aplikasi berbasis web untuk manajemen antrean, booking, dan membership pada barbershop modern. Aplikasi ini dibangun dengan pendekatan _native_ menggunakan PHP (Vanilla), MySQL, dan CSS/JS kustom untuk performa dan kemudahan _maintenance_.

## 🚀 Fitur Utama
- **Sistem Booking Antrean:** Pelanggan dapat melihat slot waktu yang tersedia dan melakukan booking secara _real-time_.
- **Manajemen Membership & Poin:** Sistem loyalitas di mana pelanggan mendapatkan poin dari setiap transaksi yang dapat ditukar dengan voucher diskon atau layanan gratis. Terdapat sistem _Tier_ (Bronze, Silver, Gold, Platinum).
- **Admin Panel:** Kelola antrean (Ubah status, batal, selesai), kelola daftar layanan (harga, durasi), dan kelola rewards poin.
- **Member Panel:** Member dapat melihat riwayat cukur, total poin, _progress tier_, dan menukarkan poin secara mandiri.
- **Chatbot AI (Gemini):** Integrasi AI untuk menjawab pertanyaan pelanggan terkait layanan dan jam operasional.

---

## 📂 Panduan Modifikasi Aplikasi

Jika Anda ingin mengubah tampilan (_UI_) atau logika sistem, berikut adalah panduan direktori dan file yang bertanggung jawab untuk setiap bagian:

### 1. Mengubah Tampilan Global (Header & Footer)
- **Footer Website Utama:** Edit file `includes/footer.php` (berisi info kontak, jam operasional, link sosial media, dan copyright).
- **Header/Navbar Website Utama:** Edit file `includes/header.php` (berisi logo, menu navigasi utama).
- **Header/Sidebar Admin Panel:** Edit file `admin/header.php`.
- **Header/Sidebar Member Panel:** Edit file `member/header.php`.

### 2. Mengubah Halaman Publik
- **Halaman Utama (Landing Page):** Edit file `index.php`. (Berisi hero section, daftar layanan, fitur).
- **Halaman Booking (Formulir):** Edit file `booking.php`.

### 3. Mengubah Logika Sistem & Konfigurasi
- **Konfigurasi Utama (Database, Jam Buka, Poin):** Edit file `includes/config.php`. Di sini Anda bisa mengubah jam operasional (`JAM_BUKA`, `JAM_TUTUP`), batas poin untuk _tier_, dll.
- **Logika & Fungsi Aplikasi (Helper):** Edit file `includes/functions.php`. Jika Anda ingin mengubah cara perhitungan poin (`hitungPoinBooking`), cara redeem poin, atau format tanggal/uang, semuanya ada di sini.
- **Logika Autentikasi (Login/Register):** Edit file `includes/auth.php`.

### 4. Mengubah Halaman Panel Admin (`/admin`)
- **Dashboard (Statistik):** Edit `admin/dashboard.php`.
- **Kelola Antrean / Booking:** Edit `admin/antrian.php` (tampilan tabel dan logika ubah status/batal).
- **Kelola Layanan (Harga/Gambar):** Edit `admin/layanan.php`.
- **Kelola Rewards:** Edit `admin/rewards.php`.

### 5. Mengubah Halaman Panel Member (`/member`)
- **Dashboard Member (Profil & Poin):** Edit `member/dashboard.php`.
- **Halaman Login/Register Member:** Edit `member/login.php` dan `member/register.php`.

### 6. Mengubah Gaya (Desain/CSS) & Animasi (JS)
- **CSS Utama:** Edit `assets/css/style.css` (untuk warna dasar, typography, dan layout publik).
- **CSS Komponen:** Edit `assets/css/components.css` (untuk tombol, form, kartu).
- **CSS Admin/Member:** Edit `assets/css/admin.css`.
- **JavaScript Interaktif:** Edit `assets/js/main.js` (untuk animasi scroll, toggle menu mobile).

---

## ⚙️ Panduan Instalasi Lokal (XAMPP)

1. Pastikan **XAMPP** sudah terinstal (Apache dan MySQL).
2. Simpan folder proyek ini (`web_barber`) di dalam `C:\xampp\htdocs\`.
3. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`), buat database baru bernama `if_barber`.
4. _Import_ file `schema.sql` (berada di dalam folder `database/`) ke dalam database `if_barber`.
5. _(Opsional)_ Jika Anda ingin menyesuaikan kredensial atau menambahkan API Key Gemini, salin `.env.example` menjadi `.env` lalu isi nilainya.
6. Akses aplikasi melalui browser:
   - **Publik:** `http://localhost/web_barber`
   - **Admin:** `http://localhost/web_barber/admin/login.php`

---

## 🔒 Akses Default

**Admin Login**
- Username/Email: (Buat secara manual melalui phpMyAdmin ke tabel `admin` jika belum ada)
- Password: (Gunakan hash bcrypt)

**Member Login**
Daftarkan diri Anda terlebih dahulu di halaman registrasi publik sebelum melakukan login.
