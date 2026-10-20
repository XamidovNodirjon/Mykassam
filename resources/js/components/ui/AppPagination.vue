<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import type { Paginated } from '@/types';

defineProps<{
    paginator: Paginated<unknown>;
}>();
</script>

<template>
    <nav
        v-if="paginator.meta.last_page > 1"
        class="flex items-center justify-between gap-3"
        aria-label="Sahifalar"
    >
        <p class="text-sm text-ink-muted">
            {{ paginator.meta.from }}–{{ paginator.meta.to }} /
            {{ paginator.meta.total }}
        </p>
        <div class="flex gap-2">
            <component
                :is="paginator.links.prev ? Link : 'span'"
                :href="paginator.links.prev ?? undefined"
                preserve-scroll
                class="flex size-10 items-center justify-center rounded-full border border-line bg-surface-raised/70 text-ink shadow-card backdrop-blur-md transition hover:bg-surface-raised"
                :class="{ 'opacity-40': !paginator.links.prev }"
                aria-label="Oldingi sahifa"
            >
                <AppIcon name="chevron-left" :size="18" />
            </component>
            <span
                class="flex h-10 min-w-10 items-center justify-center rounded-full bg-surface-muted px-4 text-sm font-medium text-ink tabular-nums"
            >
                {{ paginator.meta.current_page }} /
                {{ paginator.meta.last_page }}
            </span>
            <component
                :is="paginator.links.next ? Link : 'span'"
                :href="paginator.links.next ?? undefined"
                preserve-scroll
                class="flex size-10 items-center justify-center rounded-full border border-line bg-surface-raised/70 text-ink shadow-card backdrop-blur-md transition hover:bg-surface-raised"
                :class="{ 'opacity-40': !paginator.links.next }"
                aria-label="Keyingi sahifa"
            >
                <AppIcon name="chevron-right" :size="18" />
            </component>
        </div>
    </nav>
</template>
