<script setup lang="ts">
import { Head, Link, useForm, useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import TextField from '@/components/ui/TextField.vue';
import VerificationCodeField from '@/components/VerificationCodeField.vue';
import { useVerificationCode } from '@/composables/useVerificationCode';
import { login } from '@/routes';
import { code, store, verify } from '@/routes/register';

/**
 * Sign-up in three steps: phone, SMS code, then name and password in a modal once the code is
 * confirmed.
 */
const form = useForm({
    phone: '',
    first_name: '',
    password: '',
});

const {
    http: codeRequest,
    sent: codeSent,
    resendIn,
    send,
    reset: resetPhone,
} = useVerificationCode(code());

const verification = useHttp({ phone: '', code: '' });
const detailsOpen = ref(false);
/** The code is used up once confirmed; closing the modal must not ask for it again. */
const phoneConfirmed = ref(false);

function sendCode(): void {
    send(form.phone);
}

function changePhone(): void {
    phoneConfirmed.value = false;
    verification.reset();
    verification.clearErrors();
    resetPhone();
}

function confirmCode(): void {
    if (phoneConfirmed.value) {
        detailsOpen.value = true;

        return;
    }

    verification.phone = form.phone;
    verification
        .submit(verify(), {
            onSuccess: () => {
                form.clearErrors();
                phoneConfirmed.value = true;
                detailsOpen.value = true;
            },
            onHttpException: (response) => {
                verification.setError(
                    'code',
                    response.status === 429
                        ? "Juda ko'p urinish. Birozdan so'ng qayta urinib ko'ring."
                        : "Kodni tekshirib bo'lmadi. Qayta urinib ko'ring.",
                );
            },
            onNetworkError: () => {
                verification.setError('code', 'Internet aloqasini tekshiring.');
            },
        })
        .catch(() => undefined);
}

function submit(): void {
    form.submit(store(), {
        onError: (errors) => {
            // The confirmation expired: start over from the code step.
            if (errors.phone) {
                detailsOpen.value = false;
                phoneConfirmed.value = false;
                verification.reset('code');
                verification.setError('code', errors.phone);
            }
        },
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

        <form
            v-else
            class="mt-6 flex flex-col gap-4"
            @submit.prevent="confirmCode"
        >
            <VerificationCodeField
                v-model="verification.code"
                :phone="form.phone"
                :resend-in="resendIn"
                :sending="codeRequest.processing"
                :error="
                    verification.errors.code ??
                    verification.errors.phone ??
                    codeRequest.errors.phone
                "
                @resend="sendCode"
                @change-phone="changePhone"
            />
            <AppButton
                type="submit"
                size="lg"
                block
                :loading="verification.processing"
                :disabled="verification.code.length === 0"
            >
                {{ phoneConfirmed ? 'Davom etish' : 'Tasdiqlash' }}
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

    <AppModal v-model:open="detailsOpen" title="Ma'lumotlaringiz">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <p
                class="flex items-center gap-2 rounded-2xl bg-brand-soft px-3 py-2 text-sm text-brand-strong"
            >
                <AppIcon name="check-circle" :size="18" />
                Telefon raqam tasdiqlandi
            </p>
            <TextField
                v-model="form.first_name"
                label="Ismingiz"
                autocomplete="given-name"
                autofocus
                :error="form.errors.first_name"
            />
            <TextField
                v-model="form.password"
                type="password"
                label="Parol"
                hint="Kamida 6 ta belgi"
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Ro'yxatdan o'tish
            </AppButton>
        </form>
    </AppModal>
</template>
