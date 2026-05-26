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
    riwayat_pengobatan: string | null;
    perawat?: {
        name: string;
    };
}

interface SuratDokterData {
    id: number;
    nomor_surat: string;
    jenis_surat: 'surat_sehat' | 'surat_sakit';
    tanggal_surat: string;
    keperluan: string | null;
    jumlah_hari_istirahat: number | null;
    tanggal_mulai: string | null;
    tanggal_selesai: string | null;
    dokter?: {
        name: string;
    };
}

interface RekamMedisWithDetails extends Omit<RekamMedis, 'anamnesis' | 'pemeriksaan'> {
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
    return labels[status] || status;
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
    };
    return labels[jenis] || jenis;
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
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .header h1 { margin: 0; font-size: 18px; }
                .header p { margin: 5px 0; }
                .section { margin-bottom: 15px; }
                .section-title { font-weight: bold; background: #f0f0f0; padding: 5px; margin-bottom: 10px; }
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
                .row { display: flex; margin-bottom: 5px; }
                .label { color: #666; min-width: 120px; }
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
                        <div class="row"><span class="label">Status:</span><span class="value">${getKunjunganStatusLabel(rm.status)}</span></div>
                    </div>
                </div>
            </div>

            ${rm.anamnesis ? `
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
            ` : ''}

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
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Formulir Registrasi Pasien - ${props.pasien.nama}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; font-size: 14px; line-height: 1.5; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
                .header h1 { margin: 0; font-size: 20px; font-weight: bold; }
                .header p { margin: 5px 0; font-size: 14px; }
                .title { text-align: center; font-weight: bold; margin-bottom: 20px; font-size: 16px; text-decoration: underline; }
                .row { display: flex; margin-bottom: 10px; }
                .label { width: 200px; font-weight: bold; }
                .value { flex: 1; border-bottom: 1px dotted #000; }
                .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; font-size: 15px; }
                .signature-box { margin-top: 50px; display: flex; justify-content: space-between; text-align: center; }
                .signature-line { margin-top: 70px; border-top: 1px solid #000; width: 200px; padding-top: 5px; }
                @media print { body { margin: 0; padding: 20px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>KLINIK INSTITUT TEKNOLOGI KALIMANTAN</h1>
                <p>Jl. Soekarno Hatta Km. 15, Karang Joang, Balikpapan</p>
            </div>
            
            <div class="title">FORMULIR REGISTRASI PASIEN</div>

            <div class="section-title">A. IDENTITAS PASIEN</div>
            <div class="row"><div class="label">No. Rekam Medis</div><div class="value">${props.pasien.nomor_rm}</div></div>
            <div class="row"><div class="label">Nama Lengkap</div><div class="value">${props.pasien.nama}</div></div>
            <div class="row"><div class="label">NIK / Identitas Lain</div><div class="value">${props.pasien.nik || props.pasien.nomor_identitas || '-'}</div></div>
            <div class="row"><div class="label">Jenis Kelamin</div><div class="value">${props.pasien.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan'}</div></div>
            <div class="row"><div class="label">Tanggal Lahir / Umur</div><div class="value">${props.pasien.tanggal_lahir ? formatDate(props.pasien.tanggal_lahir) : '-'} / ${props.pasien.tanggal_lahir ? getAge(props.pasien.tanggal_lahir) + ' Tahun' : '-'}</div></div>
            <div class="row"><div class="label">Golongan Darah</div><div class="value">${props.pasien.golongan_darah || '-'}</div></div>
            
            <div class="section-title">B. INFORMASI KONTAK & AKADEMIK</div>
            <div class="row"><div class="label">Alamat Lengkap</div><div class="value">${props.pasien.alamat || '-'}</div></div>
            <div class="row"><div class="label">No. Telepon / HP</div><div class="value">${props.pasien.phone || '-'}</div></div>
            <div class="row"><div class="label">Status di ITK</div><div class="value">${getStatusLabel(props.pasien.tipe_pasien)}</div></div>
            ${props.pasien.fakultas ? `<div class="row"><div class="label">${props.pasien.tipe_pasien === 'tendik' ? 'Unit Kerja' : 'Fakultas'}</div><div class="value">${props.pasien.fakultas}</div></div>` : ''}
            ${props.pasien.prodi ? `<div class="row"><div class="label">Program Studi</div><div class="value">${props.pasien.prodi}</div></div>` : ''}

            <p style="margin-top: 40px;">Dengan menandatangani formulir ini, saya menyatakan bahwa data yang diberikan adalah benar dan saya menyetujui peraturan serta ketentuan pelayanan kesehatan di Klinik ITK.</p>

            <div class="signature-box">
                <div>
                    <p>Balikpapan, ${new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</p>
                    <p>Pasien / Wali,</p>
                    <div class="signature-line">${props.pasien.nama}</div>
                </div>
                <div>
                    <p>&nbsp;</p>
                    <p>Petugas Pendaftaran,</p>
                    <div class="signature-line">(........................................)</div>
                </div>
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
                                <Tag :value="data.jenis_layanan || 'berobat'" severity="secondary" class="!text-[10px] !px-2 uppercase" />
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
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">No. Kunjungan</span>
                            <p class="font-semibold">{{ selectedRekamMedis.nomor_kunjungan }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Tanggal</span>
                            <p class="font-semibold">{{ formatDate(selectedRekamMedis.tanggal_kunjungan) }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Status</span>
                            <p>
                                <Tag :value="getKunjunganStatusLabel(selectedRekamMedis.status)" :severity="getKunjunganStatusSeverity(selectedRekamMedis.status)" />
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Anamnesis -->
                <div v-if="selectedRekamMedis.anamnesis">
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
                                        :severity="selectedRekamMedis.surat_dokter.jenis_surat === 'surat_sehat' ? 'success' : 'warn'"
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
                <div class="flex justify-between w-full">
                    <Button label="Print" icon="pi pi-print" severity="info" @click="printDetail" />
                    <Button label="Tutup" severity="secondary" @click="closeDetailDialog" />
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>
