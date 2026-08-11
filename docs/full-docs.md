# Sistem Informasi Klinik ITK

<p align="center">
  <img src="../public/favicon.svg" alt="Klinik ITK Logo" width="120" height="120">
</p>

<p align="center">
  <strong>Sistem Informasi Manajemen Klinik Kampus</strong><br>
  Institut Teknologi Kalimantan
</p>

<p align="center">
  <a href="#tentang-aplikasi">Tentang</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#business-flow--siklus-status">Alur Kerja</a> •
  <a href="#role-based-access-control-rbac">Role & Hak Akses</a> •
  <a href="#fitur-utama--modul">Fitur Utama</a> •
  <a href="#skema-database--model-data">Skema Database</a> •
  <a href="#katalog-route--api">Katalog Route</a> •
  <a href="#instalasi--konfigurasi">Instalasi</a> •
  <a href="#akun-demo">Akun Demo</a> •
  <a href="#troubleshooting--faq">Troubleshooting</a> •
  <a href="../USER_MANUAL.md">Panduan Pengguna (User Manual)</a>
</p>

---

## Tentang Aplikasi

**Sistem Informasi Klinik ITK** adalah platform manajemen pelayanan kesehatan medis berbasis web yang dirancang khusus untuk memenuhi kebutuhan operasional Klinik Kampus Institut Teknologi Kalimantan (ITK). 

Sistem ini mengintegrasikan seluruh alur kerja operasional klinik secara *real-time*, mulai dari:
1. Pendaftaran pasien baru (Mahasiswa, Dosen, Tenaga Kependidikan, Pasien Umum) serta pencatatan draft pasien.
2. Pengelolaan antrian perawat, penilaian tanda-tanda vital (vital signs), dan pencatatan Asuhan Keperawatan (Askep).
3. Pemeriksaan fisik oleh dokter, penegakan diagnosis berbasis standar ICD-10, e-prescribing (resep obat otomatis yang mengintegrasikan pengurukan stok obat), dan pencatatan tindakan medis.
4. Penerbitan surat medis resmi PDF (Surat Keterangan Sakit, Surat Keterangan Sehat, Surat Rujukan Puskesmas/RS).
5. Pengiriman hasil screening kesehatan secara otomatis via e-mail dalam format dokumen PDF.
6. Laporan analitis komprehensif & export data dalam format PDF dan Microsoft Excel.
7. Audit log aktivitas pengguna untuk menjamin akuntabilitas data rekam medis.

---

## Tech Stack

| Layer | Teknologi / Library | Versi / Keterangan |
|---|---|---|
| **Backend Framework** | [Laravel Framework](https://laravel.com/) | v12.x (PHP 8.2+) |
| **Frontend Framework** | [Vue.js 3](https://vuejs.org/) + TypeScript | Composition API + Strict Types |
| **Single Page Bridge** | [Inertia.js](https://inertiajs.com/) | Full SSR (Server-Side Rendering) Support |
| **UI Design System** | [PrimeVue 4](https://primevue.org/) | Theme: Aura |
| **Styling & Utilities** | [Tailwind CSS 4](https://tailwindcss.com/) | Modern Utility-First CSS |
| **Database Engine** | MySQL | 8.0+ / MariaDB 10.4+ |
| **PDF Generation** | [DomPDF](https://github.com/barryvdh/laravel-dompdf) | `barryvdh/laravel-dompdf` v3.x |
| **Spreadsheet Handling** | [Laravel Excel](https://laravel-excel.com/) | `maatwebsite/excel` v3.1 |
| **Asset Bundler** | [Vite](https://vitejs.dev/) | Vite v6.x |

---

## Business Flow & Siklus Status

### 1. Alur Pelayanan Klinik

```
┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                          ALUR OPERASIONAL KLINIK ITK                                      │
└───────────────────────────────────────────────────────────────────────────────────────────┘

 ┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
 │    ADMIN     │     │   PERAWAT    │     │    DOKTER    │     │   OUTPUT     │
 │  Pendaftaran │ ──▶ │  Anamnesis   │ ──▶ │ Pemeriksaan  │ ──▶ │   Selesai    │
 └──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
       │                    │                    │                    │
       ▼                    ▼                    ▼                    ▼
 ┌──────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
 │ • Regis Pasien│    │ • Vital Sign │     │ • Diagnosis  │     │ • Rekam Medis│
 │   Baru/Draft │     │ • Lab Dasar  │     │   ICD-10     │     │ • Potong Stok│
 │ • Daftarkan  │     │ • Askep Keper│     │ • Resep Obat │     │   Obat       │
 │   Kunjungan  │     │ • Screening  │     │ • Tindakan   │     │ • PDF Surat  │
 │ • Auto No RM │     │   (Email PDF)│     │ • Surat      │     │ • Export     │
 │   & Kunjungan│     │              │     │   Dokter     │     │   Laporan    │
 └──────────────┘     └──────────────┘     └──────────────┘     └──────────────┘
```

### 2. Lifecyle Status Rekam Medis

- **`menunggu_perawat`**: Pasien baru mendaftar di admin/penerimaan dan masuk antrian perawat.
- **`proses_anamnesis`**: Perawat sedang/telah menyimpan sebagian data anamnesis (draft anamnesis).
- **`siap_dokter`**: Anamnesis & vital sign diselesaikan oleh perawat; pasien masuk antrian siap periksa dokter.
- **`sedang_diperiksa`**: Dokter membuka form pemeriksaan pasien.
- **`selesai`**: Dokter telah menyelesaikan pemeriksaan, memberikan diagnosis, resep obat, atau menerbitkan surat medis. *(Catatan: Untuk layanan khusus `screening`, status langsung berubah menjadi `selesai` setelah perawat menyelesaikan input anamnesis/screening).*
- **`batal`**: Antrian dibatalkan atau dihapus.

### 3. Format Penomoran Otomatis

* **Nomor Rekam Medis (RM)**: `RM{YYYY}{MM}{XXXX}` (Contoh: `RM2026080001`)
* **Nomor Kunjungan**: `KNJ{YYYYMMDD}{XXXX}` (Contoh: `KNJ202608030001`)
* **Nomor Surat Dokter**: `SRT/KL-ITK/{YYYY}/{MM}/{XXXX}` (Dapat disesuaikan oleh Admin/Dokter)

---

## Role-Based Access Control (RBAC)

Aplikasi memiliki 4 tingkatan hak akses berbasis role:

| Hak Akses / Fitur | Superadmin | Admin | Perawat | Dokter |
|---|:---:|:---:|:---:|:---:|
| **Dashboard Analytics & Statistics** | ✅ | ✅ | ✅ | ✅ |
| **Registrasi Pasien Baru & Draft Pasien** | ✅ | ✅ | ❌ | ❌ |
| **Aktivasi Draft Pasien & Edit Profil Pasien** | ✅ | ✅ | ❌ | ❌ |
| **Daftar Kunjungan Pasien (Buat Antrian Baru)** | ✅ | ✅ | ✅ | ❌ |
| **Master Data Obat & Master Data Tindakan** | ✅ | ✅ | ❌ | ❌ |
| **Antrian Perawat & Input Vital Sign / Askep** | ✅ | ✅ | ✅ | ❌ |
| **Kirim Hasil Screening via Email (PDF)** | ✅ | ✅ | ✅ | ❌ |
| **Antrian Dokter & Input Pemeriksaan / ICD-10** | ✅ | ❌ | ❌ | ✅ |
| **Input Resep Obat (Kurangi Stok Otomatis)** | ✅ | ❌ | ❌ | ✅ |
| **Terbitkan Surat Dokter (Sehat/Sakit/Rujukan)** | ✅ | ❌ | ❌ | ✅ |
| **Lihat & Cetak Laporan (PDF / Excel)** | ✅ | ✅ | ✅ | ✅ |
| **Manajemen User (CRUD Pengguna & Reset)** | ✅ | ❌ | ❌ | ❌ |
| **Audit Activity Log (Jejak Aktivitas System)** | ✅ | ✅ | ✅ | ✅ |
| **Hapus Rekam Medis (Permanent Soft Delete)** | ✅ | ❌ | ❌ | ❌ |

---

## Fitur Utama & Modul

### 1. Modul Pasien & Registrasi
- **Pendaftaran Pasien Multi-Kategori**: Mahasiswa, Dosen, Tenaga Kependidikan (Tendik), dan Umum.
- **Draft Registration System**: Pendaftaran cepat sementara yang dapat disimpan di tab *Tersimpan (Draft)* sebelum dipindahkan ke *Daftar Pasien Utama*.
- **Riwayat Medis Terintegrasi**: Melihat seluruh histori kunjungan, vital signs, riwayat penyakit keluarga/dahulu, alur pemeriksaan, resep obat, hingga surat medis dalam satu linimasa.
- **Cetak Draft Registrasi PDF**: Generate formulir fisik pendaftaran pasien dalam format PDF.

### 2. Modul Antrian & Anamnesis Perawat
- **Sistem Antrian Real-Time**: Antrian aktif hari ini, antrian terlewat jadwal, dan antrian selesai.
- **Pemeriksaan Vital Sign**: Tekanan darah (sistol/diastol), suhu, nadi, respirasi, tinggi badan, berat badan, lingkar perut, dan skala nyeri.
- **Pemeriksaan Laboratorium Sederhana**: Gula darah (Puasa/Sewaktu), Asam Urat, Kolesterol, Hemoglobin, dan Tes Buta Warna.
- **Asuhan Keperawatan (Askep)**: Diagnosa Keperawatan, Intervensi, Implementasi, dan Evaluasi Keperawatan.
- **Email Dispatcher Result Screening**: Mengirimkan PDF hasil screening kesehatan ke email terdaftar pasien dalam satu klik.

### 3. Modul Pemeriksaan Dokter & E-Prescribing
- **Input Diagnosis Standar International (ICD-10)**: Pencatatan diagnosis utama, diagnosis sekunder, serta kode ICD-10.
- **Auto Stock Deduction**: Pengurangan stok obat secara otomatis begitu resep disimpan oleh dokter.
- **Manajemen Penyesuaian Resep**: Saat terjadi pengeditan pemeriksaan resep, sistem otomatis mengembalikan (*revert/increment*) stok obat lama dan mengalkulasi ulang (*decrement*) stok obat baru secara aman dalam transaksi database.
- **Tindakan Medis & Biaya**: Pemilihan tindakan medis beserta kalkulasi biaya.

### 4. Modul Surat Dokter (PDF Generation)
- **Surat Keterangan Sakit**: Perhitungan tanggal mulai, tanggal selesai, dan lama hari istirahat secara otomatis.
- **Surat Keterangan Sehat**: Menampilkan hasil vital sign (TD, Suhu, TB, BB, Golongan Darah) serta status Buta Warna.
- **Surat Rujukan Medis**: Penerbitan surat rujukan ke Puskesmas/Rumah Sakit rujukan.

### 5. Modul Laporan & Export Data
- **Laporan Kunjungan Pasien**: Berdasarkan rentang tanggal, jenis layanan, dan tipe pasien.
- **Laporan Penggunaan Obat**: Monitoring pemakaian stok obat dan sisa stok minimum.
- **Laporan Tindakan Medis**: Statistik tindakan dan total pendapatan medis.
- **Export Multi-Format**: Download laporan medis dalam format PDF instan atau Microsoft Excel (`.xlsx`).
- **Import Record via Excel**: Fitur batch import rekam medis pasien dari file Excel menggunakan template standar.

### 6. Modul Activity Log & Audit Trail
- Pencatatan aktivitas (*created*, *updated*, *deleted*) secara otomatis pada seluruh model kritis (Pasien, Rekam Medis, Anamnesis, Pemeriksaan, Obat, Tindakan, Surat Dokter).
- Menyimpan IP address pengakses, ID user, nama model, serta perbandingan detail *old values* dan *new values* (JSON cast).

---

## Skema Database & Model Data

```
               ┌──────────────┐
               │    users     │
               └──────┬───────┘
                      │ 1:N (perawat_id / dokter_id)
                      ▼
┌──────────────┐ 1:N ┌──────────────┐ 1:1 ┌──────────────┐
│   pasiens    ├────▶│ rekam_medis  ├────▶│  anamnesis   │
└──────────────┘     └──────┬───────┘     └──────────────┘
                            │ 1:1
                            ▼
                     ┌──────────────┐ 1:N ┌──────────────┐ 1:N ┌──────────────┐
                     │ pemeriksaans ├────▶│ resep_obats  ├────▶│    obats     │
                     └──────┬───────┘     └──────────────┘     └──────────────┘
                            │
                            ├───────────▶ ┌───────────────────────┐ N:1 ┌───────────┐
                            │ 1:N         │ pemeriksaan_tindakans ├────▶│ tindakans │
                            │             └───────────────────────┘     └───────────┘
                            │ 1:N
                            ▼
                     ┌──────────────┐
                     │surat_dokters │
                     └──────────────┘
```

### Penjelasan Tabel Utama:

1. **`users`**: Data pengguna sistem (Superadmin, Admin, Perawat, Dokter) berbasis NIP, spesialisasi, status aktif, dan password terpola.
2. **`pasiens`**: Data identitas lengkap pasien, NIK, No. RM, tipe pasien, NIM/NIP, kontak darurat, consent, dan status draft (`is_draft`).
3. **`rekam_medis`**: Header transaksi kunjungan berisikan No. Kunjungan, tanggal, jenis layanan (`berobat`, `surat_sehat`, `screening`), status antrian, soft delete.
4. **`anamnesis`**: Data tanda-tanda vital, hasil lab sederhana, skala nyeri, keluhan utama, dan formulir Asuhan Keperawatan.
5. **`pemeriksaans`**: Data temuan fisik dokter, diagnosis utama & sekunder, kode ICD-10, prognosis, anjuran, dan penatalaksanaan medis.
6. **`obats`**: Master data obat, kode obat, stok, stok minimum, harga, dan status aktif.
7. **`tindakans`**: Master data tindakan medis, kode tindakan, biaya, dan deskripsi.
8. **`resep_obats`**: Detail resep obat yang diberikan dokter per pemeriksaan (jumlah, dosis, aturan pakai).
9. **`pemeriksaan_tindakans`**: Tabel pivot tindakan medis yang dilakukan pada saat pemeriksaan fisik.
10. **`surat_dokters`**: Data penerbitan surat medis (Surat Sakit, Surat Sehat, Surat Rujukan) yang terhubung ke Rekam Medis.
11. **`activity_logs`**: Log audit jejak aktivitas sistem (*user_id*, *action*, *model_type*, *old_values*, *new_values*, *ip_address*).

---

## Katalog Route & API

### Public Routes
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/` | Anonymous Redirection | Redirect ke `/login` |
| GET | `/login` | `AuthenticatedSessionController@create` | Halaman form login |
| POST | `/login` | `AuthenticatedSessionController@store` | Proses otentikasi login |
| POST | `/logout` | `AuthenticatedSessionController@destroy` | Sesi logout |

### Authenticated Common Routes (All Roles)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/dashboard` | `DashboardController@index` | Dashboard statistik per role |
| GET | `/dashboard/analitik-pasien` | `DashboardController@analitikPasien` | Analytics grafik pasien |
| GET | `/profile` | `ProfileController@edit` | Form edit profil pengguna |
| PATCH | `/profile` | `ProfileController@update` | Update profil pengguna |
| GET | `/activity-logs` | `ActivityLogController@index` | Halaman daftar log aktivitas |

### Admin & Superadmin Routes (`middleware: role:superadmin,admin`)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/pasien/create` | `PasienController@create` | Form registrasi & tab draft pasien |
| POST | `/pasien` | `PasienController@store` | Simpan registrasi pasien baru |
| GET | `/pasien/{pasien}/edit` | `PasienController@edit` | Form edit data pasien |
| PUT | `/pasien/{pasien}` | `PasienController@update` | Update data pasien |
| DELETE | `/pasien/{pasien}` | `PasienController@destroy` | Soft delete data pasien |
| POST | `/pasien/{pasien}/kunjungan` | `PasienController@daftarKunjungan` | Daftarkan kunjungan pasien lama |
| POST | `/pasien/{pasien}/activate` | `PasienController@activate` | Pindahkan draft ke pasien utama |
| GET | `/pasien/{pasien}/draf/pdf` | `PasienController@cetakDrafPdf` | Download PDF formulir draf registrasi |
| GET/POST | `/obat` | `ObatController@index` / `store` | Kelola master obat |
| PUT/DELETE| `/obat/{obat}` | `ObatController@update` / `destroy` | Update / hapus obat |
| GET/POST | `/tindakan` | `TindakanController@index` / `store` | Kelola master tindakan |
| PUT/DELETE| `/tindakan/{tindakan}` | `TindakanController@update` / `destroy` | Update / hapus tindakan |
| PATCH | `/surat-dokter/{id}/nomor`| `SuratDokterController@updateNomor` | Update nomor surat resmi |

### Perawat Routes (`middleware: role:superadmin,admin,perawat`)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/perawat/antrian` | `PerawatController@antrian` | Antrian pasien perawat |
| POST | `/perawat/anamnesis` | `PerawatController@storeAnamnesis` | Simpan data vital sign & askep |
| GET | `/perawat/anamnesis/{rm}/pdf`| `PerawatController@cetakAnamnesisPdf` | Stream PDF anamnesis/screening |
| POST | `/perawat/anamnesis/{rm}/send-email` | `PerawatController@sendEmailScreening` | Kirim PDF screening ke email pasien |
| POST | `/antrian` | `PerawatController@storeAntrian` | Tambah kunjungan antrian baru |
| PUT | `/antrian/{rekamMedis}` | `PerawatController@updateAntrian` | Update antrian |
| DELETE | `/antrian/{rekamMedis}` | `PerawatController@destroyAntrian` | Hapus antrian |

### Dokter Routes (`middleware: role:superadmin,dokter`)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/dokter/antrian` | `DokterController@antrian` | Antrian pasien siap periksa |
| GET | `/dokter/pemeriksaan/{rm}`| `DokterController@pemeriksaanForm` | Form pemeriksaan fisik & resep |
| POST | `/dokter/pemeriksaan` | `DokterController@storePemeriksaan` | Simpan diagnosis, resep & surat |

### Rekam Medis & Surat Dokter (Medical Records)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/pasien` | `PasienController@index` | Daftar pasien utama |
| GET | `/pasien/{pasien}` | `PasienController@show` | Detail & riwayat pasien |
| GET | `/pasien/{pasien}/rekam-medis` | `PasienController@rekamMedis` | Detail rekam medis lengkap |
| PUT | `/rekam-medis/{rm}/anamnesis` | `RekamMedisController@updateAnamnesis` | Edit data anamnesis |
| PUT | `/rekam-medis/{rm}/pemeriksaan` | `RekamMedisController@updatePemeriksaan` | Edit data pemeriksaan dokter |
| GET | `/pasien/rekam-medis/template` | `RekamMedisController@downloadTemplate` | Download template Excel rekam medis |
| POST | `/pasien/{pasien}/rekam-medis/import` | `RekamMedisController@importExcel` | Import batch rekam medis dari Excel |
| GET | `/surat-dokter/{surat}/pdf` | `SuratDokterController@generatePdf` | Download PDF surat dokter |
| GET | `/surat-dokter/{surat}/preview` | `SuratDokterController@previewPdf` | Stream preview PDF surat dokter |

### Laporan Routes (`middleware: role:superadmin,admin,dokter,perawat`)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/laporan` | `LaporanController@index` | Menu utama laporan |
| GET | `/laporan/kunjungan` | `LaporanController@kunjungan` | Laporan data kunjungan |
| GET | `/laporan/kunjungan/pdf` | `LaporanController@kunjunganPdf` | Export PDF kunjungan |
| GET | `/laporan/obat` | `LaporanController@obat` | Laporan pemakaian obat |
| GET | `/laporan/obat/pdf` | `LaporanController@obatPdf` | Export PDF obat |
| GET | `/laporan/tindakan` | `LaporanController@tindakan` | Laporan tindakan medis |
| GET | `/laporan/tindakan/pdf` | `LaporanController@tindakanPdf` | Export PDF tindakan |
| GET | `/laporan/pemeriksaan-umum` | `LaporanController@pemeriksaanUmum` | Laporan pemeriksaan umum |
| GET | `/laporan/pemeriksaan-umum/pdf` | `LaporanController@pemeriksaanUmumPdf` | Export PDF pemeriksaan umum |
| GET | `/laporan/pemeriksaan-umum/excel` | `LaporanController@pemeriksaanUmumExcel` | Export Excel pemeriksaan umum |
| GET | `/laporan/screening` | `LaporanController@screening` | Laporan hasil screening |
| GET | `/laporan/screening/pdf` | `LaporanController@screeningPdf` | Export PDF screening |
| GET | `/laporan/screening/excel` | `LaporanController@screeningExcel` | Export Excel screening |
| GET | `/laporan/diagnosis` | `LaporanController@diagnosis` | Laporan demografi diagnosis |
| GET | `/laporan/diagnosis/pdf` | `LaporanController@diagnosisPdf` | Export PDF diagnosis |

### Superadmin Special Routes (`middleware: role:superadmin`)
| Method | URI Path | Controller Action | Description |
|---|---|---|---|
| GET | `/users` | `UserController@index` | Kelola akun pengguna |
| POST | `/users` | `UserController@store` | Tambah akun pengguna baru |
| PUT | `/users/{user}` | `UserController@update` | Edit akun pengguna |
| DELETE | `/users/{user}` | `UserController@destroy` | Hapus akun pengguna |
| POST | `/users/{user}/toggle-active` | `UserController@toggleActive` | Aktifkan / Nonaktifkan akun |
| DELETE | `/rekam-medis/{rm}` | `RekamMedisController@destroy` | Hapus rekam medis secara permanen |

---

## Instalasi & Konfigurasi

### Requirements System
* **PHP**: `>= 8.2` (dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `xml`)
* **Composer**: `>= 2.x`
* **Node.js**: `>= 18.x` (disarankan LTS)
* **MySQL Engine**: `>= 8.0` atau MariaDB `>= 10.4`

### Langkah Instalasi (Local Environment)

```bash
# 1. Clone repositori ke komputer lokal Anda
git clone <repository-url>
cd klinik-itk

# 2. Install dependensi PHP via Composer
composer install

# 3. Install dependensi JavaScript/Node via NPM
npm install

# 4. Salin file environment konfigurasi
cp .env.example .env

# 5. Generate Application Encryption Key
php artisan key:generate

# 6. Konfigurasi koneksi database di file .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klinik_itk
DB_USERNAME=root
DB_PASSWORD=your_password

# Konfigurasi Pengiriman Email (Optional - SMTP / Mailtrap)
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="klinik@itk.ac.id"
MAIL_FROM_NAME="${APP_NAME}"

# 7. Jalankan migrasi tabel & seeder data awal/demo
php artisan migrate:fresh --seed

# 8. Build aset frontend (pilih salah satu alur jalan)
npm run build
```

### Menjalankan Mode Development

Jalankan 2 perintah berikut di dua jendela terminal terpisah:

**Terminal 1 (Laravel Development Server):**
```bash
php artisan serve
```

**Terminal 2 (Vite Hot Reload Server):**
```bash
npm run dev
```

Buka peramban (browser) Anda dan akses alamat: `http://localhost:8000`

---

## Akun Demo

Sistem ini dilengkapi dengan `UserSeeder` yang membuat akun demo untuk setiap role (Password default semua akun: **`password`**):

| Role | Nama Pengguna | Email | Spesialisasi / NIP | Password |
|---|---|---|---|---|
| **Super Admin** | Super Admin | `superadmin@itk.ac.id` | NIP: 198001012000011001 | `password` |
| **Admin Klinik** | Admin Klinik | `admin@itk.ac.id` | NIP: 198501012010011001 | `password` |
| **Perawat (1)** | Ns. Siti Aminah, S.Kep | `perawat@itk.ac.id` | NIP: 199001012015012001 | `password` |
| **Perawat (2)** | Ns. Dewi Kartika, S.Kep | `perawat2@itk.ac.id` | NIP: 199101012016012001 | `password` |
| **Dokter (1)** | dr. Ahmad Rizki, Sp.PD | `dokter@itk.ac.id` | Spesialis Penyakit Dalam | `password` |
| **Dokter (2)** | dr. Ratna Dewi | `dokter2@itk.ac.id` | Dokter Umum | `password` |

---

## Format Import Template Excel Rekam Medis

Untuk melakukan batch import data rekam medis pasien via Excel (`/pasien/{pasien}/rekam-medis/import`), file `.xlsx` atau `.csv` harus mengikuti header kolom berikut:

| Nama Kolom (Header) | Contoh Isian | Status |
|---|---|---|
| `tanggal_kunjungan` | `2026-08-01` atau `2026-08-01 09:30:00` | Wajib |
| `jenis_layanan` | `berobat`, `surat_sehat`, atau `screening` | Wajib |
| `keluhan_utama` | `Demam tinggi sejak 2 hari` | Wajib |
| `tekanan_darah` | `120/80` | Opsional |
| `suhu` | `36.8` | Opsional |
| `nadi` | `80` | Opsional |
| `respirasi` | `20` | Opsional |
| `tinggi_badan` | `170` | Opsional |
| `berat_badan` | `65` | Opsional |
| `diagnosis_utama` | `Febris Intermiten` | Opsional |
| `kode_icd10` | `R50.9` | Opsional |
| `pemeriksaan_fisik` | `Faring hiperemis` | Opsional |

---

## Troubleshooting & FAQ

### 1. Gagal Login / Credentials Missmatch
Jalankan ulang migrasi dan seeder database untuk menyegarkan password hash:
```bash
php artisan migrate:fresh --seed
```

### 2. Tampilan Frontend Rusak / Style Tailwind Tidak Muncul
Pastikan Vite dev server sedang berjalan atau lakukan build ulang aset frontend:
```bash
php artisan cache:clear
php artisan config:clear
npm run build
```

### 3. PDF Surat Dokter atau Laporan Blank / Timeout
Pastikan ekstensi PHP `gd` dan `mbstring` sudah diaktifkan di `php.ini` Anda. Jika menggunakan logo/gambar lokal di template Blade PDF, pastikan link gambar menggunakan path lokal atau base64 data URI.

### 4. Kirim Email Screening Error
Jika pengiriman email screening gagal:
- Pastikan pasien memiliki field `email` yang terisi valid.
- Periksa konfigurasi `MAIL_*` pada file `.env`. Untuk keperluan pengujian lokal, Anda dapat menggunakan `MAIL_MAILER=log` agar email ditulis ke file log `storage/logs/laravel.log`.

---

## Dokumentasi Terkait

- [Panduan Pengguna (User Manual)](../USER_MANUAL.md) — Panduan lengkap operasional layar per layar untuk pengguna non-IT (Admin, Perawat, Dokter).

---

## Tim Pengembang & Lisensi

- **Pengembang**: Klinik ITK Development Team
- **Institusi**: Institut Teknologi Kalimantan (ITK)
- **Lisensi**: Software ini dilindungi di bawah [MIT License](../LICENSE).
