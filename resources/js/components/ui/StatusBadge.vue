<script setup lang="ts">
withDefaults(
    defineProps<{
        tone?: 'success' | 'danger' | 'warning' | 'neutral';
    }>(),
    { tone: 'neutral' },
);
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
        :class="{
            'bg-brand-soft text-brand-strong': tone === 'success',
            'bg-danger-soft text-danger': tone === 'danger',
            'bg-warning-soft text-warning': tone === 'warning',
            'bg-surface-muted text-ink-soft': tone === 'neutral',
        }"
    >
        <span
            class="size-1.5 rounded-full bg-current"
            aria-hidden="true"
        />
        <slot />
    </span>
</template>
