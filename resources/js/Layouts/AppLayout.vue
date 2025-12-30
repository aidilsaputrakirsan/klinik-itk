<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { User, PageProps } from '@/types';

// PrimeVue Components
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';
import Menu from 'primevue/menu';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import KlinikLogo from '@/Components/KlinikLogo.vue';

const page = usePage<PageProps>();
const user = computed(() => page.props.auth?.user as User);

const sidebarOpen = ref(true);
const userMenuRef = ref();

const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const userMenuItems = ref([
    {
        label: 'Profil',
        icon: 'pi pi-user',
        command: () => {
            window.location.href = route('profile.edit');
        }
    },
    {
        separator: true
    },
    {
        label: 'Logout',
        icon: 'pi pi-sign-out',
        command: () => {
            router.post(route('logout'));
        }
    }
]);

const toggleUserMenu = (event: Event) => {
    userMenuRef.value.toggle(event);
};

// Menu items berdasarkan role
const menuItems = computed(() => {
    const items = [];

    // Dashboard - semua role
    items.push({
        label: 'Dashboard',
        icon: 'pi pi-home',
        routeName: 'dashboard',
        roles: ['superadmin', 'admin', 'perawat', 'dokter']
    });

    // Admin Menu
    if (['superadmin', 'admin'].includes(user.value?.role)) {
        items.push({
            label: 'Registrasi Pasien',
            icon: 'pi pi-user-plus',
            routeName: 'pasien.create',
            roles: ['superadmin', 'admin']
        });
        items.push({
            label: 'Daftar Pasien',
            icon: 'pi pi-users',
            routeName: 'pasien.index',
            roles: ['superadmin', 'admin']
        });
    }

    // Perawat Menu
    if (['superadmin', 'perawat'].includes(user.value?.role)) {
        items.push({
            label: 'Antrian Pasien',
            icon: 'pi pi-list',
            routeName: 'perawat.antrian',
            roles: ['superadmin', 'perawat']
        });
    }

    // Dokter Menu
    if (['superadmin', 'dokter'].includes(user.value?.role)) {
        items.push({
            label: 'Pasien Siap Periksa',
            icon: 'pi pi-check-circle',
            routeName: 'dokter.antrian',
            roles: ['superadmin', 'dokter']
        });
    }

    // Master Data - Admin & Superadmin
    if (['superadmin', 'admin'].includes(user.value?.role)) {
        items.push({
            label: 'Master Obat',
            icon: 'pi pi-box',
            routeName: 'obat.index',
            roles: ['superadmin', 'admin']
        });
        items.push({
            label: 'Master Tindakan',
            icon: 'pi pi-list-check',
            routeName: 'tindakan.index',
            roles: ['superadmin', 'admin']
        });
    }

    // Laporan - semua kecuali perawat
    if (['superadmin', 'admin', 'dokter'].includes(user.value?.role)) {
        items.push({
            label: 'Laporan',
            icon: 'pi pi-chart-bar',
            routeName: 'laporan.index',
            roles: ['superadmin', 'admin', 'dokter']
        });
    }

    // User Management - Superadmin only
    if (user.value?.role === 'superadmin') {
        items.push({
            label: 'Kelola Pengguna',
            icon: 'pi pi-cog',
            routeName: 'users.index',
            roles: ['superadmin']
        });
    }

    return items.filter(item => item.roles.includes(user.value?.role));
});

const getRoleBadgeClass = (role: string) => {
    const classes: Record<string, string> = {
        superadmin: 'bg-purple-100 text-purple-800',
        admin: 'bg-blue-100 text-blue-800',
        perawat: 'bg-green-100 text-green-800',
        dokter: 'bg-amber-100 text-amber-800'
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
};

const getRoleLabel = (role: string) => {
    const labels: Record<string, string> = {
        superadmin: 'Super Admin',
        admin: 'Admin',
        perawat: 'Perawat',
        dokter: 'Dokter'
    };
    return labels[role] || role;
};

const isCurrentRoute = (routeName: string) => {
    try {
        return route().current(routeName);
    } catch {
        return false;
    }
};

const getRouteHref = (routeName: string) => {
    try {
        return route(routeName);
    } catch {
        return '#';
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Toast position="top-right" />
        <ConfirmDialog />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 z-40 h-screen transition-transform duration-300 bg-white border-r border-gray-200',
                sidebarOpen ? 'w-64 translate-x-0' : 'w-64 -translate-x-full lg:w-20 lg:translate-x-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <Link href="/" class="flex items-center">
                    <KlinikLogo :size="sidebarOpen ? 'md' : 'sm'" :showText="sidebarOpen" variant="full" />
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
                <template v-for="item in menuItems" :key="item.routeName">
                    <Link
                        :href="getRouteHref(item.routeName)"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors',
                            isCurrentRoute(item.routeName)
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'text-gray-600 hover:bg-gray-100'
                        ]"
                    >
                        <i :class="[item.icon, 'text-lg']"></i>
                        <span v-if="sidebarOpen" class="font-medium">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </aside>

        <!-- Main Content -->
        <div
            :class="[
                'transition-all duration-300',
                sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'
            ]"
        >
            <!-- Top Navbar -->
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4">
                    <div class="flex items-center gap-4">
                        <Button
                            icon="pi pi-bars"
                            severity="secondary"
                            text
                            rounded
                            @click="toggleSidebar"
                        />
                        <h1 class="text-lg font-semibold text-gray-900">
                            <slot name="header">Dashboard</slot>
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- User Menu -->
                        <div class="flex items-center gap-3">
                            <div class="hidden md:block text-right">
                                <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                                        getRoleBadgeClass(user?.role)
                                    ]"
                                >
                                    {{ getRoleLabel(user?.role) }}
                                </span>
                            </div>
                            <Avatar
                                :label="user?.name?.charAt(0).toUpperCase()"
                                class="cursor-pointer bg-emerald-600 text-white"
                                shape="circle"
                                @click="toggleUserMenu"
                            />
                            <Menu ref="userMenuRef" :model="userMenuItems" :popup="true" />
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
