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
                <th width="15%">Pasien (RM / Nama)</th>
                <th width="10%">Layanan & Status</th>
                <th width="15%">Keluhan Utama</th>
                <th width="15%">TTV (TD, Nadi, Suhu, BB)</th>
                <th width="12%">Diagnosis & ICD-10</th>
                <th width="15%">Tindakan & Anjuran</th>
                <th width="15%">Askep</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekamMedis as $index => $rm)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $rm->tanggal_kunjungan ? $rm->tanggal_kunjungan->format('d/m/Y') : '-' }}<br>
                        <span style="color: #666; font-size: 9px;">{{ $rm->nomor_kunjungan }}</span>
                    </td>
                    <td>
                        <strong>{{ $rm->pasien ? $rm->pasien->nama : '-' }}</strong><br>
                        <span style="color: #666; font-size: 9px;">RM: {{ $rm->pasien ? $rm->pasien->no_rm : '-' }}</span>
                    </td>
                    <td>
                        {{ $rm->jenis_layanan ?? '-' }}<br>
                        <span class="badge">{{ $rm->status_label }}</span>
                    </td>
                    <td>
                        {{ $rm->anamnesis ? $rm->anamnesis->keluhan_utama : '-' }}
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            TD: {{ $rm->anamnesis->tekanan_darah ?: '-' }}<br>
                            Nd: {{ $rm->anamnesis->nadi ?: '-' }}, Sh: {{ $rm->anamnesis->suhu ?: '-' }}<br>
                            BB: {{ $rm->anamnesis->berat_badan ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->pemeriksaan)
                            <strong>{{ $rm->pemeriksaan->diagnosis_utama ?: '-' }}</strong>
                            @if($rm->pemeriksaan->kode_icd10)
                                <br><span style="color: #666; font-size: 9px;">ICD: {{ $rm->pemeriksaan->kode_icd10 }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->pemeriksaan && $rm->pemeriksaan->tindakans->count() > 0)
                            <div style="margin-bottom: 4px;">
                                <strong>Tindakan:</strong> {{ $rm->pemeriksaan->tindakans->pluck('nama')->implode(', ') }}
                            </div>
                        @endif
                        @if($rm->pemeriksaan && $rm->pemeriksaan->anjuran)
                            <div>
                                <strong>Anjuran:</strong> {{ $rm->pemeriksaan->anjuran }}
                            </div>
                        @endif
                        @if(!$rm->pemeriksaan || ($rm->pemeriksaan->tindakans->count() == 0 && !$rm->pemeriksaan->anjuran))
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            @if($rm->anamnesis->diagnosa_keperawatan)
                                <strong>D:</strong> {{ \Illuminate\Support\Str::limit($rm->anamnesis->diagnosa_keperawatan, 30) }}<br>
                            @endif
                            @if($rm->anamnesis->intervensi_keperawatan)
                                <strong>I:</strong> {{ \Illuminate\Support\Str::limit($rm->anamnesis->intervensi_keperawatan, 30) }}<br>
                            @endif
                            @if($rm->anamnesis->implementasi_keperawatan)
                                <strong>Imp:</strong> {{ \Illuminate\Support\Str::limit($rm->anamnesis->implementasi_keperawatan, 30) }}<br>
                            @endif
                            @if($rm->anamnesis->evaluasi_keperawatan)
                                <strong>E:</strong> {{ \Illuminate\Support\Str::limit($rm->anamnesis->evaluasi_keperawatan, 30) }}
                            @endif
                            @if(!$rm->anamnesis->diagnosa_keperawatan && !$rm->anamnesis->intervensi_keperawatan && !$rm->anamnesis->implementasi_keperawatan && !$rm->anamnesis->evaluasi_keperawatan)
                                -
                            @endif
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
