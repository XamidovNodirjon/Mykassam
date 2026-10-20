<script setup lang="ts">
/**
 * Label + control slot + hint/error line shared by every input component.
 */
defineProps<{
    label?: string;
    for?: string;
    error?: string;
    hint?: string;
}>();
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label
            v-if="label"
            :for="$props.for"
            class="text-sm font-medium text-ink-soft"
        >
            {{ label }}
        </label>
        <slot />
        <p v-if="error" class="text-sm text-danger" role="alert">
            {{ error }}
        </p>
        <p v-else-if="hint" class="text-xs text-ink-muted">{{ hint }}</p>
    </div>
</template>
