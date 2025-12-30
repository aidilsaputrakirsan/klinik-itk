<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Tindakan</title>
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
    </style>
</head>
<body>
    <div class="header">
        <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
        <h2>LAPORAN TINDAKAN MEDIS</h2>
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
                    <div class="summary-value">{{ $summary['totalTindakan'] }}</div>
                    <div class="summary-label">Total Tindakan</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #059669;">Rp {{ number_format($summary['totalPendapatan'], 0, ',', '.') }}</div>
                    <div class="summary-label">Total Pendapatan</div>
                </td>
                <td>
                    <div class="summary-value" style="color: #7c3aed;">{{ $summary['jenisTindakan'] }}</div>
                    <div class="summary-label">Jenis Tindakan</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Kode</th>
                <th>Nama Tindakan</th>
                <th style="width: 100px;" class="text-right">Biaya Satuan</th>
                <th style="width: 60px;" class="text-right">Jumlah</th>
                <th style="width: 70px;" class="text-right">Frekuensi</th>
                <th style="width: 110px;" class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penggunaanTindakan as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->kode }}</td>
                <td>{{ $item->nama }}</td>
                <td class="text-right">Rp {{ number_format($item->biaya, 0, ',', '.') }}</td>
                <td class="text-right"><strong>{{ $item->total_penggunaan }}x</strong></td>
                <td class="text-right">{{ $item->frekuensi }} kunjungan</td>
                <td class="text-right"><strong>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data tindakan</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($penggunaanTindakan) > 0)
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ $penggunaanTindakan->sum('total_penggunaan') }}x</strong></td>
                <td></td>
                <td class="text-right"><strong>Rp {{ number_format($penggunaanTindakan->sum('total_pendapatan'), 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
    </div>
</body>
</html>
