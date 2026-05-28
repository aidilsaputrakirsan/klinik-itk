<script setup lang="ts">
import { ref, computed } from 'vue';
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

const formatText = (text: string | null | undefined) => {
    if (!text) return '-';
    return text.toString()
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ');
};

const getStatusSeverity = (status: string) => {
    const severities: Record<string, string> = { mahasiswa: 'info', dosen: 'success', tendik: 'warn', umum: 'secondary' };
    return severities[status] || 'secondary';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = { mahasiswa: 'Mahasiswa', dosen: 'Dosen', tendik: 'Tendik', umum: 'Umum' };
    return labels[status] || status;
};

const applyFilter = () => {
    router.get(
        route('laporan.pemeriksaan-umum'),
        {
            start_date: formatDateYMD(startDate.value),
            end_date: formatDateYMD(endDate.value),
        },
        { preserveState: true }
    );
};

const exportPDF = () => {
    window.open(route('laporan.pemeriksaan-umum.pdf', {
        start_date: formatDateYMD(startDate.value),
        end_date: formatDateYMD(endDate.value),
    }), '_blank');
};

const exportExcel = () => {
    window.open(route('laporan.pemeriksaan-umum.excel', {
        start_date: formatDateYMD(startDate.value),
        end_date: formatDateYMD(endDate.value),
    }), '_blank');
};
</script>

<template>
    <Head title="Laporan Pemeriksaan Umum" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans font-inter flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            <i class="pi pi-folder-open text-purple-600 text-base"></i>
                            Laporan Pemeriksaan Umum
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Data rekam medis horizontal untuk pasien layanan umum</p>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button @click="exportPDF" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white rounded-lg font-medium shadow-sm transition-all transform hover:-translate-y-0.5">
                        <i class="pi pi-file-pdf text-xs"></i>
                        <span>Export PDF</span>
                    </button>
                    <button @click="exportExcel" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-lg font-medium shadow-sm transition-all transform hover:-translate-y-0.5">
                        <i class="pi pi-file-excel text-xs"></i>
                        <span>Export Excel</span>
                    </button>
                </div>
            </div>
        </template>

        <div class="font-sans font-inter space-y-6 pb-8">
            <!-- Filter Section -->
            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Awal</span>
                        <DatePicker v-model="startDate" dateFormat="dd/mm/yy" :showIcon="true" class="!border-gray-200 !rounded-xl !text-xs w-48 shadow-sm" inputClass="!py-2 !px-3 !text-xs" />
                    </div>
                    <div class="flex items-center h-full mt-5">
                        <i class="pi pi-arrow-right text-gray-400"></i>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Akhir</span>
                        <DatePicker v-model="endDate" dateFormat="dd/mm/yy" :showIcon="true" class="!border-gray-200 !rounded-xl !text-xs w-48 shadow-sm" inputClass="!py-2 !px-3 !text-xs" />
                    </div>
                    <div class="mt-5">
                        <Button label="Terapkan Filter" icon="pi pi-filter" @click="applyFilter" severity="success" class="!rounded-xl !text-xs font-bold shadow-sm !px-4 !py-2" />
                    </div>
                </div>
                
                <div class="flex flex-col gap-1.5 w-full md:w-64 mt-5 md:mt-0">
                    <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Data</span>
                    <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-200 focus-within:ring-2 focus-within:ring-emerald-500/20 transition-all">
                        <InputGroupAddon class="!bg-white !border-0 !px-3">
                            <i class="pi pi-search text-gray-400 text-[10px]"></i>
                        </InputGroupAddon>
                        <InputText
                            v-model="filters['global'].value"
                            placeholder="Ketik di sini..."
                            class="!border-0 !text-xs !py-2 !pl-0 focus:!ring-0 placeholder:text-gray-300"
                        />
                    </InputGroup>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">

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
                        emptyMessage="Tidak ada data rekam medis."
                    >
                        <!-- Group 1: PURPLE (#5b328a) Patient and Admin Info -->
                        <Column header="Timestamp" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body="{ data }"><span>{{ data.tanggal_kunjungan ? new Date(data.tanggal_kunjungan).toLocaleString('id-ID') : '-' }}</span></template>
                        </Column>
                        <Column header="No. RM" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body="{ data }"><span>{{ data.pasien?.no_rm || '-' }}</span></template>
                        </Column>
                        <Column header="Nama Pasien" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;" frozen>
                            <template #body="{ data }"><span>{{ data.pasien?.nama || '-' }}</span></template>
                        </Column>
                        <Column header="Tanggal Lahir" style="min-width: 140px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.tanggal_lahir ? new Date(data.pasien.tanggal_lahir).toLocaleDateString('id-ID') : '-' }}</span></template>
                        </Column>
                        <Column header="No. Identitas (NIK/NIP/NIM)" style="min-width: 180px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.nik || data.pasien?.nomor_identitas || '-' }}</span></template>
                        </Column>
                        <Column header="No. Telp" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.phone || '-' }}</span></template>
                        </Column>
                        <Column header="Jenis Kelamin" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</span></template>
                        </Column>
                        <Column header="Alamat" style="min-width: 200px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.alamat || '-' }}</span></template>
                        </Column>
                        <Column header="Agama" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ formatText(data.pasien?.agama) }}</span></template>
                        </Column>
                        <Column header="Status Perkawinan" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ formatText(data.pasien?.status_perkawinan) }}</span></template>
                        </Column>
                        <Column header="Pendidikan terakhir" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ formatText(data.pasien?.pendidikan_terakhir) }}</span></template>
                        </Column>
                        <Column header="Status di ITK" style="min-width: 120px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }">
                                <span v-if="data.pasien?.tipe_pasien" class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                    {{ getStatusLabel(data.pasien.tipe_pasien) }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Golongan Darah" style="min-width: 100px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body="{ data }"><span>{{ data.pasien?.golongan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="Petugas Administrasi" style="min-width: 150px" headerStyle="background-color: #5b328a; color: white;">
                            <template #body><span>Admin / Sistem</span></template>
                        </Column>

                        <!-- Group 2: BLUE (#4a86e8) Anamnesis / Kunjungan -->
                        <Column header="Keluhan Utama" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.keluhan_utama || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit sekarang" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_sekarang || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat penyakit dahulu" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.riwayat_penyakit_dahulu || '-' }}</span></template>
                        </Column>
                        <Column header="Riwayat Keluarga" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_keluarga || '-' }}</span></template>
                        </Column>
                        <Column header="Alergi" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.riwayat_alergi || '-' }}</span></template>
                        </Column>
                        
                        <!-- Group 3: BLUE TTV -->
                        <Column header="TTV.1 TD" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tekanan_darah || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.2 Nadi" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.nadi || '-' }}</span></template>
                        </Column>
                        <Column header="TTV.3 Suhu" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.suhu || '-' }}</span></template>
                        </Column>
                        <Column header="TTV. 4 RR" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.respirasi || '-' }}</span></template>
                        </Column>
                        <Column header="Berat Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.berat_badan || '-' }}</span></template>
                        </Column>
                        <Column header="Tinggi Badan" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.tinggi_badan || '-' }}</span></template>
                        </Column>
                        <Column header="IMT" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <span v-if="data.anamnesis?.berat_badan && data.anamnesis?.tinggi_badan">
                                    {{ (data.anamnesis.berat_badan / Math.pow(data.anamnesis.tinggi_badan / 100, 2)).toFixed(2) }}
                                </span>
                                <span v-else>-</span>
                            </template>
                        </Column>
                        <Column header="Skala Nyeri" style="min-width: 80px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.skala_nyeri ?? '-' }}</span></template>
                        </Column>

                        <!-- Group 4: BLUE Pemeriksaan/Tindakan -->
                        <Column header="Pemeriksaan Fisik Lain" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.pemeriksaan_fisik || '-' }}</span></template>
                        </Column>
                        <Column header="Dokter penanggung jawab" style="min-width: 180px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.pemeriksaan?.dokter?.name || '-' }}</span></template>
                        </Column>
                        <Column header="Diagnosa medis" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <div>
                                    <strong class="block">{{ data.pemeriksaan?.diagnosis_utama || '-' }}</strong>
                                    <span v-if="data.pemeriksaan?.diagnosis_sekunder" class="text-[10px] mt-1">{{ data.pemeriksaan.diagnosis_sekunder }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Penatalaksanaan Medis" style="min-width: 250px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.pemeriksaan?.penatalaksanaan_medis || '-' }}</span></template>
                        </Column>

                        <!-- Group 5: BLUE Keperawatan -->
                        <Column header="Diagnosa Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.diagnosa_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Intervensi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.intervensi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Implementasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.implementasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Evaluasi Keperawatan" style="min-width: 200px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span class="whitespace-break-spaces">{{ data.anamnesis?.evaluasi_keperawatan || '-' }}</span></template>
                        </Column>
                        <Column header="Perawat" style="min-width: 150px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }"><span>{{ data.anamnesis?.perawat?.name || '-' }}</span></template>
                        </Column>
                    </DataTable>
                </div>
            </div>
    </AppLayout>
</template>
