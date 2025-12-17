import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'superadmin' | 'admin' | 'perawat' | 'dokter';
    nip?: string;
    phone?: string;
    specialization?: string;
    is_active: boolean;
    email_verified_at?: string;
}

export interface Pasien {
    id: number;
    no_rm: string;
    nik: string;
    nama: string;
    tanggal_lahir: string;
    jenis_kelamin: 'L' | 'P';
    alamat: string;
    phone?: string;
    email?: string;
    golongan_darah?: string;
    status_pasien: 'mahasiswa' | 'dosen' | 'staff' | 'umum';
    nim_nip?: string;
    fakultas?: string;
    program_studi?: string;
    created_at: string;
    updated_at: string;
}

export interface Obat {
    id: number;
    kode_obat: string;
    nama_obat: string;
    satuan: string;
    stok: number;
    harga: number;
    keterangan?: string;
    is_active: boolean;
}

export interface Tindakan {
    id: number;
    kode_tindakan: string;
    nama_tindakan: string;
    tarif: number;
    keterangan?: string;
    is_active: boolean;
}

export interface RekamMedis {
    id: number;
    no_kunjungan: string;
    pasien_id: number;
    tanggal_kunjungan: string;
    status: 'menunggu' | 'anamnesis' | 'pemeriksaan' | 'selesai';
    keluhan_utama?: string;
    admin_id?: number;
    pasien?: Pasien;
}

export interface Anamnesis {
    id: number;
    rekam_medis_id: number;
    perawat_id: number;
    tekanan_darah_sistolik?: number;
    tekanan_darah_diastolik?: number;
    suhu_tubuh?: number;
    nadi?: number;
    pernapasan?: number;
    tinggi_badan?: number;
    berat_badan?: number;
    riwayat_penyakit?: string;
    riwayat_alergi?: string;
    catatan?: string;
    waktu_anamnesis: string;
}

export interface Pemeriksaan {
    id: number;
    rekam_medis_id: number;
    dokter_id: number;
    diagnosis: string;
    diagnosis_sekunder?: string;
    icd_10?: string;
    catatan_pemeriksaan?: string;
    anjuran?: string;
    waktu_pemeriksaan: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T &
    InertiaPageProps & {
        auth: {
            user: User;
        };
        ziggy: Config & { location: string };
        csrf_token: string;
        flash?: {
            success?: string;
            error?: string;
        };
    };
