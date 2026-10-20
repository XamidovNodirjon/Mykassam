<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import AppButton from '@/components/ui/AppButton.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import TextField from '@/components/ui/TextField.vue';
import VerificationCodeField from '@/components/VerificationCodeField.vue';
import { useVerificationCode } from '@/composables/useVerificationCode';
import { login } from '@/routes';
import { code, store } from '@/routes/register';

const form = useForm({
    phone: '',
    code: '',
    first_name: '',
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
    form.submit(store(), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Ro'yxatdan o'tish" />

    <div class="rounded-[2rem] glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-ink">Do'kon egasi sifatida</h1>
        <p class="mt-1 text-sm text-ink-muted">
            Telefon raqamingiz tizimga kirish uchun login bo'ladi.
        </p>
        <p
            class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-3 py-1 text-xs font-medium text-brand-strong"
        >
            <AppIcon name="gift" :size="14" />
            Business Plus tarifi 1 oy bepul
        </p>

        <form
            v-if="!codeSent"
            class="mt-6 flex flex-col gap-4"
            @submit.prevent="sendCode"
        >
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

        <form v-else class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
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
                v-model="form.first_name"
                label="Ismingiz"
                autocomplete="given-name"
                :error="form.errors.first_name"
            />
            <TextField
                v-model="form.password"
                type="password"
                label="Parol"
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Ro'yxatdan o'tish
            </AppButton>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-ink-muted">
        Hisobingiz bormi?
        <Link
            :href="login()"
            class="font-medium text-brand hover:text-brand-strong"
        >
            Kirish
        </Link>
    </p>
</template>
