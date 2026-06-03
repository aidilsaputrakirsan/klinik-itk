<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Screening Klinik ITK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .footer {
            font-size: 0.9em;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Klinik Institut Teknologi Kalimantan</h2>
    </div>
    
    <div class="content">
        <p>Yth. <strong>{{ $pasien->nama }}</strong>,</p>
        
        <p>Semoga Anda dalam keadaan sehat.</p>
        
        <p>Bersama email ini, kami lampirkan dokumen hasil pemeriksaan <strong>Screening</strong> Anda pada tanggal <strong>{{ \Carbon\Carbon::parse($rekamMedis->tanggal_kunjungan)->translatedFormat('d F Y') }}</strong> di Klinik ITK.</p>
        
        <p>Silakan unduh lampiran berformat PDF pada email ini untuk melihat rincian hasil screening Anda.</p>
        
        <p>Jika ada pertanyaan atau keluhan kesehatan lebih lanjut, jangan ragu untuk kembali berkonsultasi dengan kami.</p>
        
        <p>Terima kasih,<br>
        <strong>Tim Klinik ITK</strong></p>
    </div>
    
    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh Sistem Informasi Klinik ITK. Mohon untuk tidak membalas email ini.</p>
    </div>
</body>
</html>
