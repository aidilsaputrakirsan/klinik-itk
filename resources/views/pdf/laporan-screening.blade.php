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
        table td {
            font-size: 9px;
            vertical-align: top;
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
                <th width="12%">Tanggal & No. Kunjungan</th>
                <th width="20%">Pasien (RM/Nama/Status)</th>
                <th width="25%">Antropometri & TTV</th>
                <th width="20%">Lab Darah</th>
                <th width="20%">Keterangan & Tindak Lanjut</th>
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
                        <span style="color: #666; font-size: 9px;">RM: {{ $rm->pasien ? $rm->pasien->nomor_rm : '-' }}</span><br>
                        <span style="color: #666; font-size: 9px;">JK: {{ $rm->pasien ? ($rm->pasien->jenis_kelamin == 'L' ? 'Laki-Laki' : 'Perempuan') : '-' }} | Usia: {{ $rm->pasien ? $rm->pasien->umur : '-' }} Thn</span><br>
                        <span style="color: #666; font-size: 9px;">Status: {{ $rm->pasien ? ucwords($rm->pasien->tipe_pasien) : '-' }}</span><br>
                        @if($rm->pasien && in_array($rm->pasien->tipe_pasien, ['mahasiswa', 'dosen']))
                            <span style="color: #666; font-size: 9px;">Fakultas: {{ $rm->pasien->fakultas ?: '-' }}</span><br>
                            <span style="color: #666; font-size: 9px;">Prodi: {{ $rm->pasien->prodi ?: '-' }}</span>
                        @elseif($rm->pasien && $rm->pasien->tipe_pasien == 'tendik')
                            <span style="color: #666; font-size: 9px;">Unit Kerja: {{ $rm->pasien->fakultas ?: '-' }}</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $tb = $rm->anamnesis ? $rm->anamnesis->tinggi_badan : null;
                            $bb = $rm->anamnesis ? $rm->anamnesis->berat_badan : null;
                            $lp = $rm->anamnesis ? $rm->anamnesis->lingkar_perut : null;
                            
                            $imtVal = '-'; $imtKat = '-';
                            if ($tb && $bb) {
                                $h = $tb / 100;
                                $bmi = $bb / ($h * $h);
                                $imtVal = number_format($bmi, 2);
                                if ($bmi < 18) $imtKat = 'Underweight';
                                elseif ($bmi <= 22.9) $imtKat = 'Normal';
                                elseif ($bmi <= 24.9) $imtKat = 'Overweight';
                                elseif ($bmi <= 29.9) $imtKat = 'Obesitas 1';
                                else $imtKat = 'Obesitas 2';
                            }
                            
                            $lpKat = '-';
                            if ($rm->anamnesis && $rm->anamnesis->is_hamil) $lpKat = 'Hamil';
                            elseif ($lp) {
                                $gender = $rm->pasien ? $rm->pasien->jenis_kelamin : null;
                                $isCrit = false;
                                if ($gender == 'L' && $lp > 90) $isCrit = true;
                                if ($gender == 'P' && $lp > 80) $isCrit = true;
                                $lpKat = $isCrit ? 'Obesitas Sentral' : 'Normal';
                            }
                            
                            $td = $rm->anamnesis ? $rm->anamnesis->tekanan_darah : null;
                            $tdKat = '-';
                            if ($td) {
                                $parts = explode('/', $td);
                                if (count($parts) == 2) {
                                    $sys = (int)$parts[0]; $dia = (int)$parts[1];
                                    if ($sys < 130 && $dia < 85) $tdKat = 'Normal';
                                    elseif ($sys <= 139 || $dia <= 89) $tdKat = 'Prehipertensi';
                                    elseif ($sys <= 159 || $dia <= 99) $tdKat = 'Hipertensi 1';
                                    else $tdKat = 'Hipertensi 2';
                                }
                            }
                        @endphp
                        @if($rm->anamnesis)
                            <div style="margin-bottom: 4px;">
                                <strong>TB/BB:</strong> {{ $tb ?: '-' }} cm / {{ $bb ?: '-' }} kg<br>
                                <strong>IMT:</strong> {{ $imtVal }} ({{ $imtKat }})<br>
                                <strong>LP:</strong> {{ $lp ?: '-' }} cm ({{ $lpKat }})
                            </div>
                            <div>
                                <strong>TD:</strong> {{ $td ?: '-' }} ({{ $tdKat }})<br>
                                <strong>Nadi:</strong> {{ $rm->anamnesis->nadi ?: '-' }} x/m | <strong>Suhu:</strong> {{ $rm->anamnesis->suhu ?: '-' }} °C<br>
                                <strong>RR:</strong> {{ $rm->anamnesis->respirasi ?: '-' }} x/m
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $gd = $rm->anamnesis ? $rm->anamnesis->gula_darah : null;
                            $jgd = $rm->anamnesis ? $rm->anamnesis->jenis_gula_darah : null;
                            $gdKat = '-';
                            if ($gd) {
                                $gdKat = 'Normal';
                                if ($jgd == 'puasa' && $gd > 120) $gdKat = 'Hiperglikemia';
                                if ($jgd != 'puasa' && $gd > 200) $gdKat = 'Hiperglikemia';
                            }
                            
                            $au = $rm->anamnesis ? $rm->anamnesis->asam_urat : null;
                            $auKat = '-';
                            if ($au) {
                                $gender = $rm->pasien ? $rm->pasien->jenis_kelamin : null;
                                $auKat = 'Normal';
                                if ($gender == 'L' && $au > 7) $auKat = 'Hiperuricemia';
                                if ($gender == 'P' && $au > 6) $auKat = 'Hiperuricemia';
                            }
                            
                            $kol = $rm->anamnesis ? $rm->anamnesis->kolesterol : null;
                            $kolKat = $kol ? ($kol > 200 ? 'Hipercholesterolemia' : 'Normal') : '-';
                            
                            $hb = $rm->anamnesis ? $rm->anamnesis->hemoglobin : null;
                            $hbKat = $hb ? ($hb < 12 ? 'Anemia' : 'Normal') : '-';
                        @endphp
                        @if($rm->anamnesis)
                            Gula: {{ $gd ?: '-' }} ({{ $gdKat }})<br>
                            Kolest: {{ $kol ?: '-' }} ({{ $kolKat }})<br>
                            Asam Urat: {{ $au ?: '-' }} ({{ $auKat }})<br>
                            Hb: {{ $hb ?: '-' }} ({{ $hbKat }})
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 9px; line-height: 1.2;">
                            <strong>Ket:</strong> {{ $rm->anamnesis && $rm->anamnesis->keterangan_tindak_lanjut ? $rm->anamnesis->keterangan_tindak_lanjut : '-' }}<br>
                            <strong>Tindak Lanjut:</strong> 
                            @if($rm->anamnesis && $rm->anamnesis->tindak_lanjut == 'rujuk') Kembali ke Faskes 1
                            @elseif($rm->anamnesis && $rm->anamnesis->tindak_lanjut == 'rawat_jalan') Rawat Jalan
                            @elseif($rm->anamnesis && $rm->anamnesis->tindak_lanjut == 'edukasi') Edukasi
                            @else Belum Ada
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data screening pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
