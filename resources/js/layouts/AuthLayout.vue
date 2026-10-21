<script setup lang="ts">
import { ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import ToastHost from '@/components/ToastHost.vue';
import { usePageEnter } from '@/composables/usePageEnter';

const content = ref<HTMLElement | null>(null);
usePageEnter(content);
</script>

<template>
    <div
        class="relative flex min-h-dvh flex-col items-center justify-center px-4 pt-[max(2.5rem,env(safe-area-inset-top))] pb-[max(2.5rem,env(safe-area-inset-bottom))]"
    >
        <div
            class="absolute top-[max(0.75rem,env(safe-area-inset-top))] right-3 rounded-full glass p-0.5"
        >
            <ThemeToggle compact />
        </div>
        <div ref="content" class="w-full max-w-sm">
            <div class="mb-8 flex justify-center">
                <AppLogo />
            </div>
            <slot />
        </div>
        <ToastHost />
    </div>
</template>
