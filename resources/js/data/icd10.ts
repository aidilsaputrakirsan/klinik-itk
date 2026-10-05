export interface Icd10Item {
    code: string;
    name: string;
    category: string;
    description?: string;
}

export const icd10Dataset: Icd10Item[] = [
    // 1. Penyakit Sistem Pernapasan (Respirasi)
    {
        code: 'J00',
        name: 'Nasofaringitis akut (Common cold / Batuk pilek)',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Batuk, pilek encer, hidung tersumbat, bersin-bersin'
    },
    {
        code: 'J01.9',
        name: 'Sinusitis akut, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Nyeri tekan sinus maksilaris/frontalis, lendir purulen'
    },
    {
        code: 'J02.0',
        name: 'Faringitis streptokokus',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Radang tenggorokan berat dengan eksudat streptokokus'
    },
    {
        code: 'J02.9',
        name: 'Faringitis akut, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Nyeri telan, tenggorokan merah bergranula'
    },
    {
        code: 'J03.9',
        name: 'Tonsilitis akut, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Amandel membengkak (T2-T3), hiperemis, kripte melebar'
    },
    {
        code: 'J04.0',
        name: 'Laringitis akut',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Suara serak, parau, afonia mendadak'
    },
    {
        code: 'J06.9',
        name: 'Infeksi saluran pernapasan atas akut (ISPA) multiple/tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Gejala ISPA multipel tanpa lokalisasi tunggal'
    },
    {
        code: 'J18.9',
        name: 'Pneumonia, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Batuk produktif, sesak, ronki basah'
    },
    {
        code: 'J20.9',
        name: 'Bronkitis akut, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Batuk produktif berkepanjangan dengan wheezing/ronki basah'
    },
    {
        code: 'J30.4',
        name: 'Rhinitis alergi, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Bersin pagi hari, sekret hidung encer, dipicu debu/dingin'
    },
    {
        code: 'J44.9',
        name: 'Penyakit paru obstruktif kronik (PPOK), tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Sesak napas kronis progresif pada perokok/usia lanjut'
    },
    {
        code: 'J45.0',
        name: 'Asma dengan dominan alergi',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Serangan sesak napas mengi dipicu alergen spesifik'
    },
    {
        code: 'J45.9',
        name: 'Asma, tidak spesifik',
        category: 'Penyakit Sistem Pernapasan',
        description: 'Sesak napas episodik disertai mengi (wheezing)'
    },

    // 2. Penyakit Sistem Pencernaan & Gigi (Gastrointestinal & Oral)
    {
        code: 'A01.0',
        name: 'Demam tifoid (Typhoid fever)',
        category: 'Penyakit Saluran Cerna',
        description: 'Demam bertingkat naik malam hari, lidah kotor, mual perut'
    },
    {
        code: 'A09',
        name: 'Diare dan gastroenteritis oleh penyebab infeksi presumtif',
        category: 'Penyakit Saluran Cerna',
        description: 'BAB cair > 3 kali sehari, mules, dehidrasi ringan'
    },
    {
        code: 'K02.9',
        name: 'Karies gigi, tidak spesifik',
        category: 'Penyakit Gigi & Mulut',
        description: 'Gigi berlubang'
    },
    {
        code: 'K04.0',
        name: 'Pulpitis',
        category: 'Penyakit Gigi & Mulut',
        description: 'Nyeri tajam berdenyut pada pulpa gigi berlubang'
    },
    {
        code: 'K05.0',
        name: 'Gingivitis akut',
        category: 'Penyakit Gigi & Mulut',
        description: 'Radang gusi, bengkak dan mudah berdarah'
    },
    {
        code: 'K12.0',
        name: 'Stomatitis aftosa kambuhan (Sariawan)',
        category: 'Penyakit Gigi & Mulut',
        description: 'Ulkus dangkal mukosa mulut yang nyeri'
    },
    {
        code: 'K21.9',
        name: 'Penyakit refluks gastroesofagus (GERD)',
        category: 'Penyakit Saluran Cerna',
        description: 'Rasa panas di dada (heartburn), regurgitasi cairan asam'
    },
    {
        code: 'K29.7',
        name: 'Gastritis, tidak spesifik',
        category: 'Penyakit Saluran Cerna',
        description: 'Nyeri ulu hati (epigastric), perih, kembung'
    },
    {
        code: 'K30',
        name: 'Dispepsia (Sindrom dispepsia)',
        category: 'Penyakit Saluran Cerna',
        description: 'Rasa begah, mual, cepat kenyang, kembung berulang'
    },
    {
        code: 'K35.8',
        name: 'Apendisitis akut, tidak spesifik',
        category: 'Penyakit Saluran Cerna',
        description: 'Nyeri perut kanan bawah mendadak (titik McBurney)'
    },
    {
        code: 'K59.0',
        name: 'Konstipasi / Sembelit',
        category: 'Penyakit Saluran Cerna',
        description: 'BAB keras dan jarang kurang dari 3 kali per minggu'
    },

    // 3. Sistem Saraf & Muskuloskeletal
    {
        code: 'G43.9',
        name: 'Migrain, tidak spesifik',
        category: 'Sistem Saraf & Kepala',
        description: 'Nyeri kepala berdenyut unilateral, mual, fotofobia'
    },
    {
        code: 'G44.2',
        name: 'Nyeri kepala tipe tegang (Tension-type headache / TTH)',
        category: 'Sistem Saraf & Kepala',
        description: 'Rasa kepala terikat kuat, kaku leher, dipicu stres/begadang'
    },
    {
        code: 'G51.0',
        name: "Bell's palsy (Paresis saraf fasialis perifer)",
        category: 'Sistem Saraf & Kepala',
        description: 'Kelumpuhan otot wajah separuh akut tanpa tanda stroke'
    },
    {
        code: 'M10.9',
        name: 'Artritis gout, tidak spesifik (Asam urat)',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri bengkak merah mendadak pada sendi ibu jari kaki'
    },
    {
        code: 'M15',
        name: 'Poliartrosis',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri sendi degeneratif multipel'
    },
    {
        code: 'M19.9',
        name: 'Artrosis / Osteoartritis, tidak spesifik',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri sendi lutut/tangan kaku pagi hari < 30 menit'
    },
    {
        code: 'M54.2',
        name: 'Servikalgia (Nyeri leher / kaku leher)',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri kaku leher akibat postur layar komputer/salah bantal'
    },
    {
        code: 'M54.5',
        name: 'Low back pain (Nyeri punggung bawah)',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri lumbal akibat duduk lama atau mengangkat beban'
    },
    {
        code: 'M79.1',
        name: 'Myalgia (Nyeri otot / pegal linu)',
        category: 'Sistem Muskuloskeletal',
        description: 'Nyeri dan pegal otot sekujur badan karena kelelahan'
    },

    // 4. Penyakit Kulit & Jaringan Subkutan (Dermatologi)
    {
        code: 'B35.0',
        name: 'Tinea barbae dan tinea capitis',
        category: 'Penyakit Kulit & Alergi',
        description: 'Infeksi jamur pada area janggut atau kulit kepala'
    },
    {
        code: 'B35.3',
        name: 'Tinea pedis (Kutu air)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Infeksi jamur sela jari dan telapak kaki'
    },
    {
        code: 'B35.4',
        name: 'Tinea corporis (Kurap badan)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Lesi kemerahan tepi aktif melingkar bersisik halus'
    },
    {
        code: 'B35.6',
        name: 'Tinea cruris (Jamur selangkangan)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Gatal hebat area lipat paha dan perineum'
    },
    {
        code: 'B36.0',
        name: 'Pitiriasis versikolor (Panu)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Bercak putih/coklat bersisik halus di dada atau punggung'
    },
    {
        code: 'B86',
        name: 'Skabies (Kudis)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Gatal malam hari, lesi sela jari tangan, menular di asrama/kos'
    },
    {
        code: 'L02.9',
        name: 'Abses kutaneus, furunkel dan karbunkel (Bisul)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Nodul merah bernanah nyeri pada folikel rambut'
    },
    {
        code: 'L08.0',
        name: 'Pioderma / Impetigo',
        category: 'Penyakit Kulit & Alergi',
        description: 'Infeksi bakteri kulit berkerak kuning keemasan'
    },
    {
        code: 'L20',
        name: 'Dermatitis atopik (Eksim)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Gatal kronis kambuhan pada fosa kubiti dan poplitea'
    },
    {
        code: 'L23',
        name: 'Dermatitis kontak alergi (DKA)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Reaksi alergi kontak logam, kosmetik, plester, dsb.'
    },
    {
        code: 'L24.9',
        name: 'Dermatitis kontak iritan (DKI)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Iritasi kulit akibat sabun cuci, detergen, bahan kimia lab'
    },
    {
        code: 'L50.9',
        name: 'Urtikaria, tidak spesifik (Biduran / Kalikata)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Bentol merah gatal timbul mendadak menyebar'
    },
    {
        code: 'L70.0',
        name: 'Akne vulgaris (Jerawat)',
        category: 'Penyakit Kulit & Alergi',
        description: 'Komedo, papul, pustul pada wajah atau dada/punggung'
    },

    // 5. Penyakit Mata & THT
    {
        code: 'H00.0',
        name: 'Hordeolum dan radang kelopak mata dalam (Bintitan)',
        category: 'Penyakit Mata & THT',
        description: 'Benjolan kemerahan nyeri pada tepi kelopak mata'
    },
    {
        code: 'H01.0',
        name: 'Blefaritis',
        category: 'Penyakit Mata & THT',
        description: 'Radang tepi kelopak mata berkerak/bersisik'
    },
    {
        code: 'H10',
        name: 'Konjungtivitis (Mata merah / Belekan)',
        category: 'Penyakit Mata & THT',
        description: 'Mata merah, berair, sekret mukopurulen, rasa berpasir'
    },
    {
        code: 'H53.1',
        name: 'Asthenopia (Mata lelah / Eye strain)',
        category: 'Penyakit Mata & THT',
        description: 'Mata lelah, tegang, pusing sehabis menatap layar laptop'
    },
    {
        code: 'H60.9',
        name: 'Otitis eksterna, tidak spesifik',
        category: 'Penyakit Mata & THT',
        description: 'Nyeri liang telinga luar, nyeri tarik tragus/aurikula'
    },
    {
        code: 'H61.2',
        name: 'Impaksi serumen (Serumen prop / Kotoran telinga)',
        category: 'Penyakit Mata & THT',
        description: 'Telinga tersumbat dan penurunan pendengaran akibat serumen keras'
    },
    {
        code: 'H65.9',
        name: 'Otitis media non-supuratif, tidak spesifik',
        category: 'Penyakit Mata & THT',
        description: 'Telinga terasa penuh/tersumbat pasca batuk pilek'
    },
    {
        code: 'H66.9',
        name: 'Otitis media, tidak spesifik',
        category: 'Penyakit Mata & THT',
        description: 'Radang telinga tengah, otalgia dengan/tanpa sekret'
    },
    {
        code: 'R04.0',
        name: 'Epistaksis (Mimisan)',
        category: 'Penyakit Mata & THT',
        description: 'Perdarahan dari hidung'
    },

    // 6. Kardiovaskular, Endokrin, Metabolik, dan Urologi
    {
        code: 'E11',
        name: 'Diabetes mellitus tipe 2',
        category: 'Kardiovaskular & Metabolik',
        description: 'Kadar gula darah puasa >= 126 mg/dL atau sewaktu >= 200 mg/dL'
    },
    {
        code: 'E66.9',
        name: 'Obesitas, tidak spesifik',
        category: 'Kardiovaskular & Metabolik',
        description: 'Indeks Massa Tubuh (IMT) >= 27 kg/m2'
    },
    {
        code: 'E78.0',
        name: 'Hiperkolesterolemia murni',
        category: 'Kardiovaskular & Metabolik',
        description: 'Kadar kolesterol total serum meningkat > 200 mg/dL'
    },
    {
        code: 'E78.5',
        name: 'Hiperlipidemia, tidak spesifik',
        category: 'Kardiovaskular & Metabolik',
        description: 'Kolesterol dan/atau trigliserida darah di atas batas normal'
    },
    {
        code: 'E79.0',
        name: 'Hiperurisemia tanpa tanda artritis gout',
        category: 'Kardiovaskular & Metabolik',
        description: 'Kadar asam urat darah tinggi tanpa keluhan sendi'
    },
    {
        code: 'I10',
        name: 'Hipertensi esensial (primer)',
        category: 'Kardiovaskular & Metabolik',
        description: 'Tekanan darah sistolik >= 140 mmHg atau diastolik >= 90 mmHg'
    },
    {
        code: 'I95.9',
        name: 'Hipotensi, tidak spesifik',
        category: 'Kardiovaskular & Metabolik',
        description: 'Tekanan darah < 90/60 mmHg disertai lemas/pusing melayang'
    },
    {
        code: 'N39.0',
        name: 'Infeksi saluran kemih (ISK), lokasi tidak spesifik',
        category: 'Sistem Urinaria & Kelamin',
        description: 'Nyeri saat buang air kecil (disuria), anyang-anyangan, frekuensi meningkat'
    },

    // 7. Penyakit Infeksi Virus & Tropis
    {
        code: 'A90',
        name: 'Demam dengue (Dengue fever)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Demam tinggi mendadak, sakit kepala retro-orbital, nyeri sendi'
    },
    {
        code: 'A91',
        name: 'Demam berdarah dengue (DBD / DHF)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Demam dengue disertai trombositopenia dan kebocoran plasma'
    },
    {
        code: 'B01',
        name: 'Varisela (Cacar air)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Demam diikuti vesikel berisi cairan jernih serentak'
    },
    {
        code: 'B02.9',
        name: 'Herpes zoster tanpa komplikasi (Cacar ular)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Vesikel bergerombol unilateral sesuai dermatom saraf'
    },
    {
        code: 'B05.9',
        name: 'Morbili / Campak tanpa komplikasi',
        category: 'Penyakit Infeksi Tropis',
        description: 'Demam, batuk, pilek, konjungtivitis (3C), ruam merah'
    },
    {
        code: 'B26.9',
        name: 'Parotitis epidemika (Gondongan)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Pembengkakan nyeri pada kelenjar ludah parotis'
    },
    {
        code: 'U07.1',
        name: 'COVID-19 (Teridentifikasi lewat virus)',
        category: 'Penyakit Infeksi Tropis',
        description: 'Infeksi saluran pernapasan SARS-CoV-2 antigen/PCR positif'
    },

    // 8. Gejala, Tanda Klinis, dan Keluhan Umum (R-Codes)
    {
        code: 'R05',
        name: 'Batuk, tidak spesifik',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Keluhan batuk belum teridentifikasi etiologi definitif'
    },
    {
        code: 'R07.4',
        name: 'Nyeri dada, tidak spesifik',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Nyeri dada atipikal'
    },
    {
        code: 'R10',
        name: 'Nyeri perut dan panggul (Abdominal pain / Kolik)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Kram atau nyeri perut yang belum diketahui fokusnya'
    },
    {
        code: 'R11',
        name: 'Mual dan muntah (Nausea and vomiting)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Rasa mual hebat dengan/tanpa muntah aktif'
    },
    {
        code: 'R42',
        name: 'Pusing dan giddiness (Vertigo)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Sensasi berputar atau limbung tak seimbang'
    },
    {
        code: 'R50.9',
        name: 'Demam, tidak spesifik (Fever, unspecified / Febris)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Suhu tubuh > 37.5 C penyebab masih dalam observasi'
    },
    {
        code: 'R51',
        name: 'Sakit kepala (Headache / Cephalgia)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Nyeri kepala tanpa tanda-tanda spesifik sekunder'
    },
    {
        code: 'R53',
        name: 'Malaise dan kelelahan (Fatigue / Asthenia)',
        category: 'Gejala & Tanda Klinis (R-Codes)',
        description: 'Badan lemas, letih, tak bertenaga'
    },

    // 9. Trauma, Cedera Fisik & P3K (S-T Codes)
    {
        code: 'S60.2',
        name: 'Kontusio / Memar pergelangan tangan dan tangan',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Hematom memar akibat benturan benda tumpul'
    },
    {
        code: 'S93.4',
        name: 'Keseleo dan tegang pada pergelangan kaki (Sprain ankle)',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Cedera ligamen pergelangan kaki terkilir saat olahraga/jatuh'
    },
    {
        code: 'T14.0',
        name: 'Cedera superfisial / Luka lecet (Vulnus excoriatum)',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Lecet pada epidermis kulit karena gesekan/tersandung'
    },
    {
        code: 'T14.1',
        name: 'Luka terbuka pada regio tubuh yang tidak terspesifikasi (Vulnus laceratum)',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Luka robek terbuka akibat pecahan kaca laboratorium, pisau, dll.'
    },
    {
        code: 'T15.9',
        name: 'Benda asing pada mata eksternal (Corpus alienum mata)',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Kelilipan serpihan logam, gram pasir atau serbuk lab di mata'
    },
    {
        code: 'T20.0',
        name: 'Luka bakar termal derajat satu (Combustio Grade I)',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Kulit kemerahan kering tanpa lepuh terkena air panas/api ringan'
    },
    {
        code: 'T30.0',
        name: 'Luka bakar permukaan tubuh, derajat tidak spesifik',
        category: 'Cedera & Trauma Fisik (P3K)',
        description: 'Luka bakar akibat kontak bahan kimia atau termal'
    },

    // 10. Layanan Sehat & Administrasi Medis (Z-Codes)
    {
        code: 'Z00.0',
        name: 'Pemeriksaan medis umum (General medical examination / MCU)',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Pemeriksaan kesehatan rutin atau MCU mahasiswa baru ITK'
    },
    {
        code: 'Z02.1',
        name: 'Pemeriksaan pra-kerja / rekrutmen',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Pemeriksaan kesehatan kelayakan kerja atau asisten laboratorium'
    },
    {
        code: 'Z02.7',
        name: 'Penerbitan surat keterangan medis (Surat Sakit / Bebas Narkoba)',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Konsultasi penerbitan surat medis resmi klinik'
    },
    {
        code: 'Z23',
        name: 'Kebutuhan imunisasi terhadap penyakit bakteri (Vaksin TT)',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Pemberian vaksin profilaksis tetanus'
    },
    {
        code: 'Z25.1',
        name: 'Kebutuhan imunisasi terhadap influenza',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Vaksinasi influenza tahunan'
    },
    {
        code: 'Z71.1',
        name: 'Konsultasi keluhan tanpa kelainan (Kekhawatiran tanpa diagnosis)',
        category: 'Kunjungan Sehat & Administrasi Medis',
        description: 'Konsultasi keluhan kecemasan kesehatan tanpa ada kelainan fisik'
    }
];

export const icd10List: string[] = icd10Dataset.map(item => `${item.code} - ${item.name}`);
