<script setup lang="ts">
import { computed } from 'vue';
import AppButton from '@/components/ui/AppButton.vue';
import TextField from '@/components/ui/TextField.vue';
import { formatPhone } from '@/lib/format';

/**
 * SMS code input with the destination number and a resend button that counts down until
 * another code may be requested.
 */
const props = defineProps<{
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

const countdown = computed(() => {
    const minutes = Math.floor(props.resendIn / 60);
    const seconds = String(props.resendIn % 60).padStart(2, '0');

    return `${minutes}:${seconds}`;
});
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
        <AppButton
            variant="secondary"
            icon="refresh"
            block
            :loading="sending"
            :disabled="resendIn > 0"
            @click="$emit('resend')"
        >
            <template v-if="resendIn > 0">
                SMS'ni qayta yuborish
                <span class="tabular-nums">({{ countdown }})</span>
            </template>
            <template v-else>SMS'ni qayta yuborish</template>
        </AppButton>
        <p v-if="resendIn > 0" class="-mt-1 text-center text-xs text-ink-muted">
            SMS kelmasa, {{ countdown }} dan keyin qayta yuborishingiz mumkin.
        </p>
    </div>
</template>
