<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Registrasi Pasien</title>
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
            margin: 10px 0;
        }
        .title h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
        }
        .content {
            text-align: justify;
            margin: 10px 0;
        }
        .data-pasien {
            margin: 8px 0 8px 10px;
        }
        .data-pasien table {
            border-collapse: collapse;
            width: 100%;
        }
        .data-pasien td {
            padding: 3px 5px;
            vertical-align: top;
        }
        .data-pasien td:first-child {
            width: 150px;
        }
        .data-pasien td:nth-child(2) {
            width: 10px;
        }
        .footer {
            margin-top: 40px;
            width: 100%;
        }
        .signature-left {
            float: left;
            width: 45%;
            text-align: center;
        }
        .signature-right {
            float: right;
            width: 45%;
            text-align: center;
        }
        .signature-left p, .signature-right p {
            margin: 5px 0;
        }
        .signature-left .role, .signature-right .role {
            margin-bottom: 70px;
        }
        .signature-left .name, .signature-right .name {
            font-weight: normal;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 80%;
            margin: 0 auto 5px auto;
        }
        .date-text {
            text-align: left;
            padding-left: 10%;
            margin-bottom: 15px !important;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .consent-text {
            margin-top: 30px;
            padding: 15px;
            border: 1px solid #000;
            background-color: #f9f9f9;
            font-style: italic;
            text-align: justify;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <p>SIO : 445.5/100/DPMPTSP</p>
        <p>Jl. Soekarno-Hatta Km 15, Karang Joang, Balikpapan Utara</p>
        <p>Kalimantan Timur 76127</p>
        <p>Telp: +62 811 5390 801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="title">
        <h3>FORMULIR REGISTRASI PASIEN BARU</h3>
    </div>

    <div class="content">
        <p>Berikut adalah data identitas pasien yang akan didaftarkan:</p>

        <div class="data-pasien">
            <table>
                <tr>
                    <td>Nomor RM</td>
                    <td>:</td>
                    <td><strong>{{ $pasien->nomor_rm }}</strong></td>
                </tr>
                <tr>
                    <td>Nama Lengkap</td>
                    <td>:</td>
                    <td><strong>{{ $pasien->nama }}</strong></td>
                </tr>
                <tr>
                    <td>NIK</td>
                    <td>:</td>
                    <td>{{ $pasien->nik ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Lahir</td>
                    <td>:</td>
                    <td>{{ $pasien->tanggal_lahir ? $pasien->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pasien->alamat ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Nomor Telepon</td>
                    <td>:</td>
                    <td>{{ $pasien->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>:</td>
                    <td>{{ $pasien->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Status Pasien</td>
                    <td>:</td>
                    <td style="text-transform: capitalize;">{{ $pasien->tipe_pasien }}</td>
                </tr>

                <tr>
                    <td>Golongan Darah</td>
                    <td>:</td>
                    <td>{{ $pasien->golongan_darah ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="consent-text">
            <strong>PERNYATAAN PERSETUJUAN:</strong><br>
            Dengan menandatangani formulir ini, saya menyatakan bahwa data yang saya berikan adalah benar. Saya juga <strong>menyetujui pengumpulan dan penggunaan data rekam medis saya</strong> oleh Klinik Institut Teknologi Kalimantan untuk keperluan pemeriksaan, pengobatan, dan administrasi medis sesuai dengan ketentuan perundang-undangan yang berlaku.
        </div>
    </div>

    <div class="footer clearfix">
        <div class="signature-left">
            <p class="date-text">Balikpapan, {{ now()->translatedFormat('d F Y') }}</p>
            <p class="role">Pasien / Wali,</p>
            <div class="signature-line"></div>
            <p class="name">{{ $pasien->nama }}</p>
        </div>
        <div class="signature-right">
            <p class="date-text" style="visibility: hidden;">Balikpapan, {{ now()->translatedFormat('d F Y') }}</p>
            <p class="role">Petugas Pendaftaran,</p>
            <div class="signature-line"></div>
            <p class="name">(.................................................)</p>
        </div>
    </div>
</body>
</html>
