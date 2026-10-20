<script setup lang="ts">
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import { appearanceOptions, useAppearance } from '@/composables/useAppearance';

/**
 * Light / dark / system theme switch: a labelled segmented control, or a single
 * icon button that cycles through the options when space is tight.
 */
defineProps<{
    compact?: boolean;
}>();

const { appearance, setAppearance, cycleAppearance } = useAppearance();

const selected = computed(
    () =>
        appearanceOptions.find((option) => option.value === appearance.value) ??
        appearanceOptions[0],
);
</script>

<template>
    <button
        v-if="compact"
        type="button"
        class="flex size-10 items-center justify-center rounded-full text-ink-muted transition hover:bg-surface-muted hover:text-ink"
        :aria-label="`Mavzu: ${selected.label}`"
        :title="`Mavzu: ${selected.label}`"
        @click="cycleAppearance"
    >
        <AppIcon :name="selected.icon" :size="20" />
    </button>
    <div
        v-else
        class="grid grid-cols-3 gap-1 rounded-full bg-surface-muted p-1"
        role="radiogroup"
        aria-label="Mavzu"
    >
        <button
            v-for="option in appearanceOptions"
            :key="option.value"
            type="button"
            role="radio"
            class="flex items-center justify-center gap-1.5 rounded-full px-2 py-1.5 text-xs font-medium transition"
            :class="
                appearance === option.value
                    ? 'bg-surface-raised text-ink shadow-sm'
                    : 'text-ink-muted hover:text-ink'
            "
            :aria-checked="appearance === option.value"
            @click="setAppearance(option.value)"
        >
            <AppIcon :name="option.icon" :size="15" />
            {{ option.label }}
        </button>
    </div>
</template>
