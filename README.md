# Panduan Instalasi dan Pengaturan Demo Aplikasi KB Insurance

## Ringkasan

KB Insurance adalah sistem manajemen asuransi komprehensif yang dibangun dengan backend CodeIgniter 4 dan frontend Vue 3. Aplikasi ini menyediakan fitur kalkulasi premi asuransi, manajemen polis, dan pelaporan.

## Demo Aplikasi

Demo aplikasi dapat diakses di:
**[demokbinsurance.anonimak.my.id](http://demokbinsurance.anonimak.my.id)**

### Kredensial Demo
- **Username:** testing
- **Password:** KBTest123$$

Silakan jelajahi semua fitur aplikasi menggunakan kredensial di atas.

## Kebutuhan Sistem

### Backend (CodeIgniter 4)
- PHP 8.1 atau lebih tinggi
- Database MySQL/MariaDB (saat ini dikonfigurasi untuk database remote)
- Ekstensi PHP yang diperlukan:
  - intl
  - mbstring
  - json (diaktifkan secara default)
  - curl (untuk permintaan HTTP)
  - mysqlnd (untuk koneksi MySQL)

### Frontend (Vue 3 + Vite)
- Node.js 16.x atau lebih tinggi
- npm 8.x atau lebih tinggi

## Struktur Direktori

```
test-kb-insurance/
├── appstarter/               # Folder aplikasi utama
│   ├── app/                  # Aplikasi backend CodeIgniter
│   │   ├── Config/           # Konfigurasi aplikasi
│   │   ├── Controllers/      # Pengendali API
│   │   ├── Database/         # Migrasi dan seed
│   │   ├── Filters/          # Filter permintaan termasuk autentikasi JWT
│   │   ├── Models/           # Model data
│   │   └── Views/            # Tampilan yang dirender server (penggunaan minimal)
│   ├── frontend/             # Aplikasi frontend Vue.js
│   │   ├── public/           # Aset statis
│   │   ├── src/              # Kode sumber Vue
│   │   │   ├── assets/       # Aset frontend (gambar, dll)
│   │   │   ├── components/   # Komponen Vue
│   │   │   ├── composables/  # Logika bersama (misalnya, kalkulator premi)
│   │   │   ├── router/       # Konfigurasi Vue Router
│   │   │   ├── services/     # Modul layanan API
│   │   │   └── stores/       # Pinia stores (manajemen state)
│   │   └── vite.config.js    # Konfigurasi build Vite
│   ├── public/               # Root dokumen server web
│   └── writable/             # Direktori yang dapat ditulis untuk log, cache, dll.
```

## Langkah-langkah Instalasi

### 1. Kloning Repositori

```bash
git clone https://github.com/anonimak/test-kb-insurance.git
cd test-kb-insurance
```

#### 2.1. Instal Dependensi PHP

```bash
composer install
```

#### 2.2. Konfigurasi Lingkungan

Aplikasi ini dilengkapi dengan file .env yang telah dikonfigurasi sebelumnya dan mengarah ke database remote. Jika Anda ingin menggunakan database lokal, perbarui bagian berikut dalam file .env Anda:

```
database.default.hostname = localhost
database.default.database = kb_insurance
database.default.username = namapengguna
database.default.password = katasandi
database.default.DBDriver = MySQLi
```

#### 2.3. Jalankan Migrasi

```bash
php spark migrate
```

### 3. Pengaturan Frontend

```bash
cd frontend
npm install
```

## Menjalankan Aplikasi

### 1. Jalankan Server Backend

```bash
# Mulai server pengembangan PHP
php spark serve
```

API backend akan tersedia di `http://localhost:8080`.

### 2. Jalankan Server Pengembangan Frontend

Dari direktori frontend:

```bash
# Mulai server pengembangan Vite
npm run dev
```

Frontend Vue akan tersedia di `http://localhost:5173`.

### 3. Membangun untuk Produksi

#### Backend

Untuk backend, konfigurasikan server web Anda (Apache, Nginx, dll.) untuk mengarah ke direktori `public`.

#### Frontend

```bash
cd frontend
npm run build
```

File yang dibangun akan ditempatkan di direktori `public`, sehingga dapat diakses melalui aplikasi CodeIgniter.

## Fitur Utama

1. **Autentikasi Pengguna** - Sistem autentikasi berbasis JWT
2. **Manajemen Perlindungan Asuransi** - Tambah, edit, lihat, dan hapus polis asuransi
3. **Perhitungan Premi** - Hitung premi berdasarkan nilai kendaraan, jenis pertanggungan, dan risiko tambahan
4. **Laporan PDF** - Buat laporan perhitungan premi sebagai dokumen PDF

## Endpoint API

Aplikasi menyediakan endpoint API berikut:

- `POST /api/auth/register` - Pendaftaran pengguna
- `POST /api/auth/login` - Login pengguna
- `GET /api/customers` - Daftar semua pertanggungan asuransi
- `POST /api/customers` - Buat pertanggungan baru
- `GET /api/customers/{id}` - Lihat pertanggungan tertentu
- `PUT /api/customers/{id}` - Perbarui pertanggungan
- `DELETE /api/customers/{id}` - Hapus pertanggungan

## Pemecahan Masalah

### Masalah Umum

1. **Error Koneksi Database**
   - Verifikasi kredensial database di file .env
   - Pastikan layanan MySQL/MariaDB berjalan

2. **Masalah Koneksi API**
   - Periksa bahwa kedua server berjalan
   - Verifikasi pengaturan CORS jika Anda mengalami error cross-origin

3. **Masalah Memuat Gambar**
   - Tempatkan file logo di direktori public
   - Untuk file SVG, dapat langsung diimpor dalam komponen Vue

4. **Error Pembuatan PDF**
   - Pastikan jsPDF dan jspdf-autotable diinstal dengan benar:
     ```bash
     npm install jspdf jspdf-autotable
     ```
   - Verifikasi impor dan inisialisasi yang benar di komponen Anda

## Detail Struktur Folder

### Backend (CodeIgniter 4)

- Config: File konfigurasi (database, routes, dll.)
- Controllers: Pengendali API dan halaman
- Models: Model database
- Filters: Filter permintaan termasuk autentikasi JWT
- Database: Migrasi dan seeder database

### Frontend (Vue 3)

- `src/components`: Komponen Vue
- `src/composables`: Logika yang dapat digunakan kembali seperti kalkulator premi
- `src/services`: Modul layanan API
- `src/stores`: Pinia stores untuk manajemen state
- `src/router`: Konfigurasi Vue Router

## Alur Kerja Pengembangan

1. Buat perubahan API di backend CodeIgniter
2. Uji API dengan alat seperti Postman
3. Implementasikan perubahan frontend di komponen Vue
4. Uji integrasi antara frontend dan backend

## Fitur Perhitungan Premi

Aplikasi ini menyediakan perhitungan premi asuransi berdasarkan beberapa parameter:

1. **Nilai Kendaraan** - Harga pertanggungan kendaraan
2. **Jenis Pertanggungan**:
   - Comprehensive (1.5‰ dari nilai kendaraan)
   - Total Loss Only (0.5‰ dari nilai kendaraan)
3. **Pertanggungan Risiko Tambahan**:
   - Risiko Banjir (0.5‰ dari nilai kendaraan untuk Comprehensive)
   - Risiko Gempa Bumi (0.2‰ dari nilai kendaraan untuk Comprehensive)
4. **Periode Pertanggungan** - Menentukan durasi dalam tahun

Perhitungan menggunakan rumus berikut:

```
Premi Kendaraan = Nilai Kendaraan × Tarif Jenis Pertanggungan × Jumlah Tahun
Premi Risiko Banjir = Nilai Kendaraan × Tarif Risiko Banjir × Jumlah Tahun
Premi Risiko Gempa = Nilai Kendaraan × Tarif Risiko Gempa × Jumlah Tahun
Total Premi = Premi Kendaraan + Premi Risiko Banjir + Premi Risiko Gempa
```

## Kontributor

- Jonatan Teofilus - Pengembangan awal

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT.