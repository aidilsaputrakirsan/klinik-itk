<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

const reports = [
    {
        title: 'Laporan Kunjungan',
        description: 'Data kunjungan pasien berdasarkan periode',
        icon: 'pi-calendar',
        color: 'blue',
        route: 'laporan.kunjungan',
    },
    {
        title: 'Laporan Obat',
        description: 'Penggunaan dan stok obat',
        icon: 'pi-box',
        color: 'emerald',
        route: 'laporan.obat',
    },
    {
        title: 'Laporan Tindakan',
        description: 'Data tindakan medis yang dilakukan',
        icon: 'pi-wrench',
        color: 'amber',
        route: 'laporan.tindakan',
    },
];

const getColorClass = (color: string, type: 'bg' | 'text') => {
    const colors: Record<string, Record<string, string>> = {
        blue: { bg: 'bg-blue-100', text: 'text-blue-600' },
        emerald: { bg: 'bg-emerald-100', text: 'text-emerald-600' },
        amber: { bg: 'bg-amber-100', text: 'text-amber-600' },
    };
    return colors[color]?.[type] || '';
};
</script>

<template>
    <Head title="Laporan" />
    <AppLayout>
        <template #header>Laporan</template>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link
                    v-for="report in reports"
                    :key="report.route"
                    :href="route(report.route)"
                    class="block"
                >
                    <Card class="shadow-sm hover:shadow-lg transition-all cursor-pointer h-full border-2 border-transparent hover:border-emerald-200">
                        <template #content>
                            <div class="flex items-start gap-4">
                                <div
                                    :class="[
                                        'w-16 h-16 rounded-xl flex items-center justify-center',
                                        getColorClass(report.color, 'bg')
                                    ]"
                                >
                                    <i
                                        :class="[
                                            'pi text-3xl',
                                            report.icon,
                                            getColorClass(report.color, 'text')
                                        ]"
                                    ></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">{{ report.title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ report.description }}</p>
                                    <div class="mt-3">
                                        <span class="text-emerald-600 text-sm font-medium">
                                            Lihat Laporan <i class="pi pi-arrow-right text-xs"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </Link>
            </div>

            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-info-circle text-blue-500"></i>
                        Panduan Laporan
                    </div>
                </template>
                <template #content>
                    <div class="space-y-4 text-gray-600">
                        <div class="flex items-start gap-3">
                            <span class="bg-blue-100 text-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">1</span>
                            <div>
                                <p class="font-medium text-gray-800">Laporan Kunjungan</p>
                                <p class="text-sm">Melihat data kunjungan pasien, status pemeriksaan, dan statistik berdasarkan periode tertentu.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="bg-emerald-100 text-emerald-600 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">2</span>
                            <div>
                                <p class="font-medium text-gray-800">Laporan Obat</p>
                                <p class="text-sm">Melihat penggunaan obat, stok saat ini, dan obat dengan stok rendah.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="bg-amber-100 text-amber-600 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold">3</span>
                            <div>
                                <p class="font-medium text-gray-800">Laporan Tindakan</p>
                                <p class="text-sm">Melihat data tindakan medis yang dilakukan dan total pendapatan dari tindakan.</p>
                            </div>
                        </div>
                    </div>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
