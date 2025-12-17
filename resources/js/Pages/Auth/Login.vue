<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Login - Klinik ITK" />

    <div class="min-h-screen flex">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 relative overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 border-4 border-white rounded-full"></div>
                <div class="absolute top-40 right-20 w-24 h-24 border-4 border-white rounded-full"></div>
                <div class="absolute bottom-20 left-1/4 w-40 h-40 border-4 border-white rounded-full"></div>
                <div class="absolute bottom-40 right-10 w-20 h-20 border-4 border-white rounded-full"></div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-center items-center w-full px-12 text-white">
                <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-8">
                    <i class="pi pi-heart-fill text-5xl text-white"></i>
                </div>
                <h1 class="text-4xl font-bold mb-4 text-center">Klinik ITK</h1>
                <p class="text-xl text-emerald-100 mb-8 text-center">Sistem Informasi Klinik Kampus</p>
                <div class="text-center text-emerald-100/80 max-w-md">
                    <p class="mb-6">Melayani kesehatan civitas akademika Institut Teknologi Kalimantan dengan sepenuh hati.</p>
                    <div class="flex items-center justify-center gap-8 mt-8">
                        <div class="text-center">
                            <i class="pi pi-users text-3xl mb-2"></i>
                            <p class="text-sm">Mahasiswa</p>
                        </div>
                        <div class="text-center">
                            <i class="pi pi-briefcase text-3xl mb-2"></i>
                            <p class="text-sm">Dosen</p>
                        </div>
                        <div class="text-center">
                            <i class="pi pi-building text-3xl mb-2"></i>
                            <p class="text-sm">Staff</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <div class="w-16 h-16 bg-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="pi pi-heart-fill text-3xl text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Klinik ITK</h1>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Selamat Datang</h2>
                        <p class="text-gray-500 mt-2">Silakan masuk ke akun Anda</p>
                    </div>

                    <!-- Status Message -->
                    <div v-if="status" class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                        <p class="text-sm text-emerald-700">{{ status }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Email -->
                        <div class="flex flex-col gap-2">
                            <label for="email" class="font-medium text-gray-700">Email</label>
                            <span class="p-input-icon-left w-full">
                                <i class="pi pi-envelope" />
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="email@itk.ac.id"
                                    class="w-full"
                                    :class="{ 'p-invalid': form.errors.email }"
                                    required
                                    autofocus
                                />
                            </span>
                            <small v-if="form.errors.email" class="text-red-500">{{ form.errors.email }}</small>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-2">
                            <label for="password" class="font-medium text-gray-700">Password</label>
                            <Password
                                id="password"
                                v-model="form.password"
                                :feedback="false"
                                toggleMask
                                placeholder="Masukkan password"
                                inputClass="w-full"
                                :class="{ 'p-invalid': form.errors.password }"
                                :pt="{
                                    root: { class: 'w-full' },
                                    input: { class: 'w-full' }
                                }"
                                required
                            />
                            <small v-if="form.errors.password" class="text-red-500">{{ form.errors.password }}</small>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    v-model="form.remember"
                                    inputId="remember"
                                    :binary="true"
                                />
                                <label for="remember" class="text-sm text-gray-600 cursor-pointer">Ingat saya</label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            label="Masuk"
                            icon="pi pi-sign-in"
                            class="w-full"
                            :loading="form.processing"
                            :pt="{
                                root: { class: 'bg-emerald-600 border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700' }
                            }"
                        />
                    </form>

                    <!-- Demo Accounts Info -->
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 mb-3">Akun Demo:</p>
                        <div class="space-y-2 text-xs text-gray-600">
                            <div class="flex justify-between items-center p-2 bg-white rounded border">
                                <span class="font-medium">Super Admin</span>
                                <code class="text-emerald-600">superadmin@itk.ac.id</code>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-white rounded border">
                                <span class="font-medium">Admin</span>
                                <code class="text-emerald-600">admin@itk.ac.id</code>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-white rounded border">
                                <span class="font-medium">Perawat</span>
                                <code class="text-emerald-600">perawat@itk.ac.id</code>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-white rounded border">
                                <span class="font-medium">Dokter</span>
                                <code class="text-emerald-600">dokter@itk.ac.id</code>
                            </div>
                            <p class="text-center text-gray-500 mt-2">Password: <code class="text-emerald-600">password</code></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-gray-400 text-sm mt-6">
                    &copy; {{ new Date().getFullYear() }} Klinik ITK - Institut Teknologi Kalimantan
                </p>
            </div>
        </div>
    </div>
</template>
