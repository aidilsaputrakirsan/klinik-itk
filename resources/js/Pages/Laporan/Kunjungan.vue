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

interface Pasien {
    id: number;
    nama: string;
    nomor_rm: string;
    tipe_pasien: string;
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

const startDate = ref(new Date(props.filters.start_date));
const endDate = ref(new Date(props.filters.end_date));

const applyFilter = () => {
    router.get(route('laporan.kunjungan'), {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
    }, { preserveState: true });
};

const downloadPdf = () => {
    window.open(route('laporan.kunjungan.pdf', {
        start_date: startDate.value.toISOString().split('T')[0],
        end_date: endDate.value.toISOString().split('T')[0],
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
        berobat: 'Berobat',
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
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('laporan.index')">
                    <Button icon="pi pi-arrow-left" text rounded />
                </Link>
                <span>Laporan Kunjungan</span>
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-blue-600">{{ summary.total }}</p>
                            <p class="text-sm text-gray-500">Total Kunjungan</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-emerald-600">{{ summary.selesai }}</p>
                            <p class="text-sm text-gray-500">Selesai</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-amber-600">{{ summary.menunggu }}</p>
                            <p class="text-sm text-gray-500">Dalam Proses</p>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #content>
                        <div class="text-center">
                            <p class="text-3xl font-bold text-red-600">{{ summary.batal }}</p>
                            <p class="text-sm text-gray-500">Batal</p>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Summary by Category -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Card class="shadow-sm">
                    <template #title>Berdasarkan Jenis Layanan</template>
                    <template #content>
                        <div class="space-y-2">
                            <div v-for="(count, jenis) in byJenisLayanan" :key="jenis" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>{{ getJenisLayananLabel(jenis as string) }}</span>
                                <Tag :value="count.toString()" severity="info" />
                            </div>
                            <div v-if="Object.keys(byJenisLayanan).length === 0" class="text-center text-gray-500 py-4">
                                Tidak ada data
                            </div>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #title>Berdasarkan Tipe Pasien</template>
                    <template #content>
                        <div class="space-y-2">
                            <div v-for="(count, tipe) in byTipePasien" :key="tipe" class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>{{ getTipePasienLabel(tipe as string) }}</span>
                                <Tag :value="count.toString()" severity="success" />
                            </div>
                            <div v-if="Object.keys(byTipePasien).length === 0" class="text-center text-gray-500 py-4">
                                Tidak ada data
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Data Table -->
            <Card class="shadow-sm">
                <template #title>Detail Kunjungan</template>
                <template #content>
                    <DataTable
                        :value="kunjungan"
                        :paginator="kunjungan.length > 10"
                        :rows="10"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        emptyMessage="Tidak ada data kunjungan"
                    >
                        <Column field="nomor_kunjungan" header="No. Kunjungan" style="width: 140px" />
                        <Column field="tanggal_kunjungan" header="Tanggal" style="width: 120px">
                            <template #body="{ data }">
                                {{ formatDate(data.tanggal_kunjungan) }}
                            </template>
                        </Column>
                        <Column header="Pasien">
                            <template #body="{ data }">
                                <div v-if="data.pasien">
                                    <p class="font-medium">{{ data.pasien.nama }}</p>
                                    <p class="text-xs text-gray-500">{{ data.pasien.nomor_rm }}</p>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </template>
                        </Column>
                        <Column field="jenis_layanan" header="Layanan" style="width: 120px">
                            <template #body="{ data }">
                                {{ getJenisLayananLabel(data.jenis_layanan) }}
                            </template>
                        </Column>
                        <Column field="status" header="Status" style="width: 150px">
                            <template #body="{ data }">
                                <Tag :value="getStatusLabel(data.status)" :severity="getStatusSeverity(data.status)" />
                            </template>
                        </Column>
                        <Column header="Dokter" style="width: 150px">
                            <template #body="{ data }">
                                {{ data.dokter?.name || '-' }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
