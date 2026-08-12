<script setup lang="ts">
import { ref, computed } from 'vue';
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
    if (!props.rekamMedis || props.rekamMedis.length === 0) {
        alert('Tidak ada data pemeriksaan umum untuk diexport.');
        return;
    }

    const dataToExport = props.rekamMedis.map((item) => {
        const pasien = item.pasien;
        const anamnesis = item.anamnesis;
        const pemeriksaan = item.pemeriksaan;

        let imt = '-';
        if (anamnesis && anamnesis.berat_badan && anamnesis.tinggi_badan) {
            imt = (anamnesis.berat_badan / Math.pow(anamnesis.tinggi_badan / 100, 2)).toFixed(2);
        }

        let kondisiKhusus = '-';
        if (pasien && pasien.jenis_kelamin === 'P' && anamnesis) {
            if (anamnesis.is_hamil) kondisiKhusus = 'Hamil';
            else if (anamnesis.is_menyusui) kondisiKhusus = 'Menyusui';
        }

        return {
            'Timestamp': item.tanggal_kunjungan ? formatDateYMD(new Date(item.tanggal_kunjungan)) : '-',
            'No. RM': pasien?.nomor_rm ?? '-',
            'Nama Pasien': pasien?.nama ?? '-',
            'Tanggal Lahir': pasien?.tanggal_lahir ?? '-',
            'No. Identitas (NIK/NIP/NIM)': pasien ? (pasien.nik || pasien.nomor_identitas || '-') : '-',
            'No. Telp': pasien?.phone ?? '-',
            'Jenis Kelamin': pasien ? (pasien.jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan') : '-',
            'Alamat': pasien?.alamat ?? '-',
            'Agama': formatText(pasien?.agama),
            'Status Perkawinan': formatText(pasien?.status_perkawinan),
            'Pendidikan terakhir': formatText(pasien?.pendidikan_terakhir),
            'Status di ITK': pasien ? getStatusLabel(pasien.tipe_pasien) : '-',
            'Fakultas': pasien && ['mahasiswa', 'dosen'].includes(pasien.tipe_pasien) ? (pasien.fakultas || '-') : '-',
            'Unit Kerja': pasien && pasien.tipe_pasien === 'tendik' ? (pasien.fakultas || '-') : '-',
            'Program Studi': pasien && ['mahasiswa', 'dosen'].includes(pasien.tipe_pasien) ? (pasien.prodi || '-') : '-',
            'Golongan Darah': pasien?.golongan_darah ?? '-',
            'Petugas Administrasi': 'Admin / Sistem',
            'Keluhan Utama': anamnesis?.keluhan_utama ?? '-',
            'Riwayat penyakit sekarang': anamnesis?.riwayat_penyakit_sekarang ?? '-',
            'Riwayat penyakit dahulu': anamnesis?.riwayat_penyakit_dahulu ?? '-',
            'Riwayat Keluarga': anamnesis?.riwayat_keluarga ?? '-',
            'Alergi': anamnesis?.riwayat_alergi ?? '-',
            'TTV.1 TD': anamnesis?.tekanan_darah ?? '-',
            'TTV.2 Nadi': anamnesis?.nadi ?? '-',
            'TTV.3 Suhu': anamnesis?.suhu ?? '-',
            'TTV.4 RR': anamnesis?.respirasi ?? '-',
            'Berat Badan': anamnesis?.berat_badan ?? '-',
            'Tinggi Badan': anamnesis?.tinggi_badan ?? '-',
            'IMT': imt,
            'Kondisi Khusus': kondisiKhusus,
            'Skala Nyeri': anamnesis?.skala_nyeri ?? '-',
            'Pemeriksaan Fisik Lain': pemeriksaan?.pemeriksaan_fisik ?? '-',
            'Dokter penanggung jawab': pemeriksaan?.dokter?.name ?? '-',
            'Diagnosa medis': pemeriksaan?.diagnosis_utama ?? '-',
            'Penatalaksanaan Medis': pemeriksaan?.penatalaksanaan_medis ?? '-',
            'Diagnosa Keperawatan': anamnesis?.diagnosa_keperawatan ?? '-',
            'Intervensi Keperawatan': anamnesis?.intervensi_keperawatan ?? '-',
            'Implementasi Keperawatan': anamnesis?.implementasi_keperawatan ?? '-',
            'Evaluasi Keperawatan': anamnesis?.evaluasi_keperawatan ?? '-',
            'Perawat': anamnesis?.perawat?.name ?? '-',
        };
    });

    const worksheet = XLSX.utils.json_to_sheet(dataToExport);

    // Header Color Map
    const headerColorMap: Record<string, { bg: string; text: string }> = {
        'Timestamp': { bg: '1E293B', text: 'FFFFFF' },
        'No. RM': { bg: '1E293B', text: 'FFFFFF' },
        'Nama Pasien': { bg: '1E293B', text: 'FFFFFF' },
        'Tanggal Lahir': { bg: '1E293B', text: 'FFFFFF' },
        'No. Identitas (NIK/NIP/NIM)': { bg: '1E293B', text: 'FFFFFF' },
        'No. Telp': { bg: '1E293B', text: 'FFFFFF' },
        'Jenis Kelamin': { bg: '1E293B', text: 'FFFFFF' },
        'Alamat': { bg: '1E293B', text: 'FFFFFF' },
        'Agama': { bg: '1E293B', text: 'FFFFFF' },
        'Status Perkawinan': { bg: '1E293B', text: 'FFFFFF' },
        'Pendidikan terakhir': { bg: '1E293B', text: 'FFFFFF' },
        'Status di ITK': { bg: '1E293B', text: 'FFFFFF' },
        'Fakultas': { bg: '1E293B', text: 'FFFFFF' },
        'Unit Kerja': { bg: '1E293B', text: 'FFFFFF' },
        'Program Studi': { bg: '1E293B', text: 'FFFFFF' },
        'Golongan Darah': { bg: '1E293B', text: 'FFFFFF' },
        'Petugas Administrasi': { bg: '1E293B', text: 'FFFFFF' },

        'Keluhan Utama': { bg: '0284C7', text: 'FFFFFF' },
        'Riwayat penyakit sekarang': { bg: '0284C7', text: 'FFFFFF' },
        'Riwayat penyakit dahulu': { bg: '0284C7', text: 'FFFFFF' },
        'Riwayat Keluarga': { bg: '0284C7', text: 'FFFFFF' },
        'Alergi': { bg: '0284C7', text: 'FFFFFF' },

        'TTV.1 TD': { bg: '166534', text: 'FFFFFF' },
        'TTV.2 Nadi': { bg: '166534', text: 'FFFFFF' },
        'TTV.3 Suhu': { bg: '166534', text: 'FFFFFF' },
        'TTV.4 RR': { bg: '166534', text: 'FFFFFF' },
        'Berat Badan': { bg: '166534', text: 'FFFFFF' },
        'Tinggi Badan': { bg: '166534', text: 'FFFFFF' },
        'IMT': { bg: '166534', text: 'FFFFFF' },
        'Kondisi Khusus': { bg: '166534', text: 'FFFFFF' },
        'Skala Nyeri': { bg: '166534', text: 'FFFFFF' },

        'Pemeriksaan Fisik Lain': { bg: '7C3AED', text: 'FFFFFF' },
        'Dokter penanggung jawab': { bg: '7C3AED', text: 'FFFFFF' },
        'Diagnosa medis': { bg: '7C3AED', text: 'FFFFFF' },
        'Penatalaksanaan Medis': { bg: '7C3AED', text: 'FFFFFF' },

        'Diagnosa Keperawatan': { bg: '0F766E', text: 'FFFFFF' },
        'Intervensi Keperawatan': { bg: '0F766E', text: 'FFFFFF' },
        'Implementasi Keperawatan': { bg: '0F766E', text: 'FFFFFF' },
        'Evaluasi Keperawatan': { bg: '0F766E', text: 'FFFFFF' },
        'Perawat': { bg: '0F766E', text: 'FFFFFF' },
    };

    const range = XLSX.utils.decode_range(worksheet['!ref'] || 'A1');

    // Style Header Row
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const cellAddress = XLSX.utils.encode_cell({ r: 0, c: C });
        const cell = worksheet[cellAddress];
        if (!cell) continue;

        const headerTitle = String(cell.v);
        const styleInfo = headerColorMap[headerTitle] || { bg: '1E293B', text: 'FFFFFF' };

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
                    horizontal: 'left',
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
        return { wch: Math.min(Math.max(maxLen + 4, 12), 35) };
    });
    worksheet['!cols'] = colWidths;

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Pemeriksaan Umum');

    const startStr = formatDateYMD(startDate.value);
    const endStr = formatDateYMD(endDate.value);
    XLSX.writeFile(workbook, `Laporan_Pemeriksaan_Umum_${startStr}_sampai_${endStr}.xlsx`);
};
</script>

<template>
    <Head title="Laporan Pemeriksaan Umum" />
    <AppLayout>
        <!-- Header -->
        <template #header>
            <div class="font-sans flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button icon="pi pi-arrow-left" text rounded severity="secondary" @click="router.get(route('laporan.index'))" class="!w-8 !h-8 hover:bg-gray-100 transition-colors" />
                    <div>
                        <h2 class="text-lg font-bold text-gray-800 tracking-tight flex items-center gap-2">
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
                        <Column header="Kondisi Khusus" style="min-width: 120px" headerStyle="background-color: #4a86e8; color: white;">
                            <template #body="{ data }">
                                <span v-if="data.pasien?.jenis_kelamin === 'P'" class="font-medium text-pink-600">
                                    {{ data.anamnesis?.is_hamil ? 'Hamil' : (data.anamnesis?.is_menyusui ? 'Menyusui' : '-') }}
                                </span>
                                <span v-else>-</span>
                            </template>
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
