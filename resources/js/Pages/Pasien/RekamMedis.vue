<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien, RekamMedis } from '@/types';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import FileUpload from 'primevue/fileupload';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { usePage } from '@inertiajs/vue3';

interface ActionItem { id: number; nama: string; biaya?: number; pivot?: any }
interface ObatItem { id: number; nama: string; satuan?: string }

interface ResepObatItem {
    id: number;
    nama_obat: string;
    jumlah: number;
    satuan: string;
    dosis: string | null;
    aturan_pakai: string | null;
    keterangan: string | null;
    obat?: ObatItem;
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
    penatalaksanaan_medis: string | null;
    dokter_id?: number | null;
    dokter?: { name: string; };
    resep_obats?: ResepObatItem[];
    tindakans?: ActionItem[];
}

interface AnamnesisData {
    tekanan_darah: string | null;
    suhu: number | null;
    nadi: number | null;
    respirasi: number | null;
    tinggi_badan: number | null;
    berat_badan: number | null;
    skala_nyeri: number | null;
    keluhan_utama: string;
    riwayat_penyakit_sekarang: string | null;
    riwayat_penyakit_dahulu: string | null;
    riwayat_alergi: string | null;
    riwayat_obat: string | null;
    riwayat_keluarga: string | null;
    diagnosa_keperawatan: string | null;
    intervensi_keperawatan: string | null;
    implementasi_keperawatan: string | null;
    evaluasi_keperawatan: string | null;
    bmi?: number;
    perawat_id?: number | null;
    perawat?: { name: string; };
}

interface RekamMedisWithDetails extends Omit<RekamMedis, 'anamnesis' | 'pemeriksaan'> {
    anamnesis?: AnamnesisData;
    pemeriksaan?: PemeriksaanData;
}

interface UserProp { id: number; name: string; }

interface Props {
    pasien: Pasien & { rekam_medis?: RekamMedisWithDetails[] };
    obats: ObatItem[];
    tindakans: ActionItem[];
    perawatList: UserProp[];
    dokterList: UserProp[];
}

const props = defineProps<Props>();
const toast = useToast();
const page = usePage();

const showDetailDialog = ref(false);
const showKunjunganDialog = ref(false);
const showImportDialog = ref(false);
const selectedRekamMedis = ref<RekamMedisWithDetails | null>(null);
const isEditingAll = ref(false);
const isSaving = ref(false);

const canEditPasien = computed(() => {
    // @ts-ignore
    const role = page.props.auth?.user?.role;
    return role === 'superadmin' || role === 'admin';
});

const formKunjungan = useForm({
    tanggal_kunjungan: new Date(),
    jenis_layanan: 'berobat',
    catatan: ''
});

const formImport = useForm({
    file_excel: null as File | null,
});

const serviceOptions = [
    { label: 'Umum', value: 'umum' },
    { label: 'Gigi', value: 'gigi' },
    { label: 'KIA', value: 'kia' },
    { label: 'Surat Sehat', value: 'surat_sehat' },
    { label: 'Surat Sakit', value: 'surat_sakit' }
];

const formAnamnesis = useForm({
    perawat_id: null as number | null,
    tekanan_darah: '',
    suhu: null as number | null,
    nadi: null as number | null,
    respirasi: null as number | null,
    tinggi_badan: null as number | null,
    berat_badan: null as number | null,
    skala_nyeri: null as number | null,
    keluhan_utama: '',
    riwayat_penyakit_sekarang: '',
    riwayat_penyakit_dahulu: '',
    riwayat_keluarga: '',
    riwayat_alergi: '',
    riwayat_obat: '',
    diagnosa_keperawatan: '',
    intervensi_keperawatan: '',
    implementasi_keperawatan: '',
    evaluasi_keperawatan: '',
});

const formPemeriksaan = useForm({
    dokter_id: null as number | null,
    diagnosis_utama: '',
    diagnosis_sekunder: '',
    kode_icd10: '',
    pemeriksaan_fisik: '',
    hasil_pemeriksaan: '',
    penatalaksanaan_medis: '',
    prognosis: '',
    anjuran: '',
});

const submitKunjungan = () => {
    formKunjungan.post(route('pasien.kunjungan', props.pasien.id), {
        onSuccess: () => {
            showKunjunganDialog.value = false;
            formKunjungan.reset();
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Kunjungan medis baru berhasil didaftarkan', life: 3000 });
        }
    });
};

const openDetailDialog = (rekamMedis: RekamMedisWithDetails) => {
    selectedRekamMedis.value = rekamMedis;
    isEditingAll.value = false;
    
    if (rekamMedis.anamnesis) {
        formAnamnesis.perawat_id = rekamMedis.anamnesis.perawat_id || null;
        formAnamnesis.tekanan_darah = rekamMedis.anamnesis.tekanan_darah || '';
        formAnamnesis.suhu = rekamMedis.anamnesis.suhu;
        formAnamnesis.nadi = rekamMedis.anamnesis.nadi;
        formAnamnesis.respirasi = rekamMedis.anamnesis.respirasi;
        formAnamnesis.tinggi_badan = Number(rekamMedis.anamnesis.tinggi_badan) || null;
        formAnamnesis.berat_badan = Number(rekamMedis.anamnesis.berat_badan) || null;
        formAnamnesis.skala_nyeri = rekamMedis.anamnesis.skala_nyeri;
        formAnamnesis.keluhan_utama = rekamMedis.anamnesis.keluhan_utama || '';
        formAnamnesis.riwayat_penyakit_sekarang = rekamMedis.anamnesis.riwayat_penyakit_sekarang || '';
        formAnamnesis.riwayat_penyakit_dahulu = rekamMedis.anamnesis.riwayat_penyakit_dahulu || '';
        formAnamnesis.riwayat_keluarga = rekamMedis.anamnesis.riwayat_keluarga || '';
        formAnamnesis.riwayat_alergi = rekamMedis.anamnesis.riwayat_alergi || '';
        formAnamnesis.riwayat_obat = rekamMedis.anamnesis.riwayat_obat || '';
        formAnamnesis.diagnosa_keperawatan = rekamMedis.anamnesis.diagnosa_keperawatan || '';
        formAnamnesis.intervensi_keperawatan = rekamMedis.anamnesis.intervensi_keperawatan || '';
        formAnamnesis.implementasi_keperawatan = rekamMedis.anamnesis.implementasi_keperawatan || '';
        formAnamnesis.evaluasi_keperawatan = rekamMedis.anamnesis.evaluasi_keperawatan || '';
    } else {
        formAnamnesis.clearErrors();
        formAnamnesis.perawat_id = null;
        formAnamnesis.tekanan_darah = '';
        formAnamnesis.suhu = null;
        formAnamnesis.nadi = null;
        formAnamnesis.respirasi = null;
        formAnamnesis.tinggi_badan = null;
        formAnamnesis.berat_badan = null;
        formAnamnesis.skala_nyeri = null;
        formAnamnesis.keluhan_utama = '';
        formAnamnesis.riwayat_penyakit_sekarang = '';
        formAnamnesis.riwayat_penyakit_dahulu = '';
        formAnamnesis.riwayat_keluarga = '';
        formAnamnesis.riwayat_alergi = '';
        formAnamnesis.riwayat_obat = '';
        formAnamnesis.diagnosa_keperawatan = '';
        formAnamnesis.intervensi_keperawatan = '';
        formAnamnesis.implementasi_keperawatan = '';
        formAnamnesis.evaluasi_keperawatan = '';
    }

    if (rekamMedis.pemeriksaan) {
        formPemeriksaan.dokter_id = rekamMedis.pemeriksaan.dokter_id || null;
        formPemeriksaan.diagnosis_utama = rekamMedis.pemeriksaan.diagnosis_utama || '';
        formPemeriksaan.diagnosis_sekunder = rekamMedis.pemeriksaan.diagnosis_sekunder || '';
        formPemeriksaan.kode_icd10 = rekamMedis.pemeriksaan.kode_icd10 || '';
        formPemeriksaan.pemeriksaan_fisik = rekamMedis.pemeriksaan.pemeriksaan_fisik || '';
        formPemeriksaan.hasil_pemeriksaan = rekamMedis.pemeriksaan.hasil_pemeriksaan || '';
        formPemeriksaan.penatalaksanaan_medis = rekamMedis.pemeriksaan.penatalaksanaan_medis || '';
        formPemeriksaan.prognosis = rekamMedis.pemeriksaan.prognosis || '';
        formPemeriksaan.anjuran = rekamMedis.pemeriksaan.anjuran || '';
    } else {
        formPemeriksaan.clearErrors();
        formPemeriksaan.dokter_id = null;
        formPemeriksaan.diagnosis_utama = '';
        formPemeriksaan.diagnosis_sekunder = '';
        formPemeriksaan.kode_icd10 = '';
        formPemeriksaan.pemeriksaan_fisik = '';
        formPemeriksaan.hasil_pemeriksaan = '';
        formPemeriksaan.penatalaksanaan_medis = '';
        formPemeriksaan.prognosis = '';
        formPemeriksaan.anjuran = '';
    }

    showDetailDialog.value = true;
};

const closeDetailDialog = () => {
    showDetailDialog.value = false;
    selectedRekamMedis.value = null;
    isEditingAll.value = false;
};

const submitSemua = () => {
    if (!selectedRekamMedis.value) return;
    const rmId = selectedRekamMedis.value.id;
    isSaving.value = true;

    axios.put(route('rekam-medis.anamnesis.update', rmId), formAnamnesis.data())
        .then(() => {
            formPemeriksaan.put(route('rekam-medis.pemeriksaan.update', rmId), {
                onSuccess: () => {
                    isEditingAll.value = false;
                    isSaving.value = false;
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Seluruh data rekam medis berhasil diperbarui', life: 3000 });
                },
                onError: () => {
                    isSaving.value = false;
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Pastikan format isian data benar.', life: 3000 });
                }
            });
        })
        .catch(err => {
            console.error(err);
            isSaving.value = false;
            toast.add({ severity: 'error', summary: 'Gagal Error', detail: 'Gagal Menyimpan Anamnesis', life: 3000 });
        });
};

const handleImportUpload = (event: any) => {
    formImport.file_excel = event.files[0];
};

const submitImport = () => {
    formImport.post(route('pasien.rekam-medis.import', props.pasien.id), {
        preserveScroll: true,
        onSuccess: () => {
            showImportDialog.value = false;
            formImport.reset();
            toast.add({ severity: 'success', summary: 'Sukses', detail: 'Data rekam medis berhasil diimpor.', life: 3000 });
        },
        onError: (errors) => {
            const errorMsg = errors.file_excel || 'Pastikan file Excel valid dan maksimal 2MB.';
            toast.add({ severity: 'error', summary: 'Gagal', detail: errorMsg, life: 5000 });
        }
    });
};

const downloadTemplate = () => {
    window.location.href = route('pasien.rekam-medis.template');
};

const calcBmi = computed(() => {
    if (formAnamnesis.berat_badan && formAnamnesis.tinggi_badan) {
        const h = formAnamnesis.tinggi_badan / 100;
        return (formAnamnesis.berat_badan / (h * h)).toFixed(2);
    }
    return '-';
});

// Formatting helpers
const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = { mahasiswa: 'info', dosen: 'success', tendik: 'warn', umum: 'secondary' };
    return severities[status] || 'secondary';
};
const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = { mahasiswa: 'Mahasiswa', dosen: 'Dosen', tendik: 'Tendik', umum: 'Umum' };
    return labels[status] || status;
};
const getKunjunganStatusSeverity = (status: string) => {
    const severities: Record<string, string> = { menunggu_perawat: 'warn', proses_anamnesis: 'info', siap_dokter: 'info', sedang_diperiksa: 'info', selesai: 'success', batal: 'danger' };
    return severities[status] || 'secondary';
};
const getKunjunganStatusLabel = (status: string) => {
    const labels: Record<string, string> = { menunggu_perawat: 'Menunggu Perawat', proses_anamnesis: 'Proses Anamnesis', siap_dokter: 'Siap Dokter', sedang_diperiksa: 'Sedang Diperiksa', selesai: 'Selesai', batal: 'Batal' };
    return labels[status] || status;
};
const formatDate = (date: string) => {
    return new Date(date).toLocaleString('id-ID', { 
        day: 'numeric', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
    }).replace(/\./g, ':');
};
const formatText = (text: string | null | undefined) => {
    if (!text) return '-';
    return text.toString()
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};
const getAge = (birthDate: string) => {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age;
};
</script>

<template>
    <Head :title="`Rekam Medis - ${pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('pasien.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Rekam Medis Pasien</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Info Pasien Komprehensif -->
            <Card class="shadow-sm border-l-4 border-l-purple-500">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <i class="pi pi-id-card text-3xl text-purple-600"></i>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ pasien.nama }}</h2>
                                <p class="text-sm text-gray-500">No. RM: <strong class="text-purple-700">{{ pasien.nomor_rm }}</strong></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2" v-if="canEditPasien">
                            <Button label="Buat Rekam Medis" icon="pi pi-plus" size="small" @click="showKunjunganDialog = true" severity="success" outlined />
                            <Button label="Import Template" icon="pi pi-upload" size="small" @click="showImportDialog = true" severity="info" outlined />
                        </div>
                        <div class="flex items-center gap-2">
                            <Tag :value="getStatusLabel(pasien.tipe_pasien)" :severity="getStatusSeverity(pasien.tipe_pasien)" />
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-y-4 gap-x-2 text-sm mt-2 p-3 bg-gray-50 rounded-lg">
                        <div><span class="text-gray-500 block text-xs">No Identitas</span><span class="font-semibold">{{ pasien.nik || pasien.nomor_identitas || '-' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Tanggal Lahir</span><span class="font-medium">{{ pasien.tanggal_lahir ? formatDate(pasien.tanggal_lahir) : '-' }} ({{ pasien.tanggal_lahir ? getAge(pasien.tanggal_lahir) + ' Thn' : '-' }})</span></div>
                        <div><span class="text-gray-500 block text-xs">Jenis Kelamin</span><span class="font-medium">{{ pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Golongan Darah</span><span class="text-red-600 font-bold">{{ pasien.golongan_darah || '-' }}</span></div>
                        <div><span class="text-gray-500 block text-xs">No. Telp</span><span class="font-medium">{{ pasien.phone || '-' }}</span></div>
                        
                        <div><span class="text-gray-500 block text-xs">Agama</span><span class="font-medium">{{ formatText(pasien.agama) }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Status Perkawinan</span><span class="font-medium">{{ formatText(pasien.status_perkawinan) }}</span></div>
                        <div><span class="text-gray-500 block text-xs">Pendidikan Terakhir</span><span class="font-medium">{{ formatText(pasien.pendidikan_terakhir) }}</span></div>
                        <div class="md:col-span-2"><span class="text-gray-500 block text-xs">Pekerjaan</span><span class="font-medium">{{ formatText(pasien.pekerjaan) }}</span></div>
                        
                        <div class="md:col-span-5 border-t pt-2 mt-1">
                            <span class="text-gray-500 block text-xs">Alamat Lengkap</span>
                            <span class="font-medium">{{ pasien.alamat || '-' }}</span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Horizontal Scrolling Spreadsheet DataTable -->
            <Card class="shadow-sm overflow-hidden">
                <template #title>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-table text-purple-600"></i>
                            <span>Buku Rekam Medis Horizontal (Spreadsheet)</span>
                        </div>
                    </div>
                </template>
                <template #content>
                    <div class="border rounded my-2 bg-gray-50 p-1">
                        <p class="text-xs text-gray-500 px-2 flex gap-2 items-center">
                            <i class="pi pi-info-circle text-blue-500"></i> 
                            Gunakan scroll horizontal / geser ke kanan untuk melihat semua baris rekam medis. Tekan tombol "Edit Detail" pada kolom baris akhir untuk mengeditnya.
                        </p>
                    </div>

                    <DataTable
                        :value="pasien.rekam_medis || []"
                        :paginator="(pasien.rekam_medis?.length || 0) > 10"
                        :rows="10"
                        dataKey="id"
                        scrollable
                        scrollDirection="both"
                        class="p-datatable-sm text-xs mt-2 excel-table"
                        emptyMessage="Belum ada riwayat rekam medis"
                    >
                        <!-- Group 1: PURPLE (#5b328a) Patient and Admin Info -->
                        <Column header="Timestamp" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body="{ data }"><span>{{ formatDate(data.tanggal_kunjungan) }}</span></template>
                        </Column>
                        <Column header="No. RM" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body><span>{{ pasien.nomor_rm }}</span></template>
                        </Column>
                        <Column header="Nama Pasien" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body><span>{{ pasien.nama }}</span></template>
                        </Column>
                        <Column header="Tanggal Lahir" style="min-width: 140px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.tanggal_lahir ? formatDate(pasien.tanggal_lahir) : '-' }}</span></template>
                        </Column>
                        <Column header="No. Identitas (NIK/NIP/NIM)" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.nik || pasien.nomor_identitas || '-' }}</span></template>
                        </Column>
                        <Column header="No. Telp" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.phone || '-' }}</span></template>
                        </Column>
                        <Column header="Jenis Kelamin" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span></template>
                        </Column>
                        <Column header="Alamat" style="min-width: 200px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.alamat || '-' }}</span></template>
                        </Column>
                        <Column header="Agama" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.agama) }}</span></template>
                        </Column>
                        <Column header="Status Perkawinan" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.status_perkawinan) }}</span></template>
                        </Column>
                        <Column header="Pendidikan terakhir" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ formatText(pasien.pendidikan_terakhir) }}</span></template>
                        </Column>
                        <Column header="Status di ITK" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ getStatusLabel(pasien.tipe_pasien) }}</span></template>
                        </Column>
                        <Column header="Golongan Darah" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>{{ pasien.golongan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="Pasien Baru atau Lama" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ index }"><span>{{ index === ((pasien.rekam_medis?.length || 1) - 1) ? 'Baru' : 'Lama' }}</span></template>
                        </Column>
                        <Column header="Petugas Administrasi" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>Admin / Sistem</span></template>
                        </Column>

                        <!-- Group 2: BLUE (#4a86e8) Anamnesis / Kunjungan -->
                        <Column header="Keluhan Utama" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.keluhan_utama || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit sekarang" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_sekarang || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit dahulu" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_dahulu || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat Keluarga" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_keluarga || '-' }}</span></template>
                        </Column>
                        <Column header="Alergi" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_alergi || '-' }}</span></template>
                        </Column>
                        
                        <!-- Group 3: BLUE TTV -->
                        <Column header="TTV.1 TD" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tekanan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.2 Nadi" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.nadi || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.3 Suhu" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.suhu || '-' }}</span></template>
                        </Column>
                        <Column header="TTV. 4 RR" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.respirasi || '-' }}</span></template>
                        </Column>
                        <Column header="Berat Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.berat_badan || '-' }}</span></template>
                        </Column>
                        <Column header="Tinggi Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tinggi_badan || '-' }}</span></template>
                        </Column>
                        <Column header="IMT" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <span v-if="data.anamnesis?.berat_badan && data.anamnesis?.tinggi_badan">
                                    {{ (data.anamnesis.berat_badan / Math.pow(data.anamnesis.tinggi_badan / 100, 2)).toFixed(2) }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Skala Nyeri" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.skala_nyeri ?? '-' }}</span></template>
                        </Column>

                        <!-- Group 4: BLUE Pemeriksaan/Tindakan -->
                        <Column header="Pemeriksaan Fisik Lain" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.pemeriksaan_fisik || '-' }}</span></template>
                        </Column>
                        <Column header="Dokter penanggung jawab" style="min-width: 180px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.pemeriksaan?.dokter?.name || '-' }}</span></template>
                        </Column>
                        <Column header="Diagnosa medis" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <div>
                                    <strong class="block">{{ data.pemeriksaan?.diagnosis_utama || '-' }}</strong>
                                    <span v-if="data.pemeriksaan?.diagnosis_sekunder" class="text-[10px] mt-1">{{ data.pemeriksaan.diagnosis_sekunder }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Penatalaksanaan Medis" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.penatalaksanaan_medis || '-' }}</span></template>
                        </Column>

                        <!-- Group 5: BLUE Keperawatan -->
                        <Column header="Diagnosa Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.diagnosa_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Intervensi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.intervensi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Implementasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.implementasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Evaluasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.evaluasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Perawat" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.perawat?.name || '-' }}</span></template>
                        </Column>

                        <!-- ACTION COLUMN -->
                        <Column header="Aksi" style="min-width: 100px; text-align: center" alignFrozen="right" frozen>
                            <template #body="{ data }">
                                <Button
                                    icon="pi pi-pencil"
                                    size="small"
                                    severity="help"
                                    @click="openDetailDialog(data)"
                                />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>

        <Dialog v-model:visible="showImportDialog" modal header="Import Excel Rekam Medis" :style="{ width: '400px' }">
            <div class="flex flex-col gap-4">
                <p class="text-sm text-gray-600">Terima kasih telah menggunakan sistem data bulk. Anda dapat mengunduh Template yang telah terformat otomatis melalui tombol di bawah.</p>
                <Button label="Download Template Excel" icon="pi pi-download" size="small" severity="help" class="w-full" @click="downloadTemplate" />
                
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Data Rekam Medis Baru (.xlsx)</label>
                    <FileUpload 
                        mode="basic" 
                        name="file_excel" 
                        accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" 
                        :maxFileSize="2000000" 
                        @select="handleImportUpload" 
                        chooseLabel="Pilih File Excel (Max 2MB)"
                        class="w-full"
                    />
                    <small v-if="formImport.errors.file_excel" class="text-red-500">{{ formImport.errors.file_excel }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text @click="showImportDialog = false" />
                <Button label="Mulai Import" icon="pi pi-cloud-upload" :loading="formImport.processing" @click="submitImport" />
            </template>
        </Dialog>

        <!-- DIALOG TOMBOL BUAT KUNJUNGAN/RM -->
        <Dialog
            v-model:visible="showKunjunganDialog"
            modal
            header="Pendaftaran Kunjungan Rekam Medis Baru"
            :style="{ width: '35vw' }"
        >
            <form @submit.prevent="submitKunjungan" class="space-y-4 pt-2">
                <div class="flex flex-col gap-2">
                    <label>Pasien</label>
                    <InputText :value="pasien.nama" disabled class="bg-gray-100" />
                </div>
                <div class="flex flex-col gap-2">
                    <label>Tanggal Kunjungan Rekam Medis</label>
                    <DatePicker v-model="formKunjungan.tanggal_kunjungan" dateFormat="dd/mm/yy" showIcon />
                </div>
                <!-- Layanan and Surat fields removed per user request, defaulting to regular Rekam Medis (berobat) -->
                
                <div class="flex justify-end gap-2 mt-4">
                    <Button label="Batal" severity="secondary" text @click="showKunjunganDialog = false" />
                    <Button type="submit" label="Buat Record" severity="success" icon="pi pi-plus" :loading="formKunjungan.processing" />
                </div>
            </form>
        </Dialog>

        <!-- Dialog Edit Kunjungan Rekam Medis LENGKAP -->
        <Dialog
            v-model:visible="showDetailDialog"
            modal
            :header="`Detail Rekam Medis: ${selectedRekamMedis?.nomor_kunjungan}`"
            :style="{ width: '65vw', minWidth: '800px' }"
            :maximizable="true"
            :closable="true"
            @hide="closeDetailDialog"
        >
            <div v-if="selectedRekamMedis" class="mt-2 text-sm">
                <div class="flex justify-end mb-4">
                    <Button 
                        :label="isEditingAll ? 'Batal Edit' : 'Edit Seluruh Kolom'" 
                        :icon="isEditingAll ? 'pi pi-times' : 'pi pi-pencil'" 
                        :severity="isEditingAll ? 'secondary' : 'help'"
                        @click="isEditingAll = !isEditingAll" 
                    />
                </div>

                <form @submit.prevent="submitSemua" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 p-4 rounded bg-white">
                        
                        <!-- I. Anamnesis / Kunjungan -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mb-2">I. Anamnesis / Kunjungan</div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Keluhan Utama</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.keluhan_utama" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.keluhan_utama || '-' }}</span>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Penyakit Sekarang</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.riwayat_penyakit_sekarang" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.riwayat_penyakit_sekarang || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Penyakit Dahulu</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.riwayat_penyakit_dahulu" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.riwayat_penyakit_dahulu || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Riwayat Keluarga</label>
                            <InputText v-if="isEditingAll" v-model="formAnamnesis.riwayat_keluarga" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formAnamnesis.riwayat_keluarga || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Alergi (Obat/Lainnya)</label>
                            <InputText v-if="isEditingAll" v-model="formAnamnesis.riwayat_alergi" class="w-full border-gray-300" placeholder="Cth: Amoxicillin" />
                            <span v-else class="font-bold whitespace-pre-wrap" :class="formAnamnesis.riwayat_alergi ? 'text-red-600' : ''">{{ formAnamnesis.riwayat_alergi || 'Tidak Ada Riwayat Alergi' }}</span>
                        </div>
                        
                        <!-- II. Tanda Vital & Antropometri -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2 flex justify-between items-center">
                            <span>II. Tanda Vital & Antropometri</span>
                            <div class="flex items-center gap-2 pr-2" v-if="isEditingAll">
                                <label class="text-xs font-medium text-white">Perawat Penanggung Jawab:</label>
                                <Select v-model="formAnamnesis.perawat_id" :options="perawatList" optionLabel="name" optionValue="id" placeholder="Pilih Perawat" class="w-48 p-0! text-gray-900 text-sm" filter />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 col-span-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.1 TD (mmHg)</label>
                                <InputText v-if="isEditingAll" v-model="formAnamnesis.tekanan_darah" class="border-gray-300" placeholder="120/80" />
                                <span v-else class="font-medium">{{ formAnamnesis.tekanan_darah || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.2 Nadi (x/mnt)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.nadi" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.nadi || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.3 Suhu (°C)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.suhu" inputClass="border-gray-300" :minFractionDigits="1" />
                                <span v-else class="font-medium">{{ formAnamnesis.suhu || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">TTV.4 RR (x/mnt)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.respirasi" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.respirasi || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Berat Badan (kg)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.berat_badan" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.berat_badan || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Tinggi Badan (cm)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.tinggi_badan" inputClass="border-gray-300" />
                                <span v-else class="font-medium">{{ formAnamnesis.tinggi_badan || '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Skala Nyeri (0-10)</label>
                                <InputNumber v-if="isEditingAll" v-model="formAnamnesis.skala_nyeri" inputClass="border-gray-300" :min="0" :max="10" />
                                <span v-else class="font-bold text-red-500">{{ formAnamnesis.skala_nyeri ?? '-' }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">IMT</label>
                                <span class="font-bold pt-1 text-gray-700">{{ calcBmi }}</span>
                            </div>
                        </div>

                        <!-- III. Pemeriksaan Dokter & Medis -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2 flex justify-between items-center">
                            <span>III. Pemeriksaan Dokter & Medis</span>
                            <div class="flex items-center gap-2 pr-2" v-if="isEditingAll">
                                <label class="text-xs font-medium text-white">Dokter Penanggung Jawab:</label>
                                <Select v-model="formPemeriksaan.dokter_id" :options="dokterList" optionLabel="name" optionValue="id" placeholder="Pilih Dokter" class="w-48 p-0! text-gray-900 text-sm" filter />
                            </div>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Pemeriksaan Fisik Lain</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.pemeriksaan_fisik" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.pemeriksaan_fisik || '-' }}</span>
                        </div>
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Pemeriksaan Penunjang / Laborat</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.hasil_pemeriksaan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.hasil_pemeriksaan || '-' }}</span>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Medis Utama</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.diagnosis_utama" class="w-full border-gray-300" />
                            <span v-else class="font-bold text-blue-700">{{ formPemeriksaan.diagnosis_utama || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Kode ICD-10</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.kode_icd10" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formPemeriksaan.kode_icd10 || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Sekunder</label>
                            <InputText v-if="isEditingAll" v-model="formPemeriksaan.diagnosis_sekunder" class="w-full border-gray-300" />
                            <span v-else class="font-medium">{{ formPemeriksaan.diagnosis_sekunder || '-' }}</span>
                        </div>
                        
                        <div class="col-span-2 flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Penatalaksanaan Medis (Treatment)</label>
                            <Textarea v-if="isEditingAll" v-model="formPemeriksaan.penatalaksanaan_medis" rows="3" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formPemeriksaan.penatalaksanaan_medis || '-' }}</span>
                        </div>

                        <!-- IV. Asuhan Keperawatan -->
                        <div class="col-span-2 bg-[#4a86e8] text-white p-2 font-bold mt-4 mb-2">IV. Asuhan Keperawatan</div>

                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Diagnosa Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.diagnosa_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.diagnosa_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Intervensi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.intervensi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.intervensi_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Implementasi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.implementasi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.implementasi_keperawatan || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-[10px] font-bold text-gray-500 uppercase">Evaluasi Keperawatan</label>
                            <Textarea v-if="isEditingAll" v-model="formAnamnesis.evaluasi_keperawatan" rows="2" class="w-full border-gray-300" />
                            <span v-else class="font-medium whitespace-pre-wrap">{{ formAnamnesis.evaluasi_keperawatan || '-' }}</span>
                        </div>

                    </div>
                    <div v-if="isEditingAll" class="flex justify-end gap-2 mt-4 pt-4 border-t">
                        <Button label="Batal Simpan" severity="secondary" text @click="isEditingAll = false" />
                        <Button type="submit" label="Simpan Seluruh Rekam Medis" severity="success" icon="pi pi-check" :loading="isSaving" />
                    </div>
                </form>
            </div>
            <template #footer>
                <Button v-if="!isEditingAll" label="Tutup Detail Rekam Medis" severity="secondary" @click="closeDetailDialog" />
            </template>
        </Dialog>
    </AppLayout>
</template>

<style scoped>
/* Optional custom styling for the horizontal gridlines if needed */
:deep(.p-datatable.p-datatable-gridlines .p-datatable-header) {
    border-width: 1px 1px 0 1px;
}
</style>
