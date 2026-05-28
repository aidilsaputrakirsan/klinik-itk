<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Screening</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Screening</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Tanggal & No. Kunjungan</th>
                <th width="15%">Pasien (RM / Nama)</th>
                <th width="12%">Antropometri</th>
                <th width="15%">TTV</th>
                <th width="15%">Lab Darah</th>
                <th width="10%">Buta Warna</th>
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
                        @if($rm->anamnesis)
                            TB: {{ $rm->anamnesis->tinggi_badan ?: '-' }} cm<br>
                            BB: {{ $rm->anamnesis->berat_badan ?: '-' }} kg<br>
                            LP: {{ $rm->anamnesis->lingkar_perut ?: '-' }} cm
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            TD: {{ $rm->anamnesis->tekanan_darah ?: '-' }}<br>
                            Nd: {{ $rm->anamnesis->nadi ?: '-' }}, Sh: {{ $rm->anamnesis->suhu ?: '-' }}<br>
                            RR: {{ $rm->anamnesis->respirasi ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($rm->anamnesis)
                            Gula: {{ $rm->anamnesis->gula_darah ?: '-' }} ({{ $rm->anamnesis->jenis_gula_darah ?: '-' }})<br>
                            Kolesterol: {{ $rm->anamnesis->kolesterol ?: '-' }}<br>
                            Asam Urat: {{ $rm->anamnesis->asam_urat ?: '-' }}<br>
                            Hb: {{ $rm->anamnesis->hemoglobin ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{ $rm->anamnesis ? $rm->anamnesis->buta_warna : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data screening pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
