# Dokumentasi Integrasi Laravel Excel (Maatwebsite)

Repositori ini memanfaatkan paket [maatwebsite/excel](https://docs.laravel-excel.com/) sebagai mesin utama untuk fitur Ekspor dan Impor data berskala besar, khususnya untuk import data "Rekam Medis".

## Tujuan
Library ini ditambahkan spesifik untuk memenuhi persyaratan agar pihak administrasi dan petugas klinis tidak perlu mengetik daftar panjang arsip rekam medis secara manual satu persatu jika rekaman lama sebelumnya disusun dalam wujud Spreadsheet (*Excel* `.xlsx` atau `.csv`).

## Alur Implementasi
Implementasi library ini berada di:
1. Direktori `/app/Imports/` yang akan menyimpan Class *importer* berbasis Eloquent Models.
2. Endpoint rute HTTP (`routes/web.php`) yang menyalurkan `request()->file(...)` dari UI ke sistem antrean data *Laravel Excel*.

## Kelebihan bagi sistem Klinik-ITK
1. **Keamanan Transaksi**: Setiap berkas file yang berhasil diurai baris per barisnya akan diolah ke *ORM Models*. Apabila ada satu kolom yang *error*, keseluruhan pemasukan data bisa di *rollback*.
2. **Fleksibilitas Template**: Sistem mampu membuat / menghasilkan file kosongan (Template) `Excel` sesuai dengan konfigurasi array yang didikte dalam metode *Export* untuk *matching header*.
3. **Penyambungan Relasi (*Mapping*)**: Import class memungkinkan kita mencari ID referensi pengguna asli dari nama teks string. (Contoh: Menemukan ID Dokter "Dr. Budiman" di database untuk dikaitkan dengan tabel `Pemeriksaan`).

## Catatan Dukungan
Segala dependensi tambahan yang terpasang akan berada di dalam `composer.json`. Instalasi ini diinisiasi per `April 2026` saat transisi fitur *Horizontal Grid Injection* di tabel Rekam Medis.
