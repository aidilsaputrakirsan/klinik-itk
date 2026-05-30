<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formulir Hasil Pemeriksaan Kesehatan</title>
    <style>
        @page {
            margin: 0.5cm; /* Small margins for custom paper size */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.1;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 11pt;
            font-weight: bold;
            margin: 0;
            color: #1e3a8a; /* A nice dark blue for ITK */
        }
        .header h2 {
            font-size: 10pt;
            font-weight: bold;
            margin: 2px 0 0 0;
            color: #2563eb;
        }
        .date-right {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        .row {
            margin-bottom: 6px;
            clear: both;
        }
        .label {
            display: inline-block;
            width: 85px;
            vertical-align: top;
        }
        .value {
            display: inline-block;
            border-bottom: 1px dotted #000;
            padding: 0 5px;
        }
        .flex-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .flex-cell {
            display: table-cell;
        }
        .checkbox-container {
            margin: 4px 0;
        }
        .cb {
            display: inline-block;
            font-size: 12pt;
            line-height: 10pt;
            vertical-align: middle;
            margin-right: 3px;
        }
        .cb-item {
            display: inline-block;
            margin-right: 10px;
            white-space: nowrap;
        }
        .circle {
            border: 1px solid #000;
            border-radius: 50%;
            padding: 1px 4px;
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
    <div class="header">
        <h1>FORMULIR HASIL PEMERIKSAAN KESEHATAN</h1>
        <h2>KLINIK ITK</h2>
    </div>

    <div class="date-right">
        {{ $rekamMedis->tanggal_kunjungan->format('d / m / Y') }}
    </div>

    <div class="row">
        <div class="label">Nama</div>
        : <span class="value" style="width: 215px; font-weight:bold;">{{ strtoupper($pasien->nama) }}</span>
    </div>

    <div class="row">
        <div class="label">Umur</div>
        : <span class="value" style="width: 50px; text-align:center;">{{ $age }}</span> Thn 
        ( <span style="font-weight:bold; text-decoration:underline;">{{ $jk }}</span> )
    </div>

    <div class="row">
        <div class="label">Peserta</div>
        : <span style="font-weight:bold;">{{ ucfirst($pasien->tipe_pasien) }}</span>
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">TB / BB</div>
        : <span class="value" style="width:40px; text-align:center;">{{ $tb ?: '...' }}</span> cm / 
          <span class="value" style="width:40px; text-align:center;">{{ $bb ?: '...' }}</span> kg
    </div>

    <div class="row">
        <div class="label">IMT</div>
        : <span class="value" style="width:40px; text-align:center;">{{ $imt ?: '...' }}</span> 
        @if($imt_status) <span style="font-weight:bold;">({{ $imt_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Lingkar Perut</div>
        : <span class="value" style="width:40px; text-align:center;">{{ $lp ?: '...' }}</span> cm 
        @if($lp) <span style="font-weight:bold;">({{ $lp_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Tekanan Darah</div>
        : <span class="value" style="width:60px; text-align:center;">{{ $td ?: '...' }}</span> mmHg 
        @if($td) <span style="font-weight:bold;">({{ $td_text }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Gula Darah</div>
        : <span class="value" style="width:60px; text-align:center;">{{ $gula ?: '...' }}</span> mg/dL 
        @if($gula) <span style="font-weight:bold;">({{ $gula_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Asam Urat</div>
        : <span class="value" style="width:60px; text-align:center;">{{ $au ?: '...' }}</span> mg/dL 
        @if($au) <span style="font-weight:bold;">({{ $au_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Kolestrol</div>
        : <span class="value" style="width:60px; text-align:center;">{{ $kol ?: '...' }}</span> mg/dL 
        @if($kol) <span style="font-weight:bold;">({{ $kol_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 10px;">
        <div class="label">Hemoglobin</div>
        : <span class="value" style="width:60px; text-align:center;">{{ $hb ?: '...' }}</span> g/dL 
        @if($hb) <span style="font-weight:bold;">({{ $hb_status }})</span> @endif
    </div>

    <div class="row" style="margin-top: 15px;">
        <div class="label">Tindak Lanjut</div>
        : <span style="font-weight:bold;">{{ $tindak_lanjut }}</span>
    </div>
</body>
</html>
