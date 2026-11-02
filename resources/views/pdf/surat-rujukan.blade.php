<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Rujukan Puskesmas</title>
    <style>
        @page {
            margin: 0.8cm 1.5cm 0.6cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.25;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px double #000;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }
        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 8pt;
            margin: 1px 0;
        }
        .title {
            text-align: center;
            margin: 4px 0 6px 0;
        }
        .title h3 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 1px;
        }
        .title .nomor {
            font-size: 9pt;
            margin-top: 2px;
        }
        .destination {
            float: right;
            width: 260px;
            margin-bottom: 4px;
            font-size: 9.5pt;
        }
        .destination p {
            margin: 1px 0;
        }
        .content {
            margin: 4px 0;
        }
        .data-pasien {
            width: 100%;
            border-collapse: collapse;
            margin: 2px 0 6px 0;
            font-size: 9pt;
        }
        .data-pasien td {
            padding: 1.5px 3px;
            vertical-align: top;
        }
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin: 5px 0 2px 0;
            color: #111;
        }
        .tabel-medis {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 4px;
        }
        .tabel-medis th, .tabel-medis td {
            border: 1px solid #777;
            padding: 3px 5px;
            vertical-align: top;
        }
        .tabel-medis th {
            background-color: #f1f3f5;
            font-weight: bold;
            text-align: left;
        }
        .tabel-medis .label-cell {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #222;
        }
        .tabel-vital {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            text-align: center;
        }
        .tabel-vital th {
            background-color: #f8f9fa;
            border-right: 1px solid #777;
            border-bottom: 1px solid #777;
            padding: 2px;
            font-weight: normal;
            font-size: 7.5pt;
            text-align: center;
        }
        .tabel-vital td {
            border-right: 1px solid #777;
            padding: 2.5px 3px;
            text-align: center;
        }
        .tabel-vital th:last-child, .tabel-vital td:last-child {
            border-right: none;
        }
        .footer {
            margin-top: 8px;
        }
        .signature {
            float: right;
            width: 230px;
            text-align: center;
        }
        .signature p {
            margin: 2px 0;
        }
        .signature .role {
            margin-bottom: 45px;
        }
        .signature .name {
            font-weight: bold;
            text-decoration: underline;
        }
        .signature .nip {
            font-size: 8.5pt;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .note {
            margin-top: 6px;
            font-size: 7.5pt;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <p>SIO : 445.5/100/DPMPTSP</p>
        <p>Jl. Soekarno-Hatta Km 15, Karang Joang, Balikpapan Utara, Kalimantan Timur 76127</p>
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
        <p>Sejawat Dokter</p>
        <p>Di <strong>{{ $surat->keterangan ?? 'Puskesmas Karang Joang' }}</strong></p>
    </div>
    <div class="clearfix"></div>

    <div class="content">
        <p style="margin: 2px 0;">Dengan ini kami kirimkan seorang penderita:</p>

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

        <table class="data-pasien">
            <tr>
                <td style="width: 14%;">Nama Pasien</td>
                <td style="width: 2%;">:</td>
                <td style="width: 48%;"><strong>{{ $pasien->nama }}</strong> ({{ $pasien->nomor_rm ?? '-' }})</td>
                <td style="width: 14%;">Umur / JK</td>
                <td style="width: 2%;">:</td>
                <td style="width: 20%;">{{ $pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : '-' }} Th / {{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td>:</td>
                <td>{{ $pekerjaanText }}</td>
                <td>Alamat</td>
                <td>:</td>
                <td>{{ $pasien->alamat ?? '-' }}</td>
            </tr>
        </table>

        <!-- 1. Hasil Anamnesis & Vital Sign -->
        <div class="section-title">1. Hasil Anamnesis & Vital Sign</div>
        <table class="tabel-medis">
            <tr>
                <td class="label-cell" style="width: 16%;">Keluhan Utama</td>
                <td style="width: 44%;">{{ $anamnesis->keluhan_utama ?? '-' }}</td>
                <td class="label-cell" style="width: 16%;">Riwayat Alergi</td>
                <td style="width: 24%;">
                    @if(!empty($anamnesis->riwayat_alergi) && strtolower($anamnesis->riwayat_alergi) !== 'tidak ada' && strtolower($anamnesis->riwayat_alergi) !== '-')
                        <strong style="color: #b91c1c;">{{ $anamnesis->riwayat_alergi }}</strong>
                    @else
                        Tidak ada
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label-cell">Tanda Vital</td>
                <td colspan="3" style="padding: 0;">
                    <table class="tabel-vital">
                        <tr>
                            <th>Tekanan Darah</th>
                            <th>Suhu Tubuh</th>
                            <th>Denyut Nadi</th>
                            <th>Respirasi</th>
                            <th>Berat Badan</th>
                            <th>Tinggi Badan</th>
                        </tr>
                        <tr>
                            <td style="font-weight: bold;">{{ !empty($anamnesis->tekanan_darah) ? $anamnesis->tekanan_darah . ' mmHg' : '-' }}</td>
                            <td>{{ !empty($anamnesis->suhu) ? $anamnesis->suhu . ' °C' : '-' }}</td>
                            <td>{{ !empty($anamnesis->nadi) ? $anamnesis->nadi . ' x/m' : '-' }}</td>
                            <td>{{ !empty($anamnesis->respirasi) ? $anamnesis->respirasi . ' x/m' : '-' }}</td>
                            <td>{{ !empty($anamnesis->berat_badan) ? $anamnesis->berat_badan . ' kg' : '-' }}</td>
                            <td>{{ !empty($anamnesis->tinggi_badan) ? $anamnesis->tinggi_badan . ' cm' : '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 2. Pemeriksaan Fisik dan Penunjang -->
        <div class="section-title">2. Pemeriksaan Fisik & Penunjang</div>
        <table class="tabel-medis">
            <tr>
                <th style="width: 50%;">Pemeriksaan Fisik</th>
                <th style="width: 50%;">Hasil Pemeriksaan Penunjang (Lab / Radiologi)</th>
            </tr>
            <tr>
                <td>{{ !empty($pemeriksaan->pemeriksaan_fisik) ? $pemeriksaan->pemeriksaan_fisik : '-' }}</td>
                <td>{{ !empty($pemeriksaan->hasil_pemeriksaan) ? $pemeriksaan->hasil_pemeriksaan : '-' }}</td>
            </tr>
        </table>

        <!-- 3. Diagnosis & Penatalaksanaan -->
        <div class="section-title">3. Diagnosis & Penatalaksanaan</div>
        <table class="tabel-medis">
            <tr>
                <td class="label-cell" style="width: 16%;">Diagnosis Utama</td>
                <td style="width: 44%;">
                    <strong>{{ $pemeriksaan->diagnosis_utama ?? '-' }}</strong>
                    @if(!empty($pemeriksaan->kode_icd10))
                        <span style="font-size: 8pt; color: #444;">({{ $pemeriksaan->kode_icd10 }})</span>
                    @endif
                </td>
                <td class="label-cell" style="width: 16%;">Diagnosis Sekunder</td>
                <td style="width: 24%;">{{ !empty($pemeriksaan->diagnosis_sekunder) ? $pemeriksaan->diagnosis_sekunder : '-' }}</td>
            </tr>
            <tr>
                <td class="label-cell">Penatalaksanaan</td>
                <td>{{ !empty($pemeriksaan->penatalaksanaan_medis) ? $pemeriksaan->penatalaksanaan_medis : '-' }}</td>
                <td class="label-cell">Prognosis / Anjuran</td>
                <td>
                    {{ !empty($pemeriksaan->prognosis) ? $pemeriksaan->prognosis : '-' }}
                    @if(!empty($pemeriksaan->anjuran))
                        <div style="font-size: 7.5pt; color: #333; margin-top: 1px;">
                            <em>Anjuran: {{ $pemeriksaan->anjuran }}</em>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- 4. Tindakan Medis yang Dipilih -->
        <div class="section-title">4. Tindakan Medis & Terapi yang Telah Diberikan</div>
        <table class="tabel-medis">
            <tr>
                <td class="label-cell" style="width: 16%;">Tindakan Medis</td>
                <td>
                    @if(isset($pemeriksaan->tindakans) && $pemeriksaan->tindakans->count() > 0)
                        <strong>{{ implode(', ', $pemeriksaan->tindakans->pluck('nama')->toArray()) }}</strong>
                    @else
                        <span style="color: #666; font-style: italic;">-</span>
                    @endif
                </td>
            </tr>
            @if(isset($pemeriksaan->resepObat) && $pemeriksaan->resepObat->count() > 0)
            <tr>
                <td class="label-cell">Terapi Obat</td>
                <td>
                    @php
                        $obatList = [];
                        foreach($pemeriksaan->resepObat as $resep) {
                            $str = $resep->nama_obat;
                            if ($resep->dosis) $str .= ' (' . $resep->dosis . ')';
                            if ($resep->jumlah) $str .= ' ' . $resep->jumlah . ' ' . ($resep->satuan ?? 'pcs');
                            if ($resep->aturan_pakai) $str .= ' [' . $resep->aturan_pakai . ']';
                            $obatList[] = $str;
                        }
                    @endphp
                    {{ implode('; ', $obatList) }}
                </td>
            </tr>
            @endif
        </table>

        <div style="margin-top: 6px; font-size: 9pt;">
            <strong>Dikirim Untuk:</strong>
            <span style="font-style: italic;">
                "{{ $surat->keperluan ?? 'Mohon untuk dilakukan pemeriksaan/perawatan/penatalaksanaan lebih lanjut' }}"
            </span>
        </div>

        <p style="margin-top: 4px; margin-bottom: 0; font-size: 9pt;">
            Demikian surat rujukan ini kami kirimkan. Atas kerja sama dan bantuan Sejawat, kami ucapkan terima kasih.
        </p>
    </div>

    <div class="footer clearfix">
        <div class="signature">
            <p class="date">Balikpapan, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}</p>
            <p class="role">Dokter Pemeriksa,</p>
            <p class="name">( {{ isset($dokter) && $dokter ? $dokter->name : 'dr. -' }} )</p>
            @if(!empty($dokter) && !empty($dokter->nip))
            <p class="nip">SIP. {{ $dokter->nip }}</p>
            @endif
        </div>
    </div>

    <div class="note">
        <p>*) Surat rujukan ini dicetak secara elektronik dan sah tanpa tanda tangan basah.</p>
    </div>
</body>
</html>
