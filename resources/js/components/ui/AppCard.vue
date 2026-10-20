<script setup lang="ts">
withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        padded?: boolean;
    }>(),
    { title: undefined, description: undefined, padded: true },
);
</script>

<template>
    <section class="rounded-2xl border border-line bg-surface shadow-card">
        <header
            v-if="title || $slots.actions"
            class="flex items-start justify-between gap-3 px-4 pt-4 sm:px-5 sm:pt-5"
        >
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-ink">{{ title }}</h2>
                <p v-if="description" class="mt-0.5 text-sm text-ink-muted">
                    {{ description }}
                </p>
            </div>
            <slot name="actions" />
        </header>
        <div :class="padded ? 'p-4 sm:p-5' : ''">
            <slot />
        </div>
    </section>
</template>
