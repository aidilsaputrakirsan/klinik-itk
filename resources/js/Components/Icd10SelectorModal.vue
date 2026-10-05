<script setup lang="ts">
import { ref, computed } from 'vue';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import { icd10Dataset, icd10Categories, type Icd10Item } from '@/data/icd10';

interface Props {
    visible: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'update:visible', val: boolean): void;
    (e: 'select', item: Icd10Item): void;
}>();

const isVisible = computed({
    get: () => props.visible,
    set: (val: boolean) => emit('update:visible', val),
});

const searchQuery = ref('');
const selectedCategory = ref('');

const categoryOptions = computed(() => [
    { label: 'Semua Kategori (Semua Kasus)', value: '' },
    ...icd10Categories.map(cat => ({ label: cat, value: cat }))
]);

const quickCategories = [
    { label: 'Semua', value: '' },
    { label: 'Respirasi (ISPA)', value: 'Penyakit Sistem Pernapasan' },
    { label: 'Saluran Cerna', value: 'Penyakit Saluran Cerna' },
    { label: 'Saraf & Nyeri Kepala', value: 'Sistem Saraf & Kepala' },
    { label: 'Muskuloskeletal', value: 'Sistem Muskuloskeletal' },
    { label: 'Kulit & Alergi', value: 'Penyakit Kulit & Alergi' },
    { label: 'Mata & THT', value: 'Penyakit Mata & THT' },
    { label: 'Kardiovaskular', value: 'Kardiovaskular & Metabolik' },
    { label: 'Gejala (R-Codes)', value: 'Gejala & Tanda Klinis (R-Codes)' },
    { label: 'Trauma & P3K', value: 'Cedera & Trauma Fisik (P3K)' },
    { label: 'Layanan Sehat (Z)', value: 'Kunjungan Sehat & Administrasi Medis' },
];

const filteredDiagnoses = computed(() => {
    let result = icd10Dataset;

    if (selectedCategory.value) {
        result = result.filter(item => item.category === selectedCategory.value);
    }

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(item =>
            item.code.toLowerCase().includes(query) ||
            item.name.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query) ||
            (item.description && item.description.toLowerCase().includes(query))
        );
    }

    return result;
});

const selectDiagnosis = (item: Icd10Item) => {
    emit('select', item);
    isVisible.value = false;
};

const onRowDblClick = (event: { data: Icd10Item }) => {
    if (event.data) {
        selectDiagnosis(event.data);
    }
};

const getCategorySeverity = (cat: string): 'info' | 'success' | 'warn' | 'danger' | 'secondary' => {
    if (cat.includes('Respirasi') || cat.includes('Cerna')) return 'info';
    if (cat.includes('Saraf') || cat.includes('Muskulo')) return 'secondary';
    if (cat.includes('Kulit') || cat.includes('Mata')) return 'warn';
    if (cat.includes('Gejala') || cat.includes('Tropis')) return 'danger';
    if (cat.includes('Kunjungan')) return 'success';
    return 'info';
};
</script>

<template>
    <Dialog
        v-model:visible="isVisible"
        modal
        header="Katalog & Referensi Diagnosis ICD-10"
        :style="{ width: '92vw', maxWidth: '1050px' }"
        :breakpoints="{ '960px': '95vw', '640px': '98vw' }"
        class="rounded-2xl overflow-hidden shadow-2xl border border-gray-100"
    >
        <template #header>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                    <i class="pi pi-book text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-gray-900 leading-tight">Katalog Kode Diagnosis ICD-10</h3>
                    <p class="text-xs text-gray-500">Pilih diagnosis medis untuk mengisi kolom form pemeriksaan secara otomatis</p>
                </div>
            </div>
        </template>

        <div class="space-y-4 pt-1">
            <!-- Filter & Search Controls -->
            <div class="bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                    <!-- Search Input -->
                    <div class="sm:col-span-7">
                        <InputGroup class="!shadow-sm !rounded-xl overflow-hidden border border-gray-300 bg-white focus-within:!ring-2 focus-within:!ring-emerald-500/20 focus-within:!border-emerald-500 transition-all">
                            <InputGroupAddon class="!bg-white !border-0 !px-3.5 !py-0">
                                <i class="pi pi-search text-gray-400 text-sm"></i>
                            </InputGroupAddon>
                            <InputText
                                v-model="searchQuery"
                                placeholder="Cari kode (mis: J00, K29), nama diagnosis, atau kata kunci gejala..."
                                class="!border-0 !text-sm !py-2.5 !pl-0.5 focus:!ring-0 placeholder:text-gray-400 w-full"
                            />
                            <InputGroupAddon v-if="searchQuery" class="!bg-white !border-0 !px-2.5 !py-0">
                                <button
                                    type="button"
                                    @click="searchQuery = ''"
                                    class="text-gray-400 hover:text-gray-600 transition-colors cursor-pointer p-1"
                                    title="Hapus pencarian"
                                >
                                    <i class="pi pi-times-circle text-sm"></i>
                                </button>
                            </InputGroupAddon>
                        </InputGroup>
                    </div>

                    <!-- Category Dropdown -->
                    <div class="sm:col-span-5">
                        <Select
                            v-model="selectedCategory"
                            :options="categoryOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Kategori Bab..."
                            class="w-full !rounded-xl !border-gray-300 focus:!ring-emerald-500/30 text-sm"
                        />
                    </div>
                </div>

                <!-- Quick Category Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                    <span class="text-gray-500 font-medium whitespace-nowrap mr-1 flex items-center gap-1">
                        <i class="pi pi-filter text-[11px]"></i> Filter Cepat:
                    </span>
                    <button
                        v-for="chip in quickCategories"
                        :key="chip.value"
                        type="button"
                        @click="selectedCategory = chip.value"
                        :class="[
                            'px-2.5 py-1 rounded-lg font-medium transition-all duration-150 whitespace-nowrap text-[11px]',
                            selectedCategory === chip.value
                                ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30 font-semibold'
                                : 'bg-white hover:bg-slate-200/70 text-slate-700 border border-slate-200'
                        ]"
                    >
                        {{ chip.label }}
                    </button>
                </div>
            </div>

            <!-- Table of Diagnoses -->
            <div class="border border-gray-200/80 rounded-xl overflow-hidden shadow-sm">
                <DataTable
                    :value="filteredDiagnoses"
                    paginator
                    :rows="7"
                    :rowsPerPageOptions="[7, 12, 25, 50]"
                    dataKey="code"
                    size="small"
                    class="p-datatable-sm"
                    stripedRows
                    @row-dblclick="onRowDblClick"
                >
                    <template #empty>
                        <div class="py-10 text-center text-gray-500">
                            <i class="pi pi-search text-3xl text-gray-300 mb-2"></i>
                            <p class="font-medium">Tidak ada diagnosis yang sesuai kriteria pencarian</p>
                            <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain atau pilih "Semua Kategori"</p>
                        </div>
                    </template>

                    <!-- Kode ICD -->
                    <Column field="code" header="Kode ICD-10" sortable style="width: 130px">
                        <template #body="{ data }">
                            <span class="font-mono font-bold text-xs text-emerald-800 bg-emerald-50 border border-emerald-200/70 px-2 py-0.5 rounded-md inline-block">
                                {{ data.code }}
                            </span>
                        </template>
                    </Column>

                    <!-- Nama Diagnosis & Deskripsi -->
                    <Column field="name" header="Diagnosis Medis" sortable>
                        <template #body="{ data }">
                            <div class="py-0.5">
                                <div class="font-semibold text-gray-900 text-sm leading-snug">
                                    {{ data.name }}
                                </div>
                                <div v-if="data.description" class="text-[11px] text-gray-500 mt-0.5 line-clamp-1">
                                    <span class="text-gray-400">Kasus:</span> {{ data.description }}
                                </div>
                            </div>
                        </template>
                    </Column>

                    <!-- Kategori -->
                    <Column field="category" header="Kategori Bab" sortable style="width: 220px" class="hidden md:table-cell">
                        <template #body="{ data }">
                            <Tag
                                :value="data.category"
                                :severity="getCategorySeverity(data.category)"
                                class="!text-[11px] !font-medium !py-0.5 !px-2"
                            />
                        </template>
                    </Column>

                    <!-- Aksi Pilih -->
                    <Column header="Aksi" style="width: 100px" class="text-right">
                        <template #body="{ data }">
                            <Button
                                label="Pilih"
                                icon="pi pi-check"
                                size="small"
                                severity="success"
                                class="!text-xs !py-1 !px-2.5 !rounded-lg !bg-emerald-600 hover:!bg-emerald-700 !border-none shadow-sm"
                                @click="selectDiagnosis(data)"
                            />
                        </template>
                    </Column>
                </DataTable>
            </div>
        </div>

        <template #footer>
            <div class="flex items-center justify-between w-full pt-2 text-xs text-gray-500">
                <span class="text-xs text-slate-500 hidden sm:inline">
                    Klik tombol <strong>Pilih</strong> atau klik dua kali pada baris tabel untuk memasukkan diagnosis ke form.
                </span>
                <Button
                    label="Tutup"
                    severity="success"
                    class="!bg-emerald-600 hover:!bg-emerald-700 !border-emerald-600 !text-white !rounded-xl !px-6 !py-2 text-xs font-semibold shadow-sm cursor-pointer"
                    @click="isVisible = false"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
