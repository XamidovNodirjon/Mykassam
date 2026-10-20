<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import TextField from '@/components/ui/TextField.vue';
import VerificationCodeField from '@/components/VerificationCodeField.vue';
import { useVerificationCode } from '@/composables/useVerificationCode';
import { code, update } from '@/routes/password';

const open = defineModel<boolean>('open', { default: false });

const emit = defineEmits<{
    reset: [phone: string];
}>();

const form = useForm({
    phone: '',
    code: '',
    password: '',
});

const {
    http: codeRequest,
    sent: codeSent,
    resendIn,
    send,
    reset: changePhone,
} = useVerificationCode(code());

function sendCode(): void {
    send(form.phone);
}

function submit(): void {
    form.submit(update(), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('reset', form.phone);
            open.value = false;
            form.reset();
            changePhone();
        },
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <AppModal v-model:open="open" title="Parolni tiklash">
        <form
            v-if="!codeSent"
            class="flex flex-col gap-4"
            @submit.prevent="sendCode"
        >
            <p class="text-sm text-ink-muted">
                Ro'yxatdan o'tgan telefon raqamingizni kiriting, unga tasdiqlash
                kodini yuboramiz.
            </p>
            <PhoneField
                v-model="form.phone"
                autofocus
                :error="codeRequest.errors.phone"
            />
            <AppButton
                type="submit"
                size="lg"
                block
                :loading="codeRequest.processing"
            >
                Kod olish
            </AppButton>
        </form>

        <form v-else class="flex flex-col gap-4" @submit.prevent="submit">
            <VerificationCodeField
                v-model="form.code"
                :phone="form.phone"
                :resend-in="resendIn"
                :sending="codeRequest.processing"
                :error="
                    form.errors.code ??
                    form.errors.phone ??
                    codeRequest.errors.phone
                "
                @resend="sendCode"
                @change-phone="changePhone"
            />
            <TextField
                v-model="form.password"
                type="password"
                label="Yangi parol"
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Parolni saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
