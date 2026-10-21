<script setup lang="ts">
import AppIcon from '@/components/AppIcon.vue';
import type { IconName } from '@/lib/icons';

withDefaults(
    defineProps<{
        label: string;
        value: string;
        hint?: string;
        icon?: IconName;
        highlight?: boolean;
    }>(),
    { hint: undefined, icon: undefined },
);
</script>

<template>
    <div
        class="flex flex-col gap-2 rounded-2xl border p-4 transition duration-300 ease-spring hover:-translate-y-0.5"
        :class="
            highlight
                ? 'border-transparent bg-linear-to-b from-brand-hi to-brand text-on-brand shadow-glow'
                : 'border-line bg-surface shadow-card'
        "
    >
        <div class="flex items-center justify-between gap-2">
            <span
                class="text-xs font-medium tracking-wide uppercase"
                :class="highlight ? 'opacity-80' : 'text-ink-muted'"
            >
                {{ label }}
            </span>
            <span
                v-if="icon"
                class="flex size-8 items-center justify-center rounded-lg"
                :class="
                    highlight ? 'bg-on-brand/15' : 'bg-brand-soft text-brand'
                "
            >
                <AppIcon :name="icon" :size="18" />
            </span>
        </div>
        <p
            class="text-xl font-semibold tracking-tight tabular-nums sm:text-2xl"
            :class="highlight ? '' : 'text-ink'"
        >
            {{ value }}
        </p>
        <p
            v-if="hint"
            class="text-xs"
            :class="highlight ? 'opacity-80' : 'text-ink-muted'"
        >
            {{ hint }}
        </p>
    </div>
</template>
