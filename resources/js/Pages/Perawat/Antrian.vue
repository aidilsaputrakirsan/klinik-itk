<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';
import { router } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useConfirm } from 'primevue/useconfirm';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';

interface AntrianItem {
    id: number;
    nomor_kunjungan: string;
    catatan: string;
    tanggal_kunjungan: string;
    status: string;
    pasien: {
        id: number;
        nomor_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
        alamat: string;
    };
    jenis_layanan?: string;
    anamnesis?: any;
}

interface Props {
    antrian: AntrianItem[];
    antrian_terlewat?: AntrianItem[];
    antrian_selesai?: AntrianItem[];
    pasiens?: Array<{ id: number, nama: string, nomor_rm: string }>;
    filters?: {
        filter_waktu: string;
        custom_date: string;
        searchTerlewat?: string;
        tanggal_terlewat?: string;
        is_filtered?: boolean;
        tipe_pasien?: string;
        jenis_layanan?: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();
const confirm = useConfirm();
const page = usePage<any>();
const currentUser = page.props.auth?.user;
const canManageAntrian = ['superadmin', 'admin', 'perawat'].includes(currentUser?.role);

const selectedFilterWaktu = ref(props.filters?.filter_waktu || 'semua');
const customDate = ref<Date | null>(props.filters?.custom_date ? new Date(props.filters.custom_date) : null);
const filterTipePasien = ref(props.filters?.tipe_pasien || '');
const filterJenisLayanan = ref(props.filters?.jenis_layanan || '');

const activeTab = ref(props.filters?.is_filtered ? '1' : '0');
const searchTerlewat = ref(props.filters?.searchTerlewat || '');
const filterTanggalTerlewat = ref(props.filters?.tanggal_terlewat ? new Date(props.filters.tanggal_terlewat) : null);

const doFilterTerlewat = () => {
    const params: any = { filter_waktu: selectedFilterWaktu.value };
    
    if (selectedFilterWaktu.value === 'custom' && customDate.value) {
        const d = customDate.value;
        params.custom_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }

    if (searchTerlewat.value) params.searchTerlewat = searchTerlewat.value;
    if (filterTanggalTerlewat.value) {
        const d = filterTanggalTerlewat.value;
        const pad = (n: number) => String(n).padStart(2, '0');
        params.tanggal_terlewat = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    }
    
    if (params.searchTerlewat || params.tanggal_terlewat) {
        params.is_filtered = 1;
    }
    
    router.get(route('perawat.antrian'), params, { 
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const timeOptions = [
    { label: 'Semua Antrian', value: 'semua' },
    { label: 'Hari Ini', value: 'hari_ini' },
    { label: 'Minggu Ini', value: 'minggu_ini' },
    { label: 'Bulan Ini', value: 'bulan_ini' },
    { label: 'Kustom Tanggal', value: 'custom' }
];

const applyFilter = () => {
    let payload: any = { filter_waktu: selectedFilterWaktu.value };
    if (selectedFilterWaktu.value === 'custom' && customDate.value) {
        // Format to YYYY-MM-DD
        const d = customDate.value;
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        payload.custom_date = `${year}-${month}-${day}`;
    }
    if (searchTerlewat.value) payload.searchTerlewat = searchTerlewat.value;
    if (filterTanggalTerlewat.value) {
        const d = filterTanggalTerlewat.value;
        payload.tanggal_terlewat = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }
    if (filterTipePasien.value) payload.tipe_pasien = filterTipePasien.value;
    if (filterJenisLayanan.value) payload.jenis_layanan = filterJenisLayanan.value;
    if (props.filters?.is_filtered) payload.is_filtered = 1;

    router.get(
        route('perawat.antrian'),
        payload,
        { preserveState: true, preserveScroll: true, replace: true }
    );
};

watch(selectedFilterWaktu, (newValue) => {
    if (newValue === 'custom' && !customDate.value) {
        return; // wait for date selection
    }
    applyFilter();
});

watch(customDate, (newValue) => {
    if (selectedFilterWaktu.value === 'custom') {
        applyFilter();
    }
});

watch([filterTipePasien, filterJenisLayanan], () => {
    applyFilter();
});

const showAnamnesisDialog = ref(false);
const selectedPasien = ref<AntrianItem | null>(null);

const form = useForm({
    rekam_medis_id: 0,
    tekanan_darah_sistolik: null as number | null,
    tekanan_darah_diastolik: null as number | null,
    suhu: null as number | null,
    nadi: null as number | null,
    respirasi: null as number | null,
    tinggi_badan: null as number | null,
    berat_badan: null as number | null,
    keluhan_utama: '',
    riwayat_penyakit_sekarang: '',
    riwayat_penyakit_dahulu: '',
    riwayat_alergi: '',
    riwayat_obat: '',
    riwayat_keluarga: '',
    skala_nyeri: null as number | null,
    diagnosa_keperawatan: '',
    intervensi_keperawatan: '',
    implementasi_keperawatan: '',
    evaluasi_keperawatan: '',
    lingkar_perut: null as number | null,
    is_hamil: false,
    tindak_lanjut: '',
    keterangan_tindak_lanjut: '',
    gula_darah: null as number | null,
    asam_urat: null as number | null,
    kolesterol: null as number | null,
    hemoglobin: null as number | null,
});

const getBmiData = (tb: number | null | undefined, bb: number | null | undefined) => {
    if (!tb || !bb) return { value: '-', category: '-', isCritical: false };
    const h = tb / 100;
    const bmi = bb / (h * h);
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
    
    let isCritical = false;
    if (gender === 'L' && lp > 90) isCritical = true;
    if (gender === 'P' && lp > 80) isCritical = true;
    
    return { 
        value: lp, 
        status: isCritical ? 'Obesitas Sentral' : 'Normal',
        isCritical 
    };
};

const getTdCategory = (sys: number | null | undefined, dia: number | null | undefined) => {
    if (!sys || !dia) return { status: '-', isCritical: false };
    if (sys >= 160 || dia >= 100) return { status: 'Hipertensi Grade 2 (>160/100)', isCritical: true };
    if (sys >= 140 || dia >= 90) return { status: 'Hipertensi Grade 1 (140/90-159/99)', isCritical: true };
    if (sys >= 130 || dia >= 85) return { status: 'Prehipertensi (130/85-139/89)', isCritical: true };
    return { status: 'Normal (<129/84)', isCritical: false };
};

const getGdCategory = (gd: number | null | undefined) => {
    if (!gd) return { status: '-', isCritical: false };
    if (gd > 200) return { status: 'Hiperglikemia (GDS: >200)', isCritical: true };
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
    if (hb < 12) return { status: 'Anemia (< 12)', isCritical: true };
    return { status: 'Normal', isCritical: false };
};

const openAnamnesisDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    form.rekam_medis_id = item.id;
    
    if (item.anamnesis) {
        const td = item.anamnesis.tekanan_darah?.split('/') || [];
        form.tekanan_darah_sistolik = td[0] ? parseInt(td[0]) : null;
        form.tekanan_darah_diastolik = td[1] ? parseInt(td[1]) : null;
        form.suhu = item.anamnesis.suhu;
        form.nadi = item.anamnesis.nadi;
        form.respirasi = item.anamnesis.respirasi;
        form.tinggi_badan = item.anamnesis.tinggi_badan;
        form.berat_badan = item.anamnesis.berat_badan;
        form.keluhan_utama = item.anamnesis.keluhan_utama || '';
        form.riwayat_penyakit_sekarang = item.anamnesis.riwayat_penyakit_sekarang || '';
        form.riwayat_penyakit_dahulu = item.anamnesis.riwayat_penyakit_dahulu || '';
        form.riwayat_alergi = item.anamnesis.riwayat_alergi || '';
        form.riwayat_obat = item.anamnesis.riwayat_obat || '';
        form.riwayat_keluarga = item.anamnesis.riwayat_keluarga || '';
        form.skala_nyeri = item.anamnesis.skala_nyeri;
        form.diagnosa_keperawatan = item.anamnesis.diagnosa_keperawatan || '';
        form.intervensi_keperawatan = item.anamnesis.intervensi_keperawatan || '';
        form.implementasi_keperawatan = item.anamnesis.implementasi_keperawatan || '';
        form.evaluasi_keperawatan = item.anamnesis.evaluasi_keperawatan || '';
        form.lingkar_perut = item.anamnesis.lingkar_perut ? Number(item.anamnesis.lingkar_perut) : null;
        form.is_hamil = Boolean(item.anamnesis.is_hamil);
        form.tindak_lanjut = item.anamnesis.tindak_lanjut || '';
        form.keterangan_tindak_lanjut = item.anamnesis.keterangan_tindak_lanjut || '';
        form.gula_darah = item.anamnesis.gula_darah ? Number(item.anamnesis.gula_darah) : null;
        form.asam_urat = item.anamnesis.asam_urat ? Number(item.anamnesis.asam_urat) : null;
        form.kolesterol = item.anamnesis.kolesterol ? Number(item.anamnesis.kolesterol) : null;
        form.hemoglobin = item.anamnesis.hemoglobin ? Number(item.anamnesis.hemoglobin) : null;
    } else {
        resetForm();
        form.rekam_medis_id = item.id; // re-set because resetForm clears it
    }
    
    showAnamnesisDialog.value = true;
};

const closeDialog = () => {
    showAnamnesisDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

const submitAnamnesis = (action: 'draft' | 'lanjut' = 'lanjut') => {
    // Auto-fill keluhan utama for screening if empty to pass validation
    if (selectedPasien.value?.jenis_layanan === 'screening' && !form.keluhan_utama) {
        form.keluhan_utama = 'Pemeriksaan Screening (Otomatis)';
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
            closeDialog();
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
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

// -- CRUD ANTRIAN (ADMIN/SUPERADMIN) --
const showCrudDialog = ref(false);
const crudMode = ref<'create' | 'edit'>('create');
const selectedRekamMedisId = ref<number | null>(null);

const getClientTime = () => {
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())} ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
};

const formAntrian = useForm({
    pasien_id: null as number | null,
    tanggal_kunjungan: new Date(),
    jenis_layanan: 'berobat',
    catatan: '',
    client_time: ''
});

const layananOptions = [
    { label: 'Pemeriksaan Umum', value: 'berobat' },
    { label: 'Surat Sehat', value: 'surat_sehat' },
    { label: 'Screening', value: 'screening' }
];

const openCreateDialog = () => {
    crudMode.value = 'create';
    formAntrian.reset();
    formAntrian.tanggal_kunjungan = new Date();
    formAntrian.client_time = getClientTime();
    formAntrian.jenis_layanan = 'berobat';
    showCrudDialog.value = true;
};

const openEditDialog = (item: AntrianItem) => {
    crudMode.value = 'edit';
    selectedRekamMedisId.value = item.id;
    formAntrian.pasien_id = item.pasien.id;
    formAntrian.tanggal_kunjungan = new Date(item.tanggal_kunjungan);
    formAntrian.jenis_layanan = item.nomor_kunjungan.includes('KNJ') ? 'berobat' : 'berobat';
    formAntrian.catatan = item.catatan || '';
    showCrudDialog.value = true;
};

const submitAntrian = () => {
    if (crudMode.value === 'create') {
        formAntrian.client_time = getClientTime();
        formAntrian.post(route('antrian.store'), {
            onSuccess: () => {
                showCrudDialog.value = false;
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Antrian ditambahkan', life: 3000 });
            }
        });
    } else if (selectedRekamMedisId.value) {
        formAntrian.put(route('antrian.update', { rekamMedis: selectedRekamMedisId.value }), {
            onSuccess: () => {
                showCrudDialog.value = false;
                toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Antrian diperbarui', life: 3000 });
            }
        });
    }
};

const deleteAntrian = (item: AntrianItem) => {
    confirm.require({
        message: `Apakah Anda yakin ingin membatalkan/menghapus antrian untuk pasien ${item.pasien?.nama}?`,
        header: 'Konfirmasi Penghapusan',
        icon: 'pi pi-exclamation-triangle',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(route('antrian.destroy', item.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Dihapus', detail: 'Antrian berhasil dibatalkan', life: 3000 });
                }
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
    <Head title="Antrian Pasien" />
    <AppLayout>
        <template #header>Antrian Pasien - Anamnesis</template>

        <div class="space-y-6">
            <Tabs v-model:value="activeTab" class="bg-transparent">
                <TabList class="!bg-white/80 !backdrop-blur-md !border-b !border-gray-200 !rounded-t-xl overflow-hidden shadow-sm sticky top-0 z-10">
                    <Tab value="0" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '0' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-check-circle text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '0' ? 'text-emerald-700' : 'text-gray-500'">Antrian Aktif</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ antrian.length }} Pasien Menunggu</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="1" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '1' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-calendar-times text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '1' ? 'text-orange-700' : 'text-gray-500'">Antrian Terlewat</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ antrian_terlewat?.length || 0 }} Terlewat</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="2" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '2' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-check-square text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '2' ? 'text-blue-700' : 'text-gray-500'">Selesai Diperiksa</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ antrian_selesai?.length || 0 }} Pasien</span>
                            </div>
                        </div>
                    </Tab>
                </TabList>

                <TabPanels class="!bg-transparent !p-0 !mt-4">
                    <TabPanel value="0" class="!p-0">
                        <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                            <template #content>
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                Daftar Antrian Pasien
                            </h3>
                            <Tag :value="`${antrian.length} Pasien`" severity="success" class="!rounded-lg !px-3" />
                        </div>

                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-2xl shadow-sm space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <Button
                                    v-if="canManageAntrian"
                                    label="Tambah Jadwal"
                                    icon="pi pi-plus"
                                    severity="success"
                                    class="!rounded-xl !text-xs font-bold shadow-sm"
                                    @click="openCreateDialog"
                                />
                                
                                <div class="flex items-center gap-2 flex-1 min-w-[200px]">
                                    <div class="flex flex-col gap-1.5 flex-1 max-w-[250px]">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Filter Waktu</span>
                                        <div class="flex gap-2">
                                            <Select
                                                v-model="selectedFilterWaktu"
                                                :options="timeOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                placeholder="Pilih Waktu"
                                                class="!border-gray-200 !rounded-xl !text-xs flex-1 shadow-sm focus:!ring-emerald-500/20"
                                            >
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex items-center gap-2">
                                                        <i class="pi pi-calendar text-emerald-500 text-[10px]"></i>
                                                        <span>{{ timeOptions.find(o => o.value === slotProps.value)?.label }}</span>
                                                    </div>
                                                </template>
                                            </Select>

                                            <div v-if="selectedFilterWaktu === 'custom'" class="w-40">
                                                <DatePicker
                                                    v-model="customDate"
                                                    dateFormat="dd/mm/yy"
                                                    placeholder="Pilih Tanggal"
                                                    class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                                    inputClass="!py-2 !px-3 !text-xs"
                                                    :showIcon="true"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1.5 w-40">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Jenis Layanan</span>
                                        <Select
                                            v-model="filterJenisLayanan"
                                            :options="[{label:'Semua', value:''}, ...layananOptions]"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Semua"
                                            class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                        />
                                    </div>
                                    <div class="flex flex-col gap-1.5 w-40">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tipe Pasien</span>
                                        <Select
                                            v-model="filterTipePasien"
                                            :options="[
                                                {label:'Semua', value:''},
                                                {label:'Mahasiswa', value:'mahasiswa'},
                                                {label:'Dosen', value:'dosen'},
                                                {label:'Tendik', value:'tendik'}
                                            ]"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Semua"
                                            class="!border-gray-200 !rounded-xl !text-xs w-full shadow-sm"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <DataTable
                        :value="antrian"
                        :paginator="antrian.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada pasien dalam antrian"
                    >
                        <Column header="No" style="width: 60px">
                            <template #body="{ index }">
                                {{ index + 1 }}
                            </template>
                        </Column>
                        <Column field="catatan" header="Catatan">
                            <template #body="{ data }">
                                <span class="text-gray-600 text-sm italic">{{ data.catatan || '-' }}</span>
                            </template>
                        </Column>
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-[11px] text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100/50 shadow-sm">{{ data.nomor_kunjungan }}</span>
                            </template>
                        </Column>
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div class="flex flex-col gap-1.5">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ data.pasien?.nama || 'Pasien Tidak Diketahui' }}</p>
                                        <p class="text-[11px] text-gray-500" v-if="data.pasien">
                                            {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                            {{ getAge(data.pasien.tanggal_lahir) }} thn
                                        </p>
                                        <p class="text-[11px] text-red-400 italic" v-else>
                                            Data pasien telah dihapus
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1" v-if="data.pasien">
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-100 text-emerald-800">
                                            {{ getLayananLabel(data.jenis_layanan) }}
                                        </span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-100 text-blue-800">
                                            {{ getTipePasienLabel(data.pasien.tipe_pasien) }}
                                        </span>
                                        <span v-if="data.status === 'proses_anamnesis'" class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-amber-100 text-amber-800">
                                            Draf (Belum Lanjut)
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Jadwal Kunjungan" style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ new Date(data.tanggal_kunjungan).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <div class="flex items-center gap-1 text-emerald-600 text-[10px]">
                                        <i class="pi pi-clock"></i>
                                        <span>{{ new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WITA</span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 250px" class="text-center">
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <Button
                                        label="Anamnesis"
                                        icon="pi pi-pencil"
                                        severity="success"
                                        class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold"
                                        @click="openAnamnesisDialog(data)"
                                    />
                                    <Button
                                        v-if="canManageAntrian"
                                        icon="pi pi-file-edit"
                                        severity="info"
                                        class="!rounded-xl !w-9 !h-9 shadow-sm"
                                        v-tooltip.top="'Update Antrian'"
                                        @click="openEditDialog(data)"
                                    />
                                    <Button
                                        v-if="canManageAntrian"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        class="!rounded-xl !w-9 !h-9 shadow-sm"
                                        v-tooltip.top="'Batalkan / Hapus Antrian'"
                                        @click="deleteAntrian(data)"
                                    />
                                </div>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </TabPanel>

            <TabPanel value="1" class="!p-0">
                <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                    <template #content>
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                                <span class="w-2 h-6 bg-orange-500 rounded-full"></span>
                                Daftar Antrian Terlewat
                            </h3>
                            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Nama / Nomor Kunjungan</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-search text-orange-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <InputText
                                            v-model="searchTerlewat"
                                            placeholder="Ketik di sini..."
                                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                            @keyup.enter="doFilterTerlewat"
                                        />
                                    </InputGroup>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Filter Tanggal Jadwal</span>
                                        <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                                            <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                <i class="pi pi-calendar text-orange-500 text-[10px]"></i>
                                            </InputGroupAddon>
                                            <DatePicker 
                                                v-model="filterTanggalTerlewat" 
                                                dateFormat="dd/mm/yy"
                                                placeholder="Pilih Tanggal"
                                                class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                inputClass="!border-0 !p-0 !h-9 !text-xs"
                                                :showClear="true"
                                                @date-select="doFilterTerlewat"
                                                @clear="doFilterTerlewat"
                                            />
                                        </InputGroup>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-1">
                                    <Button 
                                        label="Tampilkan Pasien" 
                                        icon="pi pi-search" 
                                        severity="warn" 
                                        @click="doFilterTerlewat" 
                                        class="!rounded-xl flex-1 h-9 shadow-sm !text-[11px] font-bold transition-all hover:shadow-md hover:shadow-orange-100" 
                                    />
                                    <Button 
                                        v-if="searchTerlewat || filterTanggalTerlewat || props.filters?.is_filtered"
                                        icon="pi pi-refresh" 
                                        severity="secondary" 
                                        outlined
                                        class="!rounded-xl h-9 w-9"
                                        title="Reset"
                                        @click="() => { searchTerlewat = ''; filterTanggalTerlewat = null; doFilterTerlewat(); }"
                                    />
                                </div>
                            </div>
                        </div>

                        <DataTable
                            :value="antrian_terlewat"
                            :paginator="antrian_terlewat && antrian_terlewat.length > 10"
                            :rows="10"
                            dataKey="id"
                            responsiveLayout="scroll"
                            class="p-datatable-sm"
                            emptyMessage="Tidak ada antrian yang terlewat"
                        >
                            <Column header="No" style="width: 60px">
                                <template #body="{ index }">
                                    {{ index + 1 }}
                                </template>
                            </Column>
                            <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="font-mono text-[11px] text-orange-700 bg-orange-50 px-2 py-1 rounded border border-orange-100/50 shadow-sm">{{ data.nomor_kunjungan }}</span>
                                </template>
                            </Column>
                            <Column field="catatan" header="Catatan">
                                <template #body="{ data }">
                                    <span class="text-gray-600 text-sm italic">{{ data.catatan || '-' }}</span>
                                </template>
                            </Column>
                            <Column header="Pasien">
                                <template #body="{ data }">
                                    <div class="flex flex-col gap-1.5">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ data.pasien?.nama || 'Pasien Tidak Diketahui' }}</p>
                                            <p class="text-[11px] text-gray-500" v-if="data.pasien">
                                                {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                                {{ getAge(data.pasien.tanggal_lahir) }} thn
                                            </p>
                                            <p class="text-[11px] text-red-400 italic" v-else>
                                                Data pasien telah dihapus
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap gap-1" v-if="data.pasien">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-100 text-emerald-800">
                                                {{ getLayananLabel(data.jenis_layanan) }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-100 text-blue-800">
                                                {{ getTipePasienLabel(data.pasien.tipe_pasien) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Jadwal Kunjungan" style="width: 180px">
                                <template #body="{ data }">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-gray-700">
                                            {{ new Date(data.tanggal_kunjungan).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                        </span>
                                        <div class="flex items-center gap-1 text-orange-600 text-[10px]">
                                            <i class="pi pi-clock"></i>
                                            <span>{{ new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WITA</span>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Aksi" style="width: 250px" class="text-center">
                                <template #body="{ data }">
                                    <div class="flex gap-2 justify-center">
                                        <Button
                                            label="Anamnesis"
                                            icon="pi pi-pencil"
                                            severity="success"
                                            class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold"
                                            @click="openAnamnesisDialog(data)"
                                        />
                                        <Button
                                            v-if="canManageAntrian"
                                            icon="pi pi-file-edit"
                                            severity="info"
                                            class="!rounded-xl !w-9 !h-9 shadow-sm"
                                            v-tooltip.top="'Update Antrian'"
                                            @click="openEditDialog(data)"
                                        />
                                        <Button
                                            v-if="canManageAntrian"
                                            icon="pi pi-trash"
                                            severity="danger"
                                            class="!rounded-xl !w-9 !h-9 shadow-sm"
                                            v-tooltip.top="'Batalkan / Hapus Antrian'"
                                            @click="deleteAntrian(data)"
                                        />
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </TabPanel>

            <TabPanel value="2" class="!p-0">
                <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                    <template #content>
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                                <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                                Daftar Pasien Selesai Diperiksa Dokter
                            </h3>
                            <p class="text-xs text-gray-500 mb-4">Pasien di daftar ini sudah selesai diperiksa dokter dan membutuhkan pengisian Asuhan Keperawatan (Askep).</p>
                        </div>
                        <DataTable
                            :value="antrian_selesai"
                            :paginator="antrian_selesai && antrian_selesai.length > 10"
                            :rows="10"
                            dataKey="id"
                            responsiveLayout="scroll"
                            class="p-datatable-sm"
                            emptyMessage="Tidak ada pasien yang selesai diperiksa"
                        >
                            <Column header="No" style="width: 60px">
                                <template #body="{ index }">
                                    {{ index + 1 }}
                                </template>
                            </Column>
                            <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="font-mono text-[11px] text-blue-700 bg-blue-50 px-2 py-1 rounded border border-blue-100/50 shadow-sm">{{ data.nomor_kunjungan }}</span>
                                </template>
                            </Column>
                            <Column header="Pasien">
                                <template #body="{ data }">
                                    <div class="flex flex-col gap-1.5">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ data.pasien?.nama || 'Pasien Tidak Diketahui' }}</p>
                                            <p class="text-[11px] text-gray-500" v-if="data.pasien">
                                                {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                                {{ getAge(data.pasien.tanggal_lahir) }} thn
                                            </p>
                                        </div>
                                        <div class="flex flex-wrap gap-1" v-if="data.pasien">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-emerald-100 text-emerald-800">
                                                {{ getLayananLabel(data.jenis_layanan) }}
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium bg-blue-100 text-blue-800">
                                                {{ getTipePasienLabel(data.pasien.tipe_pasien) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </Column>
                            <Column header="Status" style="width: 120px">
                                <template #body="{ data }">
                                    <Tag :value="data.status" :severity="data.status === 'siap_dokter' ? 'info' : 'warn'" class="uppercase !text-[10px] !px-2" />
                                </template>
                            </Column>
                            <Column header="Aksi" style="width: 150px" class="text-center">
                                <template #body="{ data }">
                                    <Button
                                        :label="data.jenis_layanan === 'screening' ? 'Edit' : 'Isi Askep'"
                                        icon="pi pi-pencil"
                                        severity="info"
                                        class="!w-[105px] !rounded-xl !text-[11px] !py-2 shadow-sm hover:shadow-md transition-all font-bold"
                                        @click="openAnamnesisDialog(data)"
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </template>
                </Card>
            </TabPanel>
        </TabPanels>
    </Tabs>
</div>

        <!-- Dialog Anamnesis -->
        <Dialog
            v-model:visible="showAnamnesisDialog"
            modal
            header="Input Data Anamnesis"
            :style="{ width: '800px' }"
            :closable="true"
            @hide="closeDialog"
        >
            <div v-if="selectedPasien" class="space-y-3">
                <!-- Info Pasien -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Nama Pasien:</span>
                            <p class="font-medium">{{ selectedPasien.pasien?.nama || 'Data Dihapus' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Umur / JK:</span>
                            <p class="font-medium">
                                {{ getAge(selectedPasien.pasien?.tanggal_lahir) }} Thn / 
                                {{ selectedPasien.pasien?.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. RM:</span>
                            <p class="font-medium">{{ selectedPasien.pasien?.nomor_rm || '-' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500">Jenis Layanan:</span>
                            <p class="font-medium">
                                <Tag :value="getLayananLabel(selectedPasien.jenis_layanan || 'berobat')" severity="info" class="!text-[10px] uppercase" />
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-500">No. Kunjungan:</span>
                            <p class="font-medium">{{ selectedPasien.nomor_kunjungan }}</p>
                        </div>
                    </div>
                </div>

                <!-- Keluhan Utama (Required for Standard Anamnesis) -->
                <div v-if="selectedPasien.jenis_layanan !== 'screening'" class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Keluhan Utama <span class="text-red-500">*</span></label>
                    <Textarea
                        v-model="form.keluhan_utama"
                        rows="2"
                        placeholder="Keluhan yang dirasakan pasien saat ini"
                        :class="{ 'p-invalid': form.errors.keluhan_utama }"
                    />
                    <small v-if="form.errors.keluhan_utama" class="text-red-500">{{ form.errors.keluhan_utama }}</small>
                </div>

                <!-- Form Anamnesis - Vital Signs -->
                <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Tekanan Darah (mmHg)</label>
                        <div class="flex items-center">
                            <InputNumber
                                v-model="form.tekanan_darah_sistolik"
                                size="small"
                                placeholder="120"
                                :inputStyle="{ width: '80px', textAlign: 'center' }"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_sistolik }"
                            />
                            <span class="mx-3 font-bold text-gray-500 text-lg">/</span>
                            <InputNumber
                                v-model="form.tekanan_darah_diastolik"
                                size="small"
                                placeholder="80"
                                :inputStyle="{ width: '80px', textAlign: 'center' }"
                                :class="{ 'p-invalid': form.errors.tekanan_darah_diastolik }"
                            />
                        </div>
                        <small v-if="form.errors.tekanan_darah_sistolik" class="text-red-500">{{ form.errors.tekanan_darah_sistolik }}</small>
                        <small v-if="form.errors.tekanan_darah_diastolik" class="text-red-500">{{ form.errors.tekanan_darah_diastolik }}</small>
                        <Tag 
                            v-if="selectedPasien.jenis_layanan === 'screening' && getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).status !== '-'"
                            :value="getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).status"
                            :severity="getTdCategory(form.tekanan_darah_sistolik, form.tekanan_darah_diastolik).isCritical ? 'danger' : 'success'"
                            class="!text-[10px] mt-1 self-start"
                        />
                    </div>
                    <div v-if="selectedPasien.jenis_layanan !== 'screening'" class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Suhu Tubuh (°C)</label>
                        <InputNumber
                            v-model="form.suhu"
                            size="small"
                            :minFractionDigits="1"
                            :maxFractionDigits="1"
                            placeholder="36.5"
                            :class="{ 'p-invalid': form.errors.suhu }"
                        />
                        <small v-if="form.errors.suhu" class="text-red-500">{{ form.errors.suhu }}</small>
                    </div>
                    <div v-if="selectedPasien.jenis_layanan !== 'screening'" class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Nadi (x/menit)</label>
                        <InputNumber
                            v-model="form.nadi"
                            size="small"
                            placeholder="80"
                            :class="{ 'p-invalid': form.errors.nadi }"
                        />
                        <small v-if="form.errors.nadi" class="text-red-500">{{ form.errors.nadi }}</small>
                    </div>
                    <div v-if="selectedPasien.jenis_layanan !== 'screening'" class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Respirasi (x/menit)</label>
                        <InputNumber
                            v-model="form.respirasi"
                            size="small"
                            placeholder="20"
                            :class="{ 'p-invalid': form.errors.respirasi }"
                        />
                        <small v-if="form.errors.respirasi" class="text-red-500">{{ form.errors.respirasi }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Tinggi Badan (cm)</label>
                        <InputNumber
                            v-model="form.tinggi_badan"
                            size="small"
                            placeholder="170"
                            :class="{ 'p-invalid': form.errors.tinggi_badan }"
                        />
                        <small v-if="form.errors.tinggi_badan" class="text-red-500">{{ form.errors.tinggi_badan }}</small>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Berat Badan (kg)</label>
                        <InputNumber
                            v-model="form.berat_badan"
                            size="small"
                            :minFractionDigits="1"
                            placeholder="65.0"
                            :class="{ 'p-invalid': form.errors.berat_badan }"
                        />
                        <small v-if="form.errors.berat_badan" class="text-red-500">{{ form.errors.berat_badan }}</small>
                    </div>
                    <div v-if="selectedPasien.jenis_layanan !== 'screening'" class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Skala Nyeri (0-10)</label>
                        <InputNumber
                            v-model="form.skala_nyeri"
                            size="small"
                            placeholder="0"
                            :min="0"
                            :max="10"
                            showButtons
                            buttonLayout="horizontal"
                            :step="1"
                            decrementButtonClass="p-button-secondary p-button-sm"
                            incrementButtonClass="p-button-secondary p-button-sm"
                            decrementButtonIcon="pi pi-minus text-sm"
                            incrementButtonIcon="pi pi-plus text-sm"
                            :class="{ 'p-invalid': form.errors.skala_nyeri }"
                        />
                        <small v-if="form.errors.skala_nyeri" class="text-red-500">{{ form.errors.skala_nyeri }}</small>
                    </div>
                </div>

                <!-- Tampilan Kalkulasi Screening & Form Tambahan Screening -->
                <div v-if="selectedPasien.jenis_layanan === 'screening'" class="bg-blue-50/50 p-4 rounded-xl border border-blue-100 mb-2 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- BMI Calculation Display -->
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500 font-medium">Index Massa Tubuh (IMT)</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold" :class="{'text-red-600': getBmiData(form.tinggi_badan, form.berat_badan).isCritical}">
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

                        <!-- LP Calculation Display -->
                        <div class="flex flex-col gap-1">
                            <span class="text-xs text-gray-500 font-medium">Status Lingkar Perut</span>
                            <div class="flex items-center gap-2">
                                <span class="text-lg font-bold" :class="{'text-red-600': getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).isCritical}">
                                    {{ getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).value }} cm
                                </span>
                                <Tag 
                                    v-if="getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).status !== '-'"
                                    :value="getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).status"
                                    :severity="getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).isCritical ? 'danger' : (getLpData(form.lingkar_perut, form.is_hamil, selectedPasien.pasien?.jenis_kelamin).status === 'Hamil' ? 'info' : 'success')"
                                    class="!text-[10px]"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <!-- Lingkar Perut Input -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Lingkar Perut (cm) <span class="text-red-500">*</span></label>
                            <InputNumber
                                v-model="form.lingkar_perut"
                                size="small"
                                placeholder="80"
                                :class="{ 'p-invalid': form.errors.lingkar_perut }"
                            />
                            <small v-if="form.errors.lingkar_perut" class="text-red-500">{{ form.errors.lingkar_perut }}</small>
                        </div>
                        
                        <!-- Is Hamil Checkbox (Only for Females) -->
                        <div class="flex flex-col gap-1 justify-center" v-if="selectedPasien.pasien?.jenis_kelamin === 'P'">
                            <label class="flex items-center gap-2 cursor-pointer mt-4">
                                <input type="checkbox" v-model="form.is_hamil" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="text-sm font-medium text-gray-700">Pasien sedang hamil?</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <!-- Gula Darah Input -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Gula Darah (mg/dL)</label>
                            <InputNumber
                                v-model="form.gula_darah"
                                size="small"
                                placeholder="100"
                                :class="{ 'p-invalid': form.errors.gula_darah }"
                            />
                            <small v-if="form.errors.gula_darah" class="text-red-500">{{ form.errors.gula_darah }}</small>
                            <Tag 
                                v-if="getGdCategory(form.gula_darah).status !== '-'"
                                :value="getGdCategory(form.gula_darah).status"
                                :severity="getGdCategory(form.gula_darah).isCritical ? 'danger' : 'success'"
                                class="!text-[10px] self-start mt-1"
                            />
                        </div>

                        <!-- Asam Urat Input -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Asam Urat (mg/dL)</label>
                            <InputNumber
                                v-model="form.asam_urat"
                                size="small"
                                :minFractionDigits="1"
                                placeholder="5.5"
                                :class="{ 'p-invalid': form.errors.asam_urat }"
                            />
                            <small v-if="form.errors.asam_urat" class="text-red-500">{{ form.errors.asam_urat }}</small>
                            <Tag 
                                v-if="getAuCategory(form.asam_urat, selectedPasien.pasien?.jenis_kelamin).status !== '-'"
                                :value="getAuCategory(form.asam_urat, selectedPasien.pasien?.jenis_kelamin).status"
                                :severity="getAuCategory(form.asam_urat, selectedPasien.pasien?.jenis_kelamin).isCritical ? 'danger' : 'success'"
                                class="!text-[10px] self-start mt-1"
                            />
                        </div>

                        <!-- Kolesterol Input -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Kolesterol (mg/dL)</label>
                            <InputNumber
                                v-model="form.kolesterol"
                                size="small"
                                placeholder="150"
                                :class="{ 'p-invalid': form.errors.kolesterol }"
                            />
                            <small v-if="form.errors.kolesterol" class="text-red-500">{{ form.errors.kolesterol }}</small>
                            <Tag 
                                v-if="getCholCategory(form.kolesterol).status !== '-'"
                                :value="getCholCategory(form.kolesterol).status"
                                :severity="getCholCategory(form.kolesterol).isCritical ? 'danger' : 'success'"
                                class="!text-[10px] self-start mt-1"
                            />
                        </div>

                        <!-- Hemoglobin Input -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Hemoglobin (g/dL)</label>
                            <InputNumber
                                v-model="form.hemoglobin"
                                size="small"
                                :minFractionDigits="1"
                                placeholder="13.5"
                                :class="{ 'p-invalid': form.errors.hemoglobin }"
                            />
                            <small v-if="form.errors.hemoglobin" class="text-red-500">{{ form.errors.hemoglobin }}</small>
                            <Tag 
                                v-if="getHbCategory(form.hemoglobin).status !== '-'"
                                :value="getHbCategory(form.hemoglobin).status"
                                :severity="getHbCategory(form.hemoglobin).isCritical ? 'danger' : 'success'"
                                class="!text-[10px] self-start mt-1"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-blue-100 mt-2">
                        <!-- Tindak Lanjut Screening -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Tindak Lanjut Screening</label>
                            <Select
                                v-model="form.tindak_lanjut"
                                :options="[
                                    {label: 'Edukasi', value: 'edukasi'},
                                    {label: 'Kembali ke Faskes 1', value: 'faskes_1'}
                                ]"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Pilih Tindak Lanjut"
                                class="w-full"
                            />
                        </div>
                        <!-- Keterangan Tindak Lanjut -->
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Keterangan Tambahan</label>
                            <InputText
                                v-model="form.keterangan_tindak_lanjut"
                                placeholder="Detail tindak lanjut..."
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Riwayat (Hidden for Screening) -->
                <template v-if="selectedPasien.jenis_layanan !== 'screening'">
                    <div class="flex flex-col gap-1">
                        <label class="font-medium text-xs text-gray-700">Riwayat Penyakit Sekarang</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_sekarang"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit yang sedang dialami"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_sekarang }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_sekarang" class="text-red-500">{{ form.errors.riwayat_penyakit_sekarang }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Penyakit Dahulu</label>
                    <Textarea
                        v-model="form.riwayat_penyakit_dahulu"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit yang pernah diderita sebelumnya"
                        :class="{ 'p-invalid': form.errors.riwayat_penyakit_dahulu }"
                    />
                    <small v-if="form.errors.riwayat_penyakit_dahulu" class="text-red-500">{{ form.errors.riwayat_penyakit_dahulu }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Alergi</label>
                    <Textarea
                        v-model="form.riwayat_alergi"
                        rows="1"
                        autoResize
                        placeholder="Alergi obat, makanan, dll"
                        :class="{ 'p-invalid': form.errors.riwayat_alergi }"
                    />
                    <small v-if="form.errors.riwayat_alergi" class="text-red-500">{{ form.errors.riwayat_alergi }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Obat</label>
                    <Textarea
                        v-model="form.riwayat_obat"
                        rows="1"
                        autoResize
                        placeholder="Obat-obatan yang sedang dikonsumsi"
                        :class="{ 'p-invalid': form.errors.riwayat_obat }"
                    />
                    <small v-if="form.errors.riwayat_obat" class="text-red-500">{{ form.errors.riwayat_obat }}</small>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-medium text-xs text-gray-700">Riwayat Keluarga</label>
                    <Textarea
                        v-model="form.riwayat_keluarga"
                        rows="1"
                        autoResize
                        placeholder="Riwayat penyakit dalam keluarga"
                        :class="{ 'p-invalid': form.errors.riwayat_keluarga }"
                    />
                    <small v-if="form.errors.riwayat_keluarga" class="text-red-500">{{ form.errors.riwayat_keluarga }}</small>
                </div>

                <!-- Asuhan Keperawatan -->
                <div class="border-t pt-3 mt-2" v-if="['sedang_diperiksa', 'selesai'].includes(selectedPasien?.status)">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Asuhan Keperawatan</h3>
                    <div class="space-y-3">
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Diagnosa Keperawatan</label>
                            <Textarea
                                v-model="form.diagnosa_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Tuliskan diagnosa keperawatan"
                                :class="{ 'p-invalid': form.errors.diagnosa_keperawatan }"
                            />
                            <small v-if="form.errors.diagnosa_keperawatan" class="text-red-500">{{ form.errors.diagnosa_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Intervensi Keperawatan</label>
                            <Textarea
                                v-model="form.intervensi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Rencana tindakan keperawatan"
                                :class="{ 'p-invalid': form.errors.intervensi_keperawatan }"
                            />
                            <small v-if="form.errors.intervensi_keperawatan" class="text-red-500">{{ form.errors.intervensi_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Implementasi Keperawatan</label>
                            <Textarea
                                v-model="form.implementasi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="Tindakan yang telah dilakukan"
                                :class="{ 'p-invalid': form.errors.implementasi_keperawatan }"
                            />
                            <small v-if="form.errors.implementasi_keperawatan" class="text-red-500">{{ form.errors.implementasi_keperawatan }}</small>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-medium text-xs text-gray-700">Evaluasi Keperawatan (SOAP)</label>
                            <Textarea
                                v-model="form.evaluasi_keperawatan"
                                rows="2"
                                autoResize
                                placeholder="S: ... O: ... A: ... P: ..."
                                :class="{ 'p-invalid': form.errors.evaluasi_keperawatan }"
                            />
                            <small v-if="form.errors.evaluasi_keperawatan" class="text-red-500">{{ form.errors.evaluasi_keperawatan }}</small>
                        </div>
                    </div>
                </div>
                </template>
            </div>

            <template #footer>
                <div class="flex items-center justify-between w-full">
                    <div>
                        <a v-if="selectedPasien?.status === 'proses_anamnesis'"
                           :href="route('perawat.anamnesis.pdf', selectedPasien.id)"
                           target="_blank"
                           class="p-button p-component p-button-info p-button-sm !rounded-md flex items-center gap-2 no-underline"
                        >
                            <i class="pi pi-print"></i> Cetak Hasil Anamnesis Awal
                        </a>
                    </div>
                    <div class="flex gap-2">
                        <Button label="Batal" severity="secondary" @click="closeDialog" :disabled="form.processing" />
                        
                        <template v-if="selectedPasien?.jenis_layanan === 'screening'">
                            <Button
                                label="Simpan Data Screening"
                                icon="pi pi-check"
                                @click="submitAnamnesis('lanjut')"
                                severity="success"
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                        </template>
                        <template v-else>
                            <Button
                                label="Simpan sebagai Draf"
                                icon="pi pi-save"
                                @click="submitAnamnesis('draft')"
                                severity="warning"
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                            <Button
                                label="Simpan & Lanjut ke Dokter"
                                icon="pi pi-arrow-right"
                                @click="submitAnamnesis('lanjut')"
                                severity="success"
                                :loading="form.processing"
                                :disabled="form.processing"
                            />
                        </template>
                    </div>
                </div>
            </template>
        </Dialog>

        <!-- Dialog CRUD Antrian (Admin/Superadmin) -->
        <Dialog
            v-model:visible="showCrudDialog"
            modal
            header="CRUD Antrian"
            :style="{ width: '600px' }"
            :closable="true"
            @hide="showCrudDialog = false; selectedRekamMedisId = null"
        >
            <div class="space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Pasien</label>
                    <Select
                        v-model="formAntrian.pasien_id"
                        :options="pasiens"
                        optionLabel="nama"
                        optionValue="id"
                        placeholder="Pilih Pasien"
                        class="w-full"
                        :class="{ 'p-invalid': formAntrian.errors.pasien_id }"
                    />
                    <small v-if="formAntrian.errors.pasien_id" class="text-red-500">{{ formAntrian.errors.pasien_id }}</small>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Tanggal Kunjungan</label>
                        <DatePicker
                            v-model="formAntrian.tanggal_kunjungan"
                            dateFormat="dd/mm/yy"
                            placeholder="Pilih Tanggal dan Waktu"
                            class="w-full"
                            :showIcon="true"
                            :showTime="true"
                            hourFormat="24"
                            :class="{ 'p-invalid': formAntrian.errors.tanggal_kunjungan }"
                        />
                        <small v-if="formAntrian.errors.tanggal_kunjungan" class="text-red-500">{{ formAntrian.errors.tanggal_kunjungan }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Jenis Layanan</label>
                        <Select
                            v-model="formAntrian.jenis_layanan"
                            :options="layananOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Jenis Layanan"
                            class="w-full"
                            :class="{ 'p-invalid': formAntrian.errors.jenis_layanan }"
                        />
                        <small v-if="formAntrian.errors.jenis_layanan" class="text-red-500">{{ formAntrian.errors.jenis_layanan }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Catatan</label>
                    <Textarea
                        v-model="formAntrian.catatan"
                        rows="2"
                        placeholder="Catatan tambahan untuk antrian"
                        :class="{ 'p-invalid': formAntrian.errors.catatan }"
                    />
                    <small v-if="formAntrian.errors.catatan" class="text-red-500">{{ formAntrian.errors.catatan }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="showCrudDialog = false; selectedRekamMedisId = null" />
                <Button
                    v-if="crudMode === 'create'"
                    label="Tambah Antrian"
                    icon="pi pi-plus"
                    @click="submitAntrian"
                    :loading="formAntrian.processing"
                    :disabled="formAntrian.processing"
                />
                <Button
                    v-else
                    label="Perbarui Antrian"
                    icon="pi pi-refresh"
                    @click="submitAntrian"
                    :loading="formAntrian.processing"
                    :disabled="formAntrian.processing"
                />
            </template>
        </Dialog>
    </AppLayout>
</template>
