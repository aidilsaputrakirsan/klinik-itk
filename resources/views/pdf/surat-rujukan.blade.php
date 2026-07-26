<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Rujukan Puskesmas</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 5px;
            margin-bottom: 15px;
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
            margin: 10px 0 15px 0;
        }
        .title h3 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 1px;
        }
        .title .nomor {
            font-size: 10pt;
            margin-top: 4px;
        }
        .destination {
            float: right;
            width: 280px;
            margin-bottom: 15px;
            font-size: 11pt;
        }
        .destination p {
            margin: 2px 0;
        }
        .content {
            margin: 10px 0;
            text-align: justify;
        }
        .data-pasien {
            margin: 10px 0 15px 20px;
        }
        .data-pasien table {
            border-collapse: collapse;
            width: 100%;
        }
        .data-pasien td {
            padding: 3px 8px 3px 0;
            vertical-align: top;
        }
        .data-pasien td:first-child {
            width: 160px;
        }
        .terapi-box {
            margin: 10px 0 15px 20px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            background-color: #fcfcfc;
            border-radius: 4px;
        }
        .terapi-box p {
            margin: 3px 0;
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
            margin: 4px 0;
        }
        .signature .role {
            margin-bottom: 75px;
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
            margin-top: 25px;
            font-size: 8.5pt;
            font-style: italic;
            color: #555;
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
        <h3>SURAT RUJUKAN</h3>
        <p class="nomor">
            Nomor: {{ $surat->nomor_surat ?? '....... / IT10 / TU.03 / ' . \Carbon\Carbon::parse($surat->tanggal_surat)->format('Y') }}
        </p>
    </div>

    <div class="destination">
        <p>Kepada Yth,</p>
        <p>Sejawat</p>
        <p>Di <strong>{{ $surat->keterangan ?? 'Puskesmas Karang Joang' }}</strong></p>
    </div>
    <div class="clearfix"></div>

    <div class="content">
        <p>Dengan ini kami kirimkan seorang penderita:</p>

        <div class="data-pasien">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ $pasien->nama }}</strong></td>
                </tr>
                <tr>
                    <td>Umur</td>
                    <td>:</td>
                    <td>{{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '-' }} Tahun</td>
                </tr>
                <tr>
                    <td>Jenis Kelamin</td>
                    <td>:</td>
                    <td>{{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                @php
                    $pekerjaanText = $pasien->pekerjaan ?? '-';
                    if ($pasien->tipe_pasien === 'dosen') {
                        $pekerjaanText = 'Dosen';
                    } elseif ($pasien->tipe_pasien === 'tendik') {
                        $pekerjaanText = 'Tenaga Kependidikan';
                    } elseif ($pasien->tipe_pasien === 'mahasiswa') {
                        $pekerjaanText = 'Pelajar/Mahasiswa';
                    } else {
                        $pekerjaanText = $pasien->pekerjaan ? ucwords(str_replace('_', ' ', $pasien->pekerjaan)) : '-';
                    }
                @endphp
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>{{ $pekerjaanText }}</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>{{ $pasien->alamat ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Diagnosa Sementara</td>
                    <td>:</td>
                    <td><strong>{{ $pemeriksaan->diagnosis_utama ?? '-' }}{{ !empty($pemeriksaan->diagnosis_sekunder) ? ' (' . $pemeriksaan->diagnosis_sekunder . ')' : '' }}</strong></td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 10px; font-weight: bold;">Terapi Yang Telah Diberikan:</p>
        <div class="terapi-box">
            @php
                $hasTerapi = false;
            @endphp
            @if(isset($pemeriksaan->tindakans) && $pemeriksaan->tindakans->count() > 0)
                @php $hasTerapi = true; @endphp
                <p><strong>Tindakan:</strong> {{ implode(', ', $pemeriksaan->tindakans->pluck('nama')->toArray()) }}</p>
            @endif

            @if(isset($pemeriksaan->resepObat) && $pemeriksaan->resepObat->count() > 0)
                @php $hasTerapi = true; @endphp
                <p><strong>Obat:</strong></p>
                <ul style="margin: 2px 0 2px 20px; padding: 0;">
                    @foreach($pemeriksaan->resepObat as $resep)
                        <li>
                            {{ $resep->nama_obat }}
                            @if($resep->dosis) ({{ $resep->dosis }}) @endif
                            - {{ $resep->jumlah }} {{ $resep->satuan ?? 'pcs' }}
                            @if($resep->aturan_pakai) [{{ $resep->aturan_pakai }}] @endif
                            @if($resep->keterangan) <em>({{ $resep->keterangan }})</em> @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(!$hasTerapi)
                <p style="color: #777; italic">- Belum ada terapi/tindakan khusus yang diberikan -</p>
            @endif
        </div>

        <p style="margin-top: 15px; font-weight: bold;">Dikirim Untuk:</p>
        <p style="margin-left: 20px; margin-top: 4px;">
            "{{ $surat->keperluan ?? 'Mohon untuk dilakukan pemeriksaan/perawatan/penatalaksanaan lebih lanjut' }}"
        </p>

        <p style="margin-top: 20px;">Demikian surat rujukan ini kami kirimkan. Atas kerja sama dan bantuan Sejawat, kami ucapkan terima kasih.</p>
    </div>

    <div class="footer clearfix">
        <div class="signature">
            <p class="date">Balikpapan, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</p>
            <p class="role">Dokter Pemeriksa,</p>
            <p class="name">( {{ $dokter->name ?? 'dr. -' }} )</p>
            @if($dokter->nip)
            <p class="nip">SIP. {{ $dokter->nip }}</p>
            @endif
        </div>
    </div>

    <div class="note">
        <p>*) Surat rujukan ini dicetak secara elektronik dan sah tanpa tanda tangan basah.</p>
    </div>
</body>
</html>
