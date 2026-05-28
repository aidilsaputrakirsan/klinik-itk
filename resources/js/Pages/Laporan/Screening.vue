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
import Tag from 'primevue/tag';

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

const formatDate = (date: string) => {
    if (!date) return '-';
    const d = new Date(date);
    const day = d.getDate().toString().padStart(2, '0');
    const month = d.toLocaleString('id-ID', { month: 'short' });
    const year = d.getFullYear();
    const hours = d.getHours().toString().padStart(2, '0');
    const minutes = d.getMinutes().toString().padStart(2, '0');
    if (hours === '00' && minutes === '00') {
        return `${day} ${month} ${year}`;
    }
    return `${day} ${month} ${year}, ${hours}:${minutes}`;
};

const getAge = (birthDate: string | undefined) => {
    if (!birthDate) return '-';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    return age;
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = { mahasiswa: 'Mahasiswa', dosen: 'Dosen', tendik: 'Tendik', umum: 'Umum' };
    return labels[status] || status;
};

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
    
    return { value: lp, status: isCritical ? 'Obesitas Sentral' : 'Normal', isCritical };
};

const getTdData = (td: string | null | undefined) => {
    if (!td) return { value: '-', category: '-', isCritical: false };
    const parts = td.split('/');
    if (parts.length !== 2) return { value: td, category: '-', isCritical: false };
    
    const sys = parseInt(parts[0]);
    const dia = parseInt(parts[1]);
    let category = '';
    let isCritical = false;
    
    if (sys < 130 && dia < 85) { category = 'Normal (<129/84)'; }
    else if (sys <= 139 || dia <= 89) { category = 'Prehipertensi (130/85-139/89)'; isCritical = true; }
    else if (sys <= 159 || dia <= 99) { category = 'Hipertensi Grade 1 (140/90-159/99)'; isCritical = true; }
    else { category = 'Hipertensi Grade 2 (>160/100)'; isCritical = true; }
    
    return { value: td, category, isCritical };
};

const getGdData = (gd: number | null | undefined, jenis: string | null | undefined) => {
    if (!gd) return { value: '-', category: '-', isCritical: false };
    let category = 'Normal';
    let isCritical = false;
    if (jenis === 'puasa') {
        if (gd > 120) { isCritical = true; category = 'Hiperglikemia (GDP: >120)'; }
    } else {
        if (gd > 200) { isCritical = true; category = 'Hiperglikemia (GDS: >200)'; }
    }
    return { value: gd, category, isCritical };
};

const getAuData = (au: number | null | undefined, gender: string | undefined) => {
    if (!au) return { value: '-', category: '-', isCritical: false };
    let isCritical = false;
    let label = 'Normal';
    if (gender === 'L' && au > 7) { isCritical = true; label = 'Hiperuricemia (L: >7)'; }
    if (gender === 'P' && au > 6) { isCritical = true; label = 'Hiperuricemia (P: >6)'; }
    return { value: au, category: label, isCritical };
};

const getCholData = (chol: number | null | undefined) => {
    if (!chol) return { value: '-', category: '-', isCritical: false };
    const isCritical = chol > 200;
    return { value: chol, category: isCritical ? 'Hipercholesterolemia (>200)' : 'Normal', isCritical };
};

const getHbData = (hb: number | null | undefined) => {
    if (!hb) return { value: '-', category: '-', isCritical: false };
    const isCritical = hb < 12;
    return { value: hb, category: isCritical ? 'Anemia (< 12)' : 'Normal', isCritical };
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
                        class="p-datatable-sm w-full excel-table text-xs"
                        responsiveLayout="scroll"
                        scrollable
                        scrollDirection="both"
                        stripedRows
                        :rowsPerPageOptions="[10, 25, 50]"
                        emptyMessage="Tidak ada data screening."
                    >
                        <!-- Meta Info -->
                        <Column header="No" style="min-width: 50px" frozen>
                            <template #body="{ index }"><span>{{ index + 1 }}</span></template>
                        </Column>
                        <Column header="Timestamp" style="min-width: 130px" frozen>
                            <template #body="{ data }"><span>{{ formatDate(data.created_at || data.tanggal_kunjungan) }}</span></template>
                        </Column>
                        
                        <Column header="Nama" style="min-width: 180px" frozen>
                            <template #body="{ data }"><span>{{ data.pasien?.nama || '-' }}</span></template>
                        </Column>
                        <Column header="Umur" style="min-width: 80px">
                            <template #body="{ data }"><span>{{ getAge(data.pasien?.tanggal_lahir) }} Thn</span></template>
                        </Column>
                        <Column header="J.K" style="min-width: 80px">
                            <template #body="{ data }"><span>{{ data.pasien?.jenis_kelamin === 'L' ? 'L' : 'P' }}</span></template>
                        </Column>
                        <Column header="Status ITK" style="min-width: 100px">
                            <template #body="{ data }"><span>{{ getStatusLabel(data.pasien?.tipe_pasien) }}</span></template>
                        </Column>
                        
                        <!-- Screening Measurements -->
                        <Column header="Tinggi Badan (cm)" style="min-width: 130px" headerStyle="background-color: #2e7d32; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tinggi_badan || '-' }}</span></template>
                        </Column>
                        <Column header="Berat Badan (kg)" style="min-width: 130px" headerStyle="background-color: #2e7d32; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.berat_badan || '-' }}</span></template>
                        </Column>
                        <Column header="IMT" style="min-width: 80px" headerStyle="background-color: #2e7d32; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).isCritical}">
                                    {{ getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori IMT" style="min-width: 150px" headerStyle="background-color: #2e7d32; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).isCritical}">
                                    {{ getBmiData(data.anamnesis?.tinggi_badan, data.anamnesis?.berat_badan).category }}
                                </div>
                            </template>
                        </Column>
                        
                        <Column header="Lingkar Perut (cm)" style="min-width: 150px" headerStyle="background-color: #0277bd; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, data.pasien?.jenis_kelamin).isCritical}">
                                    {{ getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, data.pasien?.jenis_kelamin).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Status LP" style="min-width: 150px" headerStyle="background-color: #0277bd; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, data.pasien?.jenis_kelamin).isCritical, 'bg-pink-100 text-pink-800 font-bold px-2 py-1 rounded': getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, data.pasien?.jenis_kelamin).status === 'Hamil'}">
                                    {{ getLpData(data.anamnesis?.lingkar_perut, data.anamnesis?.is_hamil, data.pasien?.jenis_kelamin).status }}
                                </div>
                            </template>
                        </Column>
                        
                        <Column header="Tensi Sistolik/Diastolik" style="min-width: 170px" headerStyle="background-color: #f57c00; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getTdData(data.anamnesis?.tekanan_darah).isCritical}">
                                    {{ data.anamnesis?.tekanan_darah || '-' }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori Tekanan Darah" style="min-width: 180px" headerStyle="background-color: #f57c00; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getTdData(data.anamnesis?.tekanan_darah).isCritical}">
                                    {{ getTdData(data.anamnesis?.tekanan_darah).category }}
                                </div>
                            </template>
                        </Column>
                        
                        <!-- Gula Darah -->
                        <Column header="Gula Darah (mg/dL)" style="min-width: 150px" headerStyle="background-color: #d84315; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).isCritical}">
                                    {{ getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori Gula Darah" style="min-width: 170px" headerStyle="background-color: #d84315; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).isCritical}">
                                    {{ getGdData(data.anamnesis?.gula_darah, data.anamnesis?.jenis_gula_darah).category }}
                                </div>
                            </template>
                        </Column>

                        <!-- Asam Urat -->
                        <Column header="Asam Urat (mg/dL)" style="min-width: 150px" headerStyle="background-color: #558b2f; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getAuData(data.anamnesis?.asam_urat, data.pasien?.jenis_kelamin).isCritical}">
                                    {{ getAuData(data.anamnesis?.asam_urat, data.pasien?.jenis_kelamin).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori Asam Urat" style="min-width: 170px" headerStyle="background-color: #558b2f; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getAuData(data.anamnesis?.asam_urat, data.pasien?.jenis_kelamin).isCritical}">
                                    {{ getAuData(data.anamnesis?.asam_urat, data.pasien?.jenis_kelamin).category }}
                                </div>
                            </template>
                        </Column>

                        <!-- Kolesterol -->
                        <Column header="Kolesterol (mg/dL)" style="min-width: 150px" headerStyle="background-color: #6a1b9a; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getCholData(data.anamnesis?.kolesterol).isCritical}">
                                    {{ getCholData(data.anamnesis?.kolesterol).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori Kolesterol" style="min-width: 170px" headerStyle="background-color: #6a1b9a; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getCholData(data.anamnesis?.kolesterol).isCritical}">
                                    {{ getCholData(data.anamnesis?.kolesterol).category }}
                                </div>
                            </template>
                        </Column>

                        <!-- Hemoglobin -->
                        <Column header="Hemoglobin (g/dL)" style="min-width: 150px" headerStyle="background-color: #c62828; color: white;">
                            <template #body="{ data }">
                                <span :class="{'text-red-600 font-bold': getHbData(data.anamnesis?.hemoglobin).isCritical}">
                                    {{ getHbData(data.anamnesis?.hemoglobin).value }}
                                </span>
                            </template>
                        </Column>
                        <Column header="Kategori Hemoglobin" style="min-width: 170px" headerStyle="background-color: #c62828; color: white;">
                            <template #body="{ data }">
                                <div :class="{'bg-red-100 text-red-800 font-bold px-2 py-1 rounded': getHbData(data.anamnesis?.hemoglobin).isCritical}">
                                    {{ getHbData(data.anamnesis?.hemoglobin).category }}
                                </div>
                            </template>
                        </Column>
                        <!-- History & Actions -->
                        <Column header="Keterangan" style="min-width: 150px" headerStyle="background-color: #4527a0; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.keterangan_tindak_lanjut || '-' }}</span></template>
                        </Column>
                        <Column header="Tindak Lanjut" style="min-width: 150px" headerStyle="background-color: #4527a0; color: white;">
                            <template #body="{ data }">
                                <Tag :value="data.anamnesis?.tindak_lanjut === 'rujuk' ? 'Kembali ke Faskes 1' : (data.anamnesis?.tindak_lanjut === 'rawat_jalan' ? 'Rawat Jalan' : (data.anamnesis?.tindak_lanjut === 'edukasi' ? 'Edukasi' : 'Belum Ada'))" :severity="data.anamnesis?.tindak_lanjut === 'rujuk' ? 'danger' : (data.anamnesis?.tindak_lanjut === 'rawat_jalan' ? 'info' : (data.anamnesis?.tindak_lanjut === 'edukasi' ? 'success' : 'secondary'))" />
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
