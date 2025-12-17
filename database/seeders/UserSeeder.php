<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'superadmin',
                'nip' => '198001012000011001',
                'phone' => '08123456789',
                'is_active' => true,
            ],
            [
                'name' => 'Admin Klinik',
                'email' => 'admin@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nip' => '198501012010011001',
                'phone' => '08123456790',
                'is_active' => true,
            ],
            [
                'name' => 'Ns. Siti Aminah, S.Kep',
                'email' => 'perawat@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'perawat',
                'nip' => '199001012015012001',
                'phone' => '08123456791',
                'is_active' => true,
            ],
            [
                'name' => 'Ns. Dewi Kartika, S.Kep',
                'email' => 'perawat2@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'perawat',
                'nip' => '199101012016012001',
                'phone' => '08123456792',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Ahmad Rizki, Sp.PD',
                'email' => 'dokter@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'nip' => '198201012008011001',
                'phone' => '08123456793',
                'specialization' => 'Spesialis Penyakit Dalam',
                'is_active' => true,
            ],
            [
                'name' => 'dr. Ratna Dewi',
                'email' => 'dokter2@itk.ac.id',
                'password' => Hash::make('password'),
                'role' => 'dokter',
                'nip' => '198501012010012001',
                'phone' => '08123456794',
                'specialization' => 'Dokter Umum',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
