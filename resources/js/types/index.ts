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
    nomor_rm: string;
    nik?: string;
    nama: string;
    jenis_kelamin: 'L' | 'P';
    tempat_lahir?: string;
    tanggal_lahir?: string;
    alamat?: string;
    phone?: string;
    email?: string;
    tipe_pasien: 'mahasiswa' | 'dosen' | 'tendik' | 'umum';
    nomor_identitas?: string;
    fakultas?: string;
    prodi?: string;
    golongan_darah?: string;
    riwayat_alergi?: string;
    riwayat_penyakit?: string;
    kontak_darurat_nama?: string;
    kontak_darurat_phone?: string;
    kontak_darurat_hubungan?: string;
    created_at: string;
    updated_at: string;
}

export interface Obat {
    id: number;
    kode: string;
    nama: string;
    satuan: string;
    jenis?: string;
    stok: number;
    stok_minimum?: number;
    harga: number;
    keterangan?: string;
    is_active: boolean;
}

export interface Tindakan {
    id: number;
    kode: string;
    nama: string;
    deskripsi?: string;
    biaya: number;
    is_active: boolean;
}

export interface RekamMedis {
    id: number;
    nomor_kunjungan: string;
    pasien_id: number;
    perawat_id?: number;
    dokter_id?: number;
    tanggal_kunjungan: string;
    jenis_layanan: 'berobat' | 'surat_sehat' | 'screening';
    keperluan_surat?: string;
    nama_event?: string;
    status: 'menunggu_perawat' | 'proses_anamnesis' | 'siap_dokter' | 'sedang_diperiksa' | 'selesai' | 'batal';
    catatan?: string;
    pasien?: Pasien;
    anamnesis?: Anamnesis;
    pemeriksaan?: Pemeriksaan;
}

export interface Anamnesis {
    id: number;
    rekam_medis_id: number;
    perawat_id: number;
    tekanan_darah?: string;
    suhu?: number;
    nadi?: number;
    respirasi?: number;
    tinggi_badan?: number;
    berat_badan?: number;
    keluhan_utama: string;
    riwayat_penyakit_sekarang?: string;
    riwayat_penyakit_dahulu?: string;
    riwayat_alergi?: string;
    riwayat_obat?: string;
    created_at?: string;
}

export interface Pemeriksaan {
    id: number;
    rekam_medis_id: number;
    dokter_id: number;
    pemeriksaan_fisik?: string;
    hasil_pemeriksaan?: string;
    diagnosis_utama: string;
    diagnosis_sekunder?: string;
    kode_icd10?: string;
    prognosis?: string;
    anjuran?: string;
    created_at?: string;
}

export interface SuratDokter {
    id: number;
    nomor_surat: string;
    rekam_medis_id: number;
    dokter_id: number;
    jenis_surat: 'surat_sehat' | 'surat_sakit';
    tanggal_surat: string;
    keperluan?: string;
    jumlah_hari_istirahat?: number;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    created_at?: string;
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
