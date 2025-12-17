# Sistem Informasi Klinik ITK

Sistem Informasi Klinik Kampus Institut Teknologi Kalimantan (ITK) - Aplikasi manajemen klinik kampus berbasis web.

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue 3 + TypeScript |
| **Bridge** | Inertia.js (SSR Enabled) |
| **UI Library** | PrimeVue 4 + Aura Theme |
| **CSS** | Tailwind CSS 4 |
| **Database** | MySQL |
| **Build Tool** | Vite 6 |

## Fitur Utama

### 1. Role-Based Access Control
- **Superadmin**: Akses penuh ke semua fitur
- **Admin**: Pendaftaran pasien, master data, laporan
- **Perawat**: Antrian pasien, input anamnesis
- **Dokter**: Pemeriksaan, diagnosis, resep obat

### 2. Modul Aplikasi
- Dashboard dengan statistik per role
- Registrasi & manajemen pasien
- Antrian pasien real-time
- Input anamnesis (vital sign)
- Pemeriksaan dokter
- Resep obat dengan manajemen stok
- Master data obat & tindakan
- Laporan

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
│   No. RM     │     │ • TB/BB      │     │   dokter     │     │              │
│ • Generate   │     │ • Riwayat    │     │              │     │              │
│   No. Kunj   │     │              │     │              │     │              │
└──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘

Status Flow:
┌──────────┐    ┌───────────┐    ┌─────────────┐    ┌─────────┐
│ menunggu │ ─▶ │ anamnesis │ ─▶ │ pemeriksaan │ ─▶ │ selesai │
└──────────┘    └───────────┘    └─────────────┘    └─────────┘
```

### Detail Flow per Role

#### 1. Admin - Pendaftaran
```
Pasien Datang
     │
     ▼
┌─────────────────────┐
│ Cek Pasien Lama?    │
└─────────────────────┘
     │
     ├── Ya ──▶ Daftar Kunjungan Baru
     │                │
     │                ▼
     │         Generate No. Kunjungan
     │                │
     └── Tidak ──▶ Registrasi Pasien Baru
                      │
                      ▼
               Generate No. RM
               Generate No. Kunjungan
                      │
                      ▼
              Status: "menunggu"
```

#### 2. Perawat - Anamnesis
```
Lihat Antrian (status: menunggu)
     │
     ▼
Panggil Pasien
     │
     ▼
┌─────────────────────────────┐
│ Input Data Anamnesis:       │
│ • Tekanan darah (mmHg)      │
│ • Suhu tubuh (°C)           │
│ • Denyut nadi (x/menit)     │
│ • Pernapasan (x/menit)      │
│ • Tinggi badan (cm)         │
│ • Berat badan (kg)          │
│ • Riwayat penyakit          │
│ • Riwayat alergi            │
└─────────────────────────────┘
     │
     ▼
Simpan & Update Status: "anamnesis"
     │
     ▼
Pasien Lanjut ke Antrian Dokter
```

#### 3. Dokter - Pemeriksaan
```
Lihat Antrian (status: anamnesis)
     │
     ▼
Review Data Anamnesis
     │
     ▼
┌─────────────────────────────┐
│ Input Pemeriksaan:          │
│ • Diagnosis utama           │
│ • Diagnosis sekunder        │
│ • Kode ICD-10               │
│ • Catatan pemeriksaan       │
│ • Anjuran                   │
└─────────────────────────────┘
     │
     ▼
┌─────────────────────────────┐
│ Pilih Tindakan (opsional):  │
│ □ Pemeriksaan umum          │
│ □ Nebulizer                 │
│ □ Injeksi                   │
│ □ dll                       │
└─────────────────────────────┘
     │
     ▼
┌─────────────────────────────┐
│ Input Resep Obat (opsional):│
│ • Pilih obat                │
│ • Jumlah                    │
│ • Aturan pakai              │
│ (Stok obat auto berkurang)  │
└─────────────────────────────┘
     │
     ▼
Simpan & Update Status: "selesai"
```

---

## Struktur Database

### Entity Relationship

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    users    │       │   pasiens   │       │    obats    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │       │ id          │
│ name        │       │ no_rm       │       │ kode_obat   │
│ email       │       │ nik         │       │ nama_obat   │
│ password    │       │ nama        │       │ satuan      │
│ role        │──┐    │ tanggal_lahir│      │ stok        │
│ nip         │  │    │ jenis_kelamin│      │ harga       │
│ phone       │  │    │ alamat      │       │ is_active   │
│ is_active   │  │    │ status_pasien│      └──────┬──────┘
└──────┬──────┘  │    │ nim_nip     │              │
       │         │    │ fakultas    │              │
       │         │    └──────┬──────┘              │
       │         │           │                     │
       │         │    ┌──────┴──────┐              │
       │         │    │ rekam_medis │              │
       │         │    ├─────────────┤              │
       │         └───▶│ pasien_id   │              │
       │              │ no_kunjungan│              │
       │              │ tanggal     │              │
       │              │ status      │              │
       │              │ keluhan     │              │
       │              │ admin_id    │◀─────────────┘
       │              └──────┬──────┘
       │                     │
       │         ┌───────────┼───────────┐
       │         │           │           │
       │    ┌────┴────┐ ┌────┴────┐ ┌────┴────┐
       │    │anamnesis│ │pemeriksaan│ │resep_obat│
       │    ├─────────┤ ├─────────┤ ├─────────┤
       └───▶│perawat_id│ │dokter_id │ │obat_id  │
            │tekanan_d│ │diagnosis │ │jumlah   │
            │suhu     │ │icd_10    │ │aturan   │
            │nadi     │ │anjuran   │ └─────────┘
            │pernapasan│ └─────────┘
            │tb/bb    │
            └─────────┘
```

### Tabel Utama

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Data pengguna sistem (admin, perawat, dokter) |
| `pasiens` | Data pasien klinik |
| `rekam_medis` | Data kunjungan pasien |
| `anamnesis` | Data vital sign dari perawat |
| `pemeriksaans` | Data diagnosis dari dokter |
| `resep_obats` | Data resep obat |
| `obats` | Master data obat |
| `tindakans` | Master data tindakan medis |
| `surat_dokters` | Surat keterangan dokter |

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
│   │   ├── Controllers/        # Controller untuk setiap modul
│   │   │   ├── DashboardController.php
│   │   │   ├── PasienController.php
│   │   │   ├── PerawatController.php
│   │   │   ├── DokterController.php
│   │   │   ├── ObatController.php
│   │   │   └── TindakanController.php
│   │   └── Middleware/
│   │       └── CheckRole.php   # Middleware untuk role-based access
│   └── Models/                 # Eloquent Models
│       ├── User.php
│       ├── Pasien.php
│       ├── RekamMedis.php
│       ├── Anamnesis.php
│       ├── Pemeriksaan.php
│       ├── ResepObat.php
│       ├── Obat.php
│       ├── Tindakan.php
│       └── SuratDokter.php
├── database/
│   ├── migrations/             # Database migrations
│   └── seeders/                # Demo data seeders
├── resources/
│   ├── js/
│   │   ├── app.ts              # Vue app entry point
│   │   ├── ssr.ts              # SSR entry point
│   │   ├── Layouts/
│   │   │   └── AppLayout.vue   # Main layout dengan sidebar
│   │   ├── Pages/              # Vue pages (Inertia)
│   │   │   ├── Dashboard.vue
│   │   │   ├── Pasien/
│   │   │   ├── Perawat/
│   │   │   ├── Dokter/
│   │   │   ├── Obat/
│   │   │   ├── Tindakan/
│   │   │   ├── Laporan/
│   │   │   └── Users/
│   │   └── types/              # TypeScript definitions
│   └── css/
│       └── app.css             # Tailwind CSS
└── routes/
    └── web.php                 # Route definitions
```

---

## API Routes

### Public Routes
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/` | Welcome page |
| GET | `/login` | Login page |
| POST | `/login` | Process login |
| POST | `/logout` | Logout |

### Admin Routes (role: superadmin, admin)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/pasien` | Daftar pasien |
| GET | `/pasien/create` | Form registrasi |
| POST | `/pasien` | Simpan pasien baru |
| GET | `/pasien/{id}` | Detail pasien |
| GET | `/pasien/{id}/edit` | Edit pasien |
| PUT | `/pasien/{id}` | Update pasien |
| DELETE | `/pasien/{id}` | Hapus pasien |
| GET | `/obat` | Master obat |
| POST | `/obat` | Tambah obat |
| PUT | `/obat/{id}` | Update obat |
| DELETE | `/obat/{id}` | Hapus obat |
| GET | `/tindakan` | Master tindakan |
| POST | `/tindakan` | Tambah tindakan |
| PUT | `/tindakan/{id}` | Update tindakan |
| DELETE | `/tindakan/{id}` | Hapus tindakan |

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

### Superadmin Routes (role: superadmin)
| Method | URI | Description |
|--------|-----|-------------|
| GET | `/users` | Daftar pengguna |
| POST | `/users` | Tambah pengguna |
| PUT | `/users/{id}` | Update pengguna |
| DELETE | `/users/{id}` | Hapus pengguna |
| POST | `/users/{id}/toggle-active` | Aktifkan/nonaktifkan pengguna |

---

## Pengembangan Lanjutan

### Fitur yang Bisa Ditambahkan

1. **Laporan & Statistik**
   - Export PDF laporan kunjungan
   - Grafik statistik bulanan
   - Export Excel

2. **Surat Dokter**
   - Generate PDF surat sakit/sehat
   - Template surat yang bisa dikustom
   - Nomor surat otomatis

3. **Notifikasi**
   - Notifikasi antrian pasien
   - Reminder stok obat menipis

4. **Integrasi**
   - Integrasi BPJS
   - Integrasi sistem akademik ITK

5. **Mobile**
   - PWA support
   - Mobile responsive optimization

### Cara Menambah Fitur Baru

1. **Tambah Migration** (jika perlu tabel baru):
   ```bash
   php artisan make:migration create_nama_tabel_table
   ```

2. **Tambah Model**:
   ```bash
   php artisan make:model NamaModel
   ```

3. **Tambah Controller**:
   ```bash
   php artisan make:controller NamaController
   ```

4. **Tambah Vue Page** di `resources/js/Pages/`

5. **Tambah Route** di `routes/web.php`

6. **Build ulang**:
   ```bash
   npm run build
   ```

---

## Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## Troubleshooting

### Login tidak berhasil
```bash
# Reset password semua user
php artisan app:reset-passwords
```

### Build error
```bash
# Clear cache dan rebuild
php artisan cache:clear
php artisan config:clear
npm run build
```

### Database error
```bash
# Fresh migration
php artisan migrate:fresh --seed
```

---

## Checklist Requirements

### Tech Stack
- [x] Laravel 12 (latest)
- [x] Vue 3 + Inertia.js
- [x] SSR Enabled
- [x] Code Splitting (Vite)
- [x] Tailwind CSS
- [x] MySQL
- [x] PrimeVue UI Library

### Business Flow
- [x] Registrasi (Admin) → Anamnesis (Perawat) → Pemeriksaan (Dokter) → Output
- [x] Auto-generate No. RM
- [x] Auto-generate No. Kunjungan
- [x] Status flow tracking

### Roles
- [x] Superadmin
- [x] Admin
- [x] Perawat
- [x] Dokter

### Fitur
- [x] Dashboard per role
- [x] CRUD Pasien
- [x] CRUD Master Obat
- [x] CRUD Master Tindakan
- [x] Antrian Perawat + Anamnesis
- [x] Antrian Dokter + Pemeriksaan
- [x] Resep Obat (auto kurangi stok)
- [ ] Generate PDF Surat Dokter (placeholder)
- [ ] Laporan lengkap (placeholder)
- [x] User Management lengkap (CRUD)

---

## Lisensi

MIT License

---

## Kontributor

- Klinik ITK Development Team
- Institut Teknologi Kalimantan

---

*Dokumentasi ini dibuat untuk memudahkan pengembangan dan maintenance sistem.*
