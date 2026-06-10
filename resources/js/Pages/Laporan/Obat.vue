<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import DatePicker from 'primevue/datepicker';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';

interface PenggunaanObat {
    id: number;
    kode: string;
    nama: string;
    satuan: string;
    total_penggunaan: number;
    frekuensi: number;
}

interface StokObat {
    id: number;
    kode: string;
    nama: string;
    satuan: string;
    stok: number;
    stok_minimum: number;
    status: 'rendah' | 'normal';
}

interface Props {
    penggunaanObat: PenggunaanObat[];
    stokObat: StokObat[];
    summary: {
        totalPenggunaan: number;
        jenisObatDigunakan: number;
        obatStokRendah: number;
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
    router.get(route('laporan.obat'), {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const downloadPdf = () => {
    window.open(route('laporan.obat.pdf', {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }), '_blank');
};
</script>

<template>
    <Head title="Laporan Obat" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            Laporan Obat
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Pemantauan penggunaan, ketersediaan, dan peringatan stok obat</p>
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
                    <p class="text-4xl font-black text-blue-600 z-10">{{ summary.totalPenggunaan }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Total Penggunaan Obat</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-emerald-600 z-10">{{ summary.jenisObatDigunakan }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Jenis Obat Digunakan</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden group hover:shadow-lg transition-shadow">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <p class="text-4xl font-black text-red-600 z-10">{{ summary.obatStokRendah }}</p>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mt-2 z-10">Obat Stok Rendah</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden p-4">
                <TabView>
                    <TabPanel value="0" header="Penggunaan Obat">
                        <DataTable
                            :value="penggunaanObat"
                            :paginator="penggunaanObat.length > 10"
                            :rows="10"
                            dataKey="id"
                            responsiveLayout="scroll"
                            class="p-datatable-sm text-sm mt-2"
                            emptyMessage="Tidak ada data penggunaan obat"
                            stripedRows
                        >
                            <Column field="kode" header="Kode" style="width: 100px" />
                            <Column field="nama" header="Nama Obat" />
                            <Column field="satuan" header="Satuan" style="width: 100px" />
                            <Column field="total_penggunaan" header="Total Penggunaan" style="width: 150px">
                                <template #body="{ data }">
                                    <span class="font-bold text-blue-600">{{ data.total_penggunaan }}</span> <span class="text-gray-500 text-xs">{{ data.satuan }}</span>
                                </template>
                            </Column>
                            <Column field="frekuensi" header="Frekuensi Resep" style="width: 150px">
                                <template #body="{ data }">
                                    <Tag :value="data.frekuensi + 'x'" severity="info" rounded />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                    <TabPanel value="1" header="Stok Obat Saat Ini">
                        <DataTable
                            :value="stokObat"
                            :paginator="stokObat.length > 10"
                            :rows="10"
                            dataKey="id"
                            responsiveLayout="scroll"
                            class="p-datatable-sm text-sm mt-2"
                            emptyMessage="Tidak ada data obat"
                            stripedRows
                        >
                            <Column field="kode" header="Kode" style="width: 100px" />
                            <Column field="nama" header="Nama Obat">
                                <template #body="{ data }">
                                    <span class="font-medium text-gray-800">{{ data.nama }}</span>
                                </template>
                            </Column>
                            <Column field="satuan" header="Satuan" style="width: 100px" />
                            <Column field="stok" header="Stok" style="width: 100px">
                                <template #body="{ data }">
                                    <span :class="data.status === 'rendah' ? 'text-red-600 font-bold bg-red-50 px-2 py-1 rounded' : 'font-medium'">
                                        {{ data.stok }}
                                    </span>
                                </template>
                            </Column>
                            <Column field="stok_minimum" header="Stok Min" style="width: 100px">
                                <template #body="{ data }">
                                    <span class="text-gray-500">{{ data.stok_minimum }}</span>
                                </template>
                            </Column>
                            <Column field="status" header="Status" style="width: 120px">
                                <template #body="{ data }">
                                    <Tag
                                        :value="data.status === 'rendah' ? 'Stok Rendah' : 'Normal'"
                                        :severity="data.status === 'rendah' ? 'danger' : 'success'"
                                        rounded
                                    />
                                </template>
                            </Column>
                        </DataTable>
                    </TabPanel>
                </TabView>
            </div>
        </div>
    </AppLayout>
</template>
