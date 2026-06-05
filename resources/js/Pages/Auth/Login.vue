<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Swal from 'sweetalert2';
import axios from 'axios';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = async () => {
    form.processing = true;
    form.clearErrors();

    try {
        await axios.post(route('login'), {
            email: form.email,
            password: form.password,
            remember: form.remember,
        });

        Swal.fire({
            icon: 'success',
            title: 'Berhasil Login!',
            text: 'Mengarahkan ke Dashboard...',
            showConfirmButton: false,
            timer: 2000,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-gray-100',
                title: 'text-2xl font-bold text-gray-900',
                htmlContainer: 'text-gray-500 text-sm mt-2',
            },
        }).then(() => {
            window.location.href = route('dashboard');
        });
    } catch (error: any) {
        form.processing = false;
        form.reset('password');

        if (error.response?.status === 422) {
            form.setError(error.response.data.errors);
        }

        Swal.fire({
            icon: 'error',
            title: 'Gagal Login!',
            text: 'Email atau password yang Anda masukkan salah.',
            confirmButtonText: 'Coba Lagi',
            buttonsStyling: false,
            background: '#ffffff',
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-gray-100',
                title: 'text-2xl font-bold text-gray-900',
                htmlContainer: 'text-gray-500 text-sm mt-2',
                confirmButton:
                    'w-full mt-6 bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6 py-3 font-semibold transition-all shadow-md hover:shadow-lg',
            },
        });
    }
};
</script>

<template>
    <Head title="Login - Klinik ITK" />

    <div class="login-root min-h-screen flex">
        <!-- ═══════════════════════════════════════════ -->
        <!-- LEFT PANEL – animated green branding side  -->
        <!-- ═══════════════════════════════════════════ -->
        <div class="hidden lg:flex lg:w-1/2 login-blue-panel relative overflow-hidden items-center justify-center">

            <!-- Animated gradient overlay -->
            <div class="gradient-overlay absolute inset-0 pointer-events-none"></div>

            <!-- ── Floating decorative circles ── -->
            <div class="circles-layer absolute inset-0 pointer-events-none overflow-hidden">
                <!-- large, top-left -->
                <div class="circle c1"></div>
                <!-- medium, top-right -->
                <div class="circle c2"></div>
                <!-- extra-large, bottom-left -->
                <div class="circle c3"></div>
                <!-- small, bottom-right -->
                <div class="circle c4"></div>
                <!-- tiny dashed, mid-left -->
                <div class="circle c5"></div>
                <!-- tiny, top-center -->
                <div class="circle c6"></div>
                <!-- medium-small, mid-right -->
                <div class="circle c7"></div>
            </div>

            <!-- ── Branding content ── -->
            <div class="relative z-10 flex flex-col items-center text-center px-12 text-white">
                <!-- Logo wrapped in glassmorphism card -->
                <div class="logo-card mb-8 flex items-center justify-center">
                    <div class="bg-white rounded-full w-24 h-24 shadow-xl flex items-center justify-center">
                        <img src="/images/Lambang.png" alt="Lambang ITK" class="w-16 h-16 object-contain drop-shadow-sm -mt-2" />
                    </div>
                </div>

                <h1 class="brand-title text-5xl font-extrabold mb-3 tracking-tight">Klinik ITK</h1>
                <p class="brand-sub text-lg text-blue-100 font-medium mb-10">Sistem Informasi Klinik ITK</p>

                <p class="brand-desc text-sm text-blue-100/80 max-w-xs leading-relaxed mb-10">
                    Melayani kesehatan civitas akademika Institut Teknologi Kalimantan dengan sepenuh hati.
                </p>

                <!-- Role pills -->
                <div class="flex items-center justify-center gap-6">
                    <div class="role-pill" style="animation-delay: 0s">Mahasiswa</div>
                    <div class="role-pill" style="animation-delay: 0.15s">Dosen</div>
                    <div class="role-pill" style="animation-delay: 0.3s">Tendik</div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- RIGHT PANEL – login form               -->
        <!-- ═══════════════════════════════════════ -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-gray-50">
            <div class="w-full max-w-md">

                <!-- Mobile logo (shown only on small screens) -->
                <div class="lg:hidden text-center mb-8">
                    <div class="flex justify-center mb-3">
                        <img src="/images/Lambang.png" alt="Lambang ITK" class="w-16 h-16 object-contain" />
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Klinik ITK</h1>
                </div>

                <!-- Login Card -->
                <div class="login-card bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Selamat Datang</h2>
                        <p class="text-gray-500 text-sm mt-1">Silakan masuk ke akun Anda</p>
                    </div>

                    <!-- Status Message -->
                    <div v-if="status" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <p class="text-sm text-blue-700">{{ status }}</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
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
                            <small v-if="form.errors.email" class="text-red-500 text-xs">{{ form.errors.email }}</small>
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
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
                                    input: { class: 'w-full' },
                                }"
                                required
                            />
                            <small v-if="form.errors.password" class="text-red-500 text-xs">{{ form.errors.password }}</small>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center gap-2">
                            <Checkbox v-model="form.remember" inputId="remember" :binary="true" />
                            <label for="remember" class="text-sm text-gray-600 cursor-pointer select-none">Ingat saya</label>
                        </div>

                        <!-- Submit -->
                        <Button
                            type="submit"
                            label="Masuk"
                            class="w-full !bg-blue-600 !border-blue-600 hover:!bg-blue-700 hover:!border-blue-700"
                            :loading="form.processing"
                            :pt="{
                                root: { class: 'justify-center' },
                            }"
                        />
                    </form>
                </div>

                <!-- Footer -->
                <p class="text-center text-gray-400 text-xs mt-6">
                    &copy; {{ new Date().getFullYear() }}
                    <span class="text-blue-600 font-medium">Klinik ITK</span>
                    &bull; Institut Teknologi Kalimantan
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ══════════════════════════════════════════════════
   FONT – Inter from Google Fonts
══════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&display=swap');

.login-root,
.login-root * {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* ══════════════════════════════════════════════════
   ANIMATED BLUE GRADIENT PANEL
══════════════════════════════════════════════════ */
.login-blue-panel {
    background: linear-gradient(135deg, #3b82f6, #2563eb, #1d4ed8, #1e40af, #1e3a8a);
    background-size: 400% 400%;
    animation: gradientShift 10s ease infinite;
}

.gradient-overlay {
    background: radial-gradient(
        ellipse at 30% 20%,
        rgba(96, 165, 250, 0.30) 0%,
        transparent 60%
    ),
    radial-gradient(
        ellipse at 80% 80%,
        rgba(30, 58, 138, 0.50) 0%,
        transparent 55%
    );
    animation: overlayPulse 7s ease-in-out infinite alternate;
}

@keyframes gradientShift {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes overlayPulse {
    0%   { opacity: 0.6; }
    100% { opacity: 1; }
}

/* ══════════════════════════════════════════════════
   DECORATIVE CIRCLES – base styles
══════════════════════════════════════════════════ */
.circle {
    position: absolute;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.22);
    background: transparent;
}

/* Circle 1 – large, top-left */
.c1 {
    width: 140px;
    height: 140px;
    top: 5%;
    left: 5%;
    animation: floatUp 7s ease-in-out infinite, glowPulse 5s ease-in-out infinite;
}

/* Circle 2 – medium, top-right */
.c2 {
    width: 95px;
    height: 95px;
    top: 22%;
    right: 10%;
    border-width: 2.5px;
    animation: floatDown 5.5s ease-in-out infinite 1s, glowPulse 4s ease-in-out infinite 0.5s;
}

/* Circle 3 – extra-large, bottom-left */
.c3 {
    width: 170px;
    height: 170px;
    bottom: 10%;
    left: 12%;
    animation: orbitFloat 9s ease-in-out infinite 0.3s, glowPulse 6s ease-in-out infinite 1s;
}

/* Circle 4 – small, bottom-right */
.c4 {
    width: 72px;
    height: 72px;
    bottom: 18%;
    right: 6%;
    border-width: 2px;
    animation: floatUp 4.2s ease-in-out infinite 0.7s, glowPulse 3.5s ease-in-out infinite 0.2s;
}

/* Circle 5 – tiny dashed, mid-left */
.c5 {
    width: 52px;
    height: 52px;
    top: 50%;
    left: 7%;
    border-style: dashed;
    border-width: 2px;
    animation: spinFloat 14s linear infinite;
}

/* Circle 6 – tiny, top-center */
.c6 {
    width: 38px;
    height: 38px;
    top: 13%;
    left: 46%;
    border-width: 1.5px;
    animation: floatDown 6s ease-in-out infinite 2s, glowPulse 4.5s ease-in-out infinite 1.5s;
}

/* Circle 7 – medium-small, mid-right */
.c7 {
    width: 60px;
    height: 60px;
    top: 58%;
    right: 14%;
    border-width: 2px;
    animation: floatUp 5.8s ease-in-out infinite 1.2s, glowPulse 5s ease-in-out infinite 0.8s;
}

/* ══════════════════════════════════════════════════
   CIRCLE KEYFRAMES
══════════════════════════════════════════════════ */
@keyframes floatUp {
    0%, 100% { transform: translateY(0px) scale(1); }
    50%       { transform: translateY(-20px) scale(1.04); }
}

@keyframes floatDown {
    0%, 100% { transform: translateY(0px) scale(1); }
    50%       { transform: translateY(18px) scale(0.97); }
}

@keyframes orbitFloat {
    0%   { transform: translate(0px, 0px) scale(1); }
    25%  { transform: translate(12px, -14px) scale(1.03); }
    50%  { transform: translate(0px, -22px) scale(1.06); }
    75%  { transform: translate(-12px, -14px) scale(1.03); }
    100% { transform: translate(0px, 0px) scale(1); }
}

@keyframes spinFloat {
    0%   { transform: rotate(0deg) translateY(0px); }
    50%  { transform: rotate(180deg) translateY(-16px); }
    100% { transform: rotate(360deg) translateY(0px); }
}

@keyframes glowPulse {
    0%, 100% {
        border-color: rgba(255, 255, 255, 0.18);
        box-shadow: 0 0 0 rgba(255, 255, 255, 0);
    }
    50% {
        border-color: rgba(255, 255, 255, 0.55);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.12);
    }
}

/* ══════════════════════════════════════════════════
   BRANDING CONTENT ANIMATIONS
══════════════════════════════════════════════════ */
.logo-card {
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.20);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1px solid rgba(255, 255, 255, 0.30);
    border-radius: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    animation: floatUp 5s ease-in-out infinite;
}

.brand-title {
    animation: fadeSlideUp 0.7s ease both;
}
.brand-sub {
    animation: fadeSlideUp 0.7s ease 0.12s both;
}
.brand-desc {
    animation: fadeSlideUp 0.7s ease 0.24s both;
}

@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(22px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Role pills ── */
.role-pill {
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0.6rem 1.25rem;
    background: rgba(255, 255, 255, 0.10);
    border: 1px solid rgba(255, 255, 255, 0.20);
    border-radius: 999px;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.02em;
    animation: fadeSlideUp 0.6s ease both;
    transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
}
.role-pill:hover {
    background: rgba(255, 255, 255, 0.22);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}

/* ── Role icon (emoji) ── */
.role-icon {
    font-size: 1.5rem;
    line-height: 1;
    display: block;
    margin-bottom: 2px;
    filter: drop-shadow(0 1px 3px rgba(0,0,0,0.2));
}

/* ══════════════════════════════════════════════════
   LOGIN CARD
══════════════════════════════════════════════════ */
.login-card {
    animation: fadeSlideUp 0.6s ease both 0.1s;
}
</style>
