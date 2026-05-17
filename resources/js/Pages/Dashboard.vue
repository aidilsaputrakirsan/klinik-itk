<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
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

interface AnalitikPasien {
    harian: number;
    mingguan: number;
    bulanan: number;
    tahunan: number;
}

interface Aktivitas {
    id: string | number;
    tipe: string;
    deskripsi: string;
    pasien_nama: string;
    waktu: string;
    icon: string;
    color: string;
}

interface Props {
    stats: DashboardStats;
    analitik_pasien: AnalitikPasien;
    aktivitas_terbaru: Aktivitas[];
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
    { title: 'Sedang Diperiksa', value: props.stats.sedang_diperiksa, icon: 'pi pi-stethoscope', color: 'bg-rose-500', roles: ['superadmin', 'admin', 'dokter'] },
    { title: 'Selesai Hari Ini', value: props.stats.selesai_hari_ini, icon: 'pi pi-check', color: 'bg-teal-500', roles: ['superadmin', 'admin', 'perawat', 'dokter'] },
]);

const filteredStatCards = computed(() => {
    return statCards.value.filter(card => card.roles.includes(user.value?.role));
});

const currentTime = ref('');
let timer: ReturnType<typeof setInterval>;

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', {
        timeZone: 'Asia/Makassar',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }) + ' WITA';
};

onMounted(() => {
    updateTime();
    timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
    clearInterval(timer);
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>Dashboard</template>
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-6 text-white flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">Selamat Datang, {{ user?.name }}!</h2>
                    <p class="mt-1 text-emerald-100">Sistem Informasi Klinik Institut Teknologi Kalimantan</p>
                </div>
                <div class="text-right hidden sm:block">
                    <p class="text-3xl font-mono font-bold">{{ currentTime }}</p>
                    <p class="text-emerald-100 text-sm mt-1">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Makassar' }) }}</p>
                </div>
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
                <!-- Analitik Pasien Baru -->
                <Card class="shadow-sm">
                    <template #title><span class="text-lg font-semibold">Analitik Pasien Baru</span></template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-indigo-50 rounded-xl p-4 flex flex-col items-center justify-center text-center">
                                <span class="text-indigo-500 text-sm font-medium mb-1">Hari Ini</span>
                                <span class="text-3xl font-bold text-indigo-700">{{ props.analitik_pasien?.harian || 0 }}</span>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-4 flex flex-col items-center justify-center text-center">
                                <span class="text-blue-500 text-sm font-medium mb-1">Minggu Ini</span>
                                <span class="text-3xl font-bold text-blue-700">{{ props.analitik_pasien?.mingguan || 0 }}</span>
                            </div>
                            <div class="bg-emerald-50 rounded-xl p-4 flex flex-col items-center justify-center text-center">
                                <span class="text-emerald-500 text-sm font-medium mb-1">Bulan Ini</span>
                                <span class="text-3xl font-bold text-emerald-700">{{ props.analitik_pasien?.bulanan || 0 }}</span>
                            </div>
                            <div class="bg-amber-50 rounded-xl p-4 flex flex-col items-center justify-center text-center">
                                <span class="text-amber-500 text-sm font-medium mb-1">Tahun Ini</span>
                                <span class="text-3xl font-bold text-amber-700">{{ props.analitik_pasien?.tahunan || 0 }}</span>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Aktivitas Terbaru -->
                <Card class="shadow-sm">
                    <template #title><span class="text-lg font-semibold">Aktivitas Terkini</span></template>
                    <template #content>
                        <div class="space-y-4">
                            <template v-if="props.aktivitas_terbaru && props.aktivitas_terbaru.length > 0">
                                <div v-for="aktivitas in props.aktivitas_terbaru" :key="aktivitas.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center', aktivitas.color]">
                                        <i :class="aktivitas.icon"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ aktivitas.deskripsi }}</p>
                                        <p class="text-xs text-gray-500">{{ aktivitas.pasien_nama }} &bull; {{ aktivitas.waktu }}</p>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="text-center text-gray-500 p-4">Belum ada aktivitas terbaru.</div>
                            </template>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
