<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Obat</title>
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
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            padding: 5px;
            background-color: #f3f4f6;
            border-left: 3px solid #2563eb;
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
        .stok-rendah {
            color: #dc2626;
            font-weight: bold;
        }
        .stok-normal {
            color: #059669;
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
        <h2>LAPORAN OBAT</h2>
        <p>Jl. Soekarno Hatta KM. 15, Karang Joang, Balikpapan Utara</p>
        <p>Telp: (0542) 8530801 | Email: klinik@itk.ac.id</p>
    </div>

    <div class="period">
        <strong>Periode Penggunaan:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
    </div>

    <div class="section-title">A. Penggunaan Obat</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Kode</th>
                <th>Nama Obat</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 80px;" class="text-right">Total Penggunaan</th>
                <th style="width: 80px;" class="text-right">Frekuensi Resep</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penggunaanObat as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['kode'] ?? '-' }}</td>
                <td>{{ $item['nama'] }}</td>
                <td>{{ $item['satuan'] }}</td>
                <td class="text-right"><strong>{{ $item['total_penggunaan'] }}</strong></td>
                <td class="text-right">{{ $item['frekuensi'] }}x</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data penggunaan obat</td>
            </tr>
            @endforelse
        </tbody>
        @if($penggunaanObat->count() > 0)
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ $penggunaanObat->sum('total_penggunaan') }}</strong></td>
                <td class="text-right"><strong>{{ $penggunaanObat->sum('frekuensi') }}x</strong></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="section-title">B. Stok Obat Saat Ini</div>
    <table class="data">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Kode</th>
                <th>Nama Obat</th>
                <th style="width: 60px;">Satuan</th>
                <th style="width: 60px;" class="text-right">Stok</th>
                <th style="width: 60px;" class="text-right">Stok Min</th>
                <th style="width: 70px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stokObat as $index => $obat)
            @php
                $isRendah = $obat->stok <= ($obat->stok_minimum ?? 10);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $obat->kode }}</td>
                <td>{{ $obat->nama }}</td>
                <td>{{ $obat->satuan }}</td>
                <td class="text-right {{ $isRendah ? 'stok-rendah' : '' }}">{{ $obat->stok }}</td>
                <td class="text-right">{{ $obat->stok_minimum ?? 10 }}</td>
                <td class="text-center">
                    @if($isRendah)
                        <span class="stok-rendah">RENDAH</span>
                    @else
                        <span class="stok-normal">Normal</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data obat</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d F Y H:i') }} WIB</p>
    </div>
</body>
</html>
