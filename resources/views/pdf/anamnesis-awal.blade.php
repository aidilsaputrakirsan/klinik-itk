<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Anamnesis Awal</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        .header p {
            font-size: 9pt;
            margin: 1px 0;
        }
        .title {
            text-align: center;
            margin: 10px 0 20px 0;
        }
        .title h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        .section-title {
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 4px 8px;
            margin: 15px 0 8px 0;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .data-table td:first-child {
            width: 180px;
            font-weight: bold;
        }
        .data-table td:nth-child(2) {
            width: 10px;
        }
        .footer {
            margin-top: 30px;
        }
        .signature {
            float: right;
            width: 250px;
            text-align: center;
        }
        .signature p {
            margin: 5px 0;
        }
        .signature .role {
            margin-bottom: 60px;
        }
        .signature .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .note {
            margin-top: 15px;
            font-size: 9pt;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <p>Jl. Soekarno-Hatta Km 15, Karang Joang, Balikpapan Utara</p>
        <p>Kalimantan Timur 76127</p>
        <p>Telp: +62 811 5390 801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="title">
        <h3>HASIL PEMERIKSAAN ANAMNESIS AWAL</h3>
    </div>

    <div class="section-title">I. IDENTITAS PASIEN</div>
    <table class="data-table">
        <tr>
            <td>Nomor Rekam Medis</td><td>:</td>
            <td>{{ $pasien->nomor_rm }}</td>
        </tr>
        <tr>
            <td>Nama Pasien</td><td>:</td>
            <td>{{ $pasien->nama }}</td>
        </tr>
        <tr>
            <td>Jenis Kelamin</td><td>:</td>
            <td>{{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
        <tr>
            <td>Tanggal Lahir</td><td>:</td>
            <td>{{ $pasien->tanggal_lahir ? $pasien->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal Kunjungan</td><td>:</td>
            <td>{{ $rekamMedis->tanggal_kunjungan->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    <div class="section-title">II. KELUHAN & RIWAYAT PENYAKIT</div>
    <table class="data-table">
        <tr>
            <td>Keluhan Utama</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->keluhan_utama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Riwayat Penyakit Sekarang</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->riwayat_penyakit_sekarang ?? '-' }}</td>
        </tr>
        <tr>
            <td>Riwayat Penyakit Dahulu</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->riwayat_penyakit_dahulu ?? '-' }}</td>
        </tr>
        <tr>
            <td>Riwayat Penyakit Keluarga</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->riwayat_keluarga ?? '-' }}</td>
        </tr>
        <tr>
            <td>Riwayat Alergi</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->riwayat_alergi ?? '-' }}</td>
        </tr>
        <tr>
            <td>Riwayat Pengobatan</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->riwayat_obat ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">III. TANDA-TANDA VITAL & ANTROPOMETRI</div>
    <table class="data-table">
        <tr>
            <td>Tekanan Darah</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->tekanan_darah ?? '-' }} mmHg</td>
        </tr>
        <tr>
            <td>Nadi</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->nadi ?? '-' }} x/menit</td>
        </tr>
        <tr>
            <td>Respirasi</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->respirasi ?? '-' }} x/menit</td>
        </tr>
        <tr>
            <td>Suhu Tubuh</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->suhu ?? '-' }} &deg;C</td>
        </tr>
        <tr>
            <td>Tinggi / Berat Badan</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->tinggi_badan ?? '-' }} cm / {{ $rekamMedis->anamnesis->berat_badan ?? '-' }} kg</td>
        </tr>
        <tr>
            <td>Skala Nyeri (0-10)</td><td>:</td>
            <td>{{ $rekamMedis->anamnesis->skala_nyeri ?? '-' }}</td>
        </tr>
    </table>

    <div class="footer clearfix">
        <div class="signature">
            <p class="date">Balikpapan, {{ $rekamMedis->tanggal_kunjungan->translatedFormat('d F Y') }}</p>
            <p class="role">Perawat Pemeriksa,</p>
            <p class="name">{{ $rekamMedis->anamnesis->perawat->name ?? '.........................................' }}</p>
        </div>
    </div>

    <div class="note">
        <p>*) Dokumen ini merupakan hasil pencatatan anamnesis awal oleh perawat dan belum memuat diagnosis akhir dari dokter.</p>
    </div>
</body>
</html>
