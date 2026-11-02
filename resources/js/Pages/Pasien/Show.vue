<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien, RekamMedis } from '@/types';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Divider from 'primevue/divider';
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

interface ResepObatItem {
    id: number;
    nama_obat: string;
    jumlah: number;
    satuan: string;
    dosis: string | null;
    aturan_pakai: string | null;
    keterangan: string | null;
    obat?: {
        nama: string;
        satuan: string;
    };
}

interface TindakanItem {
    id: number;
    nama: string;
    pivot: {
        jumlah: number;
        biaya: number;
        keterangan: string | null;
    };
}

interface PemeriksaanData {
    id: number;
    pemeriksaan_fisik: string | null;
    hasil_pemeriksaan: string | null;
    diagnosis_utama: string;
    diagnosis_sekunder: string | null;
    kode_icd10: string | null;
    prognosis: string | null;
    anjuran: string | null;
    dokter?: {
        name: string;
    };
    resep_obats?: ResepObatItem[];
    tindakans?: TindakanItem[];
}

interface AnamnesisData {
    tekanan_darah: string | null;
    suhu: number | null;
    nadi: number | null;
    respirasi: number | null;
    tinggi_badan: number | null;
    berat_badan: number | null;
    keluhan_utama: string;
    riwayat_penyakit_sekarang: string | null;
    riwayat_penyakit_dahulu: string | null;
    riwayat_alergi: string | null;
    riwayat_pengobatan?: string | null;
    lingkar_perut?: number | null;
    is_hamil?: boolean;
    is_menyusui?: boolean;
    tindak_lanjut?: string | null;
    keterangan_tindak_lanjut?: string | null;
    buta_warna?: string | null;
    gula_darah?: number | null;
    jenis_gula_darah?: 'puasa' | 'sewaktu' | null;
    asam_urat?: number | null;
    kolesterol?: number | null;
    hemoglobin?: number | null;
    skala_nyeri?: number | null;
    diagnosa_keperawatan?: string | null;
    intervensi_keperawatan?: string | null;
    implementasi_keperawatan?: string | null;
    evaluasi_keperawatan?: string | null;
    perawat?: {
        name: string;
    };
}

interface SuratDokterData {
    id: number;
    nomor_surat: string;
    jenis_surat: 'surat_sehat' | 'surat_sakit' | 'surat_rujukan' | 'surat_berobat';
    tanggal_surat: string;
    keperluan: string | null;
    keterangan: string | null;
    jumlah_hari_istirahat: number | null;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    dokter?: {
        name: string;
    };
}

interface RekamMedisWithDetails extends Omit<RekamMedis, 'anamnesis' | 'pemeriksaan'> {
    perawat?: {
        name: string;
    };
    anamnesis?: AnamnesisData;
    pemeriksaan?: PemeriksaanData;
    surat_dokter?: SuratDokterData;
}

interface Props {
    pasien: Pasien & {
        rekam_medis?: RekamMedisWithDetails[];
    };
}

const props = defineProps<Props>();

const confirm = useConfirm();
const toast = useToast();

const showDetailDialog = ref(false);
const selectedRekamMedis = ref<RekamMedisWithDetails | null>(null);

const daftarKunjunganBaru = () => {
    confirm.require({
        message: `Daftarkan kunjungan baru untuk pasien "${props.pasien.nama}"?`,
        header: 'Konfirmasi Kunjungan Baru',
        icon: 'pi pi-calendar-plus',
        acceptLabel: 'Ya, Daftarkan',
        rejectLabel: 'Batal',
        accept: () => {
            const now = new Date();
            const pad = (n: number) => String(n).padStart(2, '0');
            const clientTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;

            router.post(route('pasien.kunjungan', props.pasien.id), { client_time: clientTime }, {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kunjungan baru berhasil didaftarkan', life: 3000 });
                }
            });
        }
    });
};

const openDetailDialog = (rekamMedis: RekamMedisWithDetails) => {
    selectedRekamMedis.value = rekamMedis;
    showDetailDialog.value = true;
};

const closeDetailDialog = () => {
    showDetailDialog.value = false;
    selectedRekamMedis.value = null;
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = {
        mahasiswa: 'info',
        dosen: 'success',
        tendik: 'warn',
        umum: 'secondary'
    };
    return severities[status] || 'secondary';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        mahasiswa: 'Mahasiswa',
        dosen: 'Dosen',
        tendik: 'Tendik',
        umum: 'Umum'
    };
    return labels[status] || status;
};

const getKunjunganStatusSeverity = (status: string) => {
    const severities: Record<string, string> = {
        menunggu_perawat: 'warn',
        proses_anamnesis: 'info',
        siap_dokter: 'info',
        sedang_diperiksa: 'info',
        selesai: 'success',
        batal: 'danger'
    };
    return severities[status] || 'secondary';
};

const getKunjunganStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        menunggu_perawat: 'Menunggu Perawat',
        proses_anamnesis: 'Proses Anamnesis',
        siap_dokter: 'Siap Dokter',
        sedang_diperiksa: 'Sedang Diperiksa',
        selesai: 'Selesai',
        batal: 'Batal'
    };
    return labels[status] || status?.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') || status;
};

const getJenisLayananLabel = (layanan: string) => {
    const labels: Record<string, string> = {
        berobat: 'Pemeriksaan Umum',
        surat_sehat: 'Surat Sehat',
        screening: 'Screening',
    };
    return labels[layanan] || (layanan ? layanan.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ') : 'Pemeriksaan Umum');
};

const formatDate = (date: string) => {
    if (!date) return '-';
    const d = new Date(date);
    const day = d.getDate().toString().padStart(2, '0');
    const month = d.toLocaleString('id-ID', { month: 'short' });
    const year = d.getFullYear();
    const hours = d.getHours().toString().padStart(2, '0');
    const minutes = d.getMinutes().toString().padStart(2, '0');
    
    if (hours === '00' && minutes === '00') {
        return `${day} ${month} ${year}`;
    }
    
    return `${day} ${month} ${year}, ${hours}:${minutes}`;
};

const getAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

const getJenisSuratLabel = (jenis: string) => {
    const labels: Record<string, string> = {
        surat_sehat: 'Surat Keterangan Sehat',
        surat_sakit: 'Surat Keterangan Sakit',
        surat_rujukan: 'Surat Rujukan Puskesmas',
        surat_berobat: 'Surat Keterangan Berobat',
    };
    return labels[jenis] || jenis;
};

// Screening Calculation & Label Helpers
const getBmiData = (tb: number | null | undefined, bb: number | null | undefined) => {
    if (!tb || !bb) return { value: '-', category: '-', isCritical: false };
    const h = Number(tb) / 100;
    const w = Number(bb);
    if (h <= 0 || w <= 0) return { value: '-', category: '-', isCritical: false };
    const bmi = w / (h * h);
    let category = '';
    let isCritical = false;
    
    if (bmi < 18) category = 'Underweight (<18)';
    else if (bmi <= 22.9) category = 'Normal (18-22.9)';
    else if (bmi <= 24.9) { category = 'Overweight (23-24.9)'; isCritical = true; }
    else if (bmi <= 29.9) { category = 'Obesitas Tingkat 1 (>25-29.9)'; isCritical = true; }
    else { category = 'Obesitas Tingkat 2 (>30)'; isCritical = true; }
    
    return { value: bmi.toFixed(2), category, isCritical };
};

const getLpData = (lp: number | null | undefined, isHamil: boolean | undefined, gender: string | undefined) => {
    if (isHamil) return { value: lp || '-', status: 'Hamil', isCritical: false };
    if (!lp) return { value: '-', status: '-', isCritical: false };
    
    const val = Number(lp);
    let isCritical = false;
    if (gender === 'L' && val > 90) isCritical = true;
    if (gender === 'P' && val > 80) isCritical = true;
    
    return { 
        value: val, 
        status: isCritical ? 'Obesitas Sentral' : 'Normal', 
        isCritical 
    };
};

const getTdData = (td: string | null | undefined) => {
    if (!td) return { value: '-', category: '-', isCritical: false };
    const parts = td.split('/');
    if (parts.length !== 2) return { value: td, category: '-', isCritical: false };
    
    const sys = parseInt(parts[0]);
    const dia = parseInt(parts[1]);
    if (isNaN(sys) || isNaN(dia)) return { value: td, category: '-', isCritical: false };
    
    let category = '';
    let isCritical = false;
    
    if (sys < 130 && dia < 85) {
        category = 'Normal (<129/84)';
    } else if (sys <= 139 || dia <= 89) {
        category = 'Prehipertensi (130/85-139/89)';
        isCritical = true;
    } else if (sys <= 159 || dia <= 99) {
        category = 'Hipertensi Grade 1 (140/90-159/99)';
        isCritical = true;
    } else {
        category = 'Hipertensi Grade 2 (>160/100)';
        isCritical = true;
    }
    
    return { value: td, category, isCritical };
};

const getGdData = (gd: number | null | undefined, jenis: string | null | undefined) => {
    if (!gd && gd !== 0) return { value: '-', category: '-', isCritical: false };
    const val = Number(gd);
    let category = 'Normal';
    let isCritical = false;
    if (jenis === 'puasa') {
        if (val > 120) { isCritical = true; category = 'Hiperglikemia (GDP: >120)'; }
    } else {
        if (val > 200) { isCritical = true; category = 'Hiperglikemia (GDS: >200)'; }
    }
    return { value: val, category, isCritical };
};

const getAuData = (au: number | null | undefined, gender: string | undefined) => {
    if (!au && au !== 0) return { value: '-', category: '-', isCritical: false };
    const val = Number(au);
    let isCritical = false;
    let label = 'Normal';
    if (gender === 'L' && val > 7) { isCritical = true; label = 'Hiperuricemia (L: >7)'; }
    if (gender === 'P' && val > 6) { isCritical = true; label = 'Hiperuricemia (P: >6)'; }
    return { value: val, category: label, isCritical };
};

const getCholData = (chol: number | null | undefined) => {
    if (!chol && chol !== 0) return { value: '-', category: '-', isCritical: false };
    const val = Number(chol);
    const isCritical = val > 200;
    return { value: val, category: isCritical ? 'Hipercholesterolemia (>200)' : 'Normal', isCritical };
};

const getHbData = (hb: number | null | undefined) => {
    if (!hb && hb !== 0) return { value: '-', category: '-', isCritical: false };
    const val = Number(hb);
    const isCritical = val < 12;
    return { value: val, category: isCritical ? 'Anemia (< 12)' : 'Normal', isCritical };
};

const getTindakLanjutLabel = (val: string | null | undefined) => {
    if (!val) return 'Belum Ditentukan';
    const lower = val.toLowerCase();
    if (lower === 'faskes_1' || lower === 'rujuk' || lower === 'rujuk_faskes_1') return 'Rujuk Faskes 1';
    if (lower === 'rawat_jalan') return 'Rawat Jalan';
    if (lower === 'edukasi') return 'Edukasi';
    return val.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getTindakLanjutSeverity = (val: string | null | undefined) => {
    if (!val) return 'secondary';
    const lower = val.toLowerCase();
    if (lower === 'faskes_1' || lower === 'rujuk' || lower === 'rujuk_faskes_1') return 'danger';
    if (lower === 'rawat_jalan') return 'info';
    if (lower === 'edukasi') return 'success';
    return 'secondary';
};

const printDetail = () => {
    if (!selectedRekamMedis.value) return;

    const rm = selectedRekamMedis.value;
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Detail Kunjungan - ${rm.nomor_kunjungan}</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 20px; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; }
                .section { margin-bottom: 15px; }
                .section-title { font-weight: bold; background: #f0f0f0; padding: 5px; margin-bottom: 10px; }
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .row { display: flex; margin-bottom: 5px; }
                .label { color: #666; min-width: 140px; }
                .value { font-weight: 500; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f5f5f5; }
                .patient-info { background: #f9f9f9; padding: 10px; margin-bottom: 15px; }
                .surat-info { background: #fff3cd; padding: 10px; border: 1px solid #ffc107; margin-top: 10px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
                <p>Detail Rekam Medis Kunjungan</p>
            </div>

            <div class="patient-info">
                <div class="grid">
                    <div>
                        <div class="row"><span class="label">Nama Pasien:</span><span class="value">${props.pasien.nama}</span></div>
                        <div class="row"><span class="label">No. RM:</span><span class="value">${props.pasien.nomor_rm}</span></div>
                        <div class="row"><span class="label">NIK:</span><span class="value">${props.pasien.nik || '-'}</span></div>
                    </div>
                    <div>
                        <div class="row"><span class="label">No. Kunjungan:</span><span class="value">${rm.nomor_kunjungan}</span></div>
                        <div class="row"><span class="label">Tanggal:</span><span class="value">${formatDate(rm.tanggal_kunjungan)}</span></div>
                        <div class="row"><span class="label">Jenis Layanan:</span><span class="value">${getJenisLayananLabel(rm.jenis_layanan || 'berobat')}</span></div>
                        <div class="row"><span class="label">Status:</span><span class="value">${getKunjunganStatusLabel(rm.status)}</span></div>
                    </div>
                </div>
            </div>

            ${rm.anamnesis && rm.jenis_layanan === 'screening' ? `
            <div class="section">
                <div class="section-title">HASIL PEMERIKSAAN SCREENING KESEHATAN</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 35%;">Parameter Pemeriksaan</th>
                            <th style="width: 25%;">Hasil Nilai</th>
                            <th style="width: 40%;">Kategori / Keterangan Normal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Tinggi Badan / Berat Badan</td>
                            <td>${rm.anamnesis.tinggi_badan || '-'} cm / ${rm.anamnesis.berat_badan || '-'} kg</td>
                            <td>-</td>
                        </tr>
                        <tr>
                            <td>Indeks Massa Tubuh (IMT)</td>
                            <td><strong>${getBmiData(rm.anamnesis.tinggi_badan, rm.anamnesis.berat_badan).value}</strong></td>
                            <td>${getBmiData(rm.anamnesis.tinggi_badan, rm.anamnesis.berat_badan).category}</td>
                        </tr>
                        <tr>
                            <td>Lingkar Perut</td>
                            <td><strong>${rm.anamnesis.lingkar_perut ? rm.anamnesis.lingkar_perut + ' cm' : '-'}</strong></td>
                            <td>${getLpData(rm.anamnesis.lingkar_perut, rm.anamnesis.is_hamil, props.pasien.jenis_kelamin).status}</td>
                        </tr>
                        <tr>
                            <td>Tekanan Darah</td>
                            <td><strong>${rm.anamnesis.tekanan_darah || '-'} mmHg</strong></td>
                            <td>${getTdData(rm.anamnesis.tekanan_darah).category}</td>
                        </tr>
                        <tr>
                            <td>Gula Darah (${rm.anamnesis.jenis_gula_darah === 'puasa' ? 'GDP' : 'GDS'})</td>
                            <td><strong>${rm.anamnesis.gula_darah ? rm.anamnesis.gula_darah + ' mg/dL' : '-'}</strong></td>
                            <td>${getGdData(rm.anamnesis.gula_darah, rm.anamnesis.jenis_gula_darah).category}</td>
                        </tr>
                        <tr>
                            <td>Asam Urat</td>
                            <td><strong>${rm.anamnesis.asam_urat ? rm.anamnesis.asam_urat + ' mg/dL' : '-'}</strong></td>
                            <td>${getAuData(rm.anamnesis.asam_urat, props.pasien.jenis_kelamin).category}</td>
                        </tr>
                        <tr>
                            <td>Kolesterol</td>
                            <td><strong>${rm.anamnesis.kolesterol ? rm.anamnesis.kolesterol + ' mg/dL' : '-'}</strong></td>
                            <td>${getCholData(rm.anamnesis.kolesterol).category}</td>
                        </tr>
                        <tr>
                            <td>Hemoglobin</td>
                            <td><strong>${rm.anamnesis.hemoglobin ? rm.anamnesis.hemoglobin + ' g/dL' : '-'}</strong></td>
                            <td>${getHbData(rm.anamnesis.hemoglobin).category}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;">
                    <div class="row"><span class="label">Tindak Lanjut:</span><span class="value" style="font-weight: bold;">${getTindakLanjutLabel(rm.anamnesis.tindak_lanjut)}</span></div>
                    ${rm.anamnesis.keterangan_tindak_lanjut ? `<div class="row"><span class="label">Keterangan:</span><span class="value">${rm.anamnesis.keterangan_tindak_lanjut}</span></div>` : ''}
                    ${rm.anamnesis.perawat?.name || rm.perawat?.name ? `<div class="row"><span class="label">Petugas Pemeriksa:</span><span class="value">${rm.anamnesis.perawat?.name || rm.perawat?.name}</span></div>` : ''}
                </div>
            </div>
            ` : (rm.anamnesis ? `
            <div class="section">
                <div class="section-title">HASIL ANAMNESIS</div>
                <div class="grid">
                    <div>
                        <div class="row"><span class="label">Tekanan Darah:</span><span class="value">${rm.anamnesis.tekanan_darah || '-'} mmHg</span></div>
                        <div class="row"><span class="label">Suhu:</span><span class="value">${rm.anamnesis.suhu || '-'} °C</span></div>
                        <div class="row"><span class="label">Nadi:</span><span class="value">${rm.anamnesis.nadi || '-'} x/menit</span></div>
                        <div class="row"><span class="label">Respirasi:</span><span class="value">${rm.anamnesis.respirasi || '-'} x/menit</span></div>
                        <div class="row"><span class="label">Berat Badan:</span><span class="value">${rm.anamnesis.berat_badan || '-'} kg</span></div>
                        <div class="row"><span class="label">Tinggi Badan:</span><span class="value">${rm.anamnesis.tinggi_badan || '-'} cm</span></div>
                    </div>
                    <div>
                        <div class="row"><span class="label">Keluhan Utama:</span><span class="value">${rm.anamnesis.keluhan_utama}</span></div>
                        ${rm.anamnesis.riwayat_alergi ? `<div class="row"><span class="label">Riwayat Alergi:</span><span class="value">${rm.anamnesis.riwayat_alergi}</span></div>` : ''}
                        ${rm.anamnesis.riwayat_penyakit_dahulu ? `<div class="row"><span class="label">Riwayat Penyakit:</span><span class="value">${rm.anamnesis.riwayat_penyakit_dahulu}</span></div>` : ''}
                    </div>
                </div>
            </div>
            ` : '')}

            ${rm.pemeriksaan ? `
            <div class="section">
                <div class="section-title">HASIL PEMERIKSAAN DOKTER</div>
                <div class="row"><span class="label">Diagnosis Utama:</span><span class="value">${rm.pemeriksaan.diagnosis_utama}</span></div>
                ${rm.pemeriksaan.kode_icd10 ? `<div class="row"><span class="label">Kode ICD-10:</span><span class="value">${rm.pemeriksaan.kode_icd10}</span></div>` : ''}
                ${rm.pemeriksaan.diagnosis_sekunder ? `<div class="row"><span class="label">Diagnosis Sekunder:</span><span class="value">${rm.pemeriksaan.diagnosis_sekunder}</span></div>` : ''}
                ${rm.pemeriksaan.pemeriksaan_fisik ? `<div class="row"><span class="label">Pemeriksaan Fisik:</span><span class="value">${rm.pemeriksaan.pemeriksaan_fisik}</span></div>` : ''}
                ${rm.pemeriksaan.prognosis ? `<div class="row"><span class="label">Prognosis:</span><span class="value">${rm.pemeriksaan.prognosis}</span></div>` : ''}
                ${rm.pemeriksaan.anjuran ? `<div class="row"><span class="label">Anjuran:</span><span class="value">${rm.pemeriksaan.anjuran}</span></div>` : ''}
            </div>
            ` : ''}

            ${rm.pemeriksaan?.tindakans && rm.pemeriksaan.tindakans.length > 0 ? `
            <div class="section">
                <div class="section-title">TINDAKAN</div>
                <table>
                    <thead><tr><th>Nama Tindakan</th><th>Biaya</th></tr></thead>
                    <tbody>
                        ${rm.pemeriksaan.tindakans.map(t => `<tr><td>${t.nama}</td><td>${formatCurrency(t.pivot.biaya)}</td></tr>`).join('')}
                    </tbody>
                </table>
            </div>
            ` : ''}

            ${rm.pemeriksaan?.resep_obats && rm.pemeriksaan.resep_obats.length > 0 ? `
            <div class="section">
                <div class="section-title">RESEP OBAT</div>
                <table>
                    <thead><tr><th>Nama Obat</th><th>Jumlah</th><th>Dosis</th><th>Aturan Pakai</th><th>Keterangan</th></tr></thead>
                    <tbody>
                        ${rm.pemeriksaan.resep_obats.map(r => `
                            <tr>
                                <td>${r.obat?.nama || r.nama_obat}</td>
                                <td>${r.jumlah} ${r.obat?.satuan || r.satuan}</td>
                                <td>${r.dosis || '-'}</td>
                                <td>${r.aturan_pakai || '-'}</td>
                                <td>${r.keterangan || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            ` : ''}

            ${rm.surat_dokter ? `
            <div class="section">
                <div class="section-title">SURAT KETERANGAN DOKTER</div>
                <div class="surat-info">
                    <div class="row"><span class="label">Nomor Surat:</span><span class="value">${rm.surat_dokter.nomor_surat}</span></div>
                    <div class="row"><span class="label">Jenis Surat:</span><span class="value">${getJenisSuratLabel(rm.surat_dokter.jenis_surat)}</span></div>
                    <div class="row"><span class="label">Tanggal Surat:</span><span class="value">${formatDate(rm.surat_dokter.tanggal_surat)}</span></div>
                    ${rm.surat_dokter.keperluan ? `<div class="row"><span class="label">Keperluan:</span><span class="value">${rm.surat_dokter.keperluan}</span></div>` : ''}
                    ${rm.surat_dokter.jenis_surat === 'surat_sakit' ? `
                        <div class="row"><span class="label">Hari Istirahat:</span><span class="value">${rm.surat_dokter.jumlah_hari_istirahat || 0} hari</span></div>
                        ${rm.surat_dokter.tanggal_mulai ? `<div class="row"><span class="label">Periode:</span><span class="value">${formatDate(rm.surat_dokter.tanggal_mulai)} s/d ${rm.surat_dokter.tanggal_selesai ? formatDate(rm.surat_dokter.tanggal_selesai) : '-'}</span></div>` : ''}
                    ` : ''}
                    ${rm.surat_dokter.jenis_surat === 'surat_berobat' && rm.surat_dokter.keterangan ? `
                        <div class="row"><span class="label">Waktu Berobat:</span><span class="value">Pukul ${rm.surat_dokter.keterangan}</span></div>
                    ` : ''}
                </div>
            </div>
            ` : ''}

            <div style="margin-top: 30px; text-align: right;">
                <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
    }, 250);
};
const printRegistrasi = () => {
    window.open(route('pasien.draf.pdf', props.pasien.id), '_blank');
};
</script>

<template>
    <Head :title="`Detail Pasien - ${pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('pasien.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Detail Pasien</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info Pasien -->
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-user text-2xl text-emerald-600"></i>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ pasien.nama }}</h2>
                                <p class="text-sm text-gray-500">{{ pasien.nomor_rm }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Tag :value="getStatusLabel(pasien.tipe_pasien)" :severity="getStatusSeverity(pasien.tipe_pasien)" />
                            <Button
                                label="Cetak Registrasi"
                                icon="pi pi-print"
                                severity="info"
                                size="small"
                                @click="printRegistrasi"
                            />
                            <Button
                                label="Daftar Kunjungan Baru"
                                icon="pi pi-calendar-plus"
                                severity="success"
                                size="small"
                                @click="daftarKunjunganBaru"
                            />
                            <Link :href="route('pasien.edit', pasien.id)">
                                <Button label="Edit" icon="pi pi-pencil" severity="warn" size="small" />
                            </Link>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-700 border-b pb-2">Data Pribadi</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">NIK</span>
                                    <span class="font-medium">{{ pasien.nik || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Jenis Kelamin</span>
                                    <span class="font-medium">{{ pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Tanggal Lahir</span>
                                    <span class="font-medium">{{ pasien.tanggal_lahir ? formatDate(pasien.tanggal_lahir) : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Umur</span>
                                    <span class="font-medium">{{ pasien.tanggal_lahir ? getAge(pasien.tanggal_lahir) + ' tahun' : '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Golongan Darah</span>
                                    <span class="font-medium">{{ pasien.golongan_darah || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Terdaftar Pada</span>
                                    <span class="font-medium">{{ pasien.created_at ? formatDate(pasien.created_at) : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-700 border-b pb-2">Kontak</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Telepon</span>
                                    <span class="font-medium">{{ pasien.phone || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Email</span>
                                    <span class="font-medium">{{ pasien.email || '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Alamat</span>
                                    <p class="font-medium mt-1">{{ pasien.alamat || '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-700 border-b pb-2">Info Akademik</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">NIM/NIP</span>
                                    <span class="font-medium">{{ pasien.nomor_identitas || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">{{ pasien.tipe_pasien === 'tendik' ? 'Unit Kerja' : 'Fakultas' }}</span>
                                    <span class="font-medium">{{ pasien.fakultas || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Program Studi</span>
                                    <span class="font-medium">{{ pasien.prodi || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Riwayat Kunjungan -->
            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200 mt-6">
                <template #content>
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                            Riwayat Kunjungan Pasien
                        </h3>
                    </div>
                    <DataTable
                        :value="pasien.rekam_medis || []"
                        :paginator="(pasien.rekam_medis?.length || 0) > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        stripedRows
                        emptyMessage="Belum ada riwayat kunjungan"
                    >
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">
                                    {{ data.nomor_kunjungan }}
                                </span>
                            </template>
                        </Column>
                        <Column field="tanggal_kunjungan" header="Tanggal" style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ new Date(data.tanggal_kunjungan || data.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <div class="flex items-center gap-1 text-emerald-600 text-[10px]">
                                        <i class="pi pi-clock"></i>
                                        <span>{{ new Date(data.created_at || data.tanggal_kunjungan).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WITA</span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column field="jenis_layanan" header="Jenis Layanan" style="width: 120px">
                            <template #body="{ data }">
                                <Tag :value="getJenisLayananLabel(data.jenis_layanan || 'berobat')" severity="secondary" class="!text-[10px] !px-2 font-medium" />
                            </template>
                        </Column>
                        <Column field="status" header="Status" style="width: 150px">
                            <template #body="{ data }">
                                <Tag :value="getKunjunganStatusLabel(data.status)" :severity="getKunjunganStatusSeverity(data.status)" class="!text-[10px] !px-2" />
                            </template>
                        </Column>
                        <Column header="Diagnosis">
                            <template #body="{ data }">
                                <span v-if="data.pemeriksaan" class="font-medium">{{ data.pemeriksaan.diagnosis_utama }}</span>
                                <span v-else class="text-gray-400 italic">Belum ada diagnosis</span>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 120px" class="text-center">
                            <template #body="{ data }">
                                <div class="flex justify-center">
                                    <Button
                                        v-if="data.status === 'selesai'"
                                        label="Detail"
                                        icon="pi pi-eye"
                                        severity="info"
                                        class="!rounded-xl !text-[11px] !py-1.5 !px-3 shadow-sm hover:shadow-md transition-all font-bold"
                                        @click="openDetailDialog(data)"
                                    />
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <!-- Dialog Detail Pemeriksaan -->
        <Dialog
            v-model:visible="showDetailDialog"
            modal
            header="Detail Kunjungan"
            :style="{ width: '900px' }"
            :closable="true"
            @hide="closeDetailDialog"
        >
            <div v-if="selectedRekamMedis" class="space-y-6">
                <!-- Header Info Kunjungan -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 text-xs">No. Kunjungan</span>
                            <p class="font-semibold text-gray-900">{{ selectedRekamMedis.nomor_kunjungan }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Tanggal</span>
                            <p class="font-semibold text-gray-900">{{ formatDate(selectedRekamMedis.tanggal_kunjungan) }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Jenis Layanan</span>
                            <p class="mt-0.5">
                                <Tag :value="getJenisLayananLabel(selectedRekamMedis.jenis_layanan || 'berobat')" :severity="selectedRekamMedis.jenis_layanan === 'screening' ? 'info' : 'secondary'" class="!text-[11px] !px-2.5 font-semibold" />
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-xs">Status</span>
                            <p class="mt-0.5">
                                <Tag :value="getKunjunganStatusLabel(selectedRekamMedis.status)" :severity="getKunjunganStatusSeverity(selectedRekamMedis.status)" class="!text-[11px] !px-2.5" />
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tampilan Khusus: Hasil Pemeriksaan Screening Lengkap -->
                <div v-if="selectedRekamMedis.jenis_layanan === 'screening' && selectedRekamMedis.anamnesis" class="space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h4 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-2 h-5 bg-emerald-500 rounded-full"></span>
                            Hasil Pemeriksaan Screening
                        </h4>
                        <div class="flex items-center gap-2">
                            <span v-if="selectedRekamMedis.anamnesis.perawat?.name || selectedRekamMedis.perawat?.name" class="text-xs text-gray-500">
                                Pemeriksa: <strong class="text-gray-700">{{ selectedRekamMedis.anamnesis.perawat?.name || selectedRekamMedis.perawat?.name }}</strong>
                            </span>
                            <a :href="route('perawat.anamnesis.pdf', selectedRekamMedis.id)" target="_blank">
                                <Button label="Download PDF Formulir" icon="pi pi-file-pdf" severity="success" size="small" class="!rounded-xl !text-xs !py-1 !px-2.5 shadow-sm" />
                            </a>
                        </div>
                    </div>

                    <!-- 1. Antropometri & Indeks Massa Tubuh -->
                    <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-800">
                            <i class="pi pi-user text-emerald-600"></i>
                            <span>Antropometri & Indeks Massa Tubuh</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div class="bg-white p-3 rounded-xl border border-emerald-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Tinggi Badan</span>
                                <p class="font-bold text-gray-900 text-base">
                                    {{ selectedRekamMedis.anamnesis.tinggi_badan || '-' }} <span class="text-xs font-normal text-gray-500">cm</span>
                                </p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-emerald-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Berat Badan</span>
                                <p class="font-bold text-gray-900 text-base">
                                    {{ selectedRekamMedis.anamnesis.berat_badan || '-' }} <span class="text-xs font-normal text-gray-500">kg</span>
                                </p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-emerald-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Indeks Massa Tubuh (IMT)</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getBmiData(selectedRekamMedis.anamnesis.tinggi_badan, selectedRekamMedis.anamnesis.berat_badan).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ getBmiData(selectedRekamMedis.anamnesis.tinggi_badan, selectedRekamMedis.anamnesis.berat_badan).value }}
                                    </p>
                                    <Tag
                                        v-if="getBmiData(selectedRekamMedis.anamnesis.tinggi_badan, selectedRekamMedis.anamnesis.berat_badan).category !== '-'"
                                        :value="getBmiData(selectedRekamMedis.anamnesis.tinggi_badan, selectedRekamMedis.anamnesis.berat_badan).category"
                                        :severity="getBmiData(selectedRekamMedis.anamnesis.tinggi_badan, selectedRekamMedis.anamnesis.berat_badan).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-emerald-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Lingkar Perut</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getLpData(selectedRekamMedis.anamnesis.lingkar_perut, selectedRekamMedis.anamnesis.is_hamil, pasien.jenis_kelamin).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.lingkar_perut || '-' }} <span class="text-xs font-normal text-gray-500">cm</span>
                                    </p>
                                    <Tag
                                        v-if="getLpData(selectedRekamMedis.anamnesis.lingkar_perut, selectedRekamMedis.anamnesis.is_hamil, pasien.jenis_kelamin).status !== '-'"
                                        :value="getLpData(selectedRekamMedis.anamnesis.lingkar_perut, selectedRekamMedis.anamnesis.is_hamil, pasien.jenis_kelamin).status"
                                        :severity="getLpData(selectedRekamMedis.anamnesis.lingkar_perut, selectedRekamMedis.anamnesis.is_hamil, pasien.jenis_kelamin).isCritical ? 'danger' : (getLpData(selectedRekamMedis.anamnesis.lingkar_perut, selectedRekamMedis.anamnesis.is_hamil, pasien.jenis_kelamin).status === 'Hamil' ? 'info' : 'success')"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Pemeriksaan Tekanan Darah -->
                    <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-amber-800">
                            <i class="pi pi-heart-pulse text-amber-600"></i>
                            <span>Pemeriksaan Tekanan Darah</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div class="bg-white p-3 rounded-xl border border-amber-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Tekanan Darah</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getTdData(selectedRekamMedis.anamnesis.tekanan_darah).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.tekanan_darah || '-' }} <span class="text-xs font-normal text-gray-500">mmHg</span>
                                    </p>
                                    <Tag
                                        v-if="getTdData(selectedRekamMedis.anamnesis.tekanan_darah).category !== '-'"
                                        :value="getTdData(selectedRekamMedis.anamnesis.tekanan_darah).category"
                                        :severity="getTdData(selectedRekamMedis.anamnesis.tekanan_darah).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Pemeriksaan Laboratorium Cepat (POCT) -->
                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-800">
                            <i class="pi pi-filter text-blue-600"></i>
                            <span>Pemeriksaan Laboratorium Cepat (POCT)</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div class="bg-white p-3 rounded-xl border border-blue-100/70 shadow-sm">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs text-gray-500">Gula Darah</span>
                                    <span v-if="selectedRekamMedis.anamnesis.jenis_gula_darah" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">
                                        {{ selectedRekamMedis.anamnesis.jenis_gula_darah === 'puasa' ? 'GDP' : 'GDS' }}
                                    </span>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getGdData(selectedRekamMedis.anamnesis.gula_darah, selectedRekamMedis.anamnesis.jenis_gula_darah).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.gula_darah || '-' }} <span class="text-xs font-normal text-gray-500">mg/dL</span>
                                    </p>
                                    <Tag
                                        v-if="getGdData(selectedRekamMedis.anamnesis.gula_darah, selectedRekamMedis.anamnesis.jenis_gula_darah).category !== '-'"
                                        :value="getGdData(selectedRekamMedis.anamnesis.gula_darah, selectedRekamMedis.anamnesis.jenis_gula_darah).category"
                                        :severity="getGdData(selectedRekamMedis.anamnesis.gula_darah, selectedRekamMedis.anamnesis.jenis_gula_darah).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-blue-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Asam Urat</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getAuData(selectedRekamMedis.anamnesis.asam_urat, pasien.jenis_kelamin).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.asam_urat || '-' }} <span class="text-xs font-normal text-gray-500">mg/dL</span>
                                    </p>
                                    <Tag
                                        v-if="getAuData(selectedRekamMedis.anamnesis.asam_urat, pasien.jenis_kelamin).category !== '-'"
                                        :value="getAuData(selectedRekamMedis.anamnesis.asam_urat, pasien.jenis_kelamin).category"
                                        :severity="getAuData(selectedRekamMedis.anamnesis.asam_urat, pasien.jenis_kelamin).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-blue-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Kolesterol</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getCholData(selectedRekamMedis.anamnesis.kolesterol).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.kolesterol || '-' }} <span class="text-xs font-normal text-gray-500">mg/dL</span>
                                    </p>
                                    <Tag
                                        v-if="getCholData(selectedRekamMedis.anamnesis.kolesterol).category !== '-'"
                                        :value="getCholData(selectedRekamMedis.anamnesis.kolesterol).category"
                                        :severity="getCholData(selectedRekamMedis.anamnesis.kolesterol).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-blue-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Hemoglobin</span>
                                <div class="flex flex-col gap-1">
                                    <p class="font-bold text-base" :class="getHbData(selectedRekamMedis.anamnesis.hemoglobin).isCritical ? 'text-rose-600' : 'text-gray-900'">
                                        {{ selectedRekamMedis.anamnesis.hemoglobin || '-' }} <span class="text-xs font-normal text-gray-500">g/dL</span>
                                    </p>
                                    <Tag
                                        v-if="getHbData(selectedRekamMedis.anamnesis.hemoglobin).category !== '-'"
                                        :value="getHbData(selectedRekamMedis.anamnesis.hemoglobin).category"
                                        :severity="getHbData(selectedRekamMedis.anamnesis.hemoglobin).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2 self-start"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Tindak Lanjut & Evaluasi -->
                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-purple-800">
                            <i class="pi pi-check-circle text-purple-600"></i>
                            <span>Tindak Lanjut & Evaluasi</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                            <div class="bg-white p-3 rounded-xl border border-purple-100/70 shadow-sm">
                                <span class="text-xs text-gray-500 block mb-1">Tindak Lanjut</span>
                                <Tag
                                    :value="getTindakLanjutLabel(selectedRekamMedis.anamnesis.tindak_lanjut)"
                                    :severity="getTindakLanjutSeverity(selectedRekamMedis.anamnesis.tindak_lanjut)"
                                    class="!text-xs !px-2.5 font-bold"
                                />
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-purple-100/70 shadow-sm sm:col-span-2">
                                <span class="text-xs text-gray-500 block mb-1">Keterangan Tindak Lanjut</span>
                                <p class="font-medium text-gray-800">
                                    {{ selectedRekamMedis.anamnesis.keterangan_tindak_lanjut || '-' }}
                                </p>
                            </div>
                        </div>
                        <div v-if="selectedRekamMedis.anamnesis.keluhan_utama && selectedRekamMedis.anamnesis.keluhan_utama !== 'Pemeriksaan Screening (Otomatis)'" class="bg-white p-3 rounded-xl border border-purple-100/70 shadow-sm text-sm">
                            <span class="text-xs text-gray-500 block mb-1">Keluhan / Catatan Tambahan</span>
                            <p class="font-medium text-gray-800">{{ selectedRekamMedis.anamnesis.keluhan_utama }}</p>
                        </div>
                    </div>
                </div>

                <!-- Anamnesis Biasa (Untuk Pemeriksaan Umum / Non-Screening) -->
                <div v-else-if="selectedRekamMedis.anamnesis">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-heart text-pink-500"></i>
                        Hasil Anamnesis
                    </h4>
                    <div class="bg-pink-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h5 class="text-sm font-medium text-gray-600 mb-2">Tanda Vital</h5>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Tekanan Darah</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.tekanan_darah || '-' }} mmHg</p>
                                    </div>
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Suhu</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.suhu || '-' }} °C</p>
                                    </div>
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Nadi</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.nadi || '-' }} x/menit</p>
                                    </div>
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Respirasi</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.respirasi || '-' }} x/menit</p>
                                    </div>
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Berat Badan</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.berat_badan || '-' }} kg</p>
                                    </div>
                                    <div class="bg-white p-2 rounded">
                                        <span class="text-gray-500">Tinggi Badan</span>
                                        <p class="font-semibold">{{ selectedRekamMedis.anamnesis.tinggi_badan || '-' }} cm</p>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-500">Keluhan Utama</span>
                                    <p class="font-medium">{{ selectedRekamMedis.anamnesis.keluhan_utama }}</p>
                                </div>
                                <div v-if="selectedRekamMedis.anamnesis.riwayat_alergi">
                                    <span class="text-sm text-gray-500">Riwayat Alergi</span>
                                    <p class="font-medium text-red-600">{{ selectedRekamMedis.anamnesis.riwayat_alergi }}</p>
                                </div>
                                <div v-if="selectedRekamMedis.anamnesis.riwayat_penyakit_dahulu">
                                    <span class="text-sm text-gray-500">Riwayat Penyakit Dahulu</span>
                                    <p class="font-medium">{{ selectedRekamMedis.anamnesis.riwayat_penyakit_dahulu }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <Divider />

                <!-- Pemeriksaan Dokter -->
                <div v-if="selectedRekamMedis.pemeriksaan">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-file-edit text-blue-500"></i>
                        Hasil Pemeriksaan Dokter
                    </h4>
                    <div class="bg-blue-50 p-4 rounded-lg space-y-4">
                        <!-- Diagnosis -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Diagnosis Utama</span>
                                <p class="font-semibold text-lg">{{ selectedRekamMedis.pemeriksaan.diagnosis_utama }}</p>
                            </div>
                            <div v-if="selectedRekamMedis.pemeriksaan.kode_icd10">
                                <span class="text-sm text-gray-500">Kode ICD-10</span>
                                <p class="font-semibold">{{ selectedRekamMedis.pemeriksaan.kode_icd10 }}</p>
                            </div>
                        </div>

                        <div v-if="selectedRekamMedis.pemeriksaan.diagnosis_sekunder">
                            <span class="text-sm text-gray-500">Diagnosis Sekunder</span>
                            <p class="font-medium">{{ selectedRekamMedis.pemeriksaan.diagnosis_sekunder }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div v-if="selectedRekamMedis.pemeriksaan.pemeriksaan_fisik">
                                <span class="text-sm text-gray-500">Pemeriksaan Fisik</span>
                                <p class="font-medium">{{ selectedRekamMedis.pemeriksaan.pemeriksaan_fisik }}</p>
                            </div>
                            <div v-if="selectedRekamMedis.pemeriksaan.hasil_pemeriksaan">
                                <span class="text-sm text-gray-500">Hasil Pemeriksaan</span>
                                <p class="font-medium">{{ selectedRekamMedis.pemeriksaan.hasil_pemeriksaan }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div v-if="selectedRekamMedis.pemeriksaan.prognosis">
                                <span class="text-sm text-gray-500">Prognosis</span>
                                <p class="font-medium">{{ selectedRekamMedis.pemeriksaan.prognosis }}</p>
                            </div>
                            <div v-if="selectedRekamMedis.pemeriksaan.anjuran">
                                <span class="text-sm text-gray-500">Anjuran</span>
                                <p class="font-medium">{{ selectedRekamMedis.pemeriksaan.anjuran }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tindakan -->
                <div v-if="selectedRekamMedis.pemeriksaan?.tindakans && selectedRekamMedis.pemeriksaan.tindakans.length > 0">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-wrench text-orange-500"></i>
                        Tindakan yang Dilakukan
                    </h4>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <DataTable
                            :value="selectedRekamMedis.pemeriksaan.tindakans"
                            class="p-datatable-sm"
                        >
                            <Column field="nama" header="Nama Tindakan" />
                            <Column header="Biaya" style="width: 150px">
                                <template #body="{ data }">
                                    {{ formatCurrency(data.pivot.biaya) }}
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Resep Obat -->
                <div v-if="selectedRekamMedis.pemeriksaan?.resep_obats && selectedRekamMedis.pemeriksaan.resep_obats.length > 0">
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-box text-green-500"></i>
                        Resep Obat
                    </h4>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <DataTable
                            :value="selectedRekamMedis.pemeriksaan.resep_obats"
                            class="p-datatable-sm"
                        >
                            <Column header="Nama Obat">
                                <template #body="{ data }">
                                    {{ data.obat?.nama || data.nama_obat }}
                                </template>
                            </Column>
                            <Column header="Jumlah" style="width: 100px">
                                <template #body="{ data }">
                                    {{ data.jumlah }} {{ data.obat?.satuan || data.satuan }}
                                </template>
                            </Column>
                            <Column field="dosis" header="Dosis" style="width: 100px">
                                <template #body="{ data }">
                                    {{ data.dosis || '-' }}
                                </template>
                            </Column>
                            <Column field="aturan_pakai" header="Aturan Pakai" style="width: 120px">
                                <template #body="{ data }">
                                    {{ data.aturan_pakai || '-' }}
                                </template>
                            </Column>
                            <Column field="keterangan" header="Keterangan">
                                <template #body="{ data }">
                                    {{ data.keterangan || '-' }}
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </div>

                <!-- Surat Keterangan Dokter -->
                <div v-if="selectedRekamMedis.surat_dokter">
                    <Divider />
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="pi pi-file text-yellow-600"></i>
                        Surat Keterangan Dokter
                    </h4>
                    <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Nomor Surat</span>
                                <p class="font-semibold">{{ selectedRekamMedis.surat_dokter.nomor_surat }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Jenis Surat</span>
                                <p class="font-semibold">
                                    <Tag
                                        :value="getJenisSuratLabel(selectedRekamMedis.surat_dokter.jenis_surat)"
                                        :severity="selectedRekamMedis.surat_dokter.jenis_surat === 'surat_sehat' ? 'success' : (selectedRekamMedis.surat_dokter.jenis_surat === 'surat_berobat' ? 'info' : 'warn')"
                                    />
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Tanggal Surat</span>
                                <p class="font-medium">{{ formatDate(selectedRekamMedis.surat_dokter.tanggal_surat) }}</p>
                            </div>
                            <div v-if="selectedRekamMedis.surat_dokter.keperluan">
                                <span class="text-sm text-gray-500">Keperluan</span>
                                <p class="font-medium">{{ selectedRekamMedis.surat_dokter.keperluan }}</p>
                            </div>
                        </div>
                        <!-- Detail Surat Sakit -->
                        <div v-if="selectedRekamMedis.surat_dokter.jenis_surat === 'surat_sakit'" class="mt-4 pt-4 border-t border-yellow-200">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500">Jumlah Hari Istirahat</span>
                                    <p class="font-semibold text-lg">{{ selectedRekamMedis.surat_dokter.jumlah_hari_istirahat || 0 }} hari</p>
                                </div>
                                <div v-if="selectedRekamMedis.surat_dokter.tanggal_mulai">
                                    <span class="text-sm text-gray-500">Tanggal Mulai</span>
                                    <p class="font-medium">{{ formatDate(selectedRekamMedis.surat_dokter.tanggal_mulai) }}</p>
                                </div>
                                <div v-if="selectedRekamMedis.surat_dokter.tanggal_selesai">
                                    <span class="text-sm text-gray-500">Tanggal Selesai</span>
                                    <p class="font-medium">{{ formatDate(selectedRekamMedis.surat_dokter.tanggal_selesai) }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Detail Surat Berobat -->
                        <div v-if="selectedRekamMedis.surat_dokter.jenis_surat === 'surat_berobat'" class="mt-4 pt-4 border-t border-yellow-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-500">Tanggal Berobat</span>
                                    <p class="font-medium">{{ formatDate(selectedRekamMedis.surat_dokter.tanggal_surat) }}</p>
                                </div>
                                <div v-if="selectedRekamMedis.surat_dokter.keterangan">
                                    <span class="text-sm text-gray-500">Waktu / Jam Berobat</span>
                                    <p class="font-medium">Pukul {{ selectedRekamMedis.surat_dokter.keterangan }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Download PDF Button -->
                        <div class="mt-4 pt-4 border-t border-yellow-200 flex gap-2">
                            <a :href="route('surat-dokter.pdf', selectedRekamMedis.surat_dokter.id)" target="_blank">
                                <Button label="Download PDF" icon="pi pi-download" severity="success" size="small" />
                            </a>
                            <a :href="route('surat-dokter.preview', selectedRekamMedis.surat_dokter.id)" target="_blank">
                                <Button label="Preview" icon="pi pi-eye" severity="secondary" size="small" outlined />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between items-center w-full">
                    <div class="flex items-center gap-2">
                        <Button label="Print" icon="pi pi-print" severity="info" @click="printDetail" />
                        <a v-if="selectedRekamMedis?.jenis_layanan === 'screening'" :href="route('perawat.anamnesis.pdf', selectedRekamMedis.id)" target="_blank">
                            <Button label="Download PDF Screening" icon="pi pi-file-pdf" severity="success" outlined />
                        </a>
                    </div>
                    <Button label="Tutup" severity="secondary" @click="closeDetailDialog" />
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>
