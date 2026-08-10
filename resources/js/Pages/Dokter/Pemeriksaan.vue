<script setup lang="ts">
import { ref, computed } from 'vue';
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

interface Props {
    rekamMedis: RekamMedisDetail;
    obats: Obat[];
    tindakans: Tindakan[];
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
    tujuan_rujukan: 'Puskesmas Karang Joang',
    catatan_rujukan: 'Mohon untuk dilakukan pemeriksaan/perawatan/penatalaksanaan lebih lanjut',
    jumlah_hari_istirahat: 1,
    tanggal_mulai: null as Date | null,
    tanggal_selesai: null as Date | null,
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
];

const icd10List = [
    "A01.0 - Demam tifoid (Typhoid fever)",
    "A09 - Diare dan gastroenteritis oleh penyebab infeksi presumtif",
    "A90 - Demam dengue (Dengue fever)",
    "B01 - Varisela (Cacar air)",
    "E11 - Diabetes mellitus tipe 2",
    "E78.5 - Hiperlipidemia, tidak spesifik",
    "H10 - Konjungtivitis",
    "I10 - Hipertensi esensial (primer)",
    "J00 - Nasofaringitis akut (common cold)",
    "J01 - Sinusitis akut",
    "J02 - Faringitis akut",
    "J03 - Tonsilitis akut",
    "J06 - Infeksi saluran pernapasan atas akut (ISPA) multiple/tidak spesifik",
    "J44.9 - Penyakit paru obstruktif kronik (PPOK), tidak spesifik",
    "J45 - Asma",
    "K02 - Karies gigi",
    "K04 - Penyakit pulpa dan jaringan periapikal",
    "K05 - Gingivitis dan penyakit periodontal",
    "K29.7 - Gastritis, tidak spesifik",
    "K30 - Dispepsia",
    "L20 - Dermatitis atopik",
    "L23 - Dermatitis kontak alergi",
    "M15 - Poliartrosis",
    "M19.9 - Artrosis, tidak spesifik",
    "M54.5 - Low back pain (Nyeri punggung bawah)",
    "M79.1 - Myalgia (Nyeri otot)",
    "N39.0 - Infeksi saluran kemih (ISK), lokasi tidak spesifik",
    "R10 - Nyeri perut dan panggul",
    "R42 - Pusing dan giddiness (Vertigo)",
    "R50.9 - Demam, tidak spesifik (Fever, unspecified)",
    "R51 - Sakit kepala (Headache)",
    "Z00.0 - Pemeriksaan medis umum"
];

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

const jenisSuratOptions = [
    { label: 'Surat Keterangan Sehat', value: 'surat_sehat' },
    { label: 'Surat Keterangan Sakit', value: 'surat_sakit' },
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
                                    <label class="font-semibold text-sm text-gray-700">Diagnosis Utama <span class="text-red-500">*</span></label>
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
                                    <label class="font-semibold text-sm text-gray-700">Kode ICD-10</label>
                                    <InputText
                                        v-model="form.kode_icd10"
                                        placeholder="Contoh: J00"
                                        class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30"
                                        :class="{ 'p-invalid': form.errors.kode_icd10 }"
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
                                        Buat Surat Keterangan Dokter (Sehat / Sakit)
                                    </label>
                                </div>

                                <div v-if="form.buat_surat_keterangan" class="pt-2 space-y-4 border-t border-amber-200/60">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                        <div class="flex flex-col gap-2">
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
                                            placeholder="Mohon untuk dilakukan pemeriksaan/perawatan/penatalaksanaan lebih lanjut"
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
    </AppLayout>
</template>
