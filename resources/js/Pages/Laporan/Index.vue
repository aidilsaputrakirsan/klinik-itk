<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';

const reports = [
    {
        title: 'Laporan Kunjungan',
        description: 'Rekapitulasi data kunjungan pasien dan statistik berdasarkan periode',
        icon: 'pi-calendar-plus',
        gradient: 'from-blue-500 to-indigo-600',
        bgGlow: 'bg-blue-500/10',
        route: 'laporan.kunjungan',
    },
    {
        title: 'Laporan Obat',
        description: 'Pemantauan penggunaan, ketersediaan, dan peringatan stok obat',
        icon: 'pi-box',
        gradient: 'from-emerald-500 to-teal-600',
        bgGlow: 'bg-emerald-500/10',
        route: 'laporan.obat',
    },
    {
        title: 'Laporan Tindakan',
        description: 'Detail tindakan medis yang diberikan serta estimasi finansial',
        icon: 'pi-wrench',
        gradient: 'from-amber-500 to-orange-600',
        bgGlow: 'bg-amber-500/10',
        route: 'laporan.tindakan',
    },
    {
        title: 'Pemeriksaan Umum',
        description: 'Data rekam medis horizontal untuk pasien layanan umum',
        icon: 'pi-file-o',
        gradient: 'from-purple-500 to-fuchsia-600',
        bgGlow: 'bg-purple-500/10',
        route: 'laporan.pemeriksaan-umum',
    },
    {
        title: 'Data Screening',
        description: 'Hasil lengkap screening kesehatan, lab darah, dan antropometri',
        icon: 'pi-heart-fill',
        gradient: 'from-rose-500 to-pink-600',
        bgGlow: 'bg-rose-500/10',
        route: 'laporan.screening',
    },
];
</script>

<template>
    <Head title="Pusat Laporan" />
    <AppLayout>
        <template #header>Pusat Laporan Terpadu</template>

        <div class="font-sans space-y-8 pb-8">
            <!-- Grid Modul Laporan -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="report in reports"
                    :key="report.route"
                    :href="route(report.route)"
                    class="group block relative focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 rounded-2xl"
                >
                    <!-- Background Glow Effect on Hover -->
                    <div :class="['absolute -inset-0.5 rounded-2xl blur opacity-0 group-hover:opacity-100 transition duration-500', report.gradient]"></div>
                    
                    <!-- Main Card -->
                    <div class="relative h-full bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden">
                        
                        <!-- Top Decorator Line -->
                        <div :class="['absolute top-0 left-0 w-full h-1 bg-gradient-to-r', report.gradient]"></div>

                        <div class="flex items-start gap-5">
                            <!-- Icon Box -->
                            <div :class="['shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110 shadow-inner', report.bgGlow]">
                                <i :class="['pi text-2xl bg-gradient-to-br bg-clip-text text-transparent', report.icon, report.gradient]"></i>
                            </div>
                            
                            <!-- Content -->
                            <div class="flex-1 pt-1">
                                <h3 class="text-[17px] font-bold text-gray-800 tracking-tight group-hover:text-emerald-700 transition-colors">{{ report.title }}</h3>
                                <p class="text-[13px] text-gray-500 leading-relaxed mt-2 font-medium">{{ report.description }}</p>
                            </div>
                        </div>

                        <!-- Footer Action -->
                        <div class="mt-6 flex items-center justify-between border-t border-gray-50 pt-4">
                            <span class="text-[13px] font-bold text-emerald-600 flex items-center gap-2 group-hover:text-emerald-700">
                                Buka Laporan 
                                <i class="pi pi-arrow-right text-[10px] transform group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Panduan Penggunaan Berbasis Kartu Elegan -->
            <div class="relative mt-8 bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-8 shadow-2xl overflow-hidden border border-slate-700">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/3 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row gap-8 items-center">
                    <div class="md:w-1/3 text-center md:text-left">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white/10 mb-4 backdrop-blur-md border border-white/10">
                            <i class="pi pi-info-circle text-2xl text-emerald-400"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Panduan Analisis</h3>
                        <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                            Pusat Laporan dirancang untuk membantu Anda memonitor seluruh lini operasional. Semua data dapat diekspor secara real-time.
                        </p>
                    </div>
                    
                    <div class="md:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors cursor-default">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="pi pi-file-pdf text-rose-400"></i>
                                <span class="font-bold text-white text-sm">Cetak PDF</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">Format standar siap cetak yang dioptimalkan untuk kertas A4 dan pelaporan internal.</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors cursor-default">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="pi pi-file-excel text-emerald-400"></i>
                                <span class="font-bold text-white text-sm">Unduh Excel</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">Format *spreadsheet* mentah (.xlsx) untuk kebutuhan analisis atau olah data lebih lanjut.</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors cursor-default">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="pi pi-filter text-blue-400"></i>
                                <span class="font-bold text-white text-sm">Filter Tanggal</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">Anda selalu dapat menyaring data berdasarkan rentang tanggal mulai dan akhir bulan tertentu.</p>
                        </div>
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-xl p-4 hover:bg-white/10 transition-colors cursor-default">
                            <div class="flex items-center gap-3 mb-2">
                                <i class="pi pi-lock text-amber-400"></i>
                                <span class="font-bold text-white text-sm">Keamanan Data</span>
                            </div>
                            <p class="text-xs text-slate-400 leading-relaxed">Semua hasil *export* bersifat rahasia dan hanya dapat diakses oleh Anda (Super Admin/Admin).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
