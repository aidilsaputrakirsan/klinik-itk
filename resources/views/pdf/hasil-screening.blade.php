<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Hasil Pemeriksaan Kesehatan</title>
    <style>
        @page {
            margin: 0.5cm 1cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.2;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 15%;
            text-align: left;
            vertical-align: middle;
        }
        .header-text {
            width: 85%;
            text-align: center;
            vertical-align: middle;
            padding-right: 15%; /* To balance the logo width */
        }
        .header-text h1 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            color: #1e3a8a; /* Dark blue ITK */
        }
        .header-text h2 {
            font-size: 11pt;
            font-weight: bold;
            margin: 3px 0 0 0;
            color: #2563eb;
        }
        .date-right {
            text-align: right;
            font-size: 9.5pt;
            margin-bottom: 5px;
        }
        .value {
            display: inline-block;
            border-bottom: 1px dotted #000;
            padding: 0 5px;
        }
        /* Compute logics directly in PHP inside blade */
        @php
            // Calculate Age
            $birthDate = new DateTime($pasien->tanggal_lahir);
            $today = new DateTime($rekamMedis->tanggal_kunjungan);
            $age = $birthDate->diff($today)->y;

            $jk = $pasien->jenis_kelamin; // 'L' or 'P'

            // TB/BB & IMT
            $tb = $rekamMedis->anamnesis->tinggi_badan;
            $bb = $rekamMedis->anamnesis->berat_badan;
            $imt = 0;
            if ($tb && $bb) {
                $tb_m = $tb / 100;
                $imt = round($bb / ($tb_m * $tb_m), 1);
            }

            // Lingkar Perut
            $lp = $rekamMedis->anamnesis->lingkar_perut;
            $lp_obese = false;
            if ($lp) {
                if ($jk == 'L' && $lp > 90) $lp_obese = true;
                if ($jk == 'P' && $lp > 80) $lp_obese = true;
            }

            // Tekanan Darah
            $td = $rekamMedis->anamnesis->tekanan_darah;
            $td_status = 0; // 0=Normal, 1=Pre, 2=Grade1, 3=Grade2
            if ($td) {
                $parts = explode('/', $td);
                if (count($parts) == 2) {
                    $sistol = (int)$parts[0];
                    if ($sistol < 130) $td_status = 0;
                    elseif ($sistol <= 139) $td_status = 1;
                    elseif ($sistol <= 159) $td_status = 2;
                    else $td_status = 3;
                }
            }

            // Gula Darah
            $gula = $rekamMedis->anamnesis->gula_darah;
            $jenis_gula = $rekamMedis->anamnesis->jenis_gula_darah ?? 'sewaktu';
            $gula_hiper = false;
            if ($gula) {
                if ($jenis_gula == 'puasa' && $gula > 120) $gula_hiper = true;
                if ($jenis_gula == 'sewaktu' && $gula > 200) $gula_hiper = true;
            }

            // Asam Urat
            $au = $rekamMedis->anamnesis->asam_urat;
            $au_hiper = false;
            if ($au) {
                if ($jk == 'L' && $au > 7) $au_hiper = true;
                if ($jk == 'P' && $au > 6) $au_hiper = true;
            }

            // Kolesterol
            $kol = $rekamMedis->anamnesis->kolesterol;
            $kol_hiper = false;
            if ($kol && $kol > 200) $kol_hiper = true;

            // Hemoglobin
            $hb = $rekamMedis->anamnesis->hemoglobin;
            $hb_anemia = false;
            if ($hb && $hb < 12) $hb_anemia = true;
            
            // Calculate Status Text for display
            $imt_status = '';
            if ($imt > 0 && $imt < 18) $imt_status = 'Underweight (<18)';
            elseif ($imt >= 18 && $imt <= 22.9) $imt_status = 'Normal (18-22,9)';
            elseif ($imt >= 23 && $imt <= 24.9) $imt_status = 'Overweight (23-24,9)';
            elseif ($imt >= 25 && $imt <= 29.9) $imt_status = 'Obesitas Tingkat 1 (>25-29,9)';
            elseif ($imt >= 30) $imt_status = 'Obesitas Tingkat 2 (>30)';

            $lp_status = $lp_obese ? 'Obesitas Sentral (L: >90 cm & P: >80 cm)' : 'Normal';
            $td_text = ['Normal (<129/84)', 'Prehipertensi (130/85-139/89)', 'Hipertensi Grade 1 (140/90-159/99)', 'Hipertensi Grade 2 (>160/100)'][$td_status] ?? '...';
            $gula_status = $gula_hiper ? 'Hiperglikemia (GDS: >200 | GDP: >120)' : 'Normal';
            $au_status = $au_hiper ? 'Hiperuricemia (L: >7 | P: >6)' : 'Normal';
            $kol_status = $kol_hiper ? 'Hipercholesterolemia (>200)' : 'Normal';
            $hb_status = $hb_anemia ? 'Anemia (<12)' : 'Normal';

            // Tindak Lanjut
            $tindak_lanjut = $rekamMedis->anamnesis->tindak_lanjut ?: '...';
            if (in_array(strtolower($tindak_lanjut), ['faskes_1', 'rujuk'])) $tindak_lanjut = 'Kembali ke Faskes 1';
            elseif (strtolower($tindak_lanjut) == 'edukasi') $tindak_lanjut = 'Edukasi';

        @endphp
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('images/Lambang.png') }}" style="width: 60px; height: auto;">
            </td>
            <td class="header-text">
                <h1>FORMULIR HASIL PEMERIKSAAN KESEHATAN</h1>
                <h2>KLINIK ITK</h2>
            </td>
        </tr>
    </table>

    <div class="date-right">
        Balikpapan, {{ $rekamMedis->tanggal_kunjungan->format('d / m / Y') }}
    </div>

    <table width="100%" cellpadding="2" cellspacing="0" style="font-size: 9.5pt;">
        <tr>
            <td width="22%" style="vertical-align: top;">Nama</td>
            <td width="3%" style="vertical-align: top; text-align: center;">:</td>
            <td width="75%" style="vertical-align: top;">
                <span class="value" style="width: 300px; font-weight:bold; text-align: left;">{{ strtoupper($pasien->nama) }}</span>
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Umur</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width: 60px; text-align:center;">{{ $age }}</span> Thn 
                &nbsp;&nbsp;&nbsp;( <span style="font-weight:bold; text-decoration:underline;">{{ $jk }}</span> )
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Peserta</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top; font-weight:bold;">{{ ucfirst($pasien->tipe_pasien) }}</td>
        </tr>
        <tr>
            <td style="vertical-align: top;">TB / BB</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:60px; text-align:center;">{{ $tb ?: '...' }}</span> cm &nbsp;/&nbsp; 
                <span class="value" style="width:60px; text-align:center;">{{ $bb ?: '...' }}</span> kg
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">IMT</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:60px; text-align:center;">{{ $imt ?: '...' }}</span> 
                @if($imt_status) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $imt_status }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Lingkar Perut</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:60px; text-align:center;">{{ $lp ?: '...' }}</span> cm 
                @if($lp) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $lp_status }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Tekanan Darah</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:80px; text-align:center;">{{ $td ?: '...' }}</span> mmHg 
                @if($td) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $td_text }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Gula Darah</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:80px; text-align:center;">{{ $gula ?: '...' }}</span> mg/dL 
                @if($gula) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $gula_status }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Asam Urat</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:80px; text-align:center;">{{ $au ?: '...' }}</span> mg/dL 
                @if($au) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $au_status }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Kolestrol</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:80px; text-align:center;">{{ $kol ?: '...' }}</span> mg/dL 
                @if($kol) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $kol_status }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="vertical-align: top;">Hemoglobin</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top;">
                <span class="value" style="width:80px; text-align:center;">{{ $hb ?: '...' }}</span> g/dL 
                @if($hb) &nbsp;&nbsp;<span style="font-weight:bold;">({{ $hb_status }})</span> @endif
            </td>
        </tr>
        <tr><td colspan="3" style="height: 10px;"></td></tr> <!-- Spacer -->
        <tr>
            <td style="vertical-align: top;">Tindak Lanjut</td>
            <td style="vertical-align: top; text-align: center;">:</td>
            <td style="vertical-align: top; font-weight:bold;">{{ $tindak_lanjut }}</td>
        </tr>
    </table>
</body>
</html>
