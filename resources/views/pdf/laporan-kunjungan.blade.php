<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Kunjungan</title>
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
            color: #2563eb;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .status-selesai {
            color: #059669;
            font-weight: bold;
        }
        .status-batal {
            color: #dc2626;
            font-weight: bold;
        }
        .status-menunggu {
            color: #d97706;
            font-weight: bold;
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
        <h2>LAPORAN KUNJUNGAN PASIEN</h2>
        <p>Jl. Soekarno Hatta KM. 15, Karang Joang, Balikpapan Utara</p>
        <p>Telp: (0542) 8530801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="period">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td>
                    <div class="summary-value">{{ $summary['total'] }}</div>
                    <div class="summary-label">Total Kunjungan</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #059669;">{{ $summary['selesai'] }}</div>
                    <div class="summary-label">Selesai</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #dc2626;">{{ $summary['batal'] }}</div>
                    <div class="summary-label">Batal</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">No. Kunjungan</th>
                <th style="width: 70px;">Tanggal</th>
                <th>Pasien</th>
                <th style="width: 70px;">Tipe</th>
                <th style="width: 80px;">Layanan</th>
                <th>Dokter</th>
                <th style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kunjungan as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->nomor_kunjungan }}</td>
                <td>{{ $item->tanggal_kunjungan->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $item->pasien?->nama ?? '-' }}</strong><br>
                    <small>{{ $item->pasien?->nomor_rm ?? '-' }}</small>
                </td>
                <td>{{ ucfirst($item->pasien?->tipe_pasien ?? '-') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $item->jenis_layanan)) }}</td>
                <td>{{ $item->dokter?->name ?? '-' }}</td>
                <td>
                    @if($item->status === 'selesai')
                        <span class="status-selesai">Selesai</span>
                    @elseif($item->status === 'batal')
                        <span class="status-batal">Batal</span>
                    @else
                        <span class="status-menunggu">{{ ucfirst(str_replace('_', ' ', $item->status)) }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">Tidak ada data kunjungan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
    </div>
</body>
</html>
