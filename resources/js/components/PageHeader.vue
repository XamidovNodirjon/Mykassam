<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';

defineProps<{
    title: string;
    description?: string;
    backHref?: string;
}>();
</script>

<template>
    <div
        class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between lg:mb-8"
    >
        <div class="flex min-w-0 items-start gap-2">
            <Link
                v-if="backHref"
                :href="backHref"
                class="-ml-2 flex size-10 shrink-0 items-center justify-center rounded-xl text-ink-soft hover:bg-surface-muted hover:text-ink"
                aria-label="Orqaga"
            >
                <AppIcon name="chevron-left" :size="22" />
            </Link>
            <div class="min-w-0">
                <h1
                    class="truncate text-2xl font-semibold tracking-tight text-ink"
                >
                    {{ title }}
                </h1>
                <p v-if="description" class="mt-0.5 text-sm text-ink-muted">
                    {{ description }}
                </p>
            </div>
        </div>
        <div v-if="$slots.actions" class="flex shrink-0 gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
