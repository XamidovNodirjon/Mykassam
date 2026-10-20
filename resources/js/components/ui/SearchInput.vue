<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import { inputClass } from '@/components/ui/input';

/**
 * Debounced search that reloads the current page with ?search=... and keeps scroll/state.
 */
const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        placeholder?: string;
        only?: string[];
        param?: string;
        autofocus?: boolean;
    }>(),
    {
        placeholder: 'Qidirish...',
        only: undefined,
        param: 'search',
    },
);

const term = ref(props.modelValue ?? '');
let timer: ReturnType<typeof setTimeout> | undefined;

watch(term, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.reload({
            data: { [props.param]: value || undefined, page: undefined },
            only: props.only,
            replace: true,
        });
    }, 300);
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <div class="relative">
        <span
            class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-ink-muted"
        >
            <AppIcon name="search" :size="18" />
        </span>
        <input
            v-model="term"
            v-focus="autofocus"
            type="search"
            inputmode="search"
            autocomplete="off"
            :placeholder="placeholder"
            :aria-label="placeholder"
            :class="inputClass(false, 'pl-10')"
        />
    </div>
</template>
