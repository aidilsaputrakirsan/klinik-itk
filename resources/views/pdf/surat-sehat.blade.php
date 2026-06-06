<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Sehat</title>
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
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 2px 0;
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
        .title .nomor {
            font-size: 10pt;
            margin-top: 3px;
        }
        .content {
            text-align: justify;
            margin: 10px 0;
        }
        .content p {
            margin: 4px 0;
            text-indent: 40px;
        }
        .data-pasien {
            margin: 8px 0 8px 40px;
        }
        .data-pasien table {
            border-collapse: collapse;
        }
        .data-pasien td {
            padding: 1px 10px 1px 0;
            vertical-align: top;
        }
        .data-pasien td:first-child {
            width: 140px;
        }
        .footer {
            margin-top: 15px;
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
            margin-bottom: 80px;
        }
        .signature .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature .nip {
            font-size: 9pt;
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
        <p>SIO : 445.5/100/DPMPTSP</p>
        <p>Jl. Soekarno-Hatta Km 15, Karang Joang, Balikpapan Utara</p>
        <p>Kalimantan Timur 76127</p>
        <p>Telp: +62 811 5390 801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="title">
        <h3>SURAT KETERANGAN SEHAT</h3>
        <p class="nomor">Nomor: {{ $surat->nomor_surat }}</p>
    </div>

    <div class="content">
        <p>Yang bertanda tangan dibawah ini, Dokter Klinik ITK menerangkan bahwa:</p>

        <div class="data-pasien">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td>{{ $pasien->nama }}</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <td>Umur</td>
                    <td>:</td>
                    <td>{{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '-' }}</td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>{{ $pasien->pekerjaan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pasien->alamat ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <p>Setelah diadakan pemeriksaan fisik di Klinik ITK, dinyatakan yang bersangkutan dalam keadaan :</p>
        <h3 style="text-align: center; margin: 20px 0;">SEHAT</h3>

        <div class="data-pasien">
            <table>
                <tr>
                    <td>Tinggi/ Berat Badan</td>
                    <td>:</td>
                    <td>{{ $anamnesis->tinggi_badan ?? '-' }} cm / {{ $anamnesis->berat_badan ?? '-' }} kg</td>
                </tr>
                <tr>
                    <td>Tekanan darah</td>
                    <td>:</td>
                    <td>{{ $anamnesis->tekanan_darah ?? '-' }} mmHg</td>
                </tr>
                <tr>
                    <td>Nadi</td>
                    <td>:</td>
                    <td>{{ $anamnesis->nadi ?? '-' }} x / Menit</td>
                </tr>
                <tr>
                    <td>Suhu</td>
                    <td>:</td>
                    <td>{{ $anamnesis->suhu ?? '-' }} °C</td>
                </tr>
                <tr>
                    <td>Golongan Darah</td>
                    <td>:</td>
                    <td>{{ $pasien->golongan_darah ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Buta Warna</td>
                    <td>:</td>
                    <td>{{ $anamnesis->buta_warna ?? '-' }}</td>
                </tr>
            </table>
        </div>

        @if($surat->keperluan)
        <p style="text-indent: 0;">Surat keterangan ini diberikan untuk : <strong>{{ $surat->keperluan }}</strong></p>
        @endif
        <p style="text-indent: 0;">Demikian surat keterangan ini dibuat dengan sebenar benarnya untuk digunakan sebagaimana mestinya.</p>
    </div>

    <div class="footer clearfix">
        <div class="signature">
            <p class="date">Balikpapan, {{ $surat->tanggal_surat->translatedFormat('d F Y') }}</p>
            <p class="role">Dokter Pemeriksa,</p>
            <p class="name">{{ $dokter->name ?? 'dr. -' }}</p>
            @if($dokter->nip)
            <p class="nip">SIP. {{ $dokter->nip }}</p>
            @endif
        </div>
    </div>

    <div class="note">
        <p>*) Surat ini dicetak secara elektronik dan sah tanpa tanda tangan basah.</p>
    </div>
</body>
</html>
