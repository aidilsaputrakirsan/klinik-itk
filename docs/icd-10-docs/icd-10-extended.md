# Daftar Kode ICD-10: Cedera Kepala Superfisial & Kode Gigitan Hewan/Serangga

## 1. Kode Utama: S00 - Superficial Injury of Head
Kode **S00** digunakan untuk **Cedera superfisial/permukaan pada kepala** (seperti luka lecet, memar, luka lepuh non-termal, atau gigitan serangga non-bisa). Kode ini bersifat *non-billable* dan wajib diikuti digit tambahan untuk spesifikasi lokasi:

* **S00.0**: *Superficial injury of scalp* (Cedera superfisial pada kulit kepala)
* **S00.1**: *Contusion of eyelid and periocular area* (Memar pada kelopak mata dan area sekitar mata / *black eye*)
* **S00.2**: *Other superficial injuries of eyelid and periocular area* (Cedera superfisial lainnya pada kelopak mata dan sekitar mata)
* **S00.3**: *Superficial injury of nose* (Cedera superfisial pada hidung)
* **S00.4**: *Superficial injury of ear* (Cedera superfisial pada telinga)
* **S00.5**: *Superficial injury of lip and oral cavity* (Cedera superfisial pada bibir dan rongga mulut)
* **S00.7**: *Multiple superficial injuries of head* (Cedera superfisial majemuk/lebih dari satu lokasi di kepala)
* **S00.8**: *Superficial injury of other parts of head* (Cedera superfisial pada bagian kepala lainnya)
* **S00.9**: *Superficial injury of head, part unspecified* (Cedera superfisial pada bagian kepala yang tidak spesifik)

---

## 2. Kelompok Gigitan Serangga & Arthropoda (Insect Bites)

| Jenis Gigitan | Kode Penyebab Eksternal (External Cause) | Kode Dampak Toksik / Cedera Utama |
| :--- | :--- | :--- |
| **Serangga Umum** (Nyamuk, kutu kasur, lalat) | **W57** *(Nonvenomous insect)* | **T14.0** *(Superficial injury)* atau seri **S00-S99** (sesuai area tubuh) |
| **Lebah, Tawon, & Hornet** | **X23** *(Contact with bees/wasps)* | **T63.4** *(Toxic effect of other arthropods)* |
| **Laba-laba Berbisa** | **X21** *(Contact with venomous spiders)* | **T63.3** *(Toxic effect of spider venom)* |
| **Kalajengking** | **X22** *(Contact with scorpions)* | **T63.2** *(Toxic effect of scorpion venom)* |
| **Kelabang / Lipan** | **X24** *(Contact with centipedes)* | **T63.4** *(Toxic effect of other arthropods)* |

---

## 3. Kelompok Ular & Reptil (Snake & Reptile Bites)

| Jenis Gigitan | Kode Penyebab Eksternal (External Cause) | Kode Diagnosis Utama (Dampak) |
| :--- | :--- | :--- |
| **Ular Berbisa** (Kobra, tanah, weling) | **X20** *(Contact with venomous snakes)* | **T63.0** *(Toxic effect of snake venom)* |
| **Ular Tidak Berbisa** (Sanca, londo) | **W59** *(Bitten by other reptiles)* | **T14.1** atau seri **S** *(Open wound)* |
| **Buaya / Aligator** | **W58** *(Bitten by crocodile/alligator)* | **T14.1** atau seri **S** *(Open wound)* |

---

## 4. Kelompok Hewan Mamalia & Lainnya (Animal & Human Bites)

| Jenis Gigitan Hewan | Kode ICD-10 / Penyebab Eksternal | Keterangan / Kode Diagnosis Utama |
| :--- | :--- | :--- |
| **Anjing** | **W54** | Dipasangkan dengan luka terbuka (**T14.1** / Seri **S**) |
| **Kucing** | **W55.01** | Kode cakar/gigitan kucing. (Tambahkan **A28.1** jika terkena *Cat-scratch disease*) |
| **Tikus & Hewan Pengerat** | **W53** | Mencakup tikus got, hamster, atau tupai |
| **Mamalia Lain** (Monyet, kelelawar) | **W55** | Digunakan untuk mamalia lain yang tidak dirinci khusus |
| **Manusia** (Gigitan sesama) | **W50** | Luka robek atau cedera akibat gigitan manusia |

---

## 5. Ekstensi Karakter ke-7 (Khusus Standar ICD-10-CM)
Untuk sistem pengodean yang menggunakan standar ICD-10-CM, tambahkan karakter ekstensi berikut di akhir kode untuk mengidentifikasi status pertemuan/kunjungan pasien:
* **A** (Kunjungan Awal / *Initial encounter*), contoh: **W54.XXXA**
* **D** (Kunjungan Lanjutan/Kontrol / *Subsequent encounter*), contoh: **W54.XXXD**
* **S** (Gejala Sisa/Komplikasi / *Sequela*), contoh: **W54.XXXS**

---

## 6. Integrasi pada Sistem Informasi Manajemen Klinik ITK
Seluruh kode di atas telah diintegrasikan ke dalam katalog diagnosis sistem:
* **Modul Dataset**: [resources/js/data/icd10.ts](file:///c:/Users/Jetro/Documents/GitHub/klinik-itk/resources/js/data/icd10.ts)
* **Komponen Modal Katalog**: [resources/js/Components/Icd10SelectorModal.vue](file:///c:/Users/Jetro/Documents/GitHub/klinik-itk/resources/js/Components/Icd10SelectorModal.vue)
* **Kategori Baru di Katalog**: `Gigitan Hewan & Toksikologi` dan pelengkap `Cedera & Trauma Fisik (P3K)`.
* **Form Pemeriksaan Dokter**: Dokter dapat mengetik kata kunci langsung (misal: *anjing*, *kucing*, *ular*, *lebah*, *kepala*, *S00*, *W54*, dll.) pada input Diagnosis Utama maupun memilih melalui tombol **Katalog ICD-10**.
