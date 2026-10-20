<script setup lang="ts">
import AppIcon from '@/components/AppIcon.vue';
import type { IconName } from '@/lib/icons';

withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: IconName;
    }>(),
    { description: undefined, icon: 'cube' },
);
</script>

<template>
    <div
        class="flex flex-col items-center gap-3 rounded-2xl border border-line bg-surface px-6 py-12 text-center shadow-card"
    >
        <span
            class="flex size-14 items-center justify-center rounded-2xl bg-brand-soft text-brand"
        >
            <AppIcon :name="icon" :size="24" />
        </span>
        <div>
            <p class="font-medium text-ink">{{ title }}</p>
            <p v-if="description" class="mt-1 text-sm text-ink-muted">
                {{ description }}
            </p>
        </div>
        <slot />
    </div>
</template>
