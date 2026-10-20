<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';

type Toast = { id: number; type: 'success' | 'error'; message: string };

const toasts = ref<Toast[]>([]);
let nextId = 1;

function push(type: Toast['type'], message: string): void {
    const id = nextId++;
    toasts.value.push({ id, type, message });
    setTimeout(() => dismiss(id), type === 'error' ? 6000 : 4000);
}

function dismiss(id: number): void {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

let stopListening: (() => void) | undefined;

onMounted(() => {
    stopListening = router.on('flash', (event) => {
        const toast = event.detail.flash.toast;

        if (toast) {
            push(toast.type, toast.message);
        }
    });
});

onBeforeUnmount(() => stopListening?.());
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 top-0 z-[60] flex flex-col items-center gap-2 p-3 pt-[max(0.75rem,env(safe-area-inset-top))] sm:top-auto sm:right-0 sm:bottom-0 sm:left-auto sm:items-end sm:p-6"
        aria-live="polite"
    >
        <TransitionGroup
            enter-active-class="transition duration-200"
            enter-from-class="-translate-y-3 scale-95 opacity-0 sm:translate-y-3"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-3xl glass-strong px-4 py-3.5"
                role="status"
            >
                <span
                    :class="
                        toast.type === 'success' ? 'text-brand' : 'text-danger'
                    "
                >
                    <AppIcon
                        :name="
                            toast.type === 'success' ? 'check-circle' : 'alert'
                        "
                        :size="22"
                    />
                </span>
                <p class="flex-1 text-sm text-ink">{{ toast.message }}</p>
                <button
                    type="button"
                    class="text-ink-muted hover:text-ink"
                    aria-label="Yopish"
                    @click="dismiss(toast.id)"
                >
                    <AppIcon name="x" :size="18" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
