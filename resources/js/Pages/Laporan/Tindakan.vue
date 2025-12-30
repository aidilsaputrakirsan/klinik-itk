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
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('laporan.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Laporan Tindakan</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filter -->
            <Card class="shadow-sm">
                <template #content>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Tanggal Mulai</label>
                            <DatePicker v-model="startDate" dateFormat="dd/mm/yy" showIcon class="w-48" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium">Tanggal Akhir</label>
                            <DatePicker v-model="endDate" dateFormat="dd/mm/yy" showIcon class="w-48" />
                        </div>
                        <Button label="Terapkan Filter" icon="pi pi-filter" @click="applyFilter" />
                        <Button label="Download PDF" icon="pi pi-file-pdf" severity="success" @click="downloadPdf" />
                    </div>
                </template>
            </Card>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ summary.totalTindakan }}</p>
                            <p class="text-sm text-gray-500">Total Tindakan Dilakukan</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-emerald-600">{{ formatCurrency(summary.totalPendapatan) }}</p>
                            <p class="text-sm text-gray-500">Total Pendapatan Tindakan</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-purple-600">{{ summary.jenisTindakan }}</p>
                            <p class="text-sm text-gray-500">Jenis Tindakan</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Data Table -->
            <Card class="shadow-sm">
                <template #content>
                    <DataTable
                        :value="penggunaanTindakan"
                        :paginator="penggunaanTindakan.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada data tindakan"
                    >
                        <Column field="kode" header="Kode" style="width: 100px" />
                        <Column field="nama" header="Nama Tindakan" />
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
                                {{ data.frekuensi }} kunjungan
                            </template>
                        </Column>
                        <Column field="total_pendapatan" header="Total Pendapatan" style="width: 180px">
                            <template #body="{ data }">
                                <span class="font-bold text-emerald-600">{{ formatCurrency(data.total_pendapatan) }}</span>
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
