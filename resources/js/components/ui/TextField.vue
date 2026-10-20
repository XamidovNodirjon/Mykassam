<script setup lang="ts">
import { computed, ref, useId } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import FormField from '@/components/ui/FormField.vue';
import { inputClass } from '@/components/ui/input';

const props = withDefaults(
    defineProps<{
        label?: string;
        error?: string;
        hint?: string;
        type?: 'text' | 'password' | 'search' | 'date' | 'tel';
        placeholder?: string;
        autocomplete?: string;
        inputmode?: 'text' | 'numeric' | 'tel' | 'search';
        autofocus?: boolean;
        readonly?: boolean;
        name?: string;
    }>(),
    {
        type: 'text',
        label: undefined,
        error: undefined,
        hint: undefined,
        placeholder: undefined,
        autocomplete: undefined,
        inputmode: undefined,
        name: undefined,
    },
);

const model = defineModel<string | null>({ default: '' });
const id = useId();
const revealed = ref(false);

const inputType = computed(() =>
    props.type === 'password' && revealed.value ? 'text' : props.type,
);
</script>

<template>
    <FormField :label="label" :for="id" :error="error" :hint="hint">
        <div class="relative">
            <input
                :id="id"
                v-model="model"
                v-focus="autofocus"
                :name="name"
                :type="inputType"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :inputmode="inputmode"
                :readonly="readonly"
                :aria-invalid="!!error"
                :class="[
                    inputClass(!!error),
                    { 'pr-11': type === 'password' },
                ]"
            />
            <button
                v-if="type === 'password'"
                type="button"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center text-ink-muted hover:text-ink"
                :aria-label="revealed ? 'Parolni yashirish' : `Parolni ko'rsatish`"
                @click="revealed = !revealed"
            >
                <AppIcon :name="revealed ? 'eye-off' : 'eye'" :size="18" />
            </button>
        </div>
    </FormField>
</template>
