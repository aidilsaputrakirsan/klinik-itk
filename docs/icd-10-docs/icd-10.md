# Buku Panduan & Katalog Kode ICD-10 (Klinik ITK)

Dokumen ini merupakan panduan resmi klasifikasi diagnosis medis berbasis **International Statistical Classification of Diseases and Related Health Problems 10th Revision (ICD-10 - WHO)** yang diterapkan pada **Sistem Informasi Manajemen Klinik ITK**.

Dokumen ini disusun untuk memudahkan tenaga medis (Dokter, Perawat) dan pengembang sistem dalam:
1. Memahami struktur dan kaidah pengkodean diagnosis medis standar.
2. Mempercepat penegakan dan input diagnosis pada form pemeriksaan rekam medis.
3. Menstandarkan format penamaan diagnosis (*Bahasa Indonesia & Bahasa Inggris*) untuk pelaporan morbiditas (Laporan 10 Besar Penyakit).

---

## 1. Anatomi dan Struktur Kode ICD-10

Kode ICD-10 tersusun secara alfanumerik dengan hierarki sistematis sebagai berikut:

```
    A 0 1 . 0
    │ │ │   └── Karakter ke-4 (Sub-kategori: rincian klinis, etiologi, atau anatomi spesifik)
    │ └───┴──── Karakter ke-2 & ke-3 (Kategori numerik 3-karakter: 00 - 99)
    └────────── Karakter ke-1 (Alfabet: Menunjukkan Bab / Chapter A sampai Z)
```

### Tingkatan Hierarki Pengkodean:
1. **Bab (Chapter)**: Pengelompokan utama penyakit berdasarkan sistem biologis, etiologi, atau kondisi khusus (Contoh: *Bab X: Penyakit Sistem Pernapasan J00–J99*).
2. **Blok Kategori (Block)**: Rentang kelompok 3-karakter penyakit yang berkerabat dekat (Contoh: *J00–J06 Infeksi Saluran Pernapasan Atas Akut*).
3. **Kategori 3-Karakter (Category)**: Identifikasi umum suatu diagnosis (Contoh: *J02 Faringitis Akut*).
4. **Subkategori 4-Karakter (Subcategory)**: Tingkat spesifikasi klinis, etiologi kuman, atau letak anatomis (Contoh: *J02.0 Faringitis Streptokokus*, *J02.9 Faringitis Akut Tidak Spesifik*).
5. **Karakter Tambahan ke-5 (Opsional)**: Untuk klasifikasi tertentu seperti lokasi sendi (muskuloskeletal) atau sifat luka/fraktur terbuka/tertutup.

---

## 2. Pedoman dan Kaidah Pengkodean Medis (Coding Rules)

Dalam pengisian rekam medis di Klinik ITK, dokter diharapkan mematuhi aturan pengkodean berikut:

1. **Diagnosis Utama (Primary Diagnosis)**:
   - Kondisi medis yang ditegakkan pada akhir pemeriksaan klinis yang menjadi **alasan utama** pasien datang berobat atau membutuhkan tindakan medis paling banyak.
   - Contoh: Pasien datang demam tinggi dan nyeri perut, setelah evaluasi didiagnosis demam tifoid. Diagnosis Utama adalah `A01.0 (Demam Tifoid)`, bukan `R50.9 (Demam)`.

2. **Diagnosis Sekunder / Komorbiditas (Secondary Diagnosis)**:
   - Kondisi penyerta yang ada bersamaan dengan diagnosis utama atau timbul selama masa pelayanan yang memengaruhi tatalaksana klinis.
   - Contoh: Pasien datang dengan keluhan radang tenggorokan (`J02.9`), dan diketahui memiliki riwayat hipertensi rutin (`I10`). Diagnosis Utama: `J02.9`, Diagnosis Sekunder: `I10`.

3. **Gejala (Bab XVIII: R00–R99) vs Penyakit Definitif**:
   - Kode gejala / tanda klinis (awalan huruf **R**, misalnya `R50.9 Demam`, `R51 Sakit Kepala`, `R42 Vertigo`) **hanya digunakan apabila belum diperoleh kesimpulan penyakit definitif** hingga sesi pemeriksaan berakhir.
   - Apabila diagnosis definitif sudah tegak, gunakan kode penyakit spesifik (misal: jika sakit kepala karena migrain, gunakan `G43.9`, bukan `R51`).

4. **Kunjungan Non-Penyakit (Bab XXI: Z-Codes)**:
   - Digunakan untuk kunjungan sehat, pemeriksaan berkala (Medical Check-up Mahasiswa/Pegawai), penerbitan surat keterangan sehat, maupun layanan imunisasi.
   - Contoh: Pemeriksaan kesehatan umum mahasiswa baru menggunakan `Z00.0`, penerbitan surat keterangan medis menggunakan `Z02.7`.

5. **Format Baku Input Diagnosis di Sistem Informasi Klinik ITK**:
   - Format yang digunakan di form pemeriksaan:
     $$\text{<KODE\_ICD10> - <Nama Diagnosis Indonesia> (<Nama Diagnosis Internasional/WHO>)}$$
   - Contoh: `J00 - Nasofaringitis akut (Common Cold)`

---

## 3. Daftar Lengkap 22 Bab ICD-10 (WHO Standard)

Berikut adalah daftar lengkap 22 bab klasifikasi resmi ICD-10:

| Bab | Rentang Kode | Judul Bab (Bahasa Indonesia) | Title (WHO English) |
|:---:|:---:|---|---|
| **I** | `A00–B99` | Penyakit Infeksi dan Parasit Tertentu | *Certain infectious and parasitic diseases* |
| **II** | `C00–D48` | Neoplasma (Tumor Jinak & Kanker) | *Neoplasms* |
| **III** | `D50–D89` | Penyakit Darah, Organ Pembentuk Darah, dan Gangguan Imun | *Diseases of the blood and blood-forming organs and immune mechanism* |
| **IV** | `E00–E90` | Penyakit Endokrin, Nutrisi, dan Metabolik | *Endocrine, nutritional and metabolic diseases* |
| **V** | `F00–F99` | Gangguan Jiwa dan Perilaku | *Mental and behavioural disorders* |
| **VI** | `G00–G99` | Penyakit Sistem Saraf | *Diseases of the nervous system* |
| **VII** | `H00–H59` | Penyakit Mata dan Adneksa | *Diseases of the eye and adnexa* |
| **VIII** | `H60–H95` | Penyakit Telinga dan Prosesus Mastoid | *Diseases of the ear and mastoid process* |
| **IX** | `I00–I99` | Penyakit Sistem Sirkulasi (Kardiovaskular) | *Diseases of the circulatory system* |
| **X** | `J00–J99` | Penyakit Sistem Pernapasan (Respirasi) | *Diseases of the respiratory system* |
| **XI** | `K00–K93` | Penyakit Sistem Pencernaan | *Diseases of the digestive system* |
| **XII** | `L00–L99` | Penyakit Kulit dan Jaringan Subkutan | *Diseases of the skin and subcutaneous tissue* |
| **XIII** | `M00–M99` | Penyakit Sistem Muskuloskeletal dan Jaringan Ikat | *Diseases of the musculoskeletal system and connective tissue* |
| **XIV** | `N00–N99` | Penyakit Sistem Genitourinarius (Urinaria & Genital) | *Diseases of the genitourinary system* |
| **XV** | `O00–O99` | Kehamilan, Persalinan, dan Masa Nifas | *Pregnancy, childbirth and the puerperium* |
| **XVI** | `P00–P96` | Kondisi Tertentu yang Berasal dari Periode Perinatal | *Certain conditions originating in the perinatal period* |
| **XVII** | `Q00–Q99` | Malformasi Kongenital, Deformitas, dan Kelainan Kromosom | *Congenital malformations, deformations and chromosomal abnormalities* |
| **XVIII** | `R00–R99` | Gejala, Tanda, dan Temuan Klinis/Laboratorium Abnormal NEC | *Symptoms, signs and abnormal clinical and laboratory findings, NEC* |
| **XIX** | `S00–T98` | Cedera, Keracunan, dan Konsekuensi Sebab Luar Lainnya | *Injury, poisoning and certain other consequences of external causes* |
| **XX** | `V01–Y98` | Penyebab Eksternal Morbiditas dan Mortalitas | *External causes of morbidity and mortality* |
| **XXI** | `Z00–Z99` | Faktor yang Mempengaruhi Status Kesehatan & Kontak Faskes | *Factors influencing health status and contact with health services* |
| **XXII** | `U00–U85` | Kode untuk Tujuan Khusus (Penyakit Darurat Baru/COVID-19) | *Codes for special purposes (e.g. COVID-19, antimicrobial resistance)* |

---

## 4. Katalog Kode ICD-10 Esensial untuk Faskes Primer & Klinik Kampus (Klinik ITK)

Berikut adalah katalog kode diagnosis terperinci (3 & 4 karakter) yang paling sering ditemui dalam praktik pelayanan rawat jalan klinik kampus (Mahasiswa, Dosen, Tenaga Kependidikan, Pasien Umum):

### 4.1. Penyakit Sistem Pernapasan (Respirasi)
*Penyakit dengan frekuensi kunjungan tertinggi di faskes primer.*

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `J00` | Nasofaringitis akut (Common Cold / Batuk Pilek) | *Acute nasopharyngitis [common cold]* | Batuk, pilek encer, hidung tersumbat, meriang |
| `J01.9` | Sinusitis akut, tidak spesifik | *Acute sinusitis, unspecified* | Nyeri tekan area sinus maksilaris/frontalis, lendir purulen |
| `J02.0` | Faringitis streptokokus | *Streptococcal pharyngitis* | Radang tenggorokan berat dengan eksudat |
| `J02.9` | Faringitis akut, tidak spesifik | *Acute pharyngitis, unspecified* | Nyeri telan, tenggorokan merah bergranula |
| `J03.9` | Tonsilitis akut, tidak spesifik | *Acute tonsillitis, unspecified* | Amandel membengkak (T2–T3), hiperemis, kripte melebar |
| `J04.0` | Laringitis akut | *Acute laryngitis* | Suara serak / parau, afonia mendadak |
| `J06.9` | Infeksi saluran pernapasan atas akut (ISPA) | *Acute upper respiratory infection, unspecified* | Gejala ISPA multipel tanpa lokalisasi tunggal |
| `J18.9` | Pneumonia, tidak spesifik | *Pneumonia, unspecified* | Batuk produktif, sesak, ronki basah kasar/halus |
| `J20.9` | Bronkitis akut, tidak spesifik | *Acute bronchitis, unspecified* | Batuk berkepanjangan dengan wheezing/ronki basah |
| `J30.4` | Rhinitis alergi, tidak spesifik | *Allergic rhinitis, unspecified* | Bersin pagi hari, sekret hidung encer, alergen debu/dingin |
| `J44.9` | Penyakit paru obstruktif kronis (PPOK) | *Chronic obstructive pulmonary disease, unspecified* | Pasien usia lanjut / perokok berat dengan sesak kronis |
| `J45.0` | Asma dengan dominan alergi | *Predominantly allergic asthma* | Sesak kambuhan dengan faktor pemicu spesifik |
| `J45.9` | Asma, tidak spesifik | *Asthma, unspecified* | Serangan sesak napas akut dengan wheezing / mengi |

### 4.2. Penyakit Sistem Pencernaan & Rongga Mulut (Gastrointestinal & Oral)

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `A09` | Gastroenteritis dan kolitis infeksius (Diare akut) | *Infectious gastroenteritis and colitis, unspecified* | BAB cair > 3x/hari, mules, tanpa/dengan lendir |
| `A01.0` | Demam tifoid | *Typhoid fever* | Demam naik bertahap, lidah kotor, tes widal/tubex (+) |
| `K21.9` | Penyakit refluks gastroesofagus (GERD) | *Gastro-oesophageal reflux disease without oesophagitis* | Rasa panas terbakar di dada (*heartburn*), regurgitasi asam |
| `K29.7` | Gastritis, tidak spesifik | *Gastritis, unspecified* | Nyeri ulu hati (*epigastric pain*), perih, kembung |
| `K30` | Dispepsia (Sindrom Dispepsia) | *Dyspepsia* | Mual, begah, cepat kenyang, kembung berulang |
| `K35.8` | Apendisitis akut lainnya / belum terkomplikasi | *Other and unspecified acute appendicitis* | Nyeri perut kanan bawah (titik McBurney), rujuk segera |
| `K59.0` | Konstipasi / Sembelit | *Constipation* | Kesulitan BAB, feses keras dan jarang |
| `K02.9` | Karies gigi, tidak spesifik | *Dental caries, unspecified* | Gigi berlubang |
| `K04.0` | Pulpitis | *Pulpitis* | Nyeri tajam berdenyut pada gigi berlubang |
| `K05.0` | Gingivitis akut | *Acute gingivitis* | Gusi bengkak dan mudah berdarah |
| `K12.0` | Stomatitis aftosa kambuhan (Sariawan) | *Recurrent aphthous stomatitis* | Lesi ulseratif dangkal pada mukosa mulut |

### 4.3. Penyakit Sistem Saraf, Nyeri Kepala, dan Muskuloskeletal

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `G43.9` | Migrain, tidak spesifik | *Migraine, unspecified* | Nyeri kepala berdenyut sebelah, mual, fotofobia |
| `G44.2` | Nyeri kepala tipe tegang (Tension Headache / TTH) | *Tension-type headache* | Rasa terikat kuat di dahi/belakang leher (khas stres/begadang) |
| `G51.0` | Bell's Palsy (Paresis saraf fasialis perifer) | *Bell's palsy* | Kelumpuhan otot separuh wajah akut tanpa tanda stroke |
| `M54.5` | Nyeri punggung bawah (Low Back Pain / LBP) | *Low back pain* | Nyeri daerah lumbal akibat duduk lama/postur salah |
| `M54.2` | Servikalgia (Nyeri leher / kaku leher) | *Cervicalgia* | Kaku otot leher akibat salah bantal / postur laptop |
| `M79.1` | Mialgia (Nyeri otot / pegal linu) | *Myalgia* | Nyeri otot menyeluruh akibat kelelahan fisik/olahraga |
| `M15.9` | Poliartrosis, tidak spesifik | *Polyarthrosis, unspecified* | Nyeri sendi multipel degeneratif |
| `M19.9` | Artrosis / Osteoartritis, tidak spesifik | *Arthrosis, unspecified* | Nyeri sendi lutut/tangan, kaku pagi hari < 30 menit |
| `M10.9` | Artritis gout (Asam Urat Akut) | *Gout, unspecified* | Nyeri dan bengkak merah mendadak pada jempol kaki (*podagra*) |

### 4.4. Penyakit Kulit dan Jaringan Subkutan (Dermatologi)

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `L02.9` | Abses kutaneus, furunkel, dan karbunkel (Bisul) | *Cutaneous abscess, furuncle and carbuncle, unspecified* | Benjolan merah bernanah, nyeri lokal |
| `L08.0` | Pioderma / Impetigo | *Pyoderma* | Infeksi bakteri kulit berkerak kuning madu |
| `L20.9` | Dermatitis atopik (Eksim) | *Atopic dermatitis, unspecified* | Ruam gatal kronis pada lipatan siku/lutut |
| `L23.9` | Dermatitis kontak alergi (DKA) | *Allergic contact dermatitis, unspecified cause* | Reaksi gatal akibat zat pemicu (logam, kosmetik, plester) |
| `L24.9` | Dermatitis kontak iritan (DKI) | *Irritant contact dermatitis, unspecified cause* | Reaksi iritasi detergen, zat kimia laboratorium |
| `L50.9` | Urtikaria, tidak spesifik (Biduran / Kalikata) | *Urticaria, unspecified* | Bentol merah gatal timbul mendadak (*wheal and flare*) |
| `L70.0` | Akne vulgaris (Jerawat) | *Acne vulgaris* | Komedo, papul, pustul pada wajah/punggung |
| `B35.0` | Tinea barbae dan tinea capitis | *Tinea barbae and tinea capitis* | Jamur janggut / kulit kepala |
| `B35.3` | Tinea pedis (Kutu air) | *Tinea pedis* | Jamur sela jari kaki |
| `B35.4` | Tinea corporis (Kurap badan) | *Tinea corporis* | Lesi bulat tepi aktif kemerahan bersisik |
| `B35.6` | Tinea cruris (Jamur selangkangan) | *Tinea cruris* | Gatal hebat area lipat paha |
| `B36.0` | Pitiriasis versikolor (Panu) | *Pityriasis versicolor* | Bercak putih/coklat bersisik halus di badan/leher |
| `B86` | Skabies (Kudis) | *Scabies* | Gatal malam hari, lesi sela jari tangan, menular di asrama |

### 4.5. Penyakit Mata dan Telinga (Oftalmologi & THT)

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `H10.9` | Konjungtivitis, tidak spesifik (Mata merah) | *Conjunctivitis, unspecified* | Mata merah, belekan, rasa berpasir |
| `H00.0` | Hordeolum (Bintitan) | *Hordeolum and other deep inflammation of eyelid* | Benjolan kemerahan nyeri pada kelopak mata |
| `H01.0` | Blefaritis | *Blepharitis* | Radang tepi kelopak mata, berkeropeng |
| `H53.1` | Asthenopia (Mata lelah akibat layar monitor) | *Subjective visual disturbances (Eye strain)* | Mata tegang, pusing setelah menatap layar laptop lama |
| `H60.9` | Otitis eksterna, tidak spesifik | *Otitis externa, unspecified* | Nyeri liang telinga luar, nyeri saat daun telinga ditarik |
| `H61.2` | Impaksi serumen (Serumen prop / Kotoran telinga) | *Impacted cerumen* | Pendengaran menurun rasa tersumbat akibat gumpalan serumen |
| `H65.9` | Otitis media non-supuratif, tidak spesifik | *Nonsuppurative otitis media, unspecified* | Telinga terasa penuh setelah batuk pilek |
| `H66.9` | Otitis media supuratif, tidak spesifik | *Otitis media, unspecified* | Nyeri telinga dalam / keluar cairan kekuningan |
| `R04.0` | Epistaksis (Mimisan) | *Epistaxis* | Perdarahan aktif dari lubang hidung |

### 4.6. Penyakit Kardiovaskular, Endokrin, dan Metabolik

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `I10` | Hipertensi esensial / primer | *Essential (primary) hypertension* | Tekanan darah $\ge 140/90$ mmHg tanpa penyebab sekunder |
| `I95.9` | Hipotensi, tidak spesifik | *Hypotension, unspecified* | Tekanan darah $< 90/60$ mmHg disertai gejala pusing/lemas |
| `E11.9` | Diabetes mellitus tipe 2 tanpa komplikasi | *Type 2 diabetes mellitus without complications* | Gula darah puasa $\ge 126$ mg/dL atau sewaktu $\ge 200$ mg/dL |
| `E78.0` | Hiperkolesterolemia murni | *Pure hypercholesterolaemia* | Kolesterol total $> 200$ mg/dL atau LDL tinggi |
| `E78.5` | Hiperlipidemia, tidak spesifik | *Hyperlipidaemia, unspecified* | Kolesterol dan/atau trigliserida darah meningkat |
| `E79.0` | Hiperurisemia tanpa tanda artritis gout | *Hyperuricaemia without signs of arthritis* | Kadar asam urat darah tinggi asimptomatis |
| `E66.9` | Obesitas, tidak spesifik | *Obesity, unspecified* | Indeks Massa Tubuh (IMT) $\ge 27.0$ (standar Asia Pasifik) |

### 4.7. Penyakit Infeksi Sistemik dan Tropis Lainnya

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `A90` | Demam dengue (Dengue Fever / Demam Dengue) | *Dengue fever [classical dengue]* | Demam akut mendadak, sakit kepala retro-orbital, leukopenia |
| `A91` | Demam berdarah dengue (DHF / DBD) | *Dengue haemorrhagic fever* | Demam dengue disertai tanda kebocoran plasma / trombositopenia |
| `B01.9` | Varisela tanpa komplikasi (Cacar air) | *Varicella without complication* | Demam disertai vesikel berisi cairan menyerupai tetesan embun |
| `B02.9` | Herpes zoster tanpa komplikasi (Cacar ular) | *Zoster without complication* | Vesikel bergerombol mengikuti dermatom saraf unilateral |
| `B05.9` | Morbili / Campak tanpa komplikasi | *Measles without complication* | Demam, batuk, pilek, mata merah (3C), ruam makulopapular |
| `B26.9` | Parotitis epidemika (Gondongan) | *Mumps without complication* | Pembengkakan kelenjar parotis bilateral/unilateral |

### 4.8. Gejala, Tanda Klinis, dan Keluhan Umum (Bab XVIII: R-Codes)
*Gunakan kode ini hanya jika pemeriksaan belum sampai pada diagnosis definitif.*

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `R50.9` | Demam, tidak spesifik (Febris) | *Fever, unspecified* | Suhu aksila $> 37.5^\circ\text{C}$ etiologi masih dalam observasi |
| `R51` | Nyeri kepala, tidak spesifik (Cephalgia) | *Headache* | Sakit kepala belum terklasifikasi sebagai migrain atau TTH |
| `R42` | Pusing dan vertigo (Dizziness / Vertigo) | *Dizziness and giddiness* | Sensasi berputar (*true vertigo*) atau rasa melayang (*lightheaded*) |
| `R10.4` | Nyeri perut tidak spesifik (Abdominal pain / Kolik) | *Other and unspecified abdominal pain* | Nyeri kram perut yang etiologinya belum pasti |
| `R11` | Mual dan muntah (Nausea & Vomiting) | *Nausea and vomiting* | Gejala mual hebat dengan/tanpa muntah aktif |
| `R53` | Malaise dan kelelahan (Fatigue / Asthenia) | *Malaise and fatigue* | Badan lemas, letih, lunglai tanpa penyakit organik jelas |
| `R05` | Batuk, tidak spesifik | *Cough* | Keluhan batuk tanpa diagnosis spesifik lainnya |
| `R07.4` | Nyeri dada, tidak spesifik | *Chest pain, unspecified* | Nyeri dada atipikal (harus dievaluasi red flags jantung) |

### 4.9. Trauma, Cedera, dan Luka Luar (Bab XIX: S-T Codes)
*Kasus pertolongan pertama (P3K) di klinik kampus seperti kecelakaan kerja lab, olahraga, atau lalu lintas.*

| Kode ICD-10 | Nama Diagnosis (Indonesia) | Diagnosis Terminology (WHO English) | Keterangan / Kasus Klinis |
|:---:|---|---|---|
| `T14.0` | Cedera superfisial / Luka lecet (Vulnus Excoriatum) | *Superficial injury of unspecified body region* | Lecet pada kulit ari akibat gesekan/jatuh |
| `T14.1` | Luka terbuka (Vulnus Laceratum / Sayat / Robek) | *Open wound of unspecified body region* | Luka robek terkena pecahan kaca lab, pisau, benda tajam |
| `S93.4` | Keseleo dan tegang pada pergelangan kaki (Sprain ankle) | *Sprain and strain of ankle* | Cedera ligamen pergelangan kaki akibat olahraga/tersandung |
| `S60.2` | Kontusio / Memar pergelangan tangan dan tangan | *Contusion of other parts of wrist and hand* | Memar kebiruan akibat benturan benda tumpul |
| `T20.0` | Luka bakar termal derajat satu (Combustio Grade I) | *Burn of unspecified degree of head and neck* | Kulit kemerahan kering tanpa bula terkena panas/api ringan |
| `T30.0` | Luka bakar permukaan tubuh, derajat tidak spesifik | *Burn of unspecified body region, unspecified degree* | Luka bakar akibat tumpahan zat kimia reagen atau api |
| `T15.9` | Benda asing pada mata eksternal (Corpus Alienum Mata) | *Foreign body on external eye, part unspecified* | Kelilipan serbuk logam, pasir, atau debu lab di mata |

### 4.10. Kontak Pelayanan Non-Penyakit & Administrasi Medis (Bab XXI: Z-Codes)

| Kode ICD-10 | Nama Layanan / Diagnosis (Indonesia) | Service Terminology (WHO English) | Keterangan / Penggunaan Sistem |
|:---:|---|---|---|
| `Z00.0` | Pemeriksaan kesehatan umum (General Medical Check-Up) | *General medical examination* | Skrining kesehatan berkala, MCU Mahasiswa Baru ITK |
| `Z02.7` | Penerbitan surat keterangan medis | *Issue of medical certificate* | Penerbitan Surat Keterangan Sakit atau Bebas Narkoba |
| `Z02.1` | Pemeriksaan pra-kerja / rekrutmen | *Pre-employment examination* | Pemeriksaan kesehatan syarat lamaran kerja/asisten lab |
| `Z23` | Kebutuhan imunisasi terhadap penyakit bakteri | *Need for immunization against single bacterial diseases* | Pemberian vaksin Tetanus Toksoid (TT) |
| `Z25.1` | Kebutuhan imunisasi terhadap influenza | *Need for immunization against influenza* | Vaksinasi tahunan influenza |
| `Z71.1` | Konsultasi / Orang dengan keluhan tanpa penyakit | *Person with feared complaint in whom no diagnosis is made* | Pasien cemas berlebihan tanpa kelainan fisik ditemukan |

---

## 5. Sinkronisasi dengan Database & Antarmuka Sistem Informasi Klinik ITK

### 5.1. Struktur Penyimpanan di Database
Di dalam database sistem informasi Klinik ITK, data diagnosis tersimpan pada tabel `pemeriksaans`:
- **`kode_icd10`** (`VARCHAR(50)`, nullable): Menyimpan kode alfanumerik ICD-10 (misal: `J00`, `K29.7`, `M79.1`).
- **`diagnosis_utama`** (`VARCHAR(255)`): Menyimpan nama diagnosis dalam bahasa Indonesia/Inggris (misal: `Nasofaringitis akut (common cold)`).
- **`diagnosis_sekunder`** (`TEXT`, nullable): Menyimpan diagnosis penyerta/komplikasi bila ada.

### 5.2. Format Autocomplete pada Antarmuka Dokter (`Pemeriksaan.vue` & `Antrian.vue`)
Komponen autocomplete diagnosis menggunakan kombinasi teks terstandarisasi:
```typescript
const onDiagnosisSelect = (event: any) => {
    const selected = event.value; // Contoh: "J00 - Nasofaringitis akut (common cold)"
    const parts = selected.split(' - ');
    if (parts.length > 1) {
        form.kode_icd10 = parts[0];                // "J00"
        form.diagnosis_utama = parts.slice(1).join(' - '); // "Nasofaringitis akut (common cold)"
    }
};
```

### 5.3. Pembuatan Laporan Morbiditas (10 Besar Penyakit)
Sistem secara otomatis mengagregasi data berdasarkan kolom `kode_icd10` untuk menyusun **Laporan 10 Besar Diagnosis Morbiditas** pada modul Laporan Diagnosis (`/laporan/diagnosis`) dan cetak PDF Laporan Pemeriksaan Umum. Standardisasi penulisan kode memastikan bahwa agregasi data akurat dan tidak terduplikasi.

---

## 6. Referensi Resmi
1. **World Health Organization (WHO)** - *International Statistical Classification of Diseases and Related Health Problems 10th Revision (ICD-10)*.
2. **Kementerian Kesehatan Republik Indonesia** - *Klasifikasi Penyakit Berdasarkan ICD-10 untuk Pelayanan Kesehatan Tingkat Pertama (FKTP)*.
3. **Standar Kompetensi Dokter Indonesia (SKDI)** - *Tingkat Kemampuan 4A untuk Faskes Tingkat Pertama*.