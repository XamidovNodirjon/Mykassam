<script setup lang="ts" generic="T extends string | number | null">
import { useId } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import FormField from '@/components/ui/FormField.vue';
import { inputClass } from '@/components/ui/input';

withDefaults(
    defineProps<{
        options: { value: T; label: string }[];
        label?: string;
        error?: string;
        hint?: string;
        placeholder?: string;
    }>(),
    {
        label: undefined,
        error: undefined,
        hint: undefined,
        placeholder: undefined,
    },
);

const model = defineModel<T>();
const id = useId();
</script>

<template>
    <FormField :label="label" :for="id" :error="error" :hint="hint">
        <div class="relative">
            <select
                :id="id"
                v-model="model"
                :aria-invalid="!!error"
                :class="inputClass(!!error, 'appearance-none pr-10')"
            >
                <option v-if="placeholder" :value="null">
                    {{ placeholder }}
                </option>
                <option
                    v-for="option in options"
                    :key="String(option.value)"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <span
                class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-ink-muted"
            >
                <AppIcon name="chevron-right" :size="16" class="rotate-90" />
            </span>
        </div>
    </FormField>
</template>
