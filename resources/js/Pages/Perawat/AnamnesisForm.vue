<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';

interface Pasien {
    id: number;
    nomor_rm: string;
    nama: string;
    jenis_kelamin: string;
    tanggal_lahir: string;
    alamat?: string;
    email?: string;
    golongan_darah?: string;
    tipe_pasien?: string;
}

interface Anamnesis {
    id?: number;
    tekanan_darah?: string;
    suhu?: number;
    nadi?: number;
    respirasi?: number;
    tinggi_badan?: number;
    berat_badan?: number;
    keluhan_utama?: string;
    riwayat_penyakit_sekarang?: string;
    riwayat_penyakit_dahulu?: string;
    riwayat_alergi?: string;
    riwayat_obat?: string;
    riwayat_keluarga?: string;
    skala_nyeri?: number;
    diagnosa_keperawatan?: string;
    intervensi_keperawatan?: string;
    implementasi_keperawatan?: string;
    evaluasi_keperawatan?: string;
    lingkar_perut?: number;
    is_hamil?: boolean;
    is_menyusui?: boolean;
    tindak_lanjut?: string;
    keterangan_tindak_lanjut?: string;
    gula_darah?: number;
    jenis_gula_darah?: 'puasa' | 'sewaktu' | null;
    asam_urat?: number;
    kolesterol?: number;
    hemoglobin?: number;
    buta_warna?: string;
    perawat?: {
        name: string;
    };
}

interface RekamMedisItem {
    id: number;
    nomor_kunjungan: string;
    tanggal_kunjungan: string;
    jenis_layanan: string;
    status: string;
    catatan?: string;
    pasien: Pasien;
    anamnesis?: Anamnesis;
    perawat?: {
        name: string;
    };
}

interface Props {
    rekamMedis: RekamMedisItem;
    riwayatScreening?: RekamMedisItem[];
}

const props = defineProps<Props>();
const toast = useToast();

const isScreening = props.rekamMedis.jenis_layanan === 'screening';

// Parse initial tekanan darah (sistolik/diastolik)
const initTd = props.rekamMedis.anamnesis?.tekanan_darah?.split('/') || [];
const initSistolik = initTd[0] ? parseInt(initTd[0]) : null;
const initDiastolik = initTd[1] ? parseInt(initTd[1]) : null;

const form = useForm({
    rekam_medis_id: props.rekamMedis.id,
    tekanan_darah_sistolik: initSistolik as number | null,
    tekanan_darah_diastolik: initDiastolik as number | null,
    suhu: props.rekamMedis.anamnesis?.suhu ? Number(props.rekamMedis.anamnesis.suhu) : null,
    nadi: props.rekamMedis.anamnesis?.nadi ?? null,
    respirasi: props.rekamMedis.anamnesis?.respirasi ?? null,
    tinggi_badan: props.rekamMedis.anamnesis?.tinggi_badan ? Number(props.rekamMedis.anamnesis.tinggi_badan) : null,
    berat_badan: props.rekamMedis.anamnesis?.berat_badan ? Number(props.rekamMedis.anamnesis.berat_badan) : null,
    keluhan_utama: props.rekamMedis.anamnesis?.keluhan_utama || '',
    riwayat_penyakit_sekarang: props.rekamMedis.anamnesis?.riwayat_penyakit_sekarang || '',
    riwayat_penyakit_dahulu: props.rekamMedis.anamnesis?.riwayat_penyakit_dahulu || '',
    riwayat_alergi: props.rekamMedis.anamnesis?.riwayat_alergi || '',
    riwayat_obat: props.rekamMedis.anamnesis?.riwayat_obat || '',
    riwayat_keluarga: props.rekamMedis.anamnesis?.riwayat_keluarga || '',
    skala_nyeri: props.rekamMedis.anamnesis?.skala_nyeri ?? null,
    diagnosa_keperawatan: props.rekamMedis.anamnesis?.diagnosa_keperawatan || '',
    intervensi_keperawatan: props.rekamMedis.anamnesis?.intervensi_keperawatan || '',
    implementasi_keperawatan: props.rekamMedis.anamnesis?.implementasi_keperawatan || '',
    evaluasi_keperawatan: props.rekamMedis.anamnesis?.evaluasi_keperawatan || '',
    lingkar_perut: props.rekamMedis.anamnesis?.lingkar_perut ? Number(props.rekamMedis.anamnesis.lingkar_perut) : null,
    is_hamil: Boolean(props.rekamMedis.anamnesis?.is_hamil),
    is_menyusui: Boolean(props.rekamMedis.anamnesis?.is_menyusui),
    tindak_lanjut: props.rekamMedis.anamnesis?.tindak_lanjut || '',
    keterangan_tindak_lanjut: props.rekamMedis.anamnesis?.keterangan_tindak_lanjut || '',
    gula_darah: props.rekamMedis.anamnesis?.gula_darah ? Number(props.rekamMedis.anamnesis.gula_darah) : null,
    jenis_gula_darah: props.rekamMedis.anamnesis?.jenis_gula_darah || null,
    asam_urat: props.rekamMedis.anamnesis?.asam_urat ? Number(props.rekamMedis.anamnesis.asam_urat) : null,
    kolesterol: props.rekamMedis.anamnesis?.kolesterol ? Number(props.rekamMedis.anamnesis.kolesterol) : null,
    hemoglobin: props.rekamMedis.anamnesis?.hemoglobin ? Number(props.rekamMedis.anamnesis.hemoglobin) : null,
    buta_warna: props.rekamMedis.anamnesis?.buta_warna || null,
    golongan_darah: props.rekamMedis.pasien?.golongan_darah || null,
});

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

const getBmiData = (tb: number | null | undefined, bb: number | null | undefined) => {
    if (!tb || !bb) return { value: '-', category: '-', isCritical: false };
    const h = tb / 100;
    const bmi = bb / (h * h);
    let category = '';
    let isCritical = false;
    
    if (bmi < 18) category = 'Underweight (<18)';
    else if (bmi <= 22.9) category = 'Normal (18-22.9)';
    else if (bmi <= 24.9) { category = 'Overweight (23-24.9)'; isCritical = true; }
    else if (bmi <= 29.9) { category = 'Obesitas Tingkat 1 (25-29.9)'; isCritical = true; }
    else { category = 'Obesitas Tingkat 2 (>=30)'; isCritical = true; }
    
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
    if (!sys || !dia) return { status: '-', isCritical: false };
    if (sys >= 160 || dia >= 100) return { status: 'Hipertensi Grade 2 (>=160/100)', isCritical: true };
    if (sys >= 140 || dia >= 90) return { status: 'Hipertensi Grade 1 (140/90-159/99)', isCritical: true };
    if (sys >= 130 || dia >= 85) return { status: 'Prehipertensi (130/85-139/89)', isCritical: true };
    return { status: 'Normal (<129/84)', isCritical: false };
};

const getGdCategory = (gd: number | null | undefined, jenis: string | null | undefined) => {
    if (!gd) return { status: '-', isCritical: false };
    if (jenis === 'puasa') {
        if (gd > 120) return { status: 'Hiperglikemia (GDP >120)', isCritical: true };
    } else {
        if (gd > 200) return { status: 'Hiperglikemia (GDS >200)', isCritical: true };
    }
    return { status: 'Normal', isCritical: false };
};

const getAuCategory = (au: number | null | undefined, gender: string | undefined) => {
    if (!au) return { status: '-', isCritical: false };
    if (gender === 'L' && au > 7) return { status: 'Hiperuricemia (L: >7)', isCritical: true };
    if (gender === 'P' && au > 6) return { status: 'Hiperuricemia (P: >6)', isCritical: true };
    return { status: 'Normal', isCritical: false };
};

const getCholCategory = (chol: number | null | undefined) => {
    if (!chol) return { status: '-', isCritical: false };
    if (chol > 200) return { status: 'Hipercholesterolemia (>200)', isCritical: true };
    return { status: 'Normal', isCritical: false };
};

const getHbCategory = (hb: number | null | undefined) => {
    if (!hb) return { status: '-', isCritical: false };
    if (hb < 12) return { status: 'Anemia (<12)', isCritical: true };
    return { status: 'Normal', isCritical: false };
};

const submitAnamnesis = (action: 'draft' | 'lanjut' = 'lanjut') => {
    if (isScreening) {
        if (!form.keluhan_utama) {
            form.keluhan_utama = 'Pemeriksaan Screening (Otomatis)';
        }
        if (!form.jenis_gula_darah && action === 'lanjut') {
            toast.add({
                severity: 'error',
                summary: 'Validasi Gagal',
                detail: 'Jenis Gula Darah (Puasa/Sewaktu) wajib dipilih untuk pasien screening.',
                life: 5000
            });
            form.errors.jenis_gula_darah = 'Wajib dipilih';
            return;
        }
    } else {
        if (!form.keluhan_utama && action === 'lanjut') {
            toast.add({
                severity: 'error',
                summary: 'Validasi Gagal',
                detail: 'Keluhan utama wajib diisi untuk pasien pemeriksaan umum.',
                life: 5000
            });
            form.errors.keluhan_utama = 'Wajib diisi';
            return;
        }
    }

    form.transform((data) => ({
        ...data,
        action_type: action
    })).post(route('perawat.anamnesis.store'), {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Berhasil',
                detail: 'Data anamnesis berhasil disimpan',
                life: 3000
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Gagal',
                detail: 'Periksa kembali inputan Anda',
                life: 5000
            });
        }
    });
};

const getLayananLabel = (layanan: string) => {
    const labels: Record<string, string> = {
        berobat: 'Pemeriksaan Umum',
        surat_sehat: 'Surat Sehat',
        screening: 'Screening',
    };
    return labels[layanan] || layanan;
};

const getTipePasienLabel = (tipe?: string) => {
    if (!tipe) return '';
    const labels: Record<string, string> = {
        mahasiswa: 'Mahasiswa',
        dosen: 'Dosen',
        tendik: 'Tendik',
        umum: 'Umum'
    };
    return labels[tipe] || tipe;
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
    <Head :title="`Input Anamnesis - ${rekamMedis.pasien.nama}`" />
    <AppLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('perawat.antrian')"
                        class="w-10 h-10 rounded-xl bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 transition-all flex items-center justify-center shadow-2xs"
                        title="Kembali ke Antrian"
                    >
                        <i class="pi pi-arrow-left text-sm"></i>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-800">
                                {{ isScreening ? 'Input Data Skrining Kesehatan' : 'Input Data Anamnesis & Vital Sign' }}
                            </h1>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">
                            Nomor Kunjungan: <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 ml-1">{{ rekamMedis.nomor_kunjungan }}</span>
                        </p>
                    </div>
                </div>
                <div>
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wide shadow-2xs"
                        :class="isScreening ? 'bg-amber-500 text-white' : 'bg-emerald-600 text-white'"
                    >
                        {{ getLayananLabel(rekamMedis.jenis_layanan) }}
                    </span>
                </div>
            </div>
        </template>

        <div class="space-y-6 max-w-7xl mx-auto pb-16">
            <!-- PATIENT HEADER BANNER CARD (CLEAN & NON-OVERLAPPING) -->
            <div class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <!-- Left: Patient Avatar & Demographics -->
                    <div class="flex items-start md:items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-black text-2xl shadow-sm shrink-0">
                            {{ rekamMedis.pasien.nama.charAt(0).toUpperCase() }}
                        </div>

                        <div class="space-y-1.5">
                            <!-- Name & RM Badge -->
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h2 class="text-xl font-bold text-slate-900 leading-tight">
                                    {{ rekamMedis.pasien.nama }}
                                </h2>
                                <span class="font-mono text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200/80 inline-flex items-center gap-1">
                                    <i class="pi pi-id-card text-emerald-600 text-xs"></i>
                                    RM: {{ rekamMedis.pasien.nomor_rm }}
                                </span>
                                <span v-if="rekamMedis.pasien.tipe_pasien" class="text-xs font-bold text-teal-800 bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-200/80">
                                    {{ getTipePasienLabel(rekamMedis.pasien.tipe_pasien) }}
                                </span>
                            </div>

                            <!-- Demographics Row -->
                            <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-500">
                                <span class="inline-flex items-center gap-1 text-slate-700">
                                    <i class="pi pi-user text-slate-400"></i>
                                    {{ getAge(rekamMedis.pasien.tanggal_lahir) }} Tahun ({{ rekamMedis.pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }})
                                </span>
                                <span v-if="rekamMedis.pasien.alamat" class="inline-flex items-center gap-1 text-slate-600">
                                    <i class="pi pi-map-marker text-slate-400"></i>
                                    {{ rekamMedis.pasien.alamat }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Blood Type & Visit Date Inputs -->
                    <div class="flex flex-wrap items-center gap-4 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100 shrink-0">
                        <div class="flex flex-col gap-1 w-40">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Golongan Darah</label>
                            <Select
                                v-model="form.golongan_darah"
                                :options="['A', 'B', 'AB', 'O', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']"
                                placeholder="Pilih Goldar"
                                class="!border-slate-300 !text-xs !rounded-xl w-full !h-9 flex items-center"
                            />
                        </div>

                        <div class="flex flex-col gap-0.5 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200/80">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tgl Kunjungan</span>
                            <span class="text-xs font-bold text-slate-800">
                                {{ new Date(rekamMedis.tanggal_kunjungan).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN CONTENT LAYOUT -->
            <div :class="isScreening ? 'grid grid-cols-1 lg:grid-cols-3 gap-6 items-start' : 'space-y-6'">
                
                <!-- FORM INPUTS CONTAINER -->
                <div :class="isScreening ? 'lg:col-span-2 space-y-6' : 'space-y-6'">
                    
                    <!-- SECTION 1: VITAL SIGN & ANTROPOMETRI -->
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                            <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 font-bold text-xs flex items-center justify-center shrink-0">1</span>
                            <h3 class="font-bold text-slate-800 text-base">Pemeriksaan Vital Sign & Antropometri</h3>
                        </div>

                        <div class="space-y-4">
                            <!-- Tekanan Darah Container -->
                            <div class="bg-slate-50/90 p-4 rounded-xl border border-slate-200/80 space-y-3">
                                <div class="flex items-center justify-between gap-2 flex-wrap">
                                    <label class="font-bold text-xs text-slate-700">Tekanan Darah (mmHg)</label>
                                    <Tag
                                        v-if="getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).status !== '-'"
                                        :value="getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).status"
                                        :severity="getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px] !px-2"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-1">Sistolik</span>
                                        <InputNumber
                                            v-model="form.tekanan_darah_sistolik"
                                            placeholder="120"
                                            class="w-full"
                                            :inputClass="'!text-center !font-bold !rounded-xl !border-slate-300'"
                                        />
                                    </div>
                                    <span class="text-2xl font-bold text-slate-300 mt-4">/</span>
                                    <div class="flex-1">
                                        <span class="text-[10px] text-slate-500 font-semibold block mb-1">Diastolik</span>
                                        <InputNumber
                                            v-model="form.tekanan_darah_diastolik"
                                            placeholder="80"
                                            class="w-full"
                                            :inputClass="'!text-center !font-bold !rounded-xl !border-slate-300'"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Tinggi Badan & Berat Badan Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Tinggi Badan (cm)</label>
                                    <InputNumber v-model="form.tinggi_badan" placeholder="170" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Berat Badan (kg)</label>
                                    <InputNumber v-model="form.berat_badan" :minFractionDigits="1" placeholder="65.0" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                            </div>

                            <!-- IMT Calculation Display Banner -->
                            <div class="bg-emerald-50/80 p-4 rounded-xl border border-emerald-200/80 flex items-center justify-between flex-wrap gap-3">
                                <div>
                                    <span class="text-xs font-bold text-emerald-900 block">Index Massa Tubuh (IMT)</span>
                                    <span class="text-[11px] text-emerald-700">Dihitung otomatis dari Tinggi & Berat Badan</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-black" :class="getBmiData(form.tinggi_badan, form.berat_badan).isCritical ? 'text-rose-600' : 'text-emerald-800'">
                                        {{ getBmiData(form.tinggi_badan, form.berat_badan).value }}
                                    </span>
                                    <Tag
                                        v-if="getBmiData(form.tinggi_badan, form.berat_badan).category !== '-'"
                                        :value="getBmiData(form.tinggi_badan, form.berat_badan).category"
                                        :severity="getBmiData(form.tinggi_badan, form.berat_badan).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                            </div>

                            <!-- Vital signs Tambahan (Suhu, Nadi, Respirasi, Skala Nyeri) untuk Pemeriksaan Umum -->
                            <div v-if="!isScreening" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Suhu Tubuh (°C)</label>
                                    <InputNumber v-model="form.suhu" :minFractionDigits="1" placeholder="36.5" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Nadi (x/menit)</label>
                                    <InputNumber v-model="form.nadi" placeholder="80" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Respirasi (x/menit)</label>
                                    <InputNumber v-model="form.respirasi" placeholder="20" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Skala Nyeri (0-10)</label>
                                    <InputNumber v-model="form.skala_nyeri" :min="0" :max="10" placeholder="0" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2 KHUSUS SCREENING: LABORATERIUM & SKRINING -->
                    <div v-if="isScreening" class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                            <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center shrink-0">2</span>
                            <h3 class="font-bold text-slate-800 text-base">Pemeriksaan Laboratorium & Skrining</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Lingkar Perut -->
                            <div class="sm:col-span-2 bg-blue-50/50 p-4 rounded-xl border border-blue-200/70 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs text-slate-700">Lingkar Perut (cm) <span class="text-rose-500">*</span></label>
                                    <Tag
                                        v-if="getLpData(form.lingkar_perut, form.is_hamil, rekamMedis.pasien.jenis_kelamin).status !== '-'"
                                        :value="getLpData(form.lingkar_perut, form.is_hamil, rekamMedis.pasien.jenis_kelamin).status"
                                        :severity="getLpData(form.lingkar_perut, form.is_hamil, rekamMedis.pasien.jenis_kelamin).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                                <InputNumber v-model="form.lingkar_perut" placeholder="80" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                            </div>

                            <!-- Gula Darah -->
                            <div class="sm:col-span-2 bg-slate-50/90 p-4 rounded-xl border border-slate-200/80 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs text-slate-700">Gula Darah (mg/dL) <span class="text-rose-500">*</span></label>
                                    <Tag
                                        v-if="getGdCategory(form.gula_darah, form.jenis_gula_darah).status !== '-'"
                                        :value="getGdCategory(form.gula_darah, form.jenis_gula_darah).status"
                                        :severity="getGdCategory(form.gula_darah, form.jenis_gula_darah).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <Select
                                        v-model="form.jenis_gula_darah"
                                        :options="[
                                            {label: 'Puasa (GDP)', value: 'puasa'},
                                            {label: 'Sewaktu (GDS)', value: 'sewaktu'}
                                        ]"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Pilih Jenis Gula Darah"
                                        class="w-full !rounded-xl !border-slate-300"
                                    />
                                    <InputNumber v-model="form.gula_darah" placeholder="100" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                                </div>
                            </div>

                            <!-- Asam Urat -->
                            <div class="flex flex-col gap-1.5 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs text-slate-700">Asam Urat (mg/dL)</label>
                                    <Tag
                                        v-if="getAuCategory(form.asam_urat, rekamMedis.pasien.jenis_kelamin).status !== '-'"
                                        :value="getAuCategory(form.asam_urat, rekamMedis.pasien.jenis_kelamin).status"
                                        :severity="getAuCategory(form.asam_urat, rekamMedis.pasien.jenis_kelamin).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                                <InputNumber v-model="form.asam_urat" :minFractionDigits="1" placeholder="5.5" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                            </div>

                            <!-- Kolesterol -->
                            <div class="flex flex-col gap-1.5 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs text-slate-700">Kolesterol (mg/dL)</label>
                                    <Tag
                                        v-if="getCholCategory(form.kolesterol).status !== '-'"
                                        :value="getCholCategory(form.kolesterol).status"
                                        :severity="getCholCategory(form.kolesterol).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                                <InputNumber v-model="form.kolesterol" placeholder="150" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                            </div>

                            <!-- Hemoglobin -->
                            <div class="flex flex-col gap-1.5 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                                <div class="flex items-center justify-between">
                                    <label class="font-bold text-xs text-slate-700">Hemoglobin (g/dL)</label>
                                    <Tag
                                        v-if="getHbCategory(form.hemoglobin).status !== '-'"
                                        :value="getHbCategory(form.hemoglobin).status"
                                        :severity="getHbCategory(form.hemoglobin).isCritical ? 'danger' : 'success'"
                                        class="!text-[10px]"
                                    />
                                </div>
                                <InputNumber v-model="form.hemoglobin" :minFractionDigits="1" placeholder="14.0" class="w-full" :inputClass="'!rounded-xl !border-slate-300'" />
                            </div>

                            <!-- Buta Warna -->
                            <div class="flex flex-col gap-1.5 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/70">
                                <label class="font-bold text-xs text-slate-700">Buta Warna</label>
                                <Select
                                    v-model="form.buta_warna"
                                    :options="[
                                        {label: 'Tidak (Normal)', value: 'Tidak'},
                                        {label: 'Ya - Total', value: 'Ya (Total)'},
                                        {label: 'Ya - Parsial', value: 'Ya (Parsial)'}
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih Hasil Buta Warna"
                                    class="w-full !rounded-xl !border-slate-300"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2 KHUSUS PEMERIKSAAN UMUM: ANAMNESIS & KELUHAN PASIEN -->
                    <div v-if="!isScreening" class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                            <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 font-bold text-xs flex items-center justify-center shrink-0">2</span>
                            <h3 class="font-bold text-slate-800 text-base">Anamnesis & Keluhan Pasien</h3>
                        </div>

                        <div class="space-y-4">
                            <!-- Kondisi Khusus Pasien Wanita -->
                            <div v-if="rekamMedis.pasien.jenis_kelamin === 'P'" class="p-4 bg-pink-50/70 rounded-xl border border-pink-200/80 flex items-center gap-6">
                                <span class="text-xs font-bold text-pink-900">Kondisi Khusus:</span>
                                <label class="flex items-center gap-2 cursor-pointer font-medium text-xs text-slate-800">
                                    <input type="checkbox" v-model="form.is_hamil" @change="form.is_hamil ? form.is_menyusui = false : null" class="w-4 h-4 text-emerald-600 rounded">
                                    Hamil
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-medium text-xs text-slate-800">
                                    <input type="checkbox" v-model="form.is_menyusui" @change="form.is_menyusui ? form.is_hamil = false : null" class="w-4 h-4 text-emerald-600 rounded">
                                    Menyusui
                                </label>
                            </div>

                            <!-- Keluhan Utama (Wajib untuk Berobat) -->
                            <div class="flex flex-col gap-1.5">
                                <label class="font-bold text-xs text-slate-700">
                                    Keluhan Utama <span class="text-rose-500">*</span>
                                </label>
                                <Textarea v-model="form.keluhan_utama" rows="2" autoResize placeholder="Keluhan yang dirasakan pasien saat ini..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                <small v-if="form.errors.keluhan_utama" class="text-red-500">{{ form.errors.keluhan_utama }}</small>
                            </div>

                            <!-- Riwayat Penyakit Sekarang & Dahulu -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Riwayat Penyakit Sekarang</label>
                                    <Textarea v-model="form.riwayat_penyakit_sekarang" rows="2" autoResize placeholder="Perkembangan keluhan saat ini..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Riwayat Penyakit Dahulu</label>
                                    <Textarea v-model="form.riwayat_penyakit_dahulu" rows="2" autoResize placeholder="Riwayat penyakit terdahulu..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                </div>
                            </div>

                            <!-- Riwayat Alergi, Obat, & Keluarga -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Riwayat Alergi</label>
                                    <Textarea v-model="form.riwayat_alergi" rows="2" autoResize placeholder="Alergi obat / makanan / lingkungan..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Riwayat Obat</label>
                                    <Textarea v-model="form.riwayat_obat" rows="2" autoResize placeholder="Obat yang sedang rutin dikonsumsi..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="font-bold text-xs text-slate-700">Riwayat Keluarga</label>
                                    <Textarea v-model="form.riwayat_keluarga" rows="2" autoResize placeholder="Riwayat penyakit keluarga..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3 KHUSUS SCREENING: TINDAK LANJUT SKRINING -->
                    <div v-if="isScreening" class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                            <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 font-bold text-xs flex items-center justify-center shrink-0">3</span>
                            <h3 class="font-bold text-slate-800 text-base">Tindak Lanjut Skrining</h3>
                        </div>

                        <div class="space-y-4">
                            <!-- Kondisi Khusus Pasien Wanita -->
                            <div v-if="rekamMedis.pasien.jenis_kelamin === 'P'" class="p-4 bg-pink-50/70 rounded-xl border border-pink-200/80 flex items-center gap-6">
                                <span class="text-xs font-bold text-pink-900">Kondisi Khusus:</span>
                                <label class="flex items-center gap-2 cursor-pointer font-medium text-xs text-slate-800">
                                    <input type="checkbox" v-model="form.is_hamil" @change="form.is_hamil ? form.is_menyusui = false : null" class="w-4 h-4 text-emerald-600 rounded">
                                    Hamil
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-medium text-xs text-slate-800">
                                    <input type="checkbox" v-model="form.is_menyusui" @change="form.is_menyusui ? form.is_hamil = false : null" class="w-4 h-4 text-emerald-600 rounded">
                                    Menyusui
                                </label>
                            </div>

                            <!-- Tindak Lanjut Skrining -->
                            <div class="flex flex-col gap-1.5">
                                <label class="font-bold text-xs text-slate-700">Tindak Lanjut Skrining</label>
                                <Select
                                    v-model="form.tindak_lanjut"
                                    :options="[
                                        {label: 'Rawat Jalan', value: 'rawat_jalan'},
                                        {label: 'Rujuk Faskes 1', value: 'rujuk'},
                                        {label: 'Edukasi', value: 'edukasi'}
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih Tindak Lanjut"
                                    class="w-full !rounded-xl !border-slate-300"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3 KHUSUS PEMERIKSAAN UMUM: ASUHAN KEPERAWATAN (ASKEP - DIPISAH SETELAH DOKTER PERIKSA) -->
                    <div v-if="!isScreening && ['sedang_diperiksa', 'selesai'].includes(rekamMedis.status)" class="bg-white border border-slate-200/90 rounded-2xl p-5 md:p-6 shadow-xs space-y-5">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs flex items-center justify-center shrink-0">3</span>
                            <h3 class="font-bold text-slate-800 text-base">Asuhan Keperawatan (Askep)</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-700">Diagnosa Keperawatan</label>
                                <Textarea v-model="form.diagnosa_keperawatan" rows="2" autoResize placeholder="Diagnosa keperawatan..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-700">Intervensi Keperawatan</label>
                                <Textarea v-model="form.intervensi_keperawatan" rows="2" autoResize placeholder="Rencana tindakan..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-700">Implementasi Keperawatan</label>
                                <Textarea v-model="form.implementasi_keperawatan" rows="2" autoResize placeholder="Tindakan yang telah dilakukan..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-bold text-slate-700">Evaluasi Keperawatan (SOAP)</label>
                                <Textarea v-model="form.evaluasi_keperawatan" rows="2" autoResize placeholder="S: ... O: ... A: ... P: ..." class="w-full !rounded-xl !border-slate-300 !resize-none" />
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS FOOTER -->
                    <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
                        <Link
                            :href="route('perawat.antrian')"
                            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold hover:bg-slate-50 transition-all text-xs flex items-center gap-2"
                        >
                            <i class="pi pi-arrow-left"></i>
                            Batal / Kembali
                        </Link>
                        <div class="flex items-center gap-3">
                            <Button
                                label="Simpan Sebagai Draf"
                                icon="pi pi-save"
                                severity="secondary"
                                outlined
                                class="!rounded-xl !text-xs font-bold"
                                @click="submitAnamnesis('draft')"
                            />
                            <Button
                                :label="isScreening ? 'Simpan Data Screening' : (['sedang_diperiksa', 'selesai'].includes(rekamMedis.status) ? 'Simpan Askep' : 'Simpan & Lanjut ke Dokter')"
                                icon="pi pi-check-circle"
                                severity="success"
                                class="!rounded-xl !text-xs font-bold shadow-sm hover:shadow-md transition-all"
                                @click="submitAnamnesis('lanjut')"
                            />
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: RIWAYAT SKRINING SEBELUMNYA (KHUSUS SCREENING) -->
                <div v-if="isScreening" class="space-y-6">
                    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 shadow-xs sticky top-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2 font-bold text-slate-800 text-base">
                                <i class="pi pi-history text-emerald-600 text-lg"></i>
                                <span>Riwayat Skrining</span>
                            </div>
                            <Tag
                                :value="`${riwayatScreening?.length || 0} Records`"
                                severity="success"
                                class="!text-[10px] !px-2"
                            />
                        </div>

                        <p class="text-xs text-slate-500 font-medium">
                            Daftar <strong>5 hasil pemeriksaan skrining terakhir</strong> milik pasien <strong class="text-slate-900">{{ rekamMedis.pasien.nama }}</strong>:
                        </p>

                        <!-- List Cards Riwayat -->
                        <div v-if="riwayatScreening && riwayatScreening.length > 0" class="space-y-4 max-h-[700px] overflow-y-auto pr-1">
                            <div
                                v-for="item in riwayatScreening"
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
                                    <span class="text-[10px] text-slate-400 font-mono font-semibold bg-white px-2 py-0.5 rounded border border-slate-200">{{ item.nomor_kunjungan }}</span>
                                </div>

                                <div v-if="item.anamnesis" class="space-y-2 text-xs">
                                    <!-- Vital signs summary -->
                                    <div class="grid grid-cols-2 gap-2 bg-white p-2.5 rounded-lg border border-slate-200/60 shadow-2xs">
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">Tekanan Darah</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.tekanan_darah || '-' }} mmHg</span>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400 block font-semibold">Tinggi / Berat</span>
                                            <span class="font-bold text-slate-800">
                                                {{ item.anamnesis.tinggi_badan || '-' }} cm / {{ item.anamnesis.berat_badan || '-' }} kg
                                            </span>
                                        </div>
                                    </div>

                                    <!-- IMT & LP -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[10px] text-slate-500 font-semibold">IMT</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-slate-800">
                                                    {{ getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).value }}
                                                </span>
                                                <Tag
                                                    v-if="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).category !== '-'"
                                                    :value="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).category"
                                                    :severity="getBmiData(item.anamnesis.tinggi_badan, item.anamnesis.berat_badan).isCritical ? 'danger' : 'success'"
                                                    class="!text-[8px] !px-1"
                                                />
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[10px] text-slate-500 font-semibold">Lingkar Perut</span>
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-slate-800">
                                                    {{ item.anamnesis.lingkar_perut ? `${item.anamnesis.lingkar_perut} cm` : '-' }}
                                                </span>
                                                <Tag
                                                    v-if="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin).status !== '-'"
                                                    :value="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin).status"
                                                    :severity="getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin).status === 'Hamil' ? 'info' : (getLpData(item.anamnesis.lingkar_perut, item.anamnesis.is_hamil, item.pasien?.jenis_kelamin).isCritical ? 'danger' : 'success')"
                                                    class="!text-[8px] !px-1"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Lab results grid -->
                                    <div class="bg-blue-50/60 p-2.5 rounded-lg border border-blue-100 space-y-1.5 text-[11px]">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 font-medium">Gula Darah ({{ item.anamnesis.jenis_gula_darah || 'sewaktu' }}):</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.gula_darah ? `${item.anamnesis.gula_darah} mg/dL` : '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 font-medium">Asam Urat:</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.asam_urat ? `${item.anamnesis.asam_urat} mg/dL` : '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 font-medium">Kolesterol:</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.kolesterol ? `${item.anamnesis.kolesterol} mg/dL` : '-' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-600 font-medium">Hemoglobin:</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.hemoglobin ? `${item.anamnesis.hemoglobin} g/dL` : '-' }}</span>
                                        </div>
                                        <div v-if="item.anamnesis.buta_warna" class="flex items-center justify-between pt-1 border-t border-blue-100">
                                            <span class="text-slate-600 font-medium">Buta Warna:</span>
                                            <span class="font-bold text-slate-800">{{ item.anamnesis.buta_warna }}</span>
                                        </div>
                                    </div>

                                    <!-- Tindak Lanjut -->
                                    <div v-if="item.anamnesis.tindak_lanjut || item.anamnesis.keterangan_tindak_lanjut" class="pt-1">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Tindak Lanjut</span>
                                        <p class="text-[11px] text-slate-700 italic">
                                            {{ formatTindakLanjut(item.anamnesis.tindak_lanjut) }}
                                            <span v-if="item.anamnesis.keterangan_tindak_lanjut"> - {{ item.anamnesis.keterangan_tindak_lanjut }}</span>
                                        </p>
                                    </div>

                                    <div v-if="item.perawat?.name" class="text-[10px] text-slate-400 text-right pt-1">
                                        Pemeriksa: {{ item.perawat.name }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="py-8 text-center bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <i class="pi pi-folder-open text-slate-300 text-3xl mb-2 block"></i>
                            <p class="text-xs text-slate-500 font-medium">Belum ada riwayat skrining sebelumnya</p>
                            <p class="text-[10px] text-slate-400 mt-1">Data skrining kali ini akan menjadi riwayat pertama pasien.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
