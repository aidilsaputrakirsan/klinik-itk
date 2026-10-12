<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import AutoComplete from 'primevue/autocomplete';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';
import { icd10List, type Icd10Item } from '@/data/icd10';
import Icd10SelectorModal from '@/Components/Icd10SelectorModal.vue';

interface Obat {
    id: number;
    kode: string;
    nama: string;
    satuan: string;
    stok: number;
}

interface Tindakan {
    id: number;
    kode: string;
    nama: string;
    biaya: number;
}

interface RekamMedisDetail {
    id: number;
    nomor_kunjungan: string;
    catatan: string;
    tanggal_kunjungan: string;
    created_at: string;
    jenis_layanan: string;
    pasien: {
        id: number;
        nomor_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
        tipe_pasien: string;
        golongan_darah?: string;
    };
    anamnesis: {
        tekanan_darah: string;
        suhu: number;
        nadi: number;
        respirasi: number;
        tinggi_badan: number;
        berat_badan: number;
        keluhan_utama: string;
        riwayat_alergi: string;
        buta_warna?: string;
    } | null;
}

interface RiwayatResepItem {
    id: number;
    nama_obat?: string;
    jumlah: number;
    satuan?: string;
    dosis?: string;
    aturan_pakai?: string;
    keterangan?: string;
    obat?: {
        id: number;
        nama: string;
        satuan: string;
    };
}

interface RiwayatTindakanItem {
    id: number;
    kode?: string;
    nama: string;
    pivot?: {
        jumlah: number;
        biaya: number;
        keterangan?: string;
    };
}

interface RiwayatPemeriksaanItem {
    id: number;
    pemeriksaan_fisik?: string;
    hasil_pemeriksaan?: string;
    diagnosis_utama: string;
    diagnosis_sekunder?: string;
    kode_icd10?: string;
    prognosis?: string;
    anjuran?: string;
    penatalaksanaan_medis?: string;
    dokter?: {
        id: number;
        name: string;
    };
    tindakans?: RiwayatTindakanItem[];
    resep_obat?: RiwayatResepItem[];
    resep_obats?: RiwayatResepItem[];
}

interface RiwayatRekamMedisItem {
    id: number;
    nomor_kunjungan: string;
    tanggal_kunjungan: string;
    jenis_layanan: string;
    status: string;
    catatan?: string;
    pasien?: {
        id: number;
        nama: string;
        jenis_kelamin: string;
    };
    anamnesis?: {
        tekanan_darah?: string;
        suhu?: number;
        nadi?: number;
        respirasi?: number;
        tinggi_badan?: number;
        berat_badan?: number;
        lingkar_perut?: number;
        is_hamil?: boolean;
        keluhan_utama?: string;
        riwayat_penyakit_sekarang?: string;
        riwayat_penyakit_dahulu?: string;
        riwayat_alergi?: string;
        riwayat_obat?: string;
        gula_darah?: number;
        jenis_gula_darah?: string;
        asam_urat?: number;
        kolesterol?: number;
        hemoglobin?: number;
        buta_warna?: string;
        tindak_lanjut?: string;
        keterangan_tindak_lanjut?: string;
        perawat?: {
            name: string;
        };
    };
    pemeriksaan?: RiwayatPemeriksaanItem;
    dokter?: {
        name: string;
    };
    perawat?: {
        name: string;
    };
}

interface Props {
    rekamMedis: RekamMedisDetail;
    obats: Obat[];
    tindakans: Tindakan[];
    riwayatPasien?: RiwayatRekamMedisItem[];
}

const props = defineProps<Props>();
const toast = useToast();

const form = useForm({
    rekam_medis_id: props.rekamMedis.id,
    pemeriksaan_fisik: '',
    hasil_pemeriksaan: '',
    diagnosis_utama: '',
    diagnosis_sekunder: '',
    kode_icd10: '',
    prognosis: '',
    anjuran: '',
    penatalaksanaan_medis: '',
    selectedTindakans: [] as number[],
    resepObat: [] as { obat_id: number; jumlah: number; dosis: string; aturan_pakai: string; keterangan: string }[],
    // Surat Keterangan Dokter
    buat_surat: false,
    buat_surat_keterangan: false,
    jenis_surat: 'surat_sakit' as string,
    keperluan_surat: '',
    // Surat Rujukan Puskesmas
    buat_surat_rujukan: false,
    tujuan_rujukan: '',
    catatan_rujukan: '',
    jumlah_hari_istirahat: 1,
    tanggal_mulai: null as Date | null,
    tanggal_selesai: null as Date | null,
    tanggal_berobat: (props.rekamMedis.tanggal_kunjungan ? new Date(props.rekamMedis.tanggal_kunjungan) : new Date()) as Date | null,
    jam_berobat: (() => {
        if (props.rekamMedis.tanggal_kunjungan) {
            const d = new Date(props.rekamMedis.tanggal_kunjungan);
            if (!isNaN(d.getTime())) {
                const h = String(d.getHours()).padStart(2, '0');
                const m = String(d.getMinutes()).padStart(2, '0');
                return `${h}.${m}`;
            }
        }
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        return `${h}.${m}`;
    })(),
    // Fisik Surat Sehat
    tinggi_badan: props.rekamMedis.anamnesis?.tinggi_badan || null,
    berat_badan: props.rekamMedis.anamnesis?.berat_badan || null,
    tekanan_darah: props.rekamMedis.anamnesis?.tekanan_darah || '',
    nadi: props.rekamMedis.anamnesis?.nadi || null,
    suhu: props.rekamMedis.anamnesis?.suhu || null,
    golongan_darah: props.rekamMedis.pasien?.golongan_darah || '',
    buta_warna: props.rekamMedis.anamnesis?.buta_warna || '',
});

const golonganDarahOptions = [
    { label: 'A', value: 'A' },
    { label: 'B', value: 'B' },
    { label: 'AB', value: 'AB' },
    { label: 'O', value: 'O' },
    { label: 'Tidak Tahu', value: 'Tidak Tahu' }
];

const prognosisOptions = [
    { label: 'Baik', value: 'Baik' },
    { label: 'Sedang', value: 'Sedang' },
    { label: 'Buruk', value: 'Buruk' }
];

const butaWarnaOptions = [
    { label: 'Tidak Buta Warna', value: 'Tidak Buta Warna' },
    { label: 'Buta Warna', value: 'Buta Warna' }
];

const jenisSuratKetOptions = [
    { label: 'Surat Keterangan Sakit', value: 'surat_sakit' },
    { label: 'Surat Keterangan Sehat', value: 'surat_sehat' },
    { label: 'Surat Keterangan Berobat', value: 'surat_berobat' },
];

// icd10List imported from centralized data module @/data/icd10

const showIcdModal = ref(false);

const handleIcdSelect = (item: Icd10Item) => {
    form.kode_icd10 = item.code;
    form.diagnosis_utama = `${item.code} - ${item.name}`;
};

const filteredDiagnoses = ref<string[]>([]);

const searchDiagnosis = (event: any) => {
    const query = event.query.toLowerCase();
    filteredDiagnoses.value = icd10List.filter(item => item.toLowerCase().includes(query));
};

const onDiagnosisSelect = (event: any) => {
    const selected = event.value;
    const parts = selected.split(' - ');
    if (parts.length > 1) {
        form.kode_icd10 = parts[0];
        form.diagnosis_utama = parts.slice(1).join(' - ');
    }
};

const isIcdDisabled = computed(() => {
    return Boolean(form.diagnosis_utama && form.diagnosis_utama.trim().length > 0);
});

watch(() => form.diagnosis_utama, (newVal) => {
    if (!newVal || !newVal.trim()) {
        form.kode_icd10 = '';
    }
});

watch(() => form.jenis_surat, (newVal) => {
    if (newVal === 'surat_berobat') {
        form.keperluan_surat = '';
    }
});

const jenisSuratOptions = [
    { label: 'Surat Keterangan Sehat', value: 'surat_sehat' },
    { label: 'Surat Keterangan Sakit', value: 'surat_sakit' },
    { label: 'Surat Keterangan Berobat', value: 'surat_berobat' },
];

const addResepObat = () => {
    form.resepObat.push({
        obat_id: 0,
        jumlah: 1,
        dosis: '',
        aturan_pakai: '',
        keterangan: ''
    });
};

const removeResepObat = (index: number) => {
    form.resepObat.splice(index, 1);
};

const submitPemeriksaan = () => {
    form.post(route('dokter.pemeriksaan.store'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Berhasil',
                detail: 'Data pemeriksaan berhasil disimpan',
                life: 3000
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Gagal',
                detail: 'Periksa kembali field yang ditandai merah',
                life: 5000
            });
        }
    });
};

const getAge = (birthDate: string) => {
    if (!birthDate) return 0;
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

const getLayananLabel = (layanan: string) => {
    const labels: Record<string, string> = {
        berobat: 'Pemeriksaan Umum',
        surat_sehat: 'Surat Sehat',
        screening: 'Screening',
    };
    return labels[layanan] || layanan;
};

const getTipePasienLabel = (tipe: string) => {
    const labels: Record<string, string> = {
        mahasiswa: 'Mahasiswa',
        dosen: 'Dosen',
        tendik: 'Tendik',
        umum: 'Umum'
    };
    return labels[tipe] || tipe;
};

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
    else if (bmi <= 29.9) { category = 'Obesitas Tk 1 (25-29.9)'; isCritical = true; }
    else { category = 'Obesitas Tk 2 (>=30)'; isCritical = true; }
    
    return { value: bmi.toFixed(2), category, isCritical };
};

const getLpData = (lp: number | null | undefined, isHamil: boolean | undefined, gender: string | undefined) => {
    if (isHamil) return { value: lp ? `${lp}` : '-', status: 'Hamil', isCritical: false };
    if (!lp) return { value: '-', status: '-', isCritical: false };
    
    let isCritical = false;
    if (gender === 'L' && lp > 90) isCritical = true;
    if (gender === 'P' && lp > 80) isCritical = true;
    
    return { 
        value: `${lp}`, 
        status: isCritical ? 'Obesitas Sentral' : 'Normal',
        isCritical 
    };
};

const getTdCategory = (sys: number | null | undefined, dia: number | null | undefined) => {
    if (!sys || !dia) return { status: '-', shortStatus: '-', isCritical: false };
    if (sys >= 160 || dia >= 100) return { status: 'Hipertensi Grade 2 (>=160/100)', shortStatus: 'Hipertensi Tk 2', isCritical: true };
    if (sys >= 140 || dia >= 90) return { status: 'Hipertensi Grade 1 (140/90-159/99)', shortStatus: 'Hipertensi Tk 1', isCritical: true };
    if (sys >= 130 || dia >= 85) return { status: 'Prehipertensi (130/85-139/89)', shortStatus: 'Prehipertensi', isCritical: true };
    return { status: 'Normal (<129/84)', shortStatus: 'Normal', isCritical: false };
};

const getTdFromStr = (td?: string | null) => {
    if (!td) return { status: '-', shortStatus: '-', isCritical: false };
    const parts = td.split('/');
    if (parts.length !== 2) return { status: '-', shortStatus: '-', isCritical: false };
    const sys = parseInt(parts[0]);
    const dia = parseInt(parts[1]);
    if (isNaN(sys) || isNaN(dia)) return { status: '-', shortStatus: '-', isCritical: false };
    return getTdCategory(sys, dia);
};

const getGdCategory = (gd: number | null | undefined, jenis: string | null | undefined) => {
    if (!gd) return { status: '-', shortStatus: '-', isCritical: false };
    if (jenis === 'puasa') {
        if (gd > 120) return { status: 'Hiperglikemia (GDP >120)', shortStatus: 'Hiperglikemia', isCritical: true };
    } else {
        if (gd > 200) return { status: 'Hiperglikemia (GDS >200)', shortStatus: 'Hiperglikemia', isCritical: true };
    }
    return { status: 'Normal', shortStatus: 'Normal', isCritical: false };
};

const getAuCategory = (au: number | null | undefined, gender: string | undefined) => {
    if (!au) return { status: '-', shortStatus: '-', isCritical: false };
    if (gender === 'L' && au > 7) return { status: 'Hiperuricemia (L: >7)', shortStatus: 'Hiperuricemia', isCritical: true };
    if (gender === 'P' && au > 6) return { status: 'Hiperuricemia (P: >6)', shortStatus: 'Hiperuricemia', isCritical: true };
    return { status: 'Normal', shortStatus: 'Normal', isCritical: false };
};

const getCholCategory = (chol: number | null | undefined) => {
    if (!chol) return { status: '-', shortStatus: '-', isCritical: false };
    if (chol > 200) return { status: 'Hipercholesterolemia (>200)', shortStatus: 'Hiperkolesterolemia', isCritical: true };
    return { status: 'Normal', shortStatus: 'Normal', isCritical: false };
};

const getHbCategory = (hb: number | null | undefined) => {
    if (!hb) return { status: '-', shortStatus: '-', isCritical: false };
    if (hb < 12) return { status: 'Anemia (<12)', shortStatus: 'Anemia', isCritical: true };
    return { status: 'Normal', shortStatus: 'Normal', isCritical: false };
};

const formatTindakLanjut = (val?: string) => {
    if (!val) return '';
    const map: Record<string, string> = {
        rawat_jalan: 'Rawat Jalan',
        rujuk: 'Rujuk Faskes 1',
        rujuk_faskes_1: 'Rujuk Faskes 1',
        faskes_1: 'Rujuk Faskes 1',
        edukasi: 'Edukasi'
    };
    return map[val.toLowerCase()] || val.replace(/_/g, ' ');
};
</script>

<template>
    <Head :title="`Pemeriksaan - ${props.rekamMedis.pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('dokter.antrian')">
                    <Button 
                        icon="pi pi-arrow-left" 
                        severity="secondary" 
                        text 
                        rounded 
                        class="!w-10 !h-10 text-gray-600 hover:bg-gray-200/60" 
                        v-tooltip.top="'Kembali ke Antrian'"
                    />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Form Pemeriksaan Dokter</h1>
                    <p class="text-xs text-gray-500">Pasien: <span class="font-semibold text-emerald-700">{{ props.rekamMedis.pasien.nama }}</span> (RM: {{ props.rekamMedis.pasien.nomor_rm }})</p>
                </div>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto pb-12">
            <!-- Information Section (Pasien & Anamnesis Card) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Patient Card -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl overflow-hidden bg-white">
                    <template #content>
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl flex-shrink-0 shadow-inner">
                                {{ props.rekamMedis.pasien.nama.charAt(0).toUpperCase() }}
                            </div>
                            <div class="space-y-1 flex-1">
                                <h3 class="font-bold text-gray-900 text-lg leading-snug">{{ props.rekamMedis.pasien.nama }}</h3>
                                <p class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded inline-block">
                                    RM: {{ props.rekamMedis.pasien.nomor_rm }}
                                </p>
                                <div class="text-xs text-gray-500 pt-1 space-y-0.5">
                                    <p><i class="pi pi-user mr-1.5 text-gray-400"></i>{{ props.rekamMedis.pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}, {{ getAge(props.rekamMedis.pasien.tanggal_lahir) }} thn</p>
                                    <p><i class="pi pi-ticket mr-1.5 text-gray-400"></i>No. Kunjungan: <span class="font-semibold text-gray-700">{{ props.rekamMedis.nomor_kunjungan }}</span></p>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        {{ getLayananLabel(props.rekamMedis.jenis_layanan) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">
                                        {{ getTipePasienLabel(props.rekamMedis.pasien.tipe_pasien) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="props.rekamMedis.catatan" class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-600">
                            <span class="font-bold text-gray-500">Catatan Pendaftaran:</span> {{ props.rekamMedis.catatan }}
                        </div>
                    </template>
                </Card>

                <!-- Anamnesis & Vital Signs Card -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl overflow-hidden bg-gradient-to-br from-blue-50/70 to-white lg:col-span-2">
                    <template #content>
                        <div class="flex items-center justify-between mb-3 border-b border-blue-100 pb-2">
                            <h4 class="font-bold text-blue-900 flex items-center gap-2">
                                <i class="pi pi-heart-fill text-rose-500"></i>
                                Hasil Anamnesis & Vital Sign (Perawat)
                            </h4>
                            <span v-if="props.rekamMedis.anamnesis?.riwayat_alergi" class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-100 text-rose-700 flex items-center gap-1">
                                <i class="pi pi-exclamation-triangle text-rose-600"></i>
                                Alergi: {{ props.rekamMedis.anamnesis.riwayat_alergi }}
                            </span>
                        </div>

                        <div v-if="props.rekamMedis.anamnesis" class="space-y-3">
                            <div class="bg-white/80 backdrop-blur-sm p-3 rounded-xl border border-blue-100 shadow-2xs">
                                <span class="text-xs font-bold text-blue-800 uppercase tracking-wider block mb-1">Keluhan Utama</span>
                                <p class="text-sm font-medium text-gray-800 leading-relaxed">{{ props.rekamMedis.anamnesis.keluhan_utama }}</p>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center shadow-2xs">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase block">Tekanan Darah</span>
                                    <span class="font-bold text-gray-800 text-sm">{{ props.rekamMedis.anamnesis.tekanan_darah || '-' }} <span class="text-[10px] font-normal text-gray-500">mmHg</span></span>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center shadow-2xs">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase block">Suhu Tubuh</span>
                                    <span class="font-bold text-gray-800 text-sm">{{ props.rekamMedis.anamnesis.suhu }} <span class="text-[10px] font-normal text-gray-500">°C</span></span>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center shadow-2xs">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase block">Denyut Nadi</span>
                                    <span class="font-bold text-gray-800 text-sm">{{ props.rekamMedis.anamnesis.nadi }} <span class="text-[10px] font-normal text-gray-500">x/menit</span></span>
                                </div>
                                <div class="bg-white p-2.5 rounded-xl border border-gray-100 text-center shadow-2xs">
                                    <span class="text-[10px] text-gray-400 font-bold uppercase block">Respirasi</span>
                                    <span class="font-bold text-gray-800 text-sm">{{ props.rekamMedis.anamnesis.respirasi }} <span class="text-[10px] font-normal text-gray-500">x/menit</span></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 pt-1">
                                <div><span class="text-gray-400">Berat Badan:</span> <span class="font-semibold text-gray-800">{{ props.rekamMedis.anamnesis.berat_badan }} kg</span></div>
                                <div><span class="text-gray-400">Tinggi Badan:</span> <span class="font-semibold text-gray-800">{{ props.rekamMedis.anamnesis.tinggi_badan }} cm</span></div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6 text-gray-400 text-sm italic">
                            Belum ada data anamnesis dari perawat.
                        </div>
                    </template>
                </Card>
            </div>
            <!-- MAIN CONTENT LAYOUT: Form on Left 2 cols, Riwayat on Right 1 col (SAMA PERSIS DENGAN ANAMNESIS & SCREENING) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                <!-- LEFT COLUMN: Form Pemeriksaan Dokter (2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Form Sections -->
                    <form @submit.prevent="submitPemeriksaan" class="space-y-6">

                <!-- Section 1: Pemeriksaan Fisik & Penunjang -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl bg-white overflow-hidden">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-gray-800 border-b pb-3 border-gray-100">
                            <span class="w-2.5 h-6 bg-emerald-500 rounded-full"></span>
                            1. Pemeriksaan Fisik & Penunjang
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-sm text-gray-700">Pemeriksaan Fisik</label>
                                <Textarea
                                    v-model="form.pemeriksaan_fisik"
                                    rows="3"
                                    autoResize
                                    placeholder="Catatan hasil pemeriksaan fisik pasien..."
                                    class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30 !resize-none"
                                    :class="{ 'p-invalid': form.errors.pemeriksaan_fisik }"
                                />
                                <small v-if="form.errors.pemeriksaan_fisik" class="text-red-500">{{ form.errors.pemeriksaan_fisik }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-semibold text-sm text-gray-700">Hasil Pemeriksaan Penunjang</label>
                                <Textarea
                                    v-model="form.hasil_pemeriksaan"
                                    rows="3"
                                    autoResize
                                    placeholder="Hasil laboratorium / radiologi / penunjang lainnya..."
                                    class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30 !resize-none"
                                    :class="{ 'p-invalid': form.errors.hasil_pemeriksaan }"
                                />
                                <small v-if="form.errors.hasil_pemeriksaan" class="text-red-500">{{ form.errors.hasil_pemeriksaan }}</small>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Section 2: Diagnosis & Prognosis -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl bg-white overflow-hidden">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-gray-800 border-b pb-3 border-gray-100">
                            <span class="w-2.5 h-6 bg-blue-500 rounded-full"></span>
                            2. Diagnosis & Penatalaksanaan
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4 pt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <label class="font-semibold text-sm text-gray-700">Diagnosis Utama <span class="text-red-500">*</span></label>
                                        <button
                                            type="button"
                                            @click="showIcdModal = true"
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition-colors border border-emerald-200/80 shadow-xs cursor-pointer"
                                            title="Buka katalog lengkap kode ICD-10 dengan filter & pencarian"
                                        >
                                            <i class="pi pi-search-plus text-xs"></i>
                                            <span>Katalog ICD-10</span>
                                        </button>
                                    </div>
                                    <AutoComplete
                                        v-model="form.diagnosis_utama"
                                        :suggestions="filteredDiagnoses"
                                        @complete="searchDiagnosis"
                                        @item-select="onDiagnosisSelect"
                                        placeholder="Ketik diagnosis atau pencarian ICD-10..."
                                        class="w-full"
                                        inputClass="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30"
                                        :class="{ 'p-invalid': form.errors.diagnosis_utama }"
                                    />
                                    <small v-if="form.errors.diagnosis_utama" class="text-red-500">{{ form.errors.diagnosis_utama }}</small>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm text-gray-700">Diagnosis Sekunder</label>
                                    <Textarea
                                        v-model="form.diagnosis_sekunder"
                                        rows="2"
                                        autoResize
                                        placeholder="Diagnosis komplikasi atau penyerta (opsional)"
                                        class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30 !resize-none"
                                        :class="{ 'p-invalid': form.errors.diagnosis_sekunder }"
                                    />
                                    <small v-if="form.errors.diagnosis_sekunder" class="text-red-500">{{ form.errors.diagnosis_sekunder }}</small>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center justify-between">
                                        <label class="font-semibold text-sm text-gray-700">Kode ICD-10</label>
                                        <span v-if="isIcdDisabled" class="text-[11px] text-emerald-600 font-medium">Terkunci otomatis</span>
                                    </div>
                                    <InputText
                                        v-model="form.kode_icd10"
                                        :disabled="isIcdDisabled"
                                        :placeholder="isIcdDisabled ? 'Otomatis dari diagnosis utama' : 'Contoh: J00'"
                                        class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30"
                                        :class="{ 
                                            'bg-gray-100/90 cursor-not-allowed !text-gray-600': isIcdDisabled,
                                            'p-invalid': form.errors.kode_icd10 
                                        }"
                                    />
                                    <small v-if="form.errors.kode_icd10" class="text-red-500">{{ form.errors.kode_icd10 }}</small>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm text-gray-700">Prognosis</label>
                                    <Select
                                        v-model="form.prognosis"
                                        :options="prognosisOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Pilih prognosis..."
                                        class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30"
                                        :class="{ 'p-invalid': form.errors.prognosis }"
                                    />
                                    <small v-if="form.errors.prognosis" class="text-red-500">{{ form.errors.prognosis }}</small>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold text-sm text-gray-700">Anjuran Pasien</label>
                                    <InputText
                                        v-model="form.anjuran"
                                        placeholder="Anjuran istirahat / pola makan..."
                                        class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30"
                                        :class="{ 'p-invalid': form.errors.anjuran }"
                                    />
                                    <small v-if="form.errors.anjuran" class="text-red-500">{{ form.errors.anjuran }}</small>
                                </div>
                            </div>

                            <div class="flex flex-col gap-2 pt-2">
                                <label class="font-semibold text-sm text-gray-700">Penatalaksanaan Medis (Catatan Dokter)</label>
                                <Textarea
                                    v-model="form.penatalaksanaan_medis"
                                    rows="2"
                                    autoResize
                                    placeholder="Catatan tindakan non-farmakologi atau tata laksana khusus..."
                                    class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30 !resize-none"
                                    :class="{ 'p-invalid': form.errors.penatalaksanaan_medis }"
                                />
                                <small v-if="form.errors.penatalaksanaan_medis" class="text-red-500">{{ form.errors.penatalaksanaan_medis }}</small>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Section 3: Tindakan Medis -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl bg-white overflow-hidden">
                    <template #title>
                        <div class="flex items-center gap-2 text-base font-bold text-gray-800 border-b pb-3 border-gray-100">
                            <span class="w-2.5 h-6 bg-purple-500 rounded-full"></span>
                            3. Tindakan Medis
                        </div>
                    </template>
                    <template #content>
                        <div class="pt-2">
                            <div v-if="props.tindakans.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                <div 
                                    v-for="tindakan in props.tindakans" 
                                    :key="tindakan.id" 
                                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-200/70 hover:border-emerald-300 hover:bg-emerald-50/30 transition-all cursor-pointer"
                                    @click="() => {
                                        const idx = form.selectedTindakans.indexOf(tindakan.id);
                                        if (idx > -1) form.selectedTindakans.splice(idx, 1);
                                        else form.selectedTindakans.push(tindakan.id);
                                    }"
                                >
                                    <Checkbox
                                        v-model="form.selectedTindakans"
                                        :inputId="`tindakan-${tindakan.id}`"
                                        :value="tindakan.id"
                                        @click.stop
                                    />
                                    <label :for="`tindakan-${tindakan.id}`" class="text-sm font-medium text-gray-800 cursor-pointer flex-1">
                                        {{ tindakan.nama }}
                                    </label>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-400 italic">Tidak ada master tindakan medis aktif.</p>
                        </div>
                    </template>
                </Card>

                <!-- Section 4: Resep Obat -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl bg-white overflow-hidden">
                    <template #title>
                        <div class="flex items-center justify-between border-b pb-3 border-gray-100">
                            <div class="flex items-center gap-2 text-base font-bold text-gray-800">
                                <span class="w-2.5 h-6 bg-teal-500 rounded-full"></span>
                                4. Resep Obat Pasien
                            </div>
                            <Button 
                                label="Tambah Obat" 
                                icon="pi pi-plus" 
                                size="small" 
                                severity="emerald" 
                                class="!rounded-xl !text-xs font-bold !bg-emerald-50 hover:!bg-emerald-100 !text-emerald-700 !border-emerald-200 shadow-2xs" 
                                @click="addResepObat" 
                            />
                        </div>
                    </template>
                    <template #content>
                        <div class="pt-3 space-y-4">
                            <div v-if="form.resepObat.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50">
                                <i class="pi pi-box text-gray-300 text-3xl mb-2"></i>
                                <p class="text-sm font-medium text-gray-500">Belum ada obat yang ditambahkan ke resep.</p>
                                <Button 
                                    label="Klik di sini untuk tambah obat" 
                                    icon="pi pi-plus" 
                                    text 
                                    size="small" 
                                    severity="emerald" 
                                    class="mt-1 !text-xs font-bold" 
                                    @click="addResepObat" 
                                />
                            </div>

                            <div 
                                v-for="(item, index) in form.resepObat" 
                                :key="index" 
                                class="p-4.5 rounded-2xl border border-gray-200/90 bg-gray-50/50 hover:border-teal-300/80 transition-all space-y-3.5 shadow-2xs"
                            >
                                <div class="flex items-center justify-between gap-3 border-b border-gray-200/60 pb-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-800 flex items-center justify-center text-xs font-extrabold">
                                            {{ index + 1 }}
                                        </span>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">
                                            Item Obat #{{ index + 1 }}
                                        </span>
                                    </div>
                                    <Button 
                                        icon="pi pi-trash" 
                                        severity="danger" 
                                        text 
                                        rounded 
                                        size="small" 
                                        class="!w-8 !h-8 text-rose-500 hover:bg-rose-50" 
                                        v-tooltip.top="'Hapus Obat Ini'"
                                        @click="removeResepObat(index)" 
                                    />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-start">
                                    <!-- Field Obat -->
                                    <div class="md:col-span-8 flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-700">Nama Obat <span class="text-red-500">*</span></label>
                                        <Select
                                            v-model="item.obat_id"
                                            :options="props.obats"
                                            optionLabel="nama"
                                            optionValue="id"
                                            filter
                                            placeholder="Cari & pilih obat..."
                                            class="w-full !rounded-xl bg-white !border-gray-300"
                                        >
                                            <template #option="slotProps">
                                                <div class="flex items-center justify-between w-full text-xs py-0.5">
                                                    <span class="font-medium text-gray-800">
                                                        {{ slotProps.option.nama }} 
                                                        <span class="text-gray-400 font-normal">({{ slotProps.option.satuan }})</span>
                                                    </span>
                                                    <span 
                                                        class="px-2 py-0.5 rounded text-[10px] font-bold" 
                                                        :class="slotProps.option.stok > 10 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                                    >
                                                        Stok: {{ slotProps.option.stok }}
                                                    </span>
                                                </div>
                                            </template>
                                        </Select>
                                    </div>

                                    <!-- Field Jumlah -->
                                    <div class="md:col-span-4 flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-700">Jumlah <span class="text-red-500">*</span></label>
                                        <InputNumber 
                                            v-model="item.jumlah" 
                                            :min="1" 
                                            fluid 
                                            class="w-full bg-white !rounded-xl" 
                                            inputClass="!py-2 !text-center !rounded-xl !border-gray-300" 
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                                    <!-- Field Dosis -->
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-600">Dosis</label>
                                        <InputText 
                                            v-model="item.dosis" 
                                            placeholder="Contoh: 500 mg" 
                                            class="w-full !rounded-xl !py-2 bg-white !border-gray-300 !text-xs" 
                                        />
                                    </div>

                                    <!-- Field Aturan Pakai -->
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-600">Aturan Pakai</label>
                                        <InputText 
                                            v-model="item.aturan_pakai" 
                                            placeholder="Contoh: 3x1 sehari" 
                                            class="w-full !rounded-xl !py-2 bg-white !border-gray-300 !text-xs" 
                                        />
                                    </div>

                                    <!-- Field Keterangan -->
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-600">Keterangan Tambahan</label>
                                        <InputText 
                                            v-model="item.keterangan" 
                                            placeholder="Contoh: Sesudah makan" 
                                            class="w-full !rounded-xl !py-2 bg-white !border-gray-300 !text-xs" 
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Section 5: Pembuatan Surat Dokter & Rujukan (Opsional) -->
                <Card class="shadow-sm border border-gray-200/80 rounded-2xl bg-white overflow-hidden space-y-4">
                    <template #title>
                        <div class="flex items-center gap-3 border-b pb-3 border-gray-100">
                            <span class="w-2.5 h-6 bg-amber-500 rounded-full"></span>
                            <h3 class="text-base font-bold text-gray-800">
                                Checklist Pembuatan Surat Dokter
                            </h3>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-5 pt-2">
                            <!-- Checklist 1: Surat Keterangan Dokter (Sakit / Sehat) -->
                            <div class="border border-amber-200/80 rounded-2xl p-4 bg-amber-50/40 space-y-4">
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="form.buat_surat_keterangan" :binary="true" inputId="buat_surat_keterangan" />
                                    <label for="buat_surat_keterangan" class="text-sm font-bold text-gray-800 cursor-pointer">
                                        Buat Surat Keterangan Dokter (Sehat / Sakit / Berobat)
                                    </label>
                                </div>

                                <div v-if="form.buat_surat_keterangan" class="pt-2 space-y-4 border-t border-amber-200/60">
                                    <div class="grid grid-cols-1" :class="form.jenis_surat === 'surat_berobat' ? '' : 'sm:grid-cols-2 gap-4'">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Jenis Surat Keterangan <span class="text-red-500">*</span></label>
                                            <Select
                                                v-model="form.jenis_surat"
                                                :options="jenisSuratKetOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih jenis surat..."
                                                class="w-full !rounded-xl bg-white !border-gray-300"
                                            />
                                        </div>
                                        <div v-if="form.jenis_surat !== 'surat_berobat'" class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Keperluan Surat</label>
                                            <InputText
                                                v-model="form.keperluan_surat"
                                                placeholder="Misal: Izin sakit perkuliahan / kerja..."
                                                class="w-full !rounded-xl bg-white !border-gray-300 !py-2 text-sm"
                                            />
                                        </div>
                                    </div>

                                    <!-- Detail Surat Sakit -->
                                    <div v-if="form.jenis_surat === 'surat_sakit'" class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-amber-200/60 pt-3">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Lama Istirahat</label>
                                            <InputNumber
                                                v-model="form.jumlah_hari_istirahat"
                                                :min="1"
                                                :max="14"
                                                suffix=" hari"
                                                fluid
                                                inputClass="!rounded-xl !border-gray-300 !py-2 !text-sm bg-white"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Tanggal Mulai</label>
                                            <DatePicker
                                                v-model="form.tanggal_mulai"
                                                dateFormat="dd/mm/yy"
                                                placeholder="Pilih tanggal"
                                                fluid
                                                inputClass="!rounded-xl !border-gray-300 !py-2 !text-sm bg-white"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Tanggal Selesai</label>
                                            <DatePicker
                                                v-model="form.tanggal_selesai"
                                                dateFormat="dd/mm/yy"
                                                placeholder="Pilih tanggal"
                                                fluid
                                                inputClass="!rounded-xl !border-gray-300 !py-2 !text-sm bg-white"
                                            />
                                        </div>
                                    </div>

                                    <!-- Detail Surat Berobat -->
                                    <div v-if="form.jenis_surat === 'surat_berobat'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-amber-200/60 pt-3">
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Tanggal Berobat</label>
                                            <DatePicker
                                                v-model="form.tanggal_berobat"
                                                dateFormat="dd/mm/yy"
                                                placeholder="Pilih tanggal"
                                                fluid
                                                inputClass="!rounded-xl !border-gray-300 !py-2 !text-sm bg-white"
                                            />
                                        </div>
                                        <div class="flex flex-col gap-2">
                                            <label class="font-semibold text-sm text-gray-700">Waktu / Jam Berobat</label>
                                            <InputText
                                                v-model="form.jam_berobat"
                                                placeholder="Misal: 08.12"
                                                class="w-full !rounded-xl bg-white !border-gray-300 !py-2 text-sm"
                                            />
                                        </div>
                                    </div>

                                    <!-- Detail Surat Sehat -->
                                    <div v-if="form.jenis_surat === 'surat_sehat'" class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-t border-amber-200/60 pt-3">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Tinggi Badan (cm)</span>
                                            <InputNumber v-model="form.tinggi_badan" suffix=" cm" fluid inputClass="!rounded-xl !border-gray-300 !py-2 bg-white" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Berat Badan (kg)</span>
                                            <InputNumber v-model="form.berat_badan" suffix=" kg" fluid inputClass="!rounded-xl !border-gray-300 !py-2 bg-white" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Tekanan Darah</span>
                                            <InputText v-model="form.tekanan_darah" placeholder="120/80" class="w-full !rounded-xl bg-white !border-gray-300 !py-2 text-sm" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Nadi (x/menit)</span>
                                            <InputNumber v-model="form.nadi" fluid inputClass="!rounded-xl !border-gray-300 !py-2 bg-white" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Suhu (°C)</span>
                                            <InputNumber v-model="form.suhu" suffix=" °C" fluid inputClass="!rounded-xl !border-gray-300 !py-2 bg-white" />
                                        </div>
                                        <div class="flex flex-col gap-1">
                                            <span class="text-xs font-semibold text-gray-700">Golongan Darah</span>
                                            <Select v-model="form.golongan_darah" :options="golonganDarahOptions" optionLabel="label" optionValue="value" class="w-full !rounded-xl bg-white !border-gray-300" />
                                        </div>
                                        <div class="flex flex-col gap-1 sm:col-span-3">
                                            <span class="text-xs font-semibold text-gray-700">Buta Warna</span>
                                            <Select v-model="form.buta_warna" :options="butaWarnaOptions" optionLabel="label" optionValue="value" class="w-full !rounded-xl bg-white !border-gray-300" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Checklist 2: Surat Rujukan Puskesmas -->
                            <div class="border border-blue-200/80 rounded-2xl p-4 bg-blue-50/40 space-y-4">
                                <div class="flex items-center gap-3">
                                    <Checkbox v-model="form.buat_surat_rujukan" :binary="true" inputId="buat_surat_rujukan" />
                                    <label for="buat_surat_rujukan" class="text-sm font-bold text-gray-800 cursor-pointer">
                                        Buat Surat Rujukan Puskesmas
                                    </label>
                                </div>

                                <div v-if="form.buat_surat_rujukan" class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-blue-200/60">
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm text-gray-700">Tujuan Rujukan <span class="text-red-500">*</span></label>
                                        <InputText
                                            v-model="form.tujuan_rujukan"
                                            placeholder="Contoh: Puskesmas Karang Joang"
                                            class="w-full !rounded-xl bg-white !border-gray-300 !py-2 text-sm"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="font-semibold text-sm text-gray-700">Dikirim Untuk (Catatan Rujukan)</label>
                                        <InputText
                                            v-model="form.catatan_rujukan"
                                            placeholder="Contoh: Mohon untuk dilakukan pemeriksaan/perawatan/penatalaksanaan lebih lanjut"
                                            class="w-full !rounded-xl bg-white !border-gray-300 !py-2 text-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Bottom Action Footer Card -->
                <Card class="shadow-md border border-gray-200 rounded-2xl bg-white overflow-hidden sticky bottom-4 z-20">
                    <template #content>
                        <div class="flex items-center justify-between gap-4">
                            <Link :href="route('dokter.antrian')">
                                <Button 
                                    label="Batal / Kembali" 
                                    icon="pi pi-times" 
                                    severity="secondary" 
                                    outlined 
                                    class="!rounded-xl font-bold !px-6"
                                    :disabled="form.processing"
                                />
                            </Link>

                            <Button
                                type="submit"
                                label="Simpan Pemeriksaan"
                                icon="pi pi-check"
                                severity="emerald"
                                class="!rounded-xl font-bold !px-8 shadow-lg shadow-emerald-600/20 hover:shadow-emerald-600/40 transition-all !bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600"
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                        </div>
                    </template>
                </Card>

            </form>
                </div>

                <!-- RIGHT COLUMN: RIWAYAT REKAM MEDIS & KUNJUNGAN PASIEN (STICKY SIDEBAR) -->
                <div class="space-y-6">
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs sticky top-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 font-bold text-slate-800 text-base">
                                <i class="pi pi-history text-emerald-600 text-lg"></i>
                                <span>Riwayat Rekam Medis</span>
                            </div>
                            <Tag
                                :value="`${props.riwayatPasien?.length || 0} Records`"
                                severity="info"
                                class="!text-[10px] !px-2"
                            />
                        </div>

                        <p class="text-xs text-slate-500 font-medium">
                            Daftar <strong>5 rekam medis / kunjungan terakhir</strong> milik pasien <strong class="text-slate-900">{{ props.rekamMedis.pasien.nama }}</strong>:
                        </p>

                        <!-- List Cards Riwayat Kunjungan -->
                        <div v-if="props.riwayatPasien && props.riwayatPasien.length > 0" class="space-y-4 max-h-[760px] overflow-y-auto pr-1">
                            <div
                                v-for="item in props.riwayatPasien"
                                :key="item.id"
                                class="bg-slate-50/90 rounded-xl p-4 border border-slate-200 hover:border-emerald-400 transition-all space-y-3 shadow-2xs"
                            >
                                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                                    <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-800">
                                        <i class="pi pi-calendar text-emerald-600"></i>
                                        <span>
                                            {{ new Date(item.tanggal_kunjungan).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full uppercase" :class="item.jenis_layanan === 'screening' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'">
                                            {{ getLayananLabel(item.jenis_layanan) }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono font-semibold bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ item.nomor_kunjungan }}</span>
                                    </div>
                                </div>

                                <!-- Keluhan Utama & Alergi -->
                                <div v-if="item.anamnesis?.keluhan_utama" class="bg-white p-2.5 rounded-lg border border-slate-200/60 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Keluhan Utama</span>
                                        <span v-if="item.anamnesis.riwayat_alergi" class="text-[9px] bg-rose-50 text-rose-700 px-1.5 py-0.5 rounded font-bold border border-rose-200">
                                            Alergi: {{ item.anamnesis.riwayat_alergi }}
                                        </span>
                                    </div>
                                    <p class="text-xs font-semibold text-slate-800 leading-snug">
                                        {{ item.anamnesis.keluhan_utama }}
                                    </p>
                                </div>

                                <!-- Vital Signs Summary -->
                                <div v-if="item.anamnesis" class="space-y-1.5 text-xs">
                                    <div class="grid grid-cols-2 gap-2 bg-white p-2.5 rounded-lg border border-slate-200/60 shadow-2xs">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">Tekanan Darah</span>
                                            <div class="flex items-center gap-1 flex-wrap">
                                                <span class="font-bold text-slate-800">{{ item.anamnesis.tekanan_darah || '-' }} mmHg</span>
                                                <Tag
                                                    v-if="item.anamnesis.tekanan_darah && getTdFromStr(item.anamnesis.tekanan_darah).status !== '-'"
                                                    :value="getTdFromStr(item.anamnesis.tekanan_darah).shortStatus"
                                                    :severity="getTdFromStr(item.anamnesis.tekanan_darah).isCritical ? 'danger' : 'success'"
                                                    class="!text-[8px] !px-1"
                                                />
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">Suhu / Nadi / Resp</span>
                                            <span class="font-bold text-slate-800">
                                                {{ item.anamnesis.suhu ? `${item.anamnesis.suhu}°C` : '-' }} / {{ item.anamnesis.nadi ? `${item.anamnesis.nadi}x` : '-' }} / {{ item.anamnesis.respirasi ? `${item.anamnesis.respirasi}x` : '-' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 bg-white p-2 rounded-lg border border-slate-200/60 text-[11px]">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">Tinggi / Berat</span>
                                            <span class="font-bold text-slate-700">{{ item.anamnesis.tinggi_badan || '-' }} cm / {{ item.anamnesis.berat_badan || '-' }} kg</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">IMT</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-slate-700">{{ getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).value }}</span>
                                                <Tag
                                                    v-if="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).category !== '-'"
                                                    :value="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).category"
                                                    :severity="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).isCritical ? 'danger' : 'success'"
                                                    class="!text-[8px] !px-1"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- KHUSUS SCREENING: Lingkar Perut & Lab Identifications -->
                                    <div v-if="item.jenis_layanan === 'screening' || item.anamnesis.gula_darah || item.anamnesis.lingkar_perut" class="space-y-2 pt-1">
                                        <div v-if="item.anamnesis.lingkar_perut" class="flex items-center justify-between bg-white p-2 rounded-lg border border-slate-200/60 text-[11px]">
                                            <span class="text-slate-500 font-semibold">Lingkar Perut:</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-slate-800">{{ item.anamnesis.lingkar_perut }} cm</span>
                                                <Tag
                                                    v-if="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).status !== '-'"
                                                    :value="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).status"
                                                    :severity="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).status === 'Hamil' ? 'info' : (getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).isCritical ? 'danger' : 'success')"
                                                    class="!text-[8px] !px-1"
                                                />
                                            </div>
                                        </div>

                                        <!-- POCT Lab values -->
                                        <div v-if="item.anamnesis.gula_darah || item.anamnesis.asam_urat || item.anamnesis.kolesterol || item.anamnesis.hemoglobin" class="bg-blue-50/60 p-2.5 rounded-lg border border-blue-100 space-y-1.5 text-[11px]">
                                            <div v-if="item.anamnesis.gula_darah" class="flex items-center justify-between gap-1 flex-wrap">
                                                <span class="text-slate-600 font-medium">Gula Darah ({{ item.anamnesis.jenis_gula_darah || 'sewaktu' }}):</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-slate-800">{{ item.anamnesis.gula_darah }} mg/dL</span>
                                                    <Tag
                                                        v-if="getGdCategory(item.anamnesis.gula_darah, item.anamnesis.jenis_gula_darah).status !== '-'"
                                                        :value="getGdCategory(item.anamnesis.gula_darah, item.anamnesis.jenis_gula_darah).shortStatus"
                                                        :severity="getGdCategory(item.anamnesis.gula_darah, item.anamnesis.jenis_gula_darah).isCritical ? 'danger' : 'success'"
                                                        class="!text-[8px] !px-1"
                                                    />
                                                </div>
                                            </div>
                                            <div v-if="item.anamnesis.asam_urat" class="flex items-center justify-between gap-1 flex-wrap">
                                                <span class="text-slate-600 font-medium">Asam Urat:</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-slate-800">{{ item.anamnesis.asam_urat }} mg/dL</span>
                                                    <Tag
                                                        v-if="getAuCategory(item.anamnesis.asam_urat, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).status !== '-'"
                                                        :value="getAuCategory(item.anamnesis.asam_urat, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).shortStatus"
                                                        :severity="getAuCategory(item.anamnesis.asam_urat, item.pasien?.jenis_kelamin || props.rekamMedis.pasien.jenis_kelamin).isCritical ? 'danger' : 'success'"
                                                        class="!text-[8px] !px-1"
                                                    />
                                                </div>
                                            </div>
                                            <div v-if="item.anamnesis.kolesterol" class="flex items-center justify-between gap-1 flex-wrap">
                                                <span class="text-slate-600 font-medium">Kolesterol:</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-slate-800">{{ item.anamnesis.kolesterol }} mg/dL</span>
                                                    <Tag
                                                        v-if="getCholCategory(item.anamnesis.kolesterol).status !== '-'"
                                                        :value="getCholCategory(item.anamnesis.kolesterol).shortStatus"
                                                        :severity="getCholCategory(item.anamnesis.kolesterol).isCritical ? 'danger' : 'success'"
                                                        class="!text-[8px] !px-1"
                                                    />
                                                </div>
                                            </div>
                                            <div v-if="item.anamnesis.hemoglobin" class="flex items-center justify-between gap-1 flex-wrap">
                                                <span class="text-slate-600 font-medium">Hemoglobin:</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-slate-800">{{ item.anamnesis.hemoglobin }} g/dL</span>
                                                    <Tag
                                                        v-if="getHbCategory(item.anamnesis.hemoglobin).status !== '-'"
                                                        :value="getHbCategory(item.anamnesis.hemoglobin).shortStatus"
                                                        :severity="getHbCategory(item.anamnesis.hemoglobin).isCritical ? 'danger' : 'success'"
                                                        class="!text-[8px] !px-1"
                                                    />
                                                </div>
                                            </div>
                                            <div v-if="item.anamnesis.buta_warna" class="flex items-center justify-between pt-1 border-t border-blue-100 gap-1 flex-wrap">
                                                <span class="text-slate-600 font-medium">Buta Warna:</span>
                                                <div class="flex items-center gap-1">
                                                    <span class="font-bold text-slate-800">{{ item.anamnesis.buta_warna }}</span>
                                                    <Tag
                                                        :value="item.anamnesis.buta_warna.toLowerCase().includes('tidak') || item.anamnesis.buta_warna.toLowerCase() === 'normal' ? 'Normal' : 'Buta Warna'"
                                                        :severity="item.anamnesis.buta_warna.toLowerCase().includes('tidak') || item.anamnesis.buta_warna.toLowerCase() === 'normal' ? 'success' : 'danger'"
                                                        class="!text-[8px] !px-1"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnosis Dokter (jika ada) -->
                                <div v-if="item.pemeriksaan" class="bg-emerald-50/60 p-2.5 rounded-lg border border-emerald-100 space-y-1.5 text-xs">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Diagnosis Dokter</span>
                                    <p class="font-bold text-slate-900 leading-snug">
                                        <span v-if="item.pemeriksaan.kode_icd10" class="text-emerald-700 font-mono mr-1">[{{ item.pemeriksaan.kode_icd10 }}]</span>
                                        {{ item.pemeriksaan.diagnosis_utama }}
                                    </p>
                                    <p v-if="item.pemeriksaan.diagnosis_sekunder" class="text-[11px] text-slate-600">
                                        <span class="font-semibold text-slate-500">Sekunder:</span> {{ item.pemeriksaan.diagnosis_sekunder }}
                                    </p>

                                    <!-- Resep Obat yang pernah diberikan -->
                                    <div v-if="(item.pemeriksaan.resep_obat && item.pemeriksaan.resep_obat.length > 0) || (item.pemeriksaan.resep_obats && item.pemeriksaan.resep_obats.length > 0)" class="pt-1.5 border-t border-emerald-100">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-1">Terapi / Obat:</span>
                                        <ul class="space-y-0.5 text-[11px] text-slate-700">
                                            <li v-for="resep in (item.pemeriksaan.resep_obat || item.pemeriksaan.resep_obats)" :key="resep.id" class="flex items-center justify-between">
                                                <span class="font-medium">• {{ resep.nama_obat || resep.obat?.nama }} ({{ resep.jumlah }} {{ resep.satuan || resep.obat?.satuan || 'tab' }})</span>
                                                <span v-if="resep.aturan_pakai" class="text-[10px] text-slate-500 italic">{{ resep.aturan_pakai }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Tindakan -->
                                    <div v-if="item.pemeriksaan.tindakans && item.pemeriksaan.tindakans.length > 0" class="pt-1 border-t border-emerald-100">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase block mb-0.5">Tindakan Medis:</span>
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="tdk in item.pemeriksaan.tindakans" :key="tdk.id" class="px-1.5 py-0.5 bg-white text-slate-700 rounded border border-emerald-200 text-[10px] font-medium">
                                                {{ tdk.nama }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer Dokter/Perawat -->
                                <div class="text-[10px] text-slate-400 flex items-center justify-between pt-1 border-t border-slate-200/60">
                                    <span v-if="item.pemeriksaan?.dokter?.name || item.dokter?.name">Dokter: {{ item.pemeriksaan?.dokter?.name || item.dokter?.name }}</span>
                                    <span v-else>-</span>
                                    <span v-if="item.anamnesis?.perawat?.name || item.perawat?.name">Perawat: {{ item.anamnesis?.perawat?.name || item.perawat?.name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State Kunjungan -->
                        <div v-else class="py-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <i class="pi pi-folder-open text-slate-300 text-3xl mb-2 block"></i>
                            <p class="text-xs text-slate-500 font-medium">Belum ada riwayat rekam medis sebelumnya</p>
                            <p class="text-[10px] text-slate-400 mt-1">Data kunjungan kali ini akan menjadi riwayat awal pasien.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Icd10SelectorModal
            v-model:visible="showIcdModal"
            @select="handleIcdSelect"
        />
    </AppLayout>
</template>
