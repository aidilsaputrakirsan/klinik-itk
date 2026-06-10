<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import DatePicker from 'primevue/datepicker';

interface PenggunaanTindakan {
    id: number;
    kode: string;
    nama: string;
    biaya: number;
    total_penggunaan: number;
    total_pendapatan: number;
    frekuensi: number;
}

interface Props {
    penggunaanTindakan: PenggunaanTindakan[];
    summary: {
        totalTindakan: number;
        totalPendapatan: number;
        jenisTindakan: number;
    };
    filters: {
        start_date: string;
        end_date: string;
    };
}

const props = defineProps<Props>();

const startDate = ref(new Date(props.filters.start_date));
const endDate = ref(new Date(props.filters.end_date));

const applyFilter = () => {
    router.get(route('laporan.tindakan'), {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const downloadPdf = () => {
    window.open(route('laporan.tindakan.pdf', {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }), '_blank');
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Laporan Tindakan" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            Laporan Tindakan
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Detail tindakan medis yang diberikan serta estimasi finansial</p>
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

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-blue-600 z-10">{{ summary.totalTindakan }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Tindakan Dilakukan</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-emerald-600 z-10">{{ formatCurrency(summary.totalPendapatan) }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Pendapatan Tindakan</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-purple-600 z-10">{{ summary.jenisTindakan }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Jenis Tindakan</p>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 font-bold text-gray-800">
                    Detail Penggunaan Tindakan
                </div>
                <DataTable
                    :value="penggunaanTindakan"
                    :paginator="penggunaanTindakan.length > 10"
                    :rows="10"
                    dataKey="id"
                    responsiveLayout="scroll"
                    class="p-datatable-sm text-sm"
                    emptyMessage="Tidak ada data tindakan"
                    stripedRows
                >
                    <Column field="kode" header="Kode" style="width: 100px" />
                    <Column field="nama" header="Nama Tindakan">
                        <template #body="{ data }">
                            <span class="font-medium text-gray-800">{{ data.nama }}</span>
                        </template>
                    </Column>
                    <Column field="biaya" header="Biaya Satuan" style="width: 150px">
                        <template #body="{ data }">
                            {{ formatCurrency(data.biaya) }}
                        </template>
                    </Column>
                    <Column field="total_penggunaan" header="Jumlah" style="width: 100px">
                        <template #body="{ data }">
                            <span class="font-bold text-blue-600">{{ data.total_penggunaan }}x</span>
                        </template>
                    </Column>
                    <Column field="frekuensi" header="Frekuensi Resep" style="width: 150px">
                        <template #body="{ data }">
                            <span class="text-gray-500">{{ data.frekuensi }} kunjungan</span>
                        </template>
                    </Column>
                    <Column field="total_pendapatan" header="Total Pendapatan" style="width: 180px">
                        <template #body="{ data }">
                            <span class="font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg">{{ formatCurrency(data.total_pendapatan) }}</span>
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>
    </AppLayout>
</template>
