<script setup lang="ts">
import { computed, useId } from 'vue';
import FormField from '@/components/ui/FormField.vue';
import { inputClass } from '@/components/ui/input';
import { localPhoneDigits } from '@/lib/format';

/**
 * Uzbek phone input with a fixed +998 prefix. The model holds the full "998XXXXXXXXX" value.
 */
withDefaults(
    defineProps<{
        label?: string;
        error?: string;
        hint?: string;
        autofocus?: boolean;
    }>(),
    { label: 'Telefon raqam', error: undefined, hint: undefined },
);

const model = defineModel<string | null>({ default: '' });
const id = useId();

const display = computed({
    get: () => {
        const digits = localPhoneDigits(model.value);
        const parts = [
            digits.slice(0, 2),
            digits.slice(2, 5),
            digits.slice(5, 7),
            digits.slice(7, 9),
        ].filter(Boolean);

        return parts.join(' ');
    },
    set: (value: string) => {
        const digits = value.replace(/\D/g, '').slice(0, 9);
        model.value = digits === '' ? '' : `998${digits}`;
    },
});
</script>

<template>
    <FormField :label="label" :for="id" :error="error" :hint="hint">
        <div class="relative">
            <span
                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-base text-ink-muted"
            >
                +998
            </span>
            <input
                :id="id"
                v-model="display"
                v-focus="autofocus"
                type="tel"
                inputmode="tel"
                autocomplete="tel-national"
                placeholder="90 123 45 67"
                :aria-invalid="!!error"
                :class="inputClass(!!error, 'pl-15')"
            />
        </div>
    </FormField>
</template>
