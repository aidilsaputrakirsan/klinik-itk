<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import { useToast } from 'primevue/usetoast';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import Avatar from 'primevue/avatar';
import Swal from 'sweetalert2';


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

interface AntrianItem {
    id: number;
    nomor_kunjungan: string;
    catatan: string;
    tanggal_kunjungan: string;
    created_at: string;
    pasien: {
        id: number;
        nomor_rm: string;
        nama: string;
        jenis_kelamin: string;
        tanggal_lahir: string;
        tipe_pasien: string;
    };
    jenis_layanan: string;
    anamnesis: {
        tekanan_darah: string;
        suhu: number;
        nadi: number;
        respirasi: number;
        tinggi_badan: number;
        berat_badan: number;
        keluhan_utama: string;
        riwayat_alergi: string;
    } | null;
}

interface Props {
    antrian: AntrianItem[];
    obats: Obat[];
    tindakans: Tindakan[];
    pasien_selesai: AntrianItem[];
    antrian_terlewat: AntrianItem[];
    filters: {
        tanggal_selesai?: string;
        searchSelesai?: string;
        is_filtered?: boolean;
        jenis_layanan?: string;
        tipe_pasien?: string;
    };
}

const props = defineProps<Props>();
const toast = useToast();
const page = usePage<any>();
const currentUser = page.props.auth?.user;
const canProcessPemeriksaan = ['superadmin', 'dokter'].includes(currentUser?.role);
const canManageAntrian = ['superadmin', 'admin'].includes(currentUser?.role);

const showPemeriksaanDialog = ref(false);
const selectedPasien = ref<AntrianItem | null>(null);

const searchSelesai = ref(props.filters?.searchSelesai || '');
const searchAntrian = ref('');
const searchTerlewat = ref('');
const filterTanggal = ref(props.filters?.tanggal_selesai ? new Date(props.filters.tanggal_selesai) : null);
const activeTab = ref(props.filters?.is_filtered ? '1' : '0');

const filterJenisLayanan = ref(props.filters?.jenis_layanan === 'semua' ? '' : (props.filters?.jenis_layanan || ''));
const filterTipePasien = ref(props.filters?.tipe_pasien === 'semua' ? '' : (props.filters?.tipe_pasien || ''));

const sortOptions = [
    { label: 'Waktu: Terbaru', value: { field: 'updated_at', order: -1 } },
    { label: 'Waktu: Terlama', value: { field: 'updated_at', order: 1 } },
    { label: 'Pasien: A-Z', value: { field: 'pasien.nama', order: 1 } },
    { label: 'Pasien: Z-A', value: { field: 'pasien.nama', order: -1 } },
    { label: 'No. Kunjungan', value: { field: 'nomor_kunjungan', order: 1 } },
];
const selectedSort = ref(sortOptions[0].value);


const doFilterSelesai = () => {
    applyGlobalFilter();
};

const applyGlobalFilter = () => {
    const params: any = {};

    if (searchSelesai.value) params.searchSelesai = searchSelesai.value;
    if (filterTanggal.value) {
        const d = filterTanggal.value;
        const pad = (n: number) => String(n).padStart(2, '0');
        params.tanggal_selesai = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    }
    
    if (filterJenisLayanan.value) params.jenis_layanan = filterJenisLayanan.value;
    if (filterTipePasien.value) params.tipe_pasien = filterTipePasien.value;
    
    // Aktifkan filter hanya jika ada input
    if (Object.keys(params).length > 0) {
        params.is_filtered = 1;
    }
    
    router.get(route('dokter.antrian'), params, { 
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};


const form = useForm({
    rekam_medis_id: 0,
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
    jenis_surat: '' as string,
    keperluan_surat: '',
    jumlah_hari_istirahat: 1,
    tanggal_mulai: null as Date | null,
    tanggal_selesai: null as Date | null,
});

const jenisSuratOptions = [
    { label: 'Surat Keterangan Sehat', value: 'surat_sehat' },
    { label: 'Surat Keterangan Sakit', value: 'surat_sakit' },
];

const openPemeriksaanDialog = (item: AntrianItem) => {
    selectedPasien.value = item;
    form.rekam_medis_id = item.id;
    showPemeriksaanDialog.value = true;
};

const closeDialog = () => {
    showPemeriksaanDialog.value = false;
    selectedPasien.value = null;
    resetForm();
};

const showNomorSuratDialog = ref(false);
const selectedSuratDokter = ref<any>(null);
const nomorSuratForm = useForm({
    nomor_input: null as number | null
});
const currentYear = new Date().getFullYear();

const openNomorSuratDialog = (surat: any) => {
    selectedSuratDokter.value = surat;
    nomorSuratForm.nomor_input = null;
    showNomorSuratDialog.value = true;
};

const submitNomorSurat = () => {
    if (!selectedSuratDokter.value) return;
    
    nomorSuratForm.patch(route('surat-dokter.update-nomor', selectedSuratDokter.value.id), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Nomor surat berhasil disimpan', life: 3000 });
            showNomorSuratDialog.value = false;
            selectedSuratDokter.value = null;
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Gagal', detail: 'Periksa kembali isian Anda', life: 3000 });
        }
    });
};

const deleteAntrian = (item: AntrianItem) => {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: `Apakah Anda yakin ingin menghapus antrian untuk pasien ${item.pasien?.nama}?`,
        icon: 'warning',
        iconColor: '#f43f5e',
        showCancelButton: true,
        buttonsStyling: false,
        background: '#ffffff',
        customClass: {
            popup: 'rounded-3xl shadow-2xl border border-gray-100',
            title: 'text-2xl font-bold !text-rose-700',
            htmlContainer: 'text-gray-500 text-sm mt-2',
            actions: 'flex gap-3 mt-6',
            confirmButton: '!bg-gradient-to-r !from-rose-500 !to-red-600 hover:!from-rose-600 hover:!to-red-700 !text-white rounded-xl px-6 py-2.5 font-semibold transition-all shadow-md hover:shadow-lg',
            cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl px-6 py-2.5 font-semibold transition-all border border-gray-200'
        },
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('antrian.destroy', item.id), {
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Antrian berhasil dihapus', life: 3000 });
                },
                onError: () => {
                    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal menghapus antrian', life: 3000 });
                }
            });
        }
    });
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

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

const filteredAntrian = computed(() => {
    if (!searchAntrian.value) return props.antrian;
    const s = searchAntrian.value.toLowerCase();
    return props.antrian.filter(item => 
        item.pasien.nama.toLowerCase().includes(s) || 
        item.pasien.nomor_rm.toLowerCase().includes(s) ||
        item.nomor_kunjungan.toLowerCase().includes(s)
    );
});

const filteredTerlewat = computed(() => {
    if (!searchTerlewat.value) return props.antrian_terlewat;
    const s = searchTerlewat.value.toLowerCase();
    return props.antrian_terlewat.filter(item => 
        item.pasien.nama.toLowerCase().includes(s) || 
        item.pasien.nomor_rm.toLowerCase().includes(s) ||
        item.nomor_kunjungan.toLowerCase().includes(s)
    );
});

// Watcher untuk filter global
import { watch } from 'vue';
watch([filterJenisLayanan, filterTipePasien], () => {
    applyGlobalFilter();
});

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
    <Head title="Pasien Siap Periksa" />
    <AppLayout>
        <template #header>Antrian Pasien</template>

        <div class="space-y-6">


            <Tabs v-model:value="activeTab" class="bg-transparent">
                <TabList class="!bg-white/80 !backdrop-blur-md !border-b !border-gray-200 !rounded-t-xl overflow-hidden shadow-sm sticky top-0 z-10">
                    <Tab value="0" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '0' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-check-circle text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '0' ? 'text-emerald-700' : 'text-gray-500'">Siap Diperiksa</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ antrian.length }} Pasien Menunggu</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="1" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '1' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-history text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '1' ? 'text-blue-700' : 'text-gray-500'">Sudah Diperiksa</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ pasien_selesai?.length || 0 }} Riwayat</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="2" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === '2' ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-exclamation-triangle text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === '2' ? 'text-orange-700' : 'text-gray-500'">Terlewat Jadwal</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">{{ antrian_terlewat?.length || 0 }} Terlewat</span>
                            </div>
                        </div>
                    </Tab>
                </TabList>

                <TabPanels class="!bg-transparent !p-0 !mt-4">
                    <TabPanel value="0" class="!p-0">
                        <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                            <template #content>
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                                        <span class="w-2 h-6 bg-emerald-500 rounded-full"></span>
                                        Daftar Antrian Pasien
                                    </h3>
                                    
                                    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <!-- Field: Search -->
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Pasien</span>
                                                <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                                    <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                        <i class="pi pi-search text-emerald-500 text-[10px]"></i>
                                                    </InputGroupAddon>
                                                    <InputText
                                                        v-model="searchAntrian"
                                                        placeholder="Nama / RM / No. Kunj..."
                                                        class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                                    />
                                                </InputGroup>
                                            </div>

                                            <!-- Field: Sort -->
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Urutan Antrian</span>
                                                <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                                                    <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                        <i class="pi pi-sort-alt text-emerald-500 text-[10px]"></i>
                                                    </InputGroupAddon>
                                                    <Select 
                                                        v-model="selectedSort" 
                                                        :options="sortOptions" 
                                                        optionLabel="label" 
                                                        optionValue="value"
                                                        placeholder="Urutkan" 
                                                        class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                        :pt="{
                                                            root: { class: '!border-0 !shadow-none' },
                                                            label: { class: '!py-2 !px-0 !text-xs' }
                                                        }"
                                                    />
                                                </InputGroup>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                    <DataTable
                        :value="filteredAntrian"
                        :paginator="antrian.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada pasien yang siap diperiksa"
                        :sortField="selectedSort.field"
                        :sortOrder="selectedSort.order"
                    >
                        <Column header="No" style="width: 60px">
                            <template #body="{ index }">
                                {{ index + 1 }}
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
                                        <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                        <p class="text-[11px] text-gray-500">
                                            {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                            {{ getAge(data.pasien.tanggal_lahir) }} thn
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
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
                        <Column header="Vital Sign" style="width: 200px">
                            <template #body="{ data }">
                                <div class="text-xs space-y-1" v-if="data.anamnesis">
                                    <p>TD: {{ data.anamnesis.tekanan_darah || '-' }} mmHg</p>
                                    <p>Suhu: {{ data.anamnesis.suhu }}°C | Nadi: {{ data.anamnesis.nadi }}x/m</p>
                                    <p class="text-gray-600">{{ data.anamnesis.keluhan_utama }}</p>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column header="Catatan">
                            <template #body="{ data }">
                                <span class="text-gray-600 text-sm italic">{{ data.catatan || '-' }}</span>
                            </template>
                        </Column>
                        <Column header="Waktu Daftar" style="width: 140px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ new Date(data.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <div class="flex items-center gap-1 text-emerald-600 text-[10px]">
                                        <i class="pi pi-clock"></i>
                                        <span>{{ new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WITA</span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 140px" class="text-center" v-if="canProcessPemeriksaan || canManageAntrian">
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <Button
                                        v-if="canProcessPemeriksaan"
                                        label="Periksa"
                                        severity="success"
                                        @click="openPemeriksaanDialog(data)"
                                        class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md hover:shadow-emerald-100 transition-all font-bold"
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

        <!-- Tab: Terlewat Jadwal -->
        <TabPanel value="2" class="!p-0">
            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2 mb-4">
                            <span class="w-2 h-6 bg-orange-500 rounded-full"></span>
                            Daftar Antrian Terlewat
                        </h3>
                        
                        <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <!-- Field: Search -->
                                <div class="flex flex-col gap-1.5 col-span-2">
                                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Pasien Terlewat</span>
                                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                                            <i class="pi pi-search text-orange-500 text-[10px]"></i>
                                        </InputGroupAddon>
                                        <InputText
                                            v-model="searchTerlewat"
                                            placeholder="Nama / RM / No. Kunj..."
                                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                        />
                                    </InputGroup>
                                </div>
                            </div>
                        </div>
                    </div>

                    <DataTable
                        :value="filteredTerlewat"
                        :paginator="filteredTerlewat && filteredTerlewat.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        stripedRows
                        emptyMessage="Tidak ada antrian yang terlewat"
                    >
                        <Column header="No" style="width: 60px">
                            <template #body="{ index }">
                                {{ index + 1 }}
                            </template>
                        </Column>
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 150px">
                            <template #body="{ data }">
                                <span class="font-mono text-[11px] font-bold text-orange-700 bg-orange-50 px-2 py-1 rounded border border-orange-100/50 shadow-sm">{{ data.nomor_kunjungan }}</span>
                            </template>
                        </Column>
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div class="flex flex-col gap-1.5">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                        <p class="text-[11px] text-gray-500">
                                            {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                            {{ getAge(data.pasien.tanggal_lahir) }} thn
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
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
                        <Column field="status" header="Status" style="width: 150px">
                            <template #body="{ data }">
                                <Tag :value="data.status" :severity="data.status === 'siap_dokter' ? 'info' : 'warn'" class="uppercase !text-[10px] !px-2" />
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 150px" class="text-center" v-if="canProcessPemeriksaan || canManageAntrian">
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <Button
                                        v-if="canProcessPemeriksaan"
                                        label="Periksa"
                                        severity="success"
                                        class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold w-full flex justify-center text-center"
                                        @click="openPemeriksaanDialog(data)"
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
                                        <span class="w-2 h-6 bg-blue-500 rounded-full"></span>
                                        Riwayat Pemeriksaan
                                    </h3>
                                    <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 w-full max-w-xl shadow-sm space-y-3">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Nama / Nomor Rekam Medis</span>
                                            <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all">
                                                <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                    <i class="pi pi-search text-blue-500 text-[10px]"></i>
                                                </InputGroupAddon>
                                                <InputText
                                                    v-model="searchSelesai"
                                                    placeholder="Ketik di sini..."
                                                    class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                                                    @keyup.enter="doFilterSelesai"
                                                />
                                            </InputGroup>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <!-- Field: Date Picker -->
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Filter Tanggal Pemeriksaan</span>
                                                <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all">
                                                    <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                        <i class="pi pi-calendar text-blue-500 text-[10px]"></i>
                                                    </InputGroupAddon>
                                                    <DatePicker 
                                                        v-model="filterTanggal" 
                                                        dateFormat="dd/mm/yy"
                                                        placeholder="Pilih Tanggal"
                                                        class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                        inputClass="!border-0 !p-0 !h-9 !text-xs"
                                                        :showClear="true"
                                                        @date-select="doFilterSelesai"
                                                        @clear="doFilterSelesai"
                                                    />
                                                </InputGroup>
                                            </div>

                                            <!-- Field: Sort -->
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Urutan Data</span>
                                                <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all">
                                                    <InputGroupAddon class="!bg-white !border-0 !px-3">
                                                        <i class="pi pi-sort-alt text-blue-500 text-[10px]"></i>
                                                    </InputGroupAddon>
                                                    <Select 
                                                        v-model="selectedSort" 
                                                        :options="sortOptions" 
                                                        optionLabel="label" 
                                                        optionValue="value"
                                                        placeholder="Urutkan" 
                                                        class="!border-0 !text-xs !py-0 focus:!ring-0 flex-1"
                                                        :pt="{
                                                            root: { class: '!border-0 !shadow-none' },
                                                            label: { class: '!py-2 !px-0 !text-xs' }
                                                        }"
                                                    />
                                                </InputGroup>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-2 pt-1">
                                            <Button 
                                                label="Tampilkan Pasien" 
                                                icon="pi pi-search" 
                                                severity="info" 
                                                @click="doFilterSelesai" 
                                                class="!rounded-xl flex-1 h-9 shadow-sm !text-[11px] font-bold transition-all hover:shadow-md hover:shadow-blue-100" 
                                            />
                                            <Button 
                                                v-if="searchSelesai || filterTanggal || props.filters?.is_filtered"
                                                icon="pi pi-refresh" 
                                                severity="secondary" 
                                                outlined
                                                class="!rounded-xl h-9 w-9"
                                                title="Reset"
                                                @click="() => { searchSelesai = ''; filterTanggal = null; router.get(route('dokter.antrian'), {}, { replace: true }); }"
                                            />
                                        </div>
                                    </div>

                                </div>

                    <DataTable
                        :value="pasien_selesai"
                        :paginator="true"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada riwayat pasien yang selesai diperiksa"
                        :sortField="selectedSort.field"
                        :sortOrder="selectedSort.order"
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
                                        <p class="font-medium text-gray-900">{{ data.pasien.nama }}</p>
                                        <p class="text-[11px] text-gray-500">
                                            {{ data.pasien.nomor_rm }} | {{ data.pasien.jenis_kelamin === 'L' ? 'L' : 'P' }} |
                                            {{ getAge(data.pasien.tanggal_lahir) }} thn
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
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
                        <Column header="Diagnosis" style="width: 200px">
                            <template #body="{ data }">
                                <div class="text-xs" v-if="data.pemeriksaan">
                                    <p class="font-semibold text-gray-800">{{ data.pemeriksaan.diagnosis_utama }}</p>
                                    <p class="text-gray-500 mt-0.5" v-if="data.pemeriksaan.diagnosis_sekunder">{{ data.pemeriksaan.diagnosis_sekunder }}</p>
                                </div>
                                <span v-else class="text-gray-400 italic">Belum ada diagnosis</span>
                            </template>
                        </Column>
                        <Column header="Dokter" style="width: 160px">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2">
                                    <Avatar :label="data.dokter ? data.dokter.name.charAt(0) : '?'" class="bg-blue-100 text-blue-800 text-xs" shape="circle" size="small" />
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ data.dokter ? data.dokter.name : '-' }}
                                    </span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Waktu Selesai" style="width: 140px">
                            <template #body="{ data }">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-700">
                                        {{ new Date(data.updated_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                                    </span>
                                    <div class="flex items-center gap-1 text-blue-600 text-[10px]">
                                        <i class="pi pi-clock"></i>
                                        <span>{{ new Date(data.updated_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WITA</span>
                                    </div>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 250px" class="text-center">
                            <template #body="{ data }">
                                <div class="flex gap-2 justify-center">
                                    <template v-if="data.surat_dokter">
                                        <a
                                            v-if="data.surat_dokter.nomor_surat"
                                            :href="route('surat-dokter.pdf', data.surat_dokter.id)"
                                            target="_blank"
                                            class="p-button p-component p-button-warning p-button-sm !rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold text-white no-underline flex items-center justify-center gap-1"
                                        >
                                            <i class="pi pi-print"></i>
                                            <span>Cetak Surat</span>
                                        </a>
                                        <Button
                                            v-else
                                            disabled
                                            severity="warning"
                                            class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm font-bold flex items-center justify-center gap-1 opacity-50 cursor-not-allowed"
                                        >
                                            <i class="pi pi-print"></i>
                                            <span>Cetak Surat</span>
                                        </Button>

                                        <Button
                                            v-if="!data.surat_dokter.nomor_surat && canManageAntrian"
                                            severity="info"
                                            class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md transition-all font-bold flex items-center justify-center gap-1"
                                            @click="openNomorSuratDialog(data.surat_dokter)"
                                        >
                                            <i class="pi pi-pencil"></i>
                                            <span>Input No. Surat</span>
                                        </Button>
                                    </template>
                                    <Link :href="route('pasien.rekam-medis', data.pasien.id)">
                                        <Button
                                            label="Rekam Medis"
                                            icon="pi pi-folder-open"
                                            severity="info"
                                            class="!rounded-xl !text-[11px] !py-2 !px-4 shadow-sm hover:shadow-md hover:shadow-blue-100 transition-all font-bold"
                                        />
                                    </Link>
                                    <Button
                                        v-if="canManageAntrian"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        class="!rounded-xl !w-9 !h-9 shadow-sm"
                                        v-tooltip.top="'Hapus Antrian'"
                                        @click="deleteAntrian(data)"
                                    />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </TabPanel>
    </TabPanels>
</Tabs>
        </div>


        <!-- Dialog Pemeriksaan -->
        <Dialog
            v-model:visible="showPemeriksaanDialog"
            modal
            header="Pemeriksaan Dokter"
            :style="{ width: '900px' }"
            :closable="true"
            @hide="closeDialog"
        >
            <div v-if="selectedPasien" class="space-y-4">
                <!-- Info Pasien & Anamnesis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Data Pasien</h4>
                        <div class="text-sm space-y-1">
                            <p><span class="text-gray-500">Nama:</span> {{ selectedPasien.pasien.nama }}</p>
                            <p><span class="text-gray-500">No. RM:</span> {{ selectedPasien.pasien.nomor_rm }}</p>
                            <p><span class="text-gray-500">Catatan:</span> {{ selectedPasien.catatan || '-' }}</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg" v-if="selectedPasien.anamnesis">
                        <h4 class="font-medium mb-2">Hasil Anamnesis</h4>
                        <div class="text-sm space-y-1">
                            <p><span class="text-gray-500">Keluhan:</span> {{ selectedPasien.anamnesis.keluhan_utama }}</p>
                            <p>TD: {{ selectedPasien.anamnesis.tekanan_darah || '-' }} mmHg</p>
                            <p>Suhu: {{ selectedPasien.anamnesis.suhu }}°C | Nadi: {{ selectedPasien.anamnesis.nadi }}x/m | RR: {{ selectedPasien.anamnesis.respirasi }}x/m</p>
                            <p>BB: {{ selectedPasien.anamnesis.berat_badan }} kg | TB: {{ selectedPasien.anamnesis.tinggi_badan }} cm</p>
                            <p v-if="selectedPasien.anamnesis.riwayat_alergi">
                                <span class="text-red-600">Alergi: {{ selectedPasien.anamnesis.riwayat_alergi }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Pemeriksaan Fisik -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Pemeriksaan Fisik</label>
                        <Textarea
                            v-model="form.pemeriksaan_fisik"
                            rows="2"
                            placeholder="Hasil pemeriksaan fisik"
                            :class="{ 'p-invalid': form.errors.pemeriksaan_fisik }"
                        />
                        <small v-if="form.errors.pemeriksaan_fisik" class="text-red-500">{{ form.errors.pemeriksaan_fisik }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Hasil Pemeriksaan</label>
                        <Textarea
                            v-model="form.hasil_pemeriksaan"
                            rows="2"
                            placeholder="Hasil pemeriksaan penunjang"
                            :class="{ 'p-invalid': form.errors.hasil_pemeriksaan }"
                        />
                        <small v-if="form.errors.hasil_pemeriksaan" class="text-red-500">{{ form.errors.hasil_pemeriksaan }}</small>
                    </div>
                </div>

                <!-- Form Diagnosis -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Utama <span class="text-red-500">*</span></label>
                        <Textarea
                            v-model="form.diagnosis_utama"
                            rows="2"
                            placeholder="Masukkan diagnosis utama"
                            :class="{ 'p-invalid': form.errors.diagnosis_utama }"
                        />
                        <small v-if="form.errors.diagnosis_utama" class="text-red-500">{{ form.errors.diagnosis_utama }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Diagnosis Sekunder</label>
                        <Textarea
                            v-model="form.diagnosis_sekunder"
                            rows="2"
                            placeholder="Diagnosis sekunder (opsional)"
                            :class="{ 'p-invalid': form.errors.diagnosis_sekunder }"
                        />
                        <small v-if="form.errors.diagnosis_sekunder" class="text-red-500">{{ form.errors.diagnosis_sekunder }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Kode ICD-10</label>
                        <InputText
                            v-model="form.kode_icd10"
                            placeholder="Contoh: J00"
                            :class="{ 'p-invalid': form.errors.kode_icd10 }"
                        />
                        <small v-if="form.errors.kode_icd10" class="text-red-500">{{ form.errors.kode_icd10 }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Prognosis</label>
                        <InputText
                            v-model="form.prognosis"
                            placeholder="Baik / Sedang / Buruk"
                            :class="{ 'p-invalid': form.errors.prognosis }"
                        />
                        <small v-if="form.errors.prognosis" class="text-red-500">{{ form.errors.prognosis }}</small>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="font-medium text-sm">Anjuran</label>
                        <InputText
                            v-model="form.anjuran"
                            placeholder="Anjuran untuk pasien"
                            :class="{ 'p-invalid': form.errors.anjuran }"
                        />
                        <small v-if="form.errors.anjuran" class="text-red-500">{{ form.errors.anjuran }}</small>
                    </div>
                </div>

                <!-- Penatalaksanaan Medis (NEW) -->
                <div class="flex flex-col gap-2">
                    <label class="font-medium text-sm">Penatalaksanaan Medis (Catatan Tambahan)</label>
                    <Textarea
                        v-model="form.penatalaksanaan_medis"
                        rows="3"
                        placeholder="Catatan penatalaksanaan medis selain resep"
                        :class="{ 'p-invalid': form.errors.penatalaksanaan_medis }"
                    />
                    <small v-if="form.errors.penatalaksanaan_medis" class="text-red-500">{{ form.errors.penatalaksanaan_medis }}</small>
                </div>

                <!-- Tindakan -->
                <div class="border-t pt-4">
                    <h4 class="font-medium mb-3">Tindakan yang Dilakukan</h4>
                    <div class="grid grid-cols-3 gap-2">
                        <div v-for="tindakan in tindakans" :key="tindakan.id" class="flex items-center gap-2">
                            <Checkbox
                                v-model="form.selectedTindakans"
                                :inputId="`tindakan-${tindakan.id}`"
                                :value="tindakan.id"
                            />
                            <label :for="`tindakan-${tindakan.id}`" class="text-sm">{{ tindakan.nama }}</label>
                        </div>
                    </div>
                </div>

                <!-- Resep Obat -->
                <div class="border-t pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium">Resep Obat</h4>
                        <Button label="Tambah Obat" icon="pi pi-plus" size="small" severity="secondary" @click="addResepObat" />
                    </div>
                    <div v-for="(item, index) in form.resepObat" :key="index" class="grid grid-cols-12 gap-2 mb-2 items-end">
                        <div class="col-span-4">
                            <label class="text-xs text-gray-500">Obat</label>
                            <select v-model="item.obat_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                <option :value="0">Pilih obat...</option>
                                <option v-for="obat in obats" :key="obat.id" :value="obat.id">
                                    {{ obat.nama }} ({{ obat.satuan }}) - Stok: {{ obat.stok }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-1">
                            <label class="text-xs text-gray-500">Jumlah</label>
                            <InputNumber v-model="item.jumlah" size="small" :min="1" :inputStyle="{ width: '100%', textAlign: 'center' }" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Dosis</label>
                            <InputText v-model="item.dosis" size="small" placeholder="500mg" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Aturan Pakai</label>
                            <InputText v-model="item.aturan_pakai" size="small" placeholder="3x1" class="w-full" />
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500">Keterangan</label>
                            <InputText v-model="item.keterangan" size="small" placeholder="Sesudah makan" class="w-full" />
                        </div>
                        <div class="col-span-1">
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="removeResepObat(index)" />
                        </div>
                    </div>
                </div>

                <!-- Surat Keterangan Dokter -->
                <div class="border-t pt-4">
                    <div class="flex items-center gap-3 mb-3">
                        <Checkbox v-model="form.buat_surat" :binary="true" inputId="buat_surat" />
                        <label for="buat_surat" class="font-medium">Buat Surat Keterangan Dokter</label>
                    </div>

                    <div v-if="form.buat_surat" class="bg-amber-50 p-4 rounded-lg space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Jenis Surat <span class="text-red-500">*</span></label>
                                <Select
                                    v-model="form.jenis_surat"
                                    :options="jenisSuratOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Pilih jenis surat"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.jenis_surat }"
                                />
                                <small v-if="form.errors.jenis_surat" class="text-red-500">{{ form.errors.jenis_surat }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Keperluan</label>
                                <InputText
                                    v-model="form.keperluan_surat"
                                    placeholder="Misal: Pendaftaran beasiswa"
                                    :class="{ 'p-invalid': form.errors.keperluan_surat }"
                                />
                                <small v-if="form.errors.keperluan_surat" class="text-red-500">{{ form.errors.keperluan_surat }}</small>
                            </div>
                        </div>

                        <!-- Fields khusus Surat Sakit -->
                        <div v-if="form.jenis_surat === 'surat_sakit'" class="grid grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Jumlah Hari Istirahat</label>
                                <InputNumber
                                    v-model="form.jumlah_hari_istirahat"
                                    :min="1"
                                    :max="14"
                                    suffix=" hari"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Tanggal Mulai</label>
                                <DatePicker
                                    v-model="form.tanggal_mulai"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Pilih tanggal"
                                    class="w-full"
                                />
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="font-medium text-sm">Tanggal Selesai</label>
                                <DatePicker
                                    v-model="form.tanggal_selesai"
                                    dateFormat="dd/mm/yy"
                                    placeholder="Pilih tanggal"
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Batal" severity="secondary" @click="closeDialog" :disabled="form.processing" />
                <Button
                    label="Simpan Pemeriksaan"
                    icon="pi pi-check"
                    @click="submitPemeriksaan"
                    :loading="form.processing"
                    :disabled="form.processing"
                />
            </template>
        </Dialog>
        <!-- Dialog Input Nomor Surat -->
        <Dialog 
            v-model:visible="showNomorSuratDialog" 
            modal 
            header="Input Nomor Surat" 
            :style="{ width: '30rem' }"
        >
            <div class="space-y-4 pt-2">
                <p class="text-sm text-gray-600 mb-4">Masukkan nomor urut surat. Format nomor lengkap akan digenerate otomatis.</p>
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Nomor Surat <span class="text-red-500">*</span></label>
                    <InputGroup>
                        <InputNumber 
                            v-model="nomorSuratForm.nomor_input" 
                            inputId="withoutgrouping" 
                            :useGrouping="false" 
                            placeholder="Contoh: 11541"
                            :class="{ 'p-invalid': nomorSuratForm.errors.nomor_input }"
                        />
                        <InputGroupAddon>/IT10/TU.03/{{ currentYear }}</InputGroupAddon>
                    </InputGroup>
                    <small v-if="nomorSuratForm.errors.nomor_input" class="text-red-500">{{ nomorSuratForm.errors.nomor_input }}</small>
                </div>
            </div>
            
            <template #footer>
                <Button label="Batal" icon="pi pi-times" text @click="showNomorSuratDialog = false" />
                <Button label="Simpan" icon="pi pi-check" @click="submitNomorSurat" :loading="nomorSuratForm.processing" />
            </template>
        </Dialog>
    </AppLayout>
</template>
