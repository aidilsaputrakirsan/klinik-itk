<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { formatDateToLocalString } from '@/utils/date';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import DatePicker from 'primevue/datepicker';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';

interface Pasien {
    id: number;
    nama: string;
    nomor_rm: string;
    tipe_pasien: string;
    jenis_kelamin: string;
}

interface Kunjungan {
    id: number;
    nomor_kunjungan: string;
    tanggal_kunjungan: string;
    jenis_layanan: string;
    status: string;
    pasien?: Pasien;
    dokter?: { name: string };
    perawat?: { name: string };
    pemeriksaan?: {
        diagnosis_utama?: string;
        diagnosis_sekunder?: string;
        tindakans?: { nama: string; kode: string }[];
    };
}

interface Props {
    kunjungan: Kunjungan[];
    summary: {
        total: number;
        selesai: number;
        batal: number;
        menunggu: number;
    };
    byJenisLayanan: Record<string, number>;
    byTipePasien: Record<string, number>;
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const activeTab = ref('umum'); // 'umum' or 'screening'

const kunjunganUmum = computed(() => props.kunjungan.filter(k => k.jenis_layanan !== 'screening'));
const kunjunganScreening = computed(() => props.kunjungan.filter(k => k.jenis_layanan === 'screening'));

const currentKunjungan = computed(() => activeTab.value === 'umum' ? kunjunganUmum.value : kunjunganScreening.value);

const summaryFiltered = computed(() => {
    const list = currentKunjungan.value;
    return {
        total: list.length,
        selesai: list.filter(k => k.status === 'selesai').length,
        batal: list.filter(k => k.status === 'batal').length,
        menunggu: list.filter(k => ['menunggu_perawat', 'proses_anamnesis', 'siap_dokter', 'sedang_diperiksa'].includes(k.status)).length,
        lakiLaki: list.filter(k => k.pasien?.jenis_kelamin === 'L').length,
        perempuan: list.filter(k => k.pasien?.jenis_kelamin === 'P').length,
    };
});

const byJenisLayananFiltered = computed(() => {
    const counts: Record<string, number> = {};
    currentKunjungan.value.forEach(k => {
        counts[k.jenis_layanan] = (counts[k.jenis_layanan] || 0) + 1;
    });
    return counts;
});

const byTipePasienFiltered = computed(() => {
    const counts: Record<string, number> = {};
    currentKunjungan.value.forEach(k => {
        const tipe = k.pasien?.tipe_pasien || 'unknown';
        counts[tipe] = (counts[tipe] || 0) + 1;
    });
    return counts;
});

const startDate = ref(new Date(props.filters.start_date));
const endDate = ref(new Date(props.filters.end_date));

const applyFilter = () => {
    router.get(route('laporan.kunjungan'), {
        start_date: formatDateToLocalString(startDate.value),
        end_date: formatDateToLocalString(endDate.value),
    }, { preserveState: true });
};

const downloadPdf = () => {
    window.open(route('laporan.kunjungan.pdf', {
        start_date: formatDateToLocalString(startDate.value),
        end_date: formatDateToLocalString(endDate.value),
        tab: activeTab.value,
    }), '_blank');
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const getStatusSeverity = (status: string) => {
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

const getStatusLabel = (status: string) => {
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

const getJenisLayananLabel = (jenis: string) => {
    const labels: Record<string, string> = {
        berobat: 'Pemeriksaan Umum',
        surat_sehat: 'Surat Sehat',
        screening: 'Screening'
    };
    return labels[jenis] || jenis;
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
    <Head title="Laporan Kunjungan" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            Laporan Kunjungan
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Rekapitulasi data kunjungan pasien dan statistik operasional</p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button @click="downloadPdf" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white rounded-lg font-medium shadow-sm transition-all transform hover:-translate-y-0.5">
                        <i class="pi pi-file-pdf text-xs"></i>
                        <span>Export PDF</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="font-sans space-y-6 pb-8">
            <!-- Filter Section -->
            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Mulai</span>
                        <DatePicker v-model="startDate" dateFormat="dd/mm/yy" showIcon iconDisplay="input" class="w-44" inputClass="!border-gray-200 !rounded-xl !text-xs !py-2 !pl-3 !pr-10 shadow-sm w-full" />
                    </div>
                    <div class="flex items-center h-full mt-5">
                        <i class="pi pi-arrow-right text-gray-400"></i>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Akhir</span>
                        <DatePicker v-model="endDate" dateFormat="dd/mm/yy" showIcon iconDisplay="input" class="w-44" inputClass="!border-gray-200 !rounded-xl !text-xs !py-2 !pl-3 !pr-10 shadow-sm w-full" />
                    </div>
                    <div class="mt-5">
                        <Button label="Terapkan Filter" icon="pi pi-filter" @click="applyFilter" severity="success" class="!rounded-xl !text-xs font-bold shadow-sm !px-4 !py-2" />
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <Tabs v-model:value="activeTab" class="bg-transparent">
                <TabList class="!bg-white/80 !backdrop-blur-md !border-b !border-gray-200 !rounded-t-xl overflow-hidden shadow-sm sticky top-0 z-10">
                    <Tab value="umum" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === 'umum' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-table text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === 'umum' ? 'text-blue-700' : 'text-gray-500'">Pemeriksaan Umum</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">Layanan Berobat & Surat Sehat</span>
                            </div>
                        </div>
                    </Tab>
                    <Tab value="screening" class="!px-8 !py-4 !transition-all !duration-300">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg" :class="activeTab === 'screening' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'">
                                <i class="pi pi-heart-fill text-lg"></i>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="font-bold" :class="activeTab === 'screening' ? 'text-emerald-700' : 'text-gray-500'">Layanan Screening</span>
                                <span class="text-[10px] uppercase tracking-wider font-semibold opacity-60">Pemeriksaan Screening</span>
                            </div>
                        </div>
                    </Tab>
                </TabList>
            </Tabs>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-blue-600 z-10">{{ summaryFiltered.total }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Kunjungan</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-emerald-600 z-10">{{ summaryFiltered.selesai }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Selesai</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-amber-500 z-10">{{ summaryFiltered.menunggu }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Dalam Proses</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-rose-600 z-10">{{ summaryFiltered.batal }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Batal</p>
                </div>
            </div>

            <!-- Summary by Category -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 font-bold text-sm text-gray-700">
                        Berdasarkan Jenis Layanan
                    </div>
                    <div class="p-5 space-y-3">
                        <div v-for="(count, jenis) in byJenisLayananFiltered" :key="jenis" class="flex justify-between items-center p-3 bg-white border border-gray-100 rounded-xl hover:bg-blue-50/50 transition-colors">
                            <span class="font-medium text-gray-700">{{ getJenisLayananLabel(jenis as string) }}</span>
                            <Tag :value="count.toString()" severity="info" rounded class="!px-3" />
                        </div>
                        <div v-if="Object.keys(byJenisLayananFiltered).length === 0" class="text-center text-gray-400 py-6 text-sm">
                            Tidak ada data
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 font-bold text-sm text-gray-700">
                        Berdasarkan Tipe Pasien
                    </div>
                    <div class="p-5 space-y-3">
                        <div v-for="(count, tipe) in byTipePasienFiltered" :key="tipe" class="flex justify-between items-center p-3 bg-white border border-gray-100 rounded-xl hover:bg-emerald-50/50 transition-colors">
                            <span class="font-medium text-gray-700">{{ getTipePasienLabel(tipe as string) }}</span>
                            <Tag :value="count.toString()" severity="success" rounded class="!px-3" />
                        </div>
                        <div v-if="Object.keys(byTipePasienFiltered).length === 0" class="text-center text-gray-400 py-6 text-sm">
                            Tidak ada data
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 font-bold text-sm text-gray-700">
                        Berdasarkan Jenis Kelamin
                    </div>
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between items-center p-3 bg-white border border-gray-100 rounded-xl hover:bg-indigo-50/50 transition-colors">
                            <span class="font-medium text-gray-700 flex items-center gap-2">
                                <i class="pi pi-user text-indigo-500"></i> Laki-laki
                            </span>
                            <Tag :value="summaryFiltered.lakiLaki.toString()" severity="info" rounded class="!px-3" />
                        </div>
                        <div class="flex justify-between items-center p-3 bg-white border border-gray-100 rounded-xl hover:bg-pink-50/50 transition-colors">
                            <span class="font-medium text-gray-700 flex items-center gap-2">
                                <i class="pi pi-user text-pink-500"></i> Perempuan
                            </span>
                            <Tag :value="summaryFiltered.perempuan.toString()" severity="danger" rounded class="!px-3" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 font-bold text-gray-800">
                    Detail Data Kunjungan
                </div>
                <DataTable
                    :value="currentKunjungan"
                    :paginator="currentKunjungan.length > 10"
                    :rows="10"
                    dataKey="id"
                    responsiveLayout="scroll"
                    class="p-datatable-sm text-sm"
                    emptyMessage="Tidak ada data kunjungan"
                    stripedRows
                >
                    <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 140px" />
                    <Column field="tanggal_kunjungan" header="Tanggal" style="width: 120px">
                        <template #body="{ data }">
                            <span class="font-medium text-gray-700">{{ formatDate(data.tanggal_kunjungan) }}</span>
                        </template>
                    </Column>
                    <Column header="Pasien">
                        <template #body="{ data }">
                            <div v-if="data.pasien" class="flex flex-col">
                                <span class="font-bold text-gray-800">{{ data.pasien.nama }}</span>
                                <span class="text-[11px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded w-max mt-1">{{ data.pasien.nomor_rm }}</span>
                            </div>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>
                    <Column header="Jenis Kelamin" style="width: 110px">
                        <template #body="{ data }">
                            <span v-if="data.pasien?.jenis_kelamin" class="font-semibold" :class="data.pasien.jenis_kelamin === 'L' ? 'text-indigo-600' : 'text-pink-600'">
                                {{ data.pasien.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>
                    <Column v-if="activeTab === 'screening'" field="jenis_layanan" header="Layanan" style="width: 120px">
                        <template #body="{ data }">
                            <span class="text-gray-700">{{ getJenisLayananLabel(data.jenis_layanan) }}</span>
                        </template>
                    </Column>
                    <Column field="status" header="Status" style="width: 150px">
                        <template #body="{ data }">
                            <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                        </template>
                    </Column>
                    <Column v-if="activeTab === 'umum'" header="Diagnosis" style="min-width: 150px">
                        <template #body="{ data }">
                            <div v-if="data.pemeriksaan?.diagnosis_utama" class="text-xs">
                                <div class="font-semibold text-gray-800">{{ data.pemeriksaan.diagnosis_utama }}</div>
                                <div v-if="data.pemeriksaan.diagnosis_sekunder" class="text-gray-500 mt-0.5">{{ data.pemeriksaan.diagnosis_sekunder }}</div>
                            </div>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>
                    <Column v-if="activeTab === 'umum'" header="Tindakan" style="min-width: 150px">
                        <template #body="{ data }">
                            <div v-if="data.pemeriksaan?.tindakans?.length" class="flex flex-wrap gap-1">
                                <Tag v-for="tindakan in data.pemeriksaan.tindakans" :key="tindakan.kode" :value="tindakan.nama" severity="info" class="!text-[10px] !px-2 !py-0.5 whitespace-nowrap" />
                            </div>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
