<script setup lang="ts">
import type { PropType } from 'vue';
import { icons } from '@/lib/icons';
import type { IconName } from '@/lib/icons';

const props = defineProps({
    name: { type: String as PropType<IconName>, required: true },
    size: { type: Number, default: 20 },
    strokeWidth: { type: Number, default: 1.75 },
});
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        :stroke-width="props.strokeWidth"
        :width="props.size"
        :height="props.size"
        aria-hidden="true"
        class="shrink-0"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            :d="icons[props.name]"
        />
    </svg>
</template>
