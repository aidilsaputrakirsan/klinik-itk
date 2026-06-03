<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemeriksaan Umum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            background-color: #e2e8f0;
            color: #475569;
        }
        table td {
            font-size: 9px;
            vertical-align: top;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pemeriksaan Umum</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal & No. Kunjungan</th>
                <th width="20%">Pasien & Demografi</th>
                <th width="15%">Anamnesis</th>
                <th width="12%">TTV & Fisik</th>
                <th width="20%">Pemeriksaan Medis</th>
                <th width="20%">Asuhan Keperawatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekamMedis as $index => $rm)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $rm->tanggal_kunjungan ? $rm->tanggal_kunjungan->format('d/m/Y H:i') : '-' }}<br>
                        <span style="color: #666; font-size: 9px;">{{ $rm->nomor_kunjungan }}</span>
                    </td>
                    <td>
                        <strong>{{ $rm->pasien ? $rm->pasien->nama : '-' }}</strong><br>
                        <span style="color: #666; font-size: 9px;">RM: {{ $rm->pasien ? $rm->pasien->nomor_rm : '-' }}</span><br>
                        <span style="color: #666; font-size: 9px;">
                            {{ $rm->pasien ? ($rm->pasien->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan') : '-' }} | {{ $rm->pasien ? $rm->pasien->umur : '-' }} Thn<br>
                            NIK/Identitas: {{ $rm->pasien ? ($rm->pasien->nik ?: ($rm->pasien->nomor_identitas ?: '-')) : '-' }}<br>
                            Telp: {{ $rm->pasien ? $rm->pasien->phone : '-' }}<br>
                            Agama: {{ $rm->pasien ? ucwords(strtolower(str_replace('_', ' ', $rm->pasien->agama))) : '-' }}<br>
                            Pendidikan: {{ $rm->pasien ? ucwords(strtolower(str_replace('_', ' ', $rm->pasien->pendidikan_terakhir))) : '-' }}<br>
                            Status ITK: {{ $rm->pasien ? ucwords($rm->pasien->tipe_pasien) : '-' }}<br>
                            @if($rm->pasien && in_array($rm->pasien->tipe_pasien, ['mahasiswa', 'dosen']))
                                Fakultas: {{ $rm->pasien->fakultas ?: '-' }}<br>
                                Prodi: {{ $rm->pasien->prodi ?: '-' }}<br>
                            @elseif($rm->pasien && $rm->pasien->tipe_pasien == 'tendik')
                                Unit Kerja: {{ $rm->pasien->fakultas ?: '-' }}<br>
                            @endif
                            Goldar: {{ $rm->pasien ? $rm->pasien->golongan_darah : '-' }}
                        </span>
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            <strong>Keluhan:</strong> {{ $rm->anamnesis->keluhan_utama ?: '-' }}<br>
                            <span style="color: #666; font-size: 9px;">
                                <strong>R. Sekarang:</strong> {{ $rm->anamnesis->riwayat_penyakit_sekarang ?: '-' }}<br>
                                <strong>R. Dahulu:</strong> {{ $rm->anamnesis->riwayat_penyakit_dahulu ?: '-' }}<br>
                                <strong>R. Keluarga:</strong> {{ $rm->anamnesis->riwayat_keluarga ?: '-' }}<br>
                                <strong>Alergi:</strong> {{ $rm->anamnesis->riwayat_alergi ?: '-' }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $tb = $rm->anamnesis ? $rm->anamnesis->tinggi_badan : null;
                            $bb = $rm->anamnesis ? $rm->anamnesis->berat_badan : null;
                            $imtVal = '-';
                            if ($tb && $bb) {
                                $h = $tb / 100;
                                $imtVal = number_format($bb / ($h * $h), 2);
                            }
                        @endphp
                        @if($rm->anamnesis)
                            TB: {{ $tb ?: '-' }} cm, BB: {{ $bb ?: '-' }} kg (IMT: {{ $imtVal }})<br>
                            TD: {{ $rm->anamnesis->tekanan_darah ?: '-' }}<br>
                            Nd: {{ $rm->anamnesis->nadi ?: '-' }}, Sh: {{ $rm->anamnesis->suhu ?: '-' }}<br>
                            RR: {{ $rm->anamnesis->respirasi ?: '-' }}<br>
                            Nyeri: {{ $rm->anamnesis->skala_nyeri ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->pemeriksaan)
                            <strong>{{ $rm->pemeriksaan->diagnosis_utama ?: '-' }}</strong>
                            @if($rm->pemeriksaan->kode_icd10)
                                (ICD: {{ $rm->pemeriksaan->kode_icd10 }})<br>
                            @else
                                <br>
                            @endif
                            <span style="color: #666; font-size: 9px;">
                                <strong>Fisik Lain:</strong> {{ $rm->pemeriksaan->pemeriksaan_fisik ?: '-' }}<br>
                                <strong>Penatalaksanaan:</strong> {{ $rm->pemeriksaan->penatalaksanaan_medis ?: '-' }}<br>
                                <strong>Dokter:</strong> {{ $rm->pemeriksaan->dokter ? $rm->pemeriksaan->dokter->name : '-' }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            <div style="font-size: 9px; line-height: 1.2;">
                                <strong>D:</strong> {{ $rm->anamnesis->diagnosa_keperawatan ?: '-' }}<br>
                                <strong>I:</strong> {{ $rm->anamnesis->intervensi_keperawatan ?: '-' }}<br>
                                <strong>Imp:</strong> {{ $rm->anamnesis->implementasi_keperawatan ?: '-' }}<br>
                                <strong>E:</strong> {{ $rm->anamnesis->evaluasi_keperawatan ?: '-' }}<br>
                                <strong>Perawat:</strong> {{ $rm->anamnesis->perawat ? $rm->anamnesis->perawat->name : '-' }}
                            </div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">Tidak ada data rekam medis pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
