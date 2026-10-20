<script setup lang="ts">
import TextField from '@/components/ui/TextField.vue';
import { formatPhone } from '@/lib/format';

/**
 * SMS code input with the destination number and a resend countdown.
 */
defineProps<{
    phone: string;
    resendIn: number;
    sending?: boolean;
    error?: string;
}>();

defineEmits<{
    resend: [];
    changePhone: [];
}>();

const code = defineModel<string>({ default: '' });
</script>

<template>
    <div class="flex flex-col gap-3">
        <p class="text-sm text-ink-muted">
            Kod
            <span class="font-medium text-ink">{{ formatPhone(phone) }}</span>
            raqamiga yuborildi.
            <button
                type="button"
                class="font-medium text-brand hover:text-brand-strong"
                @click="$emit('changePhone')"
            >
                O'zgartirish
            </button>
        </p>
        <TextField
            v-model="code"
            label="SMS kod"
            inputmode="numeric"
            autocomplete="one-time-code"
            autofocus
            :error="error"
        />
        <button
            type="button"
            class="self-start text-sm font-medium text-brand hover:text-brand-strong disabled:text-ink-muted"
            :disabled="resendIn > 0 || sending"
            @click="$emit('resend')"
        >
            {{
                resendIn > 0
                    ? `Qayta yuborish: ${resendIn} soniya`
                    : 'Kodni qayta yuborish'
            }}
        </button>
    </div>
</template>
