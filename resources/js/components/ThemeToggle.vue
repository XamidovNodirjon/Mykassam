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

const selectedIndex = computed(() =>
    Math.max(
        0,
        appearanceOptions.findIndex(
            (option) => option.value === appearance.value,
        ),
    ),
);

const selected = computed(() => appearanceOptions[selectedIndex.value]);
</script>

<template>
    <button
        v-if="compact"
        type="button"
        data-keep-motion
        class="flex size-10 items-center justify-center overflow-hidden rounded-full text-ink-muted transition hover:bg-surface-muted hover:text-ink active:scale-90"
        :aria-label="`Mavzu: ${selected.label}`"
        :title="`Mavzu: ${selected.label}`"
        @click="cycleAppearance"
    >
        <Transition
            mode="out-in"
            enter-active-class="transition duration-300 ease-spring"
            enter-from-class="-rotate-90 scale-50 opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="rotate-90 scale-50 opacity-0"
        >
            <AppIcon :key="selected.value" :name="selected.icon" :size="20" />
        </Transition>
    </button>
    <div
        v-else
        data-keep-motion
        class="relative grid grid-cols-3 gap-1 rounded-full bg-surface-muted p-1"
        role="radiogroup"
        aria-label="Mavzu"
    >
        <span
            class="absolute inset-y-1 left-1 w-[calc((100%-1rem)/3)] rounded-full bg-surface-raised shadow-sm transition-transform duration-300 ease-spring"
            :style="{
                transform: `translateX(calc(${selectedIndex} * (100% + 0.25rem)))`,
            }"
            aria-hidden="true"
        />
        <button
            v-for="option in appearanceOptions"
            :key="option.value"
            type="button"
            role="radio"
            class="relative flex items-center justify-center gap-1.5 rounded-full px-2 py-1.5 text-xs font-medium transition"
            :class="
                appearance === option.value
                    ? 'text-ink'
                    : 'text-ink-muted hover:text-ink'
            "
            :aria-checked="appearance === option.value"
            @click="setAppearance(option.value, $event)"
        >
            <AppIcon :name="option.icon" :size="15" />
            {{ option.label }}
        </button>
    </div>
</template>
