# Panduan Pengguna Sistem Informasi Klinik ITK

<p align="center">
  <img src="public/favicon.svg" alt="Klinik ITK Logo" width="80" height="80">
</p>

<p align="center">
  <strong>Panduan Lengkap untuk Pengguna</strong><br>
  Sistem Informasi Klinik Institut Teknologi Kalimantan
</p>

---

## Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Cara Login](#cara-login)
3. [Panduan untuk Admin](#panduan-untuk-admin)
4. [Panduan untuk Perawat](#panduan-untuk-perawat)
5. [Panduan untuk Dokter](#panduan-untuk-dokter)
6. [Panduan untuk Super Admin](#panduan-untuk-super-admin)
7. [Mengelola Profil](#mengelola-profil)
8. [FAQ (Pertanyaan Umum)](#faq-pertanyaan-umum)

---

## Pendahuluan

Sistem Informasi Klinik ITK adalah aplikasi berbasis web untuk membantu pengelolaan pelayanan kesehatan di Klinik Kampus Institut Teknologi Kalimantan.

### Alur Pelayanan Klinik

```
Pasien Datang → Admin Mendaftarkan → Perawat Mengukur Vital Sign → Dokter Memeriksa → Selesai
```

### Akses Aplikasi

Buka browser (Chrome, Firefox, atau Edge) dan ketik alamat:
```
https://klinik-itk.myst-tech.com/
```

---

## Cara Login

1. Buka halaman aplikasi di browser
2. Anda akan melihat halaman login dengan logo Klinik ITK
3. Masukkan **Email** dan **Password** Anda
4. Centang **"Ingat saya"** jika ingin tetap login
5. Klik tombol **"Masuk"**

> **Tips:** Jika lupa password, hubungi Super Admin untuk reset password.

---

## Panduan untuk Admin

Admin bertugas untuk **mendaftarkan pasien** dan **mengelola data master** (obat & tindakan).

### Menu yang Tersedia

| Menu | Fungsi |
|------|--------|
| Dashboard | Melihat ringkasan data klinik |
| Registrasi Pasien | Mendaftarkan pasien baru |
| Daftar Pasien | Melihat & mengelola data pasien |
| Master Obat | Mengelola daftar obat |
| Master Tindakan | Mengelola daftar tindakan medis |
| Laporan | Melihat & download laporan |

---

### A. Mendaftarkan Pasien Baru

1. Klik menu **"Registrasi Pasien"** di sidebar kiri
2. Isi formulir pendaftaran:
   - **NIK** - Nomor Induk Kependudukan (16 digit)
   - **Nama Lengkap** - Nama sesuai KTP
   - **Tanggal Lahir** - Pilih dari kalender
   - **Jenis Kelamin** - Pilih Laki-laki atau Perempuan
   - **Alamat** - Alamat lengkap
   - **No. Telepon** - Nomor HP yang bisa dihubungi
   - **Tipe Pasien** - Pilih: Mahasiswa, Dosen, Tendik, atau Umum
   - **NIM/NIP** - Isi jika mahasiswa/dosen/tendik
   - **Fakultas/Jurusan** - Isi jika civitas ITK
   - **Jenis Layanan** - Pilih: Berobat, Surat Sehat, atau Screening
3. Klik tombol **"Simpan"**
4. Sistem akan otomatis membuat:
   - **Nomor Rekam Medis (No. RM)** - contoh: RM20241230001
   - **Nomor Kunjungan** - contoh: KNJ202412300001
5. Pasien akan masuk ke antrian perawat

---

### B. Mendaftarkan Kunjungan Pasien Lama

Jika pasien **sudah pernah terdaftar** dan datang kembali:

1. Klik menu **"Daftar Pasien"**
2. Cari nama pasien di kolom pencarian
3. Klik tombol **kalender hijau** (Daftar Kunjungan Baru) di kolom Aksi
4. Konfirmasi dengan klik **"Ya, Daftarkan"**
5. Sistem akan membuat nomor kunjungan baru
6. Pasien masuk ke antrian perawat

**Atau** dari halaman Detail Pasien:
1. Klik ikon **mata** untuk melihat detail pasien
2. Klik tombol **"Daftar Kunjungan Baru"** di bagian atas

---

### C. Melihat Detail & Riwayat Pasien

1. Klik menu **"Daftar Pasien"**
2. Klik ikon **mata** di kolom Aksi
3. Anda akan melihat:
   - **Data Pasien** - Informasi lengkap pasien
   - **Riwayat Kunjungan** - Semua kunjungan sebelumnya
   - Klik salah satu kunjungan untuk melihat detail (anamnesis, diagnosis, resep, surat dokter)

---

### D. Mengelola Master Obat

1. Klik menu **"Master Obat"**
2. **Tambah Obat Baru:**
   - Klik tombol **"Tambah Obat"**
   - Isi: Kode, Nama, Satuan, Stok, Stok Minimum, Harga
   - Klik **"Simpan"**
3. **Edit Obat:**
   - Klik ikon **pensil** di baris obat
   - Ubah data yang diperlukan
   - Klik **"Simpan"**
4. **Hapus Obat:**
   - Klik ikon **tempat sampah**
   - Konfirmasi penghapusan

> **Catatan:** Obat dengan stok di bawah minimum akan ditandai merah.

---

### E. Mengelola Master Tindakan

1. Klik menu **"Master Tindakan"**
2. **Tambah Tindakan:**
   - Klik **"Tambah Tindakan"**
   - Isi: Kode, Nama, Biaya
   - Klik **"Simpan"**
3. Edit dan hapus sama seperti Master Obat

---

### F. Melihat Laporan

1. Klik menu **"Laporan"**
2. Pilih jenis laporan:
   - **Laporan Kunjungan** - Data semua kunjungan pasien
   - **Laporan Obat** - Penggunaan obat & stok saat ini
   - **Laporan Tindakan** - Tindakan yang dilakukan & pendapatan
3. Atur rentang tanggal dengan memilih **Tanggal Mulai** dan **Tanggal Akhir**
4. Klik **"Terapkan Filter"**
5. Klik **"Download PDF"** untuk mengunduh laporan

---

## Panduan untuk Perawat

Perawat bertugas untuk **memanggil pasien dari antrian** dan **mengisi data anamnesis** (vital sign).

### Menu yang Tersedia

| Menu | Fungsi |
|------|--------|
| Dashboard | Melihat jumlah pasien menunggu |
| Antrian Pasien | Melihat & memanggil pasien |

---

### A. Melihat Antrian Pasien

1. Klik menu **"Antrian Pasien"**
2. Anda akan melihat daftar pasien yang **menunggu anamnesis**
3. Data yang ditampilkan:
   - Nomor Kunjungan
   - Nama Pasien
   - Jenis Layanan
   - Waktu Daftar

---

### B. Memanggil & Input Anamnesis

1. Di halaman **Antrian Pasien**, cari pasien yang akan dipanggil
2. Klik tombol **"Panggil"** atau **"Input Anamnesis"**
3. Isi data anamnesis:

   | Field | Keterangan | Contoh |
   |-------|------------|--------|
   | Tekanan Darah | Sistol/Diastol (mmHg) | 120/80 |
   | Suhu Tubuh | Dalam Celcius | 36.5 |
   | Denyut Nadi | Per menit | 80 |
   | Pernapasan | Per menit | 20 |
   | Tinggi Badan | Dalam cm | 170 |
   | Berat Badan | Dalam kg | 65 |
   | Keluhan | Keluhan utama pasien | Demam 2 hari |
   | Riwayat Penyakit | Penyakit yang pernah diderita | Maag |
   | Riwayat Alergi | Alergi obat/makanan | Tidak ada |

4. Klik **"Simpan Anamnesis"**
5. Status pasien berubah menjadi **"Siap untuk Dokter"**
6. Pasien otomatis masuk ke antrian dokter

> **Tips:** Isi data dengan lengkap agar dokter mendapat informasi yang cukup.

---

## Panduan untuk Dokter

Dokter bertugas untuk **memeriksa pasien**, memberikan **diagnosis**, **resep obat**, dan **surat dokter**.

### Menu yang Tersedia

| Menu | Fungsi |
|------|--------|
| Dashboard | Melihat jumlah pasien menunggu |
| Pasien Siap Periksa | Melihat antrian & melakukan pemeriksaan |
| Laporan | Melihat laporan (opsional) |

---

### A. Melihat Antrian Pasien

1. Klik menu **"Pasien Siap Periksa"**
2. Anda akan melihat daftar pasien yang **sudah selesai anamnesis**
3. Setiap pasien menampilkan:
   - Data pasien
   - Data anamnesis dari perawat (vital sign)

---

### B. Melakukan Pemeriksaan

1. Di halaman **Pasien Siap Periksa**, klik **"Periksa"** pada pasien
2. Review data anamnesis yang sudah diisi perawat
3. Isi data pemeriksaan:

   **Diagnosis:**
   | Field | Keterangan |
   |-------|------------|
   | Diagnosis Utama | Diagnosis primer |
   | Diagnosis Sekunder | Diagnosis tambahan (opsional) |
   | Kode ICD-10 | Kode standar penyakit |
   | Catatan Pemeriksaan | Temuan dari pemeriksaan |
   | Anjuran | Saran untuk pasien |

   **Tindakan (opsional):**
   - Centang tindakan yang dilakukan
   - Isi jumlah jika lebih dari 1

   **Resep Obat (opsional):**
   - Pilih obat dari daftar
   - Isi jumlah
   - Isi aturan pakai (contoh: 3x1 setelah makan)
   - Klik **"+ Tambah Obat"** untuk obat lain

   **Surat Dokter (opsional):**
   - Pilih jenis: Surat Keterangan Sehat atau Surat Keterangan Sakit
   - Untuk Surat Sakit: isi tanggal mulai, tanggal selesai, dan jumlah hari istirahat

4. Klik **"Simpan Pemeriksaan"**
5. Status pasien berubah menjadi **"Selesai"**

> **Catatan:** Stok obat akan otomatis berkurang sesuai resep.

---

### C. Mencetak Surat Dokter

Setelah pemeriksaan selesai dan ada surat dokter:

1. Buka **"Daftar Pasien"** > cari pasien > klik ikon **mata**
2. Di bagian riwayat kunjungan, cari kunjungan yang memiliki surat
3. Klik untuk melihat detail
4. Di bagian **Surat Dokter**, klik:
   - **"Preview"** - untuk melihat surat di layar
   - **"Download PDF"** - untuk mengunduh file PDF

---

## Panduan untuk Super Admin

Super Admin memiliki **akses penuh** ke semua fitur, termasuk **mengelola pengguna sistem**.

### Menu Tambahan

| Menu | Fungsi |
|------|--------|
| Kelola Pengguna | Menambah, edit, hapus akun pengguna |

---

### A. Mengelola Pengguna

1. Klik menu **"Kelola Pengguna"**
2. **Tambah Pengguna Baru:**
   - Klik **"Tambah Pengguna"**
   - Isi data:
     - Nama
     - Email (untuk login)
     - Password
     - Role (Super Admin / Admin / Perawat / Dokter)
     - NIP (opsional)
     - No. Telepon (opsional)
     - Spesialisasi (untuk dokter)
   - Klik **"Simpan"**

3. **Edit Pengguna:**
   - Klik ikon **pensil**
   - Ubah data yang diperlukan
   - Kosongkan password jika tidak ingin mengubah
   - Klik **"Simpan"**

4. **Nonaktifkan/Aktifkan Pengguna:**
   - Klik toggle **"Aktif"** untuk menonaktifkan
   - Pengguna yang tidak aktif tidak bisa login

5. **Hapus Pengguna:**
   - Klik ikon **tempat sampah**
   - Konfirmasi penghapusan

> **Peringatan:** Hati-hati saat menghapus pengguna. Data tidak dapat dikembalikan.

---

## Mengelola Profil

Semua pengguna dapat mengelola profil sendiri.

### A. Mengakses Profil

1. Klik **avatar/foto** di pojok kanan atas
2. Pilih **"Profil"**

### B. Mengubah Informasi Profil

1. Di halaman Profil, ubah data yang diinginkan:
   - Nama
   - Email
   - No. Telepon
2. Klik **"Simpan Perubahan"**

### C. Mengubah Password

1. Di halaman Profil, scroll ke bagian **"Ubah Password"**
2. Isi:
   - Password Saat Ini
   - Password Baru
   - Konfirmasi Password Baru
3. Klik **"Ubah Password"**

> **Tips Keamanan:**
> - Gunakan password minimal 8 karakter
> - Kombinasikan huruf, angka, dan simbol
> - Jangan bagikan password ke orang lain

---

## FAQ (Pertanyaan Umum)

### 1. Bagaimana jika lupa password?

Hubungi Super Admin untuk mereset password Anda.

### 2. Pasien tidak muncul di antrian perawat?

Pastikan pasien sudah didaftarkan oleh Admin dan statusnya "Menunggu Perawat".

### 3. Pasien tidak muncul di antrian dokter?

Pastikan perawat sudah mengisi anamnesis dan menyimpannya.

### 4. Stok obat tidak berkurang setelah resep?

Periksa apakah pemeriksaan sudah disimpan dengan benar. Stok berkurang saat pemeriksaan disimpan.

### 5. Tidak bisa download PDF?

- Pastikan popup blocker tidak memblokir
- Coba gunakan browser lain (Chrome direkomendasikan)
- Hubungi Admin IT jika masalah berlanjut

### 6. Data yang diinput salah, bagaimana mengubahnya?

- **Data Pasien:** Admin dapat mengedit melalui menu Daftar Pasien
- **Data Kunjungan:** Hubungi Super Admin
- **Data Anamnesis/Pemeriksaan:** Data yang sudah disimpan tidak dapat diubah untuk menjaga integritas rekam medis

### 7. Bagaimana melihat riwayat kunjungan pasien?

Melalui menu **Daftar Pasien** > klik ikon **mata** pada pasien > lihat bagian **Riwayat Kunjungan**.

### 8. Bagaimana cara logout?

Klik **avatar** di pojok kanan atas > pilih **"Logout"**.

---

## Kontak Bantuan

Jika mengalami kendala teknis, hubungi:

- **Admin IT Klinik ITK**
- **Email:** -
- **Telepon:** -

---

<p align="center">
  <strong>Klinik ITK</strong><br>
  Melayani Kesehatan Civitas Akademika<br>
  Institut Teknologi Kalimantan
</p>
