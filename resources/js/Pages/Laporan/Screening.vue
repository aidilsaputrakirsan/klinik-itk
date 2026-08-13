<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Tag from 'primevue/tag';
import XLSX from 'xlsx-js-style';

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
    if (!props.rekamMedis || props.rekamMedis.length === 0) {
        alert('Tidak ada data screening untuk diexport.');
        return;
    }

    let no = 0;
    const dataToExport = props.rekamMedis.map((item) => {
        no++;
        const anamnesis = item.anamnesis;
        const pasien = item.pasien;
        const gender = pasien?.jenis_kelamin;

        const bmi = getBmiData(anamnesis?.tinggi_badan, anamnesis?.berat_badan);
        const lp = getLpData(anamnesis?.lingkar_perut, anamnesis?.is_hamil, gender);
        const td = getTdData(anamnesis?.tekanan_darah);
        const gd = getGdData(anamnesis?.gula_darah, anamnesis?.jenis_gula_darah);
        const au = getAuData(anamnesis?.asam_urat, gender);
        const chol = getCholData(anamnesis?.kolesterol);
        const hb = getHbData(anamnesis?.hemoglobin);

        let tindakLanjutLabel = 'Belum Ada';
        if (anamnesis?.tindak_lanjut) {
            if (anamnesis.tindak_lanjut === 'rujuk') tindakLanjutLabel = 'Kembali ke Faskes 1';
            else if (anamnesis.tindak_lanjut === 'rawat_jalan') tindakLanjutLabel = 'Rawat Jalan';
            else if (anamnesis.tindak_lanjut === 'edukasi') tindakLanjutLabel = 'Edukasi';
        }

        return {
            'No': no,
            'Timestamp': item.tanggal_kunjungan ? formatDate(item.tanggal_kunjungan) : '-',
            'Nama Pasien': pasien?.nama ?? '-',
            'Umur': pasien ? getAge(pasien.tanggal_lahir) + ' Thn' : '-',
            'J.K': pasien ? (pasien.jenis_kelamin === 'L' ? 'L' : 'P') : '-',
            'Status ITK': pasien ? getStatusLabel(pasien.tipe_pasien) : '-',
            'Fakultas': pasien && ['mahasiswa', 'dosen'].includes(pasien.tipe_pasien) ? (pasien.fakultas || '-') : '-',
            'Unit Kerja': pasien && pasien.tipe_pasien === 'tendik' ? (pasien.fakultas || '-') : '-',
            'Program Studi': pasien && ['mahasiswa', 'dosen'].includes(pasien.tipe_pasien) ? (pasien.prodi || '-') : '-',
            'Tinggi Badan (cm)': anamnesis?.tinggi_badan ?? '-',
            'Berat Badan (kg)': anamnesis?.berat_badan ?? '-',
            'IMT': bmi.value,
            'Kategori IMT': bmi.category,
            'Lingkar Perut (cm)': lp.value,
            'Status LP': lp.status,
            'Tensi Sistolik/Diastolik': td.value,
            'Kategori Tekanan Darah': td.category,
            'Gula Darah (mg/dL)': gd.value,
            'Kategori Gula Darah': gd.category,
            'Asam Urat (mg/dL)': au.value,
            'Kategori Asam Urat': au.category,
            'Kolesterol (mg/dL)': chol.value,
            'Kategori Kolesterol': chol.category,
            'Hemoglobin (g/dL)': hb.value,
            'Kategori Hemoglobin': hb.category,
            'Keterangan': anamnesis?.keterangan_tindak_lanjut ?? '-',
            'Tindak Lanjut': tindakLanjutLabel,
        };
    });

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);

    // Map header colors based on web UI design
    const headerColorMap: Record<string, { bg: string; text: string }> = {
        'No': { bg: 'F3F4F6', text: '374151' },
        'Timestamp': { bg: 'F3F4F6', text: '374151' },
        'Nama Pasien': { bg: 'F3F4F6', text: '374151' },
        'Umur': { bg: 'F3F4F6', text: '374151' },
        'J.K': { bg: 'F3F4F6', text: '374151' },
        'Status ITK': { bg: 'F3F4F6', text: '374151' },
        'Fakultas': { bg: 'F3F4F6', text: '374151' },
        'Unit Kerja': { bg: 'F3F4F6', text: '374151' },
        'Program Studi': { bg: 'F3F4F6', text: '374151' },
        'Tinggi Badan (cm)': { bg: '2E7D32', text: 'FFFFFF' },
        'Berat Badan (kg)': { bg: '2E7D32', text: 'FFFFFF' },
        'IMT': { bg: '2E7D32', text: 'FFFFFF' },
        'Kategori IMT': { bg: '2E7D32', text: 'FFFFFF' },
        'Lingkar Perut (cm)': { bg: '0277BD', text: 'FFFFFF' },
        'Status LP': { bg: '0277BD', text: 'FFFFFF' },
        'Tensi Sistolik/Diastolik': { bg: 'F57C00', text: 'FFFFFF' },
        'Kategori Tekanan Darah': { bg: 'F57C00', text: 'FFFFFF' },
        'Gula Darah (mg/dL)': { bg: 'D84315', text: 'FFFFFF' },
        'Kategori Gula Darah': { bg: 'D84315', text: 'FFFFFF' },
        'Asam Urat (mg/dL)': { bg: '558B2F', text: 'FFFFFF' },
        'Kategori Asam Urat': { bg: '558B2F', text: 'FFFFFF' },
        'Kolesterol (mg/dL)': { bg: '6A1B9A', text: 'FFFFFF' },
        'Kategori Kolesterol': { bg: '6A1B9A', text: 'FFFFFF' },
        'Hemoglobin (g/dL)': { bg: 'C62828', text: 'FFFFFF' },
        'Kategori Hemoglobin': { bg: 'C62828', text: 'FFFFFF' },
        'Keterangan': { bg: '4527A0', text: 'FFFFFF' },
        'Tindak Lanjut': { bg: '4527A0', text: 'FFFFFF' },
    };

    const range = XLSX.utils.decode_range(worksheet['!ref'] || 'A1');

    // Style Header Row
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C });
        const cell = worksheet[cellAddress];
        if (!cell) continue;

        const headerTitle = String(cell.v);
        const styleInfo = headerColorMap[headerTitle] || { bg: '2E7D32', text: 'FFFFFF' };

        cell.s = {
            fill: {
                patternType: 'solid',
                fgColor: { rgb: styleInfo.bg },
            },
            font: {
                name: 'Calibri',
                sz: 11,
                bold: true,
                color: { rgb: styleInfo.text },
            },
            alignment: {
                vertical: 'center',
                horizontal: 'center',
                wrapText: true,
            },
            border: {
                top: { style: 'thin', color: { rgb: 'D1D5DB' } },
                bottom: { style: 'medium', color: { rgb: '9CA3AF' } },
                left: { style: 'thin', color: { rgb: 'D1D5DB' } },
                right: { style: 'thin', color: { rgb: 'D1D5DB' } },
            },
        };
    }

    // Style Data Rows
    for (let R = 1; R <= range.e.r; ++R) {
        for (let C = range.s.c; C <= range.e.c; ++C) {
            const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
            const cell = worksheet[cellAddress];
            if (!cell) continue;

            cell.s = {
                font: { name: 'Calibri', sz: 10 },
                alignment: {
                    vertical: 'center',
                    horizontal: C === 0 ? 'center' : 'left',
                },
                border: {
                    top: { style: 'thin', color: { rgb: 'E5E7EB' } },
                    bottom: { style: 'thin', color: { rgb: 'E5E7EB' } },
                    left: { style: 'thin', color: { rgb: 'E5E7EB' } },
                    right: { style: 'thin', color: { rgb: 'E5E7EB' } },
                },
            };
        }
    }

    // Auto Column Widths
    const colWidths = (Object.keys(dataToExport[0]) as (keyof typeof dataToExport[0])[]).map(key => {
        const maxLen = Math.max(
            key.length,
            ...dataToExport.map(row => String(row[key] || '').length)
        );
        return { wch: Math.min(Math.max(maxLen + 4, 10), 35) };
    });
    worksheet['!cols'] = colWidths;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Laporan Screening');

    const startStr = formatDateYMD(startDate.value);
    const endStr = formatDateYMD(endDate.value);
    XLSX.writeFile(workbook, `Laporan_Screening_${startStr}_sampai_${endStr}.xlsx`);
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
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
                            Laporan Screening
                        </h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Data lengkap hasil screening pasien</p>
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

        <div class="font-sans space-y-6 pb-8">
            <!-- Filter Section -->
            <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Awal</span>
                        <DatePicker v-model="startDate" dateFormat="dd/mm/yy" :showIcon="true" iconDisplay="input" class="w-44" inputClass="!border-gray-200 !rounded-xl !text-xs !py-2 !pl-3 !pr-10 shadow-sm w-full" />
                    </div>
                    <div class="flex items-center h-full mt-5">
                        <i class="pi pi-arrow-right text-gray-400"></i>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest ml-1">Tanggal Akhir</span>
                        <DatePicker v-model="endDate" dateFormat="dd/mm/yy" :showIcon="true" iconDisplay="input" class="w-44" inputClass="!border-gray-200 !rounded-xl !text-xs !py-2 !pl-3 !pr-10 shadow-sm w-full" />
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
                </div>
            </div>
    </AppLayout>
</template>
