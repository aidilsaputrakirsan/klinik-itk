# Sistem Informasi Klinik ITK

Sistem Informasi Klinik ITK adalah platform manajemen layanan kesehatan digital berbasis web yang dirancang khusus untuk mendukung operasional layanan medis di Klinik Kampus Institut Teknologi Kalimantan (ITK).

Sistem ini memfasilitasi integrasi alur kerja kesehatan kampus mulai dari pendaftaran pasien, pengelolaan antrian perawat, pemeriksaan dan resep oleh dokter, hingga penerbitan dokumen medis resmi dan analisis laporan.

---

## Fitur Utama

- **Pendaftaran & Manajemen Pasien**: Mendukung registrasi mahasiswa, dosen, tenaga kependidikan, dan umum, serta dilengkapi sistem registrasi draf.
- **Antrian & Anamnesis Perawat**: Pencatatan tanda-tanda vital, asuhan keperawatan, laboratorium dasar, dan pengiriman otomatis hasil screening via email.
- **Pemeriksaan & Resep Dokter**: Diagnosis berbasis standar ICD-10, manajemen tindakan medis, serta integrasi pemotongan stok obat otomatis.
- **Penerbitan Surat Medis**: Penerbitan surat keterangan sakit, surat keterangan sehat, dan surat rujukan medis dalam format PDF.
- **Laporan & Analitis**: Rekapitulasi kunjungan, penggunaan obat, tindakan medis, serta ekspor data ke format PDF dan Excel.
- **Keamanan & Akuntabilitas**: Hak akses berbasis peran (Superadmin, Admin, Perawat, Dokter) dan audit log jejak aktivitas pengguna.

---

## Teknologi yang Digunakan

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3 + TypeScript (Inertia.js SSR)
- **UI Framework**: PrimeVue 4 (Theme Aura) & Tailwind CSS 4
- **Database**: MySQL / MariaDB
- **Dokumen & PDF**: DomPDF & Laravel Excel

---

## Petunjuk Penggunaan Cepat

### Prasyarat System
- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x
- MySQL / MariaDB

### Langkah Instalasi

1. Clone repositori dan masuk ke direktori proyek:
   ```bash
   git clone <repository-url>
   cd klinik-itk
   ```

2. Install dependensi PHP dan JavaScript:
   ```bash
   composer install
   npm install
   ```

3. Konfigurasi file lingkungan:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Jalankan migrasi dan seeder database:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. Jalankan server pengembangan:
   ```bash
   # Terminal 1
   php artisan serve

   # Terminal 2
   npm run dev
   ```

Akses aplikasi melalui peramban di `http://localhost:8000`.

---

## Dokumentasi Terkait

- [Dokumentasi Lengkap](docs/full-docs.md) - Dokumentasi teknis mendalam mengenai arsitektur, alur bisnis, skema database, katalog rute API, dan troubleshooting.
- [Panduan Pengguna (User Manual)](USER_MANUAL.md) - Panduan operasional alur kerja sistem untuk Admin, Perawat, dan Dokter.

---

## Lisensi & Pengembang

Dikembangkan oleh **Tim Pengembang Klinik ITK** — Institut Teknologi Kalimantan.  
Dilindungi di bawah lisensi MIT.
