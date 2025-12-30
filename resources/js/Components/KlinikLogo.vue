<script setup lang="ts">
interface Props {
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    showText?: boolean;
    variant?: 'full' | 'icon';
    theme?: 'light' | 'dark';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showText: true,
    variant: 'full',
    theme: 'light'
});

const sizeClasses = {
    xs: { icon: 'w-6 h-6', text: 'text-xs', gap: 'gap-1' },
    sm: { icon: 'w-8 h-8', text: 'text-sm', gap: 'gap-1.5' },
    md: { icon: 'w-10 h-10', text: 'text-base', gap: 'gap-2' },
    lg: { icon: 'w-12 h-12', text: 'text-lg', gap: 'gap-2' },
    xl: { icon: 'w-16 h-16', text: 'text-xl', gap: 'gap-3' },
    '2xl': { icon: 'w-24 h-24', text: 'text-2xl', gap: 'gap-4' }
};

const currentSize = sizeClasses[props.size];
</script>

<template>
    <div :class="['flex items-center', currentSize.gap]">
        <!-- Logo Icon SVG -->
        <svg
            :class="currentSize.icon"
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <!-- Background Circle with Gradient -->
            <defs>
                <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#059669" />
                    <stop offset="100%" style="stop-color:#0d9488" />
                </linearGradient>
                <linearGradient id="crossGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#ffffff" />
                    <stop offset="100%" style="stop-color:#f0fdf4" />
                </linearGradient>
            </defs>

            <!-- Main Circle Background -->
            <circle cx="50" cy="50" r="46" fill="url(#logoGradient)" />

            <!-- Outer Ring -->
            <circle cx="50" cy="50" r="46" stroke="#047857" stroke-width="2" fill="none" opacity="0.3" />

            <!-- Medical Cross -->
            <path
                d="M42 28 H58 V42 H72 V58 H58 V72 H42 V58 H28 V42 H42 Z"
                fill="url(#crossGradient)"
            />

            <!-- Cross Shadow/Depth -->
            <path
                d="M42 28 H58 V42 H72 V58 H58 V72 H42 V58 H28 V42 H42 Z"
                stroke="#047857"
                stroke-width="1"
                fill="none"
                opacity="0.2"
            />

            <!-- Heart in Center (small) -->
            <path
                d="M50 45 C50 42, 46 40, 44 42 C42 44, 42 47, 50 53 C58 47, 58 44, 56 42 C54 40, 50 42, 50 45 Z"
                fill="#dc2626"
                opacity="0.9"
            />

            <!-- Subtle Shine Effect -->
            <ellipse cx="38" cy="35" rx="8" ry="5" fill="white" opacity="0.15" transform="rotate(-30 38 35)" />
        </svg>

        <!-- Text -->
        <div v-if="showText && variant === 'full'" class="flex flex-col leading-tight">
            <span
                :class="[
                    'font-bold tracking-tight',
                    currentSize.text,
                    theme === 'dark' ? 'text-white' : 'text-gray-900'
                ]"
            >
                Klinik ITK
            </span>
            <span
                v-if="size === 'lg' || size === 'xl' || size === '2xl'"
                :class="[
                    'text-xs font-medium',
                    theme === 'dark' ? 'text-emerald-200' : 'text-emerald-600'
                ]"
            >
                Institut Teknologi Kalimantan
            </span>
        </div>
    </div>
</template>
