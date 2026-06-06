<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Sakit</title>
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
        .highlight-box {
            border: 1px solid #000;
            padding: 8px;
            margin: 8px 40px;
            background-color: #f9f9f9;
        }
        .highlight-box table td {
            padding: 2px 10px 2px 0;
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
        <h3>SURAT KETERANGAN SAKIT</h3>
        <p class="nomor">Nomor: {{ $surat->nomor_surat }}</p>
    </div>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, Dokter pada Klinik Institut Teknologi Kalimantan, menerangkan bahwa:</p>

        <div class="data-pasien">
            <table>
                <tr>
                    <td>Nama</td>
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
                @if($pasien->tipe_pasien !== 'umum')
                <tr>
                    <td>{{ $pasien->tipe_pasien === 'mahasiswa' ? 'NIM' : 'NIP' }}</td>
                    <td>:</td>
                    <td>{{ $pasien->nomor_identitas ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Fakultas/Prodi</td>
                    <td>:</td>
                    <td>{{ $pasien->fakultas ?? '-' }} / {{ $pasien->prodi ?? '-' }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>Berdasarkan hasil pemeriksaan kesehatan yang telah dilakukan pada tanggal <strong>{{ $surat->tanggal_surat->translatedFormat('d F Y') }}</strong>, yang bersangkutan dinyatakan <strong>SAKIT</strong> dan memerlukan istirahat.</p>

        <div class="highlight-box">
            <table>
                <tr>
                    <td style="width: 150px;"><strong>Lama Istirahat</strong></td>
                    <td>:</td>
                    <td><strong>{{ $surat->jumlah_hari_istirahat ?? 1 }} ({{ \App\Helpers\Terbilang::convert($surat->jumlah_hari_istirahat ?? 1) }}) hari</strong></td>
                </tr>
                <tr>
                    <td><strong>Mulai Tanggal</strong></td>
                    <td>:</td>
                    <td>{{ $surat->tanggal_mulai ? $surat->tanggal_mulai->translatedFormat('d F Y') : $surat->tanggal_surat->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Sampai Tanggal</strong></td>
                    <td>:</td>
                    <td>{{ $surat->tanggal_selesai ? $surat->tanggal_selesai->translatedFormat('d F Y') : '-' }}</td>
                </tr>
            </table>
        </div>

        <p>Oleh karena itu, yang bersangkutan tidak dapat melaksanakan tugas/aktivitas selama waktu tersebut di atas.</p>

        @if($surat->keperluan)
        <p>Surat keterangan ini dibuat untuk keperluan: <strong>{{ $surat->keperluan }}</strong>.</p>
        @endif

        <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
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
