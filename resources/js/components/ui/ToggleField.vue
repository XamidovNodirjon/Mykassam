<script setup lang="ts">
import { useId } from 'vue';

defineProps<{
    label: string;
    description?: string;
}>();

const model = defineModel<boolean>({ default: false });
const id = useId();
</script>

<template>
    <label
        :for="id"
        class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-line bg-surface px-4 py-3"
    >
        <span class="flex flex-col">
            <span class="text-sm font-medium text-ink">{{ label }}</span>
            <span v-if="description" class="text-xs text-ink-muted">
                {{ description }}
            </span>
        </span>
        <span class="relative inline-flex shrink-0">
            <input
                :id="id"
                v-model="model"
                type="checkbox"
                role="switch"
                class="peer sr-only"
            />
            <span
                class="h-7 w-12 rounded-full bg-ink-muted/30 transition peer-checked:bg-brand peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brand"
            />
            <span
                class="absolute top-1 left-1 size-5 rounded-full bg-white shadow-md transition peer-checked:translate-x-5"
            />
        </span>
    </label>
</template>
