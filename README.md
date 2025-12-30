# Sistem Informasi Klinik ITK

<p align="center">
  <img src="public/favicon.svg" alt="Klinik ITK Logo" width="120" height="120">
</p>

<p align="center">
  <strong>Sistem Informasi Klinik Kampus</strong><br>
  Institut Teknologi Kalimantan
</p>

<p align="center">
  <a href="#fitur-utama">Fitur</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#instalasi">Instalasi</a> •
  <a href="#akun-demo">Demo</a> •
  <a href="USER_MANUAL.md">Panduan Pengguna</a>
</p>

---

## Tentang Aplikasi

Sistem Informasi Klinik ITK adalah aplikasi manajemen klinik kampus berbasis web yang dirancang untuk memudahkan pelayanan kesehatan bagi civitas akademika Institut Teknologi Kalimantan. Aplikasi ini mendukung alur kerja dari pendaftaran pasien hingga pemeriksaan dokter dengan sistem berbasis role.

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue 3 + TypeScript |
| **Bridge** | Inertia.js (SSR Enabled) |
| **UI Library** | PrimeVue 4 + Aura Theme |
| **CSS** | Tailwind CSS 4 |
| **Database** | MySQL |
| **PDF** | DomPDF (barryvdh/laravel-dompdf) |
| **Build Tool** | Vite 6 |

---

## Fitur Utama

### Role-Based Access Control

| Role | Akses |
|------|-------|
| **Superadmin** | Akses penuh ke semua fitur termasuk manajemen pengguna |
| **Admin** | Pendaftaran pasien, master data, laporan |
| **Perawat** | Antrian pasien, input anamnesis (vital sign) |
| **Dokter** | Pemeriksaan, diagnosis, resep obat, surat dokter |

### Modul Aplikasi

- **Dashboard** - Statistik dan ringkasan sesuai role
- **Registrasi Pasien** - Pendaftaran pasien baru & kunjungan ulang
- **Antrian Real-time** - Sistem antrian untuk perawat dan dokter
- **Anamnesis** - Input vital sign oleh perawat
- **Pemeriksaan** - Diagnosis, tindakan, dan resep oleh dokter
- **Master Data** - Pengelolaan obat dan tindakan medis
- **Surat Dokter** - Generate PDF surat sehat/sakit
- **Laporan** - Laporan kunjungan, obat, dan tindakan dengan export PDF
- **Manajemen Pengguna** - CRUD pengguna sistem (superadmin only)
- **Profil** - Edit profil dan password sendiri

---

## Business Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        ALUR PELAYANAN KLINIK ITK                        │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│    ADMIN     │     │   PERAWAT    │     │    DOKTER    │     │   OUTPUT     │
│  Pendaftaran │ ──▶ │  Anamnesis   │ ──▶ │ Pemeriksaan  │ ──▶ │   Selesai    │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
      │                    │                    │                    │
      ▼                    ▼                    ▼                    ▼
┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│ • Daftar     │     │ • Tekanan    │     │ • Diagnosis  │     │ • Rekam      │
│   pasien baru│     │   darah      │     │ • ICD-10     │     │   medis      │
│ • Daftar     │     │ • Suhu       │     │ • Tindakan   │     │ • Resep obat │
│   kunjungan  │     │ • Nadi       │     │ • Resep obat │     │ • Surat      │
│ • Generate   │     │ • Pernapasan │     │ • Surat      │     │   dokter     │
│   No. RM     │     │ • TB/BB      │     │   dokter     │     │ • Laporan    │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘

Status Flow:
┌────────────────┐    ┌─────────────────┐    ┌─────────────────┐    ┌─────────┐
│ menunggu_perawat│ ─▶ │ proses_anamnesis │ ─▶ │ sedang_diperiksa │ ─▶ │ selesai │
└────────────────┘    └─────────────────┘    └─────────────────┘    └─────────┘
```

---

## Instalasi

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

### Langkah Instalasi

```bash
# 1. Clone repository
git clone <repository-url>
cd klinik-itk

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klinik_itk
DB_USERNAME=root
DB_PASSWORD=your_password

# 7. Jalankan migration dan seeder
php artisan migrate:fresh --seed

# 8. Build assets
npm run build
```

### Menjalankan Aplikasi

```bash
# Development mode (2 terminal)

# Terminal 1 - Laravel Server
php artisan serve

# Terminal 2 - Vite Dev Server
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

---

## Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@itk.ac.id | password |
| Admin | admin@itk.ac.id | password |
| Perawat | perawat@itk.ac.id | password |
| Dokter | dokter@itk.ac.id | password |

---

## Struktur Folder

```
klinik-itk/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controller untuk setiap modul
│   │   │   ├── DashboardController.php
│   │   │   ├── PasienController.php
│   │   │   ├── PerawatController.php
│   │   │   ├── DokterController.php
│   │   │   ├── ObatController.php
│   │   │   ├── TindakanController.php
│   │   │   ├── LaporanController.php
│   │   │   ├── SuratDokterController.php
│   │   │   └── UserController.php
│   │   └── Middleware/
│   │       └── CheckRole.php     # Middleware untuk role-based access
│   ├── Models/                   # Eloquent Models
│   └── Helpers/                  # Helper classes
│       └── Terbilang.php         # Angka ke teks Indonesia
├── database/
│   ├── migrations/               # Database migrations
│   └── seeders/                  # Demo data seeders
├── resources/
│   ├── js/
│   │   ├── app.ts                # Vue app entry point
│   │   ├── Components/           # Reusable components
│   │   │   └── KlinikLogo.vue    # Logo component
│   │   ├── Layouts/
│   │   │   └── AppLayout.vue     # Main layout dengan sidebar
│   │   └── Pages/                # Vue pages (Inertia)
│   │       ├── Dashboard.vue
│   │       ├── Auth/
│   │       ├── Pasien/
│   │       ├── Perawat/
│   │       ├── Dokter/
│   │       ├── Obat/
│   │       ├── Tindakan/
│   │       ├── Laporan/
│   │       ├── Profile/
│   │       └── Users/
│   └── views/
│       └── pdf/                  # PDF templates
│           ├── surat-sehat.blade.php
│           ├── surat-sakit.blade.php
│           ├── laporan-kunjungan.blade.php
│           ├── laporan-obat.blade.php
│           └── laporan-tindakan.blade.php
├── public/
│   ├── favicon.svg               # App favicon
│   └── favicon.ico
└── routes/
    └── web.php                   # Route definitions
```

---

## API Routes

### Public Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Redirect ke login |
| GET | `/login` | Halaman login |
| POST | `/login` | Proses login |
| POST | `/logout` | Logout |

### Admin Routes (role: superadmin, admin)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/pasien` | Daftar pasien |
| GET | `/pasien/create` | Form registrasi |
| POST | `/pasien` | Simpan pasien baru |
| GET | `/pasien/{id}` | Detail pasien & riwayat |
| PUT | `/pasien/{id}` | Update pasien |
| DELETE | `/pasien/{id}` | Hapus pasien |
| POST | `/pasien/{id}/kunjungan` | Daftar kunjungan baru |
| GET/POST | `/obat` | Master obat |
| GET/POST | `/tindakan` | Master tindakan |

### Perawat Routes (role: superadmin, perawat)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/perawat/antrian` | Antrian pasien |
| POST | `/perawat/anamnesis` | Simpan anamnesis |

### Dokter Routes (role: superadmin, dokter)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/dokter/antrian` | Pasien siap periksa |
| POST | `/dokter/pemeriksaan` | Simpan pemeriksaan |

### Laporan Routes (role: superadmin, admin, dokter)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/laporan` | Menu laporan |
| GET | `/laporan/kunjungan` | Laporan kunjungan |
| GET | `/laporan/obat` | Laporan obat |
| GET | `/laporan/tindakan` | Laporan tindakan |
| GET | `/laporan/*/pdf` | Download PDF laporan |

### Surat Dokter Routes (role: superadmin, admin, dokter)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/surat-dokter/{id}/pdf` | Download PDF surat |
| GET | `/surat-dokter/{id}/preview` | Preview surat |

### Superadmin Routes (role: superadmin)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/users` | Daftar pengguna |
| POST | `/users` | Tambah pengguna |
| PUT | `/users/{id}` | Update pengguna |
| DELETE | `/users/{id}` | Hapus pengguna |
| POST | `/users/{id}/toggle-active` | Aktifkan/nonaktifkan |

### Profile Routes (semua user login)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/profile` | Halaman profil |
| PATCH | `/profile` | Update profil |

---

## Checklist Fitur

### Tech Stack
- [x] Laravel 12 (latest)
- [x] Vue 3 + TypeScript + Inertia.js
- [x] SSR Enabled
- [x] PrimeVue 4 UI Library
- [x] Tailwind CSS 4
- [x] MySQL Database
- [x] DomPDF untuk generate PDF

### Business Flow
- [x] Registrasi (Admin) → Anamnesis (Perawat) → Pemeriksaan (Dokter) → Output
- [x] Auto-generate No. RM (format: RM-YYYYMMDD-XXXX)
- [x] Auto-generate No. Kunjungan (format: KNJ-YYYYMMDD-XXXX)
- [x] Status flow tracking
- [x] Kunjungan ulang pasien lama

### Roles & Access
- [x] Superadmin - Akses penuh
- [x] Admin - Pendaftaran & master data
- [x] Perawat - Anamnesis
- [x] Dokter - Pemeriksaan & surat

### Fitur Aplikasi
- [x] Dashboard statistik per role
- [x] CRUD Pasien lengkap
- [x] CRUD Master Obat dengan stok
- [x] CRUD Master Tindakan dengan biaya
- [x] Antrian Perawat + Input Anamnesis
- [x] Antrian Dokter + Pemeriksaan
- [x] Resep Obat (auto kurangi stok)
- [x] Surat Dokter (Sehat & Sakit) - PDF
- [x] Laporan Kunjungan - PDF
- [x] Laporan Obat - PDF
- [x] Laporan Tindakan - PDF
- [x] User Management lengkap (CRUD)
- [x] Edit Profil sendiri
- [x] Logo & Branding konsisten

---

## Troubleshooting

### Login tidak berhasil
```bash
php artisan migrate:fresh --seed
```

### Build error
```bash
php artisan cache:clear
php artisan config:clear
npm run build
```

### PDF tidak muncul
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

## Dokumentasi Tambahan

- [Panduan Pengguna (User Manual)](USER_MANUAL.md) - Panduan lengkap untuk pengguna non-IT

---

## Lisensi

MIT License

---

## Tim Pengembang

Klinik ITK Development Team
Institut Teknologi Kalimantan

---

*Dokumentasi ini dibuat untuk memudahkan pengembangan dan maintenance sistem.*
