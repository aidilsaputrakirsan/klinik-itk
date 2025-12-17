<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import Select from 'primevue/select';

const dateRange = ref<Date[]>([]);
const reportType = ref('');

const reportTypes = [
    { label: 'Laporan Kunjungan Harian', value: 'kunjungan_harian' },
    { label: 'Laporan Kunjungan Bulanan', value: 'kunjungan_bulanan' },
    { label: 'Laporan Pasien Baru', value: 'pasien_baru' },
    { label: 'Laporan Penggunaan Obat', value: 'penggunaan_obat' },
    { label: 'Laporan Tindakan', value: 'tindakan' },
    { label: 'Laporan Diagnosis', value: 'diagnosis' },
];

const generateReport = () => {
    // TODO: Implement report generation
    console.log('Generate report:', reportType.value, dateRange.value);
};
</script>

<template>
    <Head title="Laporan" />
    <AppLayout>
        <template #header>Laporan</template>

        <div class="space-y-6">
            <Card class="shadow-sm">
                <template #title>Generate Laporan</template>
                <template #content>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="font-medium text-sm">Jenis Laporan</label>
                            <Select
                                v-model="reportType"
                                :options="reportTypes"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Pilih jenis laporan"
                                class="w-full"
                            />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="font-medium text-sm">Rentang Tanggal</label>
                            <DatePicker
                                v-model="dateRange"
                                selectionMode="range"
                                dateFormat="dd/mm/yy"
                                placeholder="Pilih rentang tanggal"
                                showIcon
                                class="w-full"
                            />
                        </div>
                        <div class="flex items-end">
                            <Button
                                label="Generate Laporan"
                                icon="pi pi-file-pdf"
                                @click="generateReport"
                                :disabled="!reportType || dateRange.length < 2"
                            />
                        </div>
                    </div>
                </template>
            </Card>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card class="shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                    <template #content>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="pi pi-calendar text-blue-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Kunjungan Hari Ini</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                    <template #content>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center">
                                <i class="pi pi-users text-emerald-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Pasien Bulan Ini</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </template>
                </Card>

                <Card class="shadow-sm hover:shadow-md transition-shadow cursor-pointer">
                    <template #content>
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                                <i class="pi pi-box text-amber-600 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Obat Digunakan</p>
                                <p class="text-2xl font-bold text-gray-900">0</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <Card class="shadow-sm">
                <template #title>Laporan Tersedia</template>
                <template #content>
                    <div class="text-center py-8 text-gray-500">
                        <i class="pi pi-chart-bar text-4xl mb-4"></i>
                        <p>Pilih jenis laporan dan rentang tanggal untuk generate laporan</p>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
