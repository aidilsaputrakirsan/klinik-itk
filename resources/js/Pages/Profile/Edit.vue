<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PageProps } from '@/types';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import { useToast } from 'primevue/usetoast';

const page = usePage<PageProps>();
const user = page.props.auth.user;
const toast = useToast();

// Profile form
const profileForm = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
});

const updateProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Profil berhasil diperbarui', life: 3000 });
        },
    });
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Password berhasil diperbarui', life: 3000 });
        },
    });
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
</script>

<template>
    <Head title="Profil Saya" />
    <AppLayout>
        <template #header>Profil Saya</template>

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- User Info Card -->
            <Card class="shadow-sm">
                <template #content>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="pi pi-user text-3xl text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ user.name }}</h2>
                            <p class="text-gray-500">{{ user.email }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                {{ getRoleLabel(user.role) }}
                            </span>
                        </div>
                    </div>
                </template>
            </Card>

            <!-- Update Profile Form -->
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-user-edit text-blue-600"></i>
                        <span>Informasi Profil</span>
                    </div>
                </template>
                <template #subtitle>Perbarui informasi profil dan alamat email Anda.</template>
                <template #content>
                    <form @submit.prevent="updateProfile" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="name" class="font-medium text-sm">Nama <span class="text-red-500">*</span></label>
                                <InputText
                                    id="name"
                                    v-model="profileForm.name"
                                    :invalid="!!profileForm.errors.name"
                                    class="w-full"
                                />
                                <small v-if="profileForm.errors.name" class="text-red-500">{{ profileForm.errors.name }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="email" class="font-medium text-sm">Email <span class="text-red-500">*</span></label>
                                <InputText
                                    id="email"
                                    v-model="profileForm.email"
                                    type="email"
                                    :invalid="!!profileForm.errors.email"
                                    class="w-full"
                                />
                                <small v-if="profileForm.errors.email" class="text-red-500">{{ profileForm.errors.email }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="phone" class="font-medium text-sm">No. Telepon</label>
                                <InputText
                                    id="phone"
                                    v-model="profileForm.phone"
                                    placeholder="08xxxxxxxxxx"
                                    :invalid="!!profileForm.errors.phone"
                                    class="w-full"
                                />
                                <small v-if="profileForm.errors.phone" class="text-red-500">{{ profileForm.errors.phone }}</small>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <Button
                                type="submit"
                                label="Simpan Perubahan"
                                icon="pi pi-check"
                                :loading="profileForm.processing"
                            />
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Update Password Form -->
            <Card class="shadow-sm">
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-lock text-amber-600"></i>
                        <span>Ubah Password</span>
                    </div>
                </template>
                <template #subtitle>Pastikan akun Anda menggunakan password yang kuat.</template>
                <template #content>
                    <form @submit.prevent="updatePassword" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-2">
                                <label for="current_password" class="font-medium text-sm">Password Saat Ini <span class="text-red-500">*</span></label>
                                <Password
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    :feedback="false"
                                    toggleMask
                                    :invalid="!!passwordForm.errors.current_password"
                                    inputClass="w-full"
                                    class="w-full"
                                />
                                <small v-if="passwordForm.errors.current_password" class="text-red-500">{{ passwordForm.errors.current_password }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="password" class="font-medium text-sm">Password Baru <span class="text-red-500">*</span></label>
                                <Password
                                    id="password"
                                    v-model="passwordForm.password"
                                    toggleMask
                                    :invalid="!!passwordForm.errors.password"
                                    inputClass="w-full"
                                    class="w-full"
                                />
                                <small v-if="passwordForm.errors.password" class="text-red-500">{{ passwordForm.errors.password }}</small>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label for="password_confirmation" class="font-medium text-sm">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <Password
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    :feedback="false"
                                    toggleMask
                                    :invalid="!!passwordForm.errors.password_confirmation"
                                    inputClass="w-full"
                                    class="w-full"
                                />
                                <small v-if="passwordForm.errors.password_confirmation" class="text-red-500">{{ passwordForm.errors.password_confirmation }}</small>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4">
                            <Button
                                type="submit"
                                label="Ubah Password"
                                icon="pi pi-lock"
                                severity="warn"
                                :loading="passwordForm.processing"
                            />
                        </div>
                    </form>
                </template>
            </Card>
        </div>
    </AppLayout>
</template>
