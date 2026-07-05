<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

interface DiagnosisItem {
    kode_icd10: string;
    diagnosis_utama: string;
    total_kasus: number;
    total_pasien: number;
}

interface Props {
    topDiagnoses: DiagnosisItem[];
    allDiagnoses: DiagnosisItem[];
    summary: {
        total_kasus: number;
        total_pasien: number;
        total_diagnosis_unik: number;
    };
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const startDate = ref(new Date(props.filters.start_date));
const endDate = ref(new Date(props.filters.end_date));
const globalFilter = ref('');

const filteredAllDiagnoses = computed(() => {
    if (!globalFilter.value) return props.allDiagnoses;
    const term = globalFilter.value.toLowerCase();
    return props.allDiagnoses.filter(d => 
        (d.kode_icd10 && d.kode_icd10.toLowerCase().includes(term)) ||
        (d.diagnosis_utama && d.diagnosis_utama.toLowerCase().includes(term))
    );
});

const applyFilter = () => {
    router.get(route('laporan.diagnosis'), {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const downloadPdf = () => {
    window.open(route('laporan.diagnosis.pdf', {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }), '_blank');
};

// Calculate maximum cases for relative bar width
const maxCases = computed(() => {
    if (props.topDiagnoses.length === 0) return 1;
    return Math.max(...props.topDiagnoses.map(d => d.total_kasus));
});

const getBarWidth = (cases: number) => {
    return (cases / maxCases.value) * 100;
};

// Colors for top ranks
const getRankColor = (rank: number) => {
    const colors = [
        'bg-violet-600', // Rank 1
        'bg-purple-500', // Rank 2
        'bg-indigo-500', // Rank 3
    ];
    return colors[rank] || 'bg-slate-400';
};
</script>

<template>
    <Head title="Laporan Diagnosis (ICD)" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            Laporan Hasil Diagnosis (ICD-10)
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Analisis tren diagnosis penyakit berdasarkan klasifikasi ICD-10</p>
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
                <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-violet-600 z-10">{{ summary.total_kasus }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Kasus Diagnosis</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-blue-600 z-10">{{ summary.total_pasien }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Pasien Unik</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-emerald-600 z-10">{{ summary.total_diagnosis_unik }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Jenis Penyakit Terdeteksi</p>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Top 10 Section (Left / Full on small screen) -->
                <div class="lg:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                    <div class="bg-gray-50/70 px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                        <span class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-violet-600 rounded-full"></span>
                            10 Penyakit Terbanyak (Top 10)
                        </span>
                    </div>
                    <div class="p-5 flex-1 space-y-4">
                        <div v-for="(item, index) in topDiagnoses" :key="item.kode_icd10" class="space-y-1.5">
                            <div class="flex justify-between items-start text-xs">
                                <div class="flex gap-2 items-start max-w-[80%]">
                                    <span :class="['w-5 h-5 shrink-0 rounded-full flex items-center justify-center font-bold text-[10px] text-white', getRankColor(index)]">
                                        {{ index + 1 }}
                                    </span>
                                    <div>
                                        <span class="font-mono font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">{{ item.kode_icd10 }}</span>
                                        <p class="font-medium text-gray-800 mt-1 line-clamp-2 leading-tight">{{ item.diagnosis_utama }}</p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold text-violet-700 text-xs">{{ item.total_kasus }} Kasus</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ item.total_pasien }} Pasien</p>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div :class="['h-full rounded-full transition-all duration-500', getRankColor(index)]" :style="{ width: getBarWidth(item.total_kasus) + '%' }"></div>
                            </div>
                        </div>
                        <div v-if="topDiagnoses.length === 0" class="flex flex-col items-center justify-center py-12 text-gray-400">
                            <i class="pi pi-inbox text-3xl mb-2 text-gray-300"></i>
                            <p class="text-xs font-semibold">Tidak ada data diagnosis di periode ini</p>
                        </div>
                    </div>
                </div>

                <!-- Overall Table (Right / Full on small screen) -->
                <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                    <div class="bg-gray-50/70 px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                        <span class="font-bold text-sm text-gray-800 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                            Rekapitulasi Keseluruhan Diagnosis (ICD-10)
                        </span>
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="pi pi-search text-gray-400 text-xs"></i>
                            </span>
                            <input 
                                v-model="globalFilter" 
                                type="text" 
                                placeholder="Cari Kode atau Diagnosis..." 
                                class="w-full pl-9 pr-3 py-1.5 text-xs bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-inner"
                            />
                        </div>
                    </div>
                    
                    <DataTable
                        :value="filteredAllDiagnoses"
                        :paginator="filteredAllDiagnoses.length > 10"
                        :rows="10"
                        dataKey="kode_icd10"
                        responsiveLayout="scroll"
                        class="p-datatable-sm text-xs flex-1"
                        emptyMessage="Tidak ada data diagnosis"
                        stripedRows
                    >
                        <Column header="No" style="width: 50px" class="text-center">
                            <template #body="{ index }">
                                {{ index + 1 }}
                            </template>
                        </Column>
                        <Column field="kode_icd10" header="Kode ICD-10" style="width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100/50 shadow-inner">{{ data.kode_icd10 }}</span>
                            </template>
                        </Column>
                        <Column field="diagnosis_utama" header="Nama Diagnosis / Penyakit">
                            <template #body="{ data }">
                                <span class="font-medium text-gray-800 leading-normal">{{ data.diagnosis_utama }}</span>
                            </template>
                        </Column>
                        <Column field="total_kasus" header="Total Kasus" style="width: 120px" sortable class="text-right">
                            <template #body="{ data }">
                                <span class="font-bold text-violet-700 bg-violet-50 border border-violet-100/50 px-2.5 py-0.5 rounded-lg shadow-sm">{{ data.total_kasus }}x</span>
                            </template>
                        </Column>
                        <Column field="total_pasien" header="Jumlah Pasien" style="width: 120px" sortable class="text-right">
                            <template #body="{ data }">
                                <span class="text-slate-600 bg-slate-50 border border-slate-200/50 px-2.5 py-0.5 rounded-lg">{{ data.total_pasien }} orang</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
