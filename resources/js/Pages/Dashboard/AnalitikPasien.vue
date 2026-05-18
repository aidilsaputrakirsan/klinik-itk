<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Pasien } from '@/types';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Card from 'primevue/card';

interface Props {
    pasiens: {
        data: Pasien[];
        links: object[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    kategori: string;
    offset: number;
    label: string;
    startDate: string;
    endDate: string;
}

const props = defineProps<Props>();

const prevOffset = () => {
    router.get(route('dashboard.analitik-pasien'), { kategori: props.kategori, offset: props.offset - 1 }, { preserveState: true });
};

const nextOffset = () => {
    router.get(route('dashboard.analitik-pasien'), { kategori: props.kategori, offset: props.offset + 1 }, { preserveState: true });
};

const currentOffset = () => {
    router.get(route('dashboard.analitik-pasien'), { kategori: props.kategori, offset: 0 }, { preserveState: true });
};
</script>

<template>
    <Head :title="'Analitik Pasien - ' + props.label" />
    <AppLayout>
        <template #header>Analitik Pasien Terdaftar</template>

        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <Link :href="route('dashboard')">
                    <Button icon="pi pi-arrow-left" label="Kembali ke Dashboard" severity="secondary" text class="!rounded-xl" />
                </Link>
            </div>

            <Card class="shadow-md border-0 overflow-hidden ring-1 ring-gray-200">
                <template #content>
                    <!-- Header & Time Shifter -->
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                                <i class="pi pi-chart-bar text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Pendaftaran Pasien</h3>
                                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Kategori: {{ props.kategori }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 bg-white p-1 rounded-xl shadow-sm border border-gray-200">
                            <Button icon="pi pi-chevron-left" severity="secondary" text class="!rounded-lg" @click="prevOffset" />
                            <div class="px-4 text-center min-w-[150px]">
                                <span class="font-bold text-gray-800 block">{{ props.label }}</span>
                            </div>
                            <Button icon="pi pi-chevron-right" severity="secondary" text class="!rounded-lg" @click="nextOffset" :disabled="props.offset >= 0" />
                            <div class="w-px h-6 bg-gray-200 mx-1"></div>
                            <Button label="Sekarang" severity="info" text class="!rounded-lg !text-xs font-bold" @click="currentOffset" :disabled="props.offset === 0" />
                        </div>
                    </div>

                    <!-- Table -->
                    <DataTable
                        :value="props.pasiens.data"
                        :paginator="false"
                        :rows="props.pasiens.per_page"
                        dataKey="id"
                        responsiveLayout="scroll"
                        class="p-datatable-sm"
                        stripedRows
                    >
                        <template #empty>
                            <div class="text-center py-8 text-gray-500">
                                Tidak ada pasien yang terdaftar pada periode ini.
                            </div>
                        </template>
                        <Column field="nomor_rm" header="No. RM" sortable style="width: 120px">
                            <template #body="{ data }">
                                <span class="font-mono text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-100">
                                    {{ data.nomor_rm }}
                                </span>
                            </template>
                        </Column>
                        <Column field="nama" header="Nama Pasien" sortable>
                            <template #body="{ data }">
                                <div>
                                    <p class="font-medium text-gray-900">{{ data.nama }}</p>
                                    <p class="text-xs text-gray-500">{{ data.nik || '-' }}</p>
                                </div>
                            </template>
                        </Column>
                        <Column field="jenis_kelamin" header="L/P" style="width: 80px">
                            <template #body="{ data }">
                                {{ data.jenis_kelamin }}
                            </template>
                        </Column>
                        <Column field="created_at" header="Waktu Pendaftaran" sortable style="width: 180px">
                            <template #body="{ data }">
                                <div class="flex items-center gap-2 text-sm">
                                    <i class="pi pi-clock text-gray-400 text-xs"></i>
                                    <span>{{ new Date(data.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}</span>
                                </div>
                            </template>
                        </Column>
                        <Column header="Aksi" style="width: 120px">
                            <template #body="{ data }">
                                <Link :href="route('pasien.show', data.id)">
                                    <Button icon="pi pi-eye" label="Detail" severity="info" text size="small" class="!rounded-lg" />
                                </Link>
                            </template>
                        </Column>
                    </DataTable>

                    <!-- Pagination -->
                    <div v-if="props.pasiens.total > 0" class="mt-4 flex justify-between items-center text-sm text-gray-600 border-t pt-4">
                        <span>Menampilkan {{ props.pasiens.data.length }} dari {{ props.pasiens.total }} data</span>
                        <div class="flex gap-2">
                            <Button
                                icon="pi pi-chevron-left"
                                :disabled="props.pasiens.current_page === 1"
                                severity="secondary"
                                size="small"
                                class="!rounded-xl"
                                @click="router.get(route('dashboard.analitik-pasien', { kategori: props.kategori, offset: props.offset, page: props.pasiens.current_page - 1 }))"
                            />
                            <span class="px-3 py-1 font-medium bg-gray-50 rounded-xl">{{ props.pasiens.current_page }} / {{ props.pasiens.last_page }}</span>
                            <Button
                                icon="pi pi-chevron-right"
                                :disabled="props.pasiens.current_page === props.pasiens.last_page"
                                severity="secondary"
                                size="small"
                                class="!rounded-xl"
                                @click="router.get(route('dashboard.analitik-pasien', { kategori: props.kategori, offset: props.offset, page: props.pasiens.current_page + 1 }))"
                            />
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
