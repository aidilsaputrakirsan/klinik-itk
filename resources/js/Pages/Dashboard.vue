<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
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
    { title: 'Sedang Diperiksa', value: props.stats.sedang_diperiksa, icon: 'pi pi-clock', color: 'bg-rose-500', roles: ['superadmin', 'admin', 'dokter'] },
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
                <Card v-if="['superadmin', 'admin'].includes($page.props.auth.user.role)" class="shadow-sm border border-gray-100">
                    <template #title>
                        <div class="flex items-center justify-between gap-2 text-gray-800">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-chart-bar text-indigo-500"></i>
                                <span class="text-lg font-bold">Analitik Pasien Baru</span>
                            </div>
                            <Link :href="route('pasien.index')" class="text-sm font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-2">
                                <span>Lihat Data Pasien</span>
                                <i class="pi pi-arrow-right text-xs"></i>
                            </Link>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-4">
                            <Link :href="route('dashboard.analitik-pasien', { kategori: 'harian' })" class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-xl p-5 flex flex-col justify-between border border-indigo-100 transition-all hover:shadow-md hover:-translate-y-1 group">
                                <span class="text-indigo-600 text-sm font-semibold uppercase tracking-wider mb-2">Hari Ini</span>
                                <div class="flex justify-between items-end">
                                    <span class="text-4xl font-extrabold text-indigo-700">{{ props.analitik_pasien?.harian || 0 }}</span>
                                    <i class="pi pi-calendar-times text-indigo-300 text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                                </div>
                            </Link>
                            <Link :href="route('dashboard.analitik-pasien', { kategori: 'mingguan' })" class="bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl p-5 flex flex-col justify-between border border-blue-100 transition-all hover:shadow-md hover:-translate-y-1 group">
                                <span class="text-blue-600 text-sm font-semibold uppercase tracking-wider mb-2">Minggu Ini</span>
                                <div class="flex justify-between items-end">
                                    <span class="text-4xl font-extrabold text-blue-700">{{ props.analitik_pasien?.mingguan || 0 }}</span>
                                    <i class="pi pi-calendar text-blue-300 text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                                </div>
                            </Link>
                            <Link :href="route('dashboard.analitik-pasien', { kategori: 'bulanan' })" class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-5 flex flex-col justify-between border border-emerald-100 transition-all hover:shadow-md hover:-translate-y-1 group">
                                <span class="text-emerald-600 text-sm font-semibold uppercase tracking-wider mb-2">Bulan Ini</span>
                                <div class="flex justify-between items-end">
                                    <span class="text-4xl font-extrabold text-emerald-700">{{ props.analitik_pasien?.bulanan || 0 }}</span>
                                    <i class="pi pi-calendar-plus text-emerald-300 text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                                </div>
                            </Link>
                            <Link :href="route('dashboard.analitik-pasien', { kategori: 'tahunan' })" class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl p-5 flex flex-col justify-between border border-amber-100 transition-all hover:shadow-md hover:-translate-y-1 group">
                                <span class="text-amber-600 text-sm font-semibold uppercase tracking-wider mb-2">Tahun Ini</span>
                                <div class="flex justify-between items-end">
                                    <span class="text-4xl font-extrabold text-amber-700">{{ props.analitik_pasien?.tahunan || 0 }}</span>
                                    <i class="pi pi-calendar-minus text-amber-300 text-2xl mb-1 group-hover:scale-110 transition-transform"></i>
                                </div>
                            </Link>
                        </div>
                    </template>
                </Card>

                <!-- Akses Cepat (Untuk Perawat & Dokter) -->
                <Card v-if="['perawat', 'dokter'].includes($page.props.auth.user.role)" class="shadow-sm border border-gray-100">
                    <template #title>
                        <div class="flex items-center gap-2 text-gray-800">
                            <i class="pi pi-bolt text-blue-500"></i>
                            <span class="text-lg font-bold">Akses Cepat</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="grid grid-cols-2 gap-4">
                            <Link :href="route('pasien.index')" class="bg-gradient-to-br from-indigo-50 to-indigo-100/50 rounded-xl p-5 flex flex-col justify-between border border-indigo-100 transition-all hover:shadow-md hover:-translate-y-1 group items-center text-center h-full">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="pi pi-users text-indigo-600 text-2xl"></i>
                                </div>
                                <span class="text-indigo-900 font-bold mb-1">Daftar Pasien</span>
                                <span class="text-indigo-500 text-xs font-medium">Cari & lihat rekam medis</span>
                            </Link>

                            <Link v-if="user?.role === 'perawat'" :href="route('perawat.antrian')" class="bg-gradient-to-br from-amber-50 to-amber-100/50 rounded-xl p-5 flex flex-col justify-between border border-amber-100 transition-all hover:shadow-md hover:-translate-y-1 group items-center text-center h-full">
                                <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="pi pi-clipboard text-amber-600 text-2xl"></i>
                                </div>
                                <span class="text-amber-900 font-bold mb-1">Antrian Anamnesis</span>
                                <span class="text-amber-500 text-xs font-medium">Pemeriksaan awal perawat</span>
                            </Link>

                            <Link v-if="user?.role === 'dokter'" :href="route('dokter.antrian')" class="bg-gradient-to-br from-rose-50 to-rose-100/50 rounded-xl p-5 flex flex-col justify-between border border-rose-100 transition-all hover:shadow-md hover:-translate-y-1 group items-center text-center h-full">
                                <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="pi pi-check-circle text-rose-600 text-2xl"></i>
                                </div>
                                <span class="text-rose-900 font-bold mb-1">Pasien Antri</span>
                                <span class="text-rose-500 text-xs font-medium">Pemeriksaan dokter</span>
                            </Link>
                        </div>
                    </template>
                </Card>

                <!-- Aktivitas Terbaru -->
                <Card class="shadow-sm border border-gray-100">
                    <template #title>
                        <div class="flex items-center gap-2 text-gray-800">
                            <i class="pi pi-history text-teal-500"></i>
                            <span class="text-lg font-bold">Aktivitas Terkini</span>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <template v-if="props.aktivitas_terbaru && props.aktivitas_terbaru.length > 0">
                                <div v-for="aktivitas in props.aktivitas_terbaru" :key="aktivitas.id" class="flex items-start gap-3 p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 group cursor-default">
                                    <div class="mt-1.5 w-2 h-2 rounded-full shrink-0 bg-blue-500"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start mb-1">
                                            <p class="text-sm font-bold text-gray-900 truncate pr-2">{{ aktivitas.deskripsi }}</p>
                                            <span class="text-[11px] font-medium text-gray-400 shrink-0 bg-gray-50 px-2 py-1 rounded-md">{{ aktivitas.waktu }}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                            <i class="pi pi-user text-gray-400"></i>
                                            <span class="truncate">{{ aktivitas.pasien_nama }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex flex-col items-center justify-center py-8 px-4 bg-gray-50 rounded-xl border border-gray-100 border-dashed">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mb-3 shadow-sm">
                                        <i class="pi pi-inbox text-gray-300 text-xl"></i>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500">Belum ada aktivitas hari ini</p>
                                </div>
                            </template>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
