<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

defineProps<{
    compact?: boolean;
}>();

const page = usePage();
</script>

<template>
    <div class="flex items-center gap-2.5">
        <span
            class="flex size-9 items-center justify-center rounded-xl bg-linear-to-b from-brand-hi to-brand text-on-brand shadow-glow"
            aria-hidden="true"
        >
            <svg
                viewBox="0 0 24 24"
                class="size-5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path
                    d="M4 10h16l-1.5 9.5a1 1 0 0 1-1 .5h-11a1 1 0 0 1-1-.5L4 10Z"
                />
                <path d="M8 10V7a4 4 0 0 1 8 0v3" />
            </svg>
        </span>
        <span
            class="font-semibold tracking-tight text-ink"
            :class="compact ? 'text-base' : 'text-lg'"
        >
            {{ page.props.name }}
        </span>
    </div>
</template>
