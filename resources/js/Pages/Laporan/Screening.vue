<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';

const props = defineProps<{
    rekamMedis: any[];
    filters: {
        start_date: string;
        end_date: string;
    };
}>();

const startDate = ref(new Date(props.filters.start_date));
const endDate = ref(new Date(props.filters.end_date));
const filters = ref({
    global: { value: null, matchMode: 'contains' },
});

const formatDateYMD = (date: Date) => {
    const d = new Date(date);
    const month = '' + (d.getMonth() + 1);
    const day = '' + d.getDate();
    const year = d.getFullYear();
    return [year, month.padStart(2, '0'), day.padStart(2, '0')].join('-');
};

const applyFilter = () => {
    router.get(
        route('laporan.screening'),
        {
            start_date: formatDateYMD(startDate.value),
            end_date: formatDateYMD(endDate.value),
        },
        { preserveState: true }
    );
};

const exportPDF = () => {
    window.open(route('laporan.screening.pdf', {
        start_date: formatDateYMD(startDate.value),
        end_date: formatDateYMD(endDate.value),
    }), '_blank');
};

const exportExcel = () => {
    window.open(route('laporan.screening.excel', {
        start_date: formatDateYMD(startDate.value),
        end_date: formatDateYMD(endDate.value),
    }), '_blank');
};
</script>

<template>
    <Head title="Laporan Screening" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Button icon="pi pi-arrow-left" text rounded @click="router.get(route('laporan.index'))" />
                <h2>Laporan Screening</h2>
            </div>
        </template>

        <div class="space-y-6">
            <Card class="shadow-sm">
                <template #content>
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 mb-6">
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Awal</label>
                                <Calendar v-model="startDate" dateFormat="dd/mm/yy" :showIcon="true" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                <Calendar v-model="endDate" dateFormat="dd/mm/yy" :showIcon="true" />
                            </div>
                            <Button label="Terapkan Filter" icon="pi pi-filter" @click="applyFilter" />
                        </div>
                        <div class="flex gap-2">
                            <Button label="Export PDF" icon="pi pi-file-pdf" severity="danger" @click="exportPDF" />
                            <Button label="Export Excel" icon="pi pi-file-excel" severity="success" @click="exportExcel" />
                        </div>
                    </div>

                    <div class="flex justify-end mb-4">
                        <span class="p-input-icon-left">
                            <i class="pi pi-search" />
                            <InputText v-model="filters['global'].value" placeholder="Cari data..." />
                        </span>
                    </div>

                    <DataTable
                        :value="rekamMedis"
                        :paginator="true"
                        :rows="10"
                        dataKey="id"
                        :filters="filters"
                        class="p-datatable-sm w-full"
                        responsiveLayout="scroll"
                        scrollable
                        stripedRows
                        :rowsPerPageOptions="[10, 25, 50]"
                        emptyMessage="Tidak ada data screening."
                    >
                        <Column field="nomor_kunjungan" header="No. Kunjungan" sortable style="min-width: 140px" frozen></Column>
                        <Column field="tanggal_kunjungan" header="Tgl Kunjungan" sortable style="min-width: 130px" frozen>
                            <template #body="slotProps">
                                {{ slotProps.data.tanggal_kunjungan ? new Date(slotProps.data.tanggal_kunjungan).toLocaleDateString('id-ID') : '-' }}
                            </template>
                        </Column>
                        <Column field="pasien.nama" header="Nama Pasien" sortable style="min-width: 160px" frozen></Column>
                        
                        <!-- Screening / Anamnesis Data -->
                        <Column header="Antropometri" style="min-width: 150px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.anamnesis" class="text-sm">
                                    TB: {{ slotProps.data.anamnesis.tinggi_badan || '-' }} cm<br>
                                    BB: {{ slotProps.data.anamnesis.berat_badan || '-' }} kg<br>
                                    LP: {{ slotProps.data.anamnesis.lingkar_perut || '-' }} cm
                                </div>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="TTV" style="min-width: 180px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.anamnesis" class="text-sm">
                                    TD: {{ slotProps.data.anamnesis.tekanan_darah || '-' }} mmHg<br>
                                    Nadi: {{ slotProps.data.anamnesis.nadi || '-' }} x/mnt<br>
                                    Suhu: {{ slotProps.data.anamnesis.suhu || '-' }} °C<br>
                                    RR: {{ slotProps.data.anamnesis.respirasi || '-' }} x/mnt
                                </div>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Lab Darah" style="min-width: 180px">
                            <template #body="slotProps">
                                <div v-if="slotProps.data.anamnesis" class="text-sm">
                                    Gula ({{ slotProps.data.anamnesis.jenis_gula_darah || '-' }}): {{ slotProps.data.anamnesis.gula_darah || '-' }}<br>
                                    Kolesterol: {{ slotProps.data.anamnesis.kolesterol || '-' }}<br>
                                    Asam Urat: {{ slotProps.data.anamnesis.asam_urat || '-' }}<br>
                                    Hb: {{ slotProps.data.anamnesis.hemoglobin || '-' }}
                                </div>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Buta Warna" style="min-width: 120px">
                            <template #body="slotProps">
                                {{ slotProps.data.anamnesis?.buta_warna || '-' }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
