<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Hasil Diagnosis (ICD)</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            font-weight: normal;
        }
        .header p {
            margin: 2px 0;
            font-size: 9px;
            color: #666;
        }
        .period {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .summary-label {
            font-size: 9px;
            color: #666;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #7c3aed;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            margin-top: 15px;
            margin-bottom: 5px;
            border-left: 3px solid #7c3aed;
            padding-left: 6px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data th, table.data td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table.data th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.data td {
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <h2>LAPORAN HASIL DIAGNOSIS (ICD-10)</h2>
        <p>Jl. Soekarno Hatta KM. 15, Karang Joang, Balikpapan Utara</p>
        <p>Telp: +62 811 5390 801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="period">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-value" style="color: #7c3aed;">{{ $summary['total_kasus'] }}</div>
                    <div class="summary-label">Total Diagnosis / Kasus</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #2563eb;">{{ $summary['total_pasien'] }}</div>
                    <div class="summary-label">Total Pasien Didiagnosis</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #059669;">{{ $summary['total_diagnosis_unik'] }}</div>
                    <div class="summary-label">Jenis Penyakit Terdeteksi</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">10 PENYAKIT DENGAN FREKUENSI TERTINGGI (TOP 10)</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 80px;">Kode ICD-10</th>
                <th>Nama Diagnosis / Penyakit</th>
                <th style="width: 80px;" class="text-right">Total Kasus</th>
                <th style="width: 80px;" class="text-right">Total Pasien</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topDiagnoses as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $item->kode_icd10 }}</strong></td>
                <td>{{ $item->diagnosis_utama }}</td>
                <td class="text-right"><strong>{{ $item->total_kasus }}x</strong></td>
                <td class="text-right">{{ $item->total_pasien }} orang</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data diagnosis</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(count($allDiagnoses) > 0)
    <div class="page-break"></div>

    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <h2>LAPORAN HASIL DIAGNOSIS (ICD-10)</h2>
        <p>Jl. Soekarno Hatta KM. 15, Karang Joang, Balikpapan Utara</p>
    </div>

    <div class="section-title">REKAPITULASI KESELURUHAN HASIL DIAGNOSIS</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 80px;">Kode ICD-10</th>
                <th>Nama Diagnosis / Penyakit</th>
                <th style="width: 80px;" class="text-right">Total Kasus</th>
                <th style="width: 80px;" class="text-right">Total Pasien</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allDiagnoses as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td><strong>{{ $item->kode_icd10 }}</strong></td>
                <td>{{ $item->diagnosis_utama }}</td>
                <td class="text-right"><strong>{{ $item->total_kasus }}x</strong></td>
                <td class="text-right">{{ $item->total_pasien }} orang</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WITA</p>
    </div>
</body>
</html>
