<script setup lang="ts">
import { computed, useId } from 'vue';
import FormField from '@/components/ui/FormField.vue';
import { inputClass } from '@/components/ui/input';
import { formatNumber } from '@/lib/format';

/**
 * Integer amount input that shows thousands separators while typing ("1 250 000").
 */
withDefaults(
    defineProps<{
        label?: string;
        error?: string;
        hint?: string;
        suffix?: string;
        placeholder?: string;
    }>(),
    {
        label: undefined,
        error: undefined,
        hint: undefined,
        suffix: "so'm",
        placeholder: '0',
    },
);

const model = defineModel<number | null>({ default: null });
const id = useId();

const display = computed({
    get: () => (model.value === null ? '' : formatNumber(model.value)),
    set: (value: string) => {
        const digits = value.replace(/\D/g, '');
        model.value = digits === '' ? null : Number(digits);
    },
});
</script>

<template>
    <FormField :label="label" :for="id" :error="error" :hint="hint">
        <div class="relative">
            <input
                :id="id"
                v-model="display"
                type="text"
                inputmode="numeric"
                autocomplete="off"
                :placeholder="placeholder"
                :aria-invalid="!!error"
                :class="inputClass(!!error, suffix ? 'pr-14' : undefined)"
            />
            <span
                v-if="suffix"
                class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-ink-muted"
            >
                {{ suffix }}
            </span>
        </div>
    </FormField>
</template>
