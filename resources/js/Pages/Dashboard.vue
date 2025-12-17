<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { User, PageProps } from '@/types';
import Card from 'primevue/card';

interface DashboardStats {
    total_pasien: number;
    kunjungan_hari_ini: number;
    menunggu_perawat: number;
    siap_dokter: number;
    sedang_diperiksa: number;
    selesai_hari_ini: number;
}

interface Props {
    stats: DashboardStats;
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        total_pasien: 0,
        kunjungan_hari_ini: 0,
        menunggu_perawat: 0,
        siap_dokter: 0,
        sedang_diperiksa: 0,
        selesai_hari_ini: 0,
    })
});

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user as User);

const statCards = computed(() => [
    { title: 'Total Pasien', value: props.stats.total_pasien, icon: 'pi pi-users', color: 'bg-blue-500', roles: ['superadmin', 'admin'] },
    { title: 'Kunjungan Hari Ini', value: props.stats.kunjungan_hari_ini, icon: 'pi pi-calendar', color: 'bg-emerald-500', roles: ['superadmin', 'admin', 'perawat', 'dokter'] },
    { title: 'Menunggu Perawat', value: props.stats.menunggu_perawat, icon: 'pi pi-clock', color: 'bg-amber-500', roles: ['superadmin', 'admin', 'perawat'] },
    { title: 'Siap untuk Dokter', value: props.stats.siap_dokter, icon: 'pi pi-check-circle', color: 'bg-purple-500', roles: ['superadmin', 'admin', 'perawat', 'dokter'] },
    { title: 'Sedang Diperiksa', value: props.stats.sedang_diperiksa, icon: 'pi pi-spin pi-spinner', color: 'bg-rose-500', roles: ['superadmin', 'admin', 'dokter'] },
    { title: 'Selesai Hari Ini', value: props.stats.selesai_hari_ini, icon: 'pi pi-check', color: 'bg-teal-500', roles: ['superadmin', 'admin', 'perawat', 'dokter'] },
]);

const filteredStatCards = computed(() => {
    return statCards.value.filter(card => card.roles.includes(user.value?.role));
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>Dashboard</template>
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-6 text-white">
                <h2 class="text-2xl font-bold">Selamat Datang, {{ user?.name }}!</h2>
                <p class="mt-1 text-emerald-100">Sistem Informasi Klinik Institut Teknologi Kalimantan</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <Card v-for="stat in filteredStatCards" :key="stat.title" class="shadow-sm hover:shadow-md transition-shadow">
                    <template #content>
                        <div class="flex items-center gap-4">
                            <div :class="['w-14 h-14 rounded-xl flex items-center justify-center', stat.color]">
                                <i :class="[stat.icon, 'text-white text-2xl']"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ stat.title }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <Card class="shadow-sm">
                    <template #title><span class="text-lg font-semibold">Aktivitas Terbaru</span></template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                    <i class="pi pi-user-plus text-emerald-600"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Pasien baru terdaftar</p>
                                    <p class="text-xs text-gray-500">Budi Santoso - 5 menit lalu</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
                <Card class="shadow-sm">
                    <template #title><span class="text-lg font-semibold">Informasi Klinik</span></template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <i class="pi pi-clock text-emerald-600"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Jam Operasional</p>
                                    <p class="text-xs text-gray-500">Senin - Jumat: 08:00 - 16:00</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="pi pi-map-marker text-emerald-600"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Lokasi</p>
                                    <p class="text-xs text-gray-500">Kampus ITK, Karang Joang, Balikpapan</p>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
