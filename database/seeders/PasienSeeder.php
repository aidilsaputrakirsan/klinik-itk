<?php

namespace Database\Seeders;

use App\Models\Pasien;
use Illuminate\Database\Seeder;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $pasiens = [
            [
                'nomor_rm' => 'RM202412010001',
                'nik' => '6472011234567890',
                'nama' => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Balikpapan',
                'tanggal_lahir' => '2002-05-15',
                'alamat' => 'Jl. Karang Joang No. 1, Balikpapan',
                'phone' => '081234567890',
                'email' => 'budi.santoso@student.itk.ac.id',
                'tipe_pasien' => 'mahasiswa',
                'nomor_identitas' => '11201001',
                'fakultas' => 'Fakultas Teknologi Industri',
                'prodi' => 'Teknik Informatika',
                'golongan_darah' => 'A',
            ],
            [
                'nomor_rm' => 'RM202412010002',
                'nik' => '6472012345678901',
                'nama' => 'Siti Rahayu',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Samarinda',
                'tanggal_lahir' => '2001-08-22',
                'alamat' => 'Jl. Sepinggan Baru No. 5, Balikpapan',
                'phone' => '081234567891',
                'email' => 'siti.rahayu@student.itk.ac.id',
                'tipe_pasien' => 'mahasiswa',
                'nomor_identitas' => '11201002',
                'fakultas' => 'Fakultas Sains dan Teknologi',
                'prodi' => 'Teknik Kimia',
                'golongan_darah' => 'B',
            ],
            [
                'nomor_rm' => 'RM202412010003',
                'nik' => '6472013456789012',
                'nama' => 'Dr. Ir. Hendra Wijaya, M.T.',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1975-03-10',
                'alamat' => 'Jl. MT Haryono No. 10, Balikpapan',
                'phone' => '081234567892',
                'email' => 'hendra.wijaya@itk.ac.id',
                'tipe_pasien' => 'dosen',
                'nomor_identitas' => '197503102000011001',
                'fakultas' => 'Fakultas Teknologi Industri',
                'prodi' => 'Teknik Mesin',
                'golongan_darah' => 'O',
            ],
            [
                'nomor_rm' => 'RM202412010004',
                'nik' => '6472014567890123',
                'nama' => 'Ani Lestari',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Balikpapan',
                'tanggal_lahir' => '1985-11-20',
                'alamat' => 'Jl. Soekarno Hatta No. 15, Balikpapan',
                'phone' => '081234567893',
                'email' => 'ani.lestari@itk.ac.id',
                'tipe_pasien' => 'tendik',
                'nomor_identitas' => '198511202010012001',
                'golongan_darah' => 'AB',
            ],
            [
                'nomor_rm' => 'RM202412010005',
                'nik' => '6472015678901234',
                'nama' => 'Ahmad Fauzi',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Makassar',
                'tanggal_lahir' => '2003-01-05',
                'alamat' => 'Jl. Mulawarman No. 20, Balikpapan',
                'phone' => '081234567894',
                'email' => 'ahmad.fauzi@student.itk.ac.id',
                'tipe_pasien' => 'mahasiswa',
                'nomor_identitas' => '11211003',
                'fakultas' => 'Fakultas Infrastruktur dan Kewilayahan',
                'prodi' => 'Teknik Sipil',
                'golongan_darah' => 'A',
                'riwayat_alergi' => 'Alergi debu',
            ],
        ];

        foreach ($pasiens as $pasien) {
            Pasien::create($pasien);
        }
    }
}
