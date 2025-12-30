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
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('laporan.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Laporan Obat</span>
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
                            <p class="text-3xl font-bold text-blue-600">{{ summary.totalPenggunaan }}</p>
                            <p class="text-sm text-gray-500">Total Penggunaan Obat</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-emerald-600">{{ summary.jenisObatDigunakan }}</p>
                            <p class="text-sm text-gray-500">Jenis Obat Digunakan</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-red-600">{{ summary.obatStokRendah }}</p>
                            <p class="text-sm text-gray-500">Obat Stok Rendah</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Tabs -->
            <Card class="shadow-sm">
                <template #content>
                    <TabView>
                        <TabPanel value="0" header="Penggunaan Obat">
                            <DataTable
                                :value="penggunaanObat"
                                :paginator="penggunaanObat.length > 10"
                                :rows="10"
                                dataKey="id"
                                responsiveLayout="scroll"
                                class="p-datatable-sm"
                                emptyMessage="Tidak ada data penggunaan obat"
                            >
                                <Column field="kode" header="Kode" style="width: 100px" />
                                <Column field="nama" header="Nama Obat" />
                                <Column field="satuan" header="Satuan" style="width: 100px" />
                                <Column field="total_penggunaan" header="Total Penggunaan" style="width: 150px">
                                    <template #body="{ data }">
                                        <span class="font-bold text-blue-600">{{ data.total_penggunaan }}</span> {{ data.satuan }}
                                    </template>
                                </Column>
                                <Column field="frekuensi" header="Frekuensi Resep" style="width: 150px">
                                    <template #body="{ data }">
                                        {{ data.frekuensi }}x
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
                                class="p-datatable-sm"
                                emptyMessage="Tidak ada data obat"
                            >
                                <Column field="kode" header="Kode" style="width: 100px" />
                                <Column field="nama" header="Nama Obat" />
                                <Column field="satuan" header="Satuan" style="width: 100px" />
                                <Column field="stok" header="Stok" style="width: 100px">
                                    <template #body="{ data }">
                                        <span :class="data.status === 'rendah' ? 'text-red-600 font-bold' : ''">
                                            {{ data.stok }}
                                        </span>
                                    </template>
                                </Column>
                                <Column field="stok_minimum" header="Stok Min" style="width: 100px" />
                                <Column field="status" header="Status" style="width: 120px">
                                    <template #body="{ data }">
                                        <Tag
                                            :value="data.status === 'rendah' ? 'Stok Rendah' : 'Normal'"
                                            :severity="data.status === 'rendah' ? 'danger' : 'success'"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
