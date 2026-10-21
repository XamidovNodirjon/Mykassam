<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ForgotPasswordModal from '@/components/ForgotPasswordModal.vue';
import AppButton from '@/components/ui/AppButton.vue';
import TextField from '@/components/ui/TextField.vue';
import { localPhoneDigits } from '@/lib/format';
import { register } from '@/routes';
import { store } from '@/routes/login';

const form = useForm({
    login: '',
    password: '',
});

const forgotPasswordOpen = ref(false);

function submit(): void {
    form.submit(store(), {
        onFinish: () => form.reset('password'),
    });
}

function onPasswordReset(phone: string): void {
    form.login = localPhoneDigits(phone);
    form.clearErrors();
}
</script>

<template>
    <Head title="Kirish" />

    <div class="rounded-[2rem] glass p-6 sm:p-8">
        <h1 class="text-xl font-semibold text-ink">Tizimga kirish</h1>
        <p class="mt-1 text-sm text-ink-muted">
            Do'kon egalari telefon raqami, sotuvchilar berilgan login bilan
            kiradi.
        </p>

        <form class="mt-6 flex flex-col gap-4" @submit.prevent="submit">
            <TextField
                v-model="form.login"
                label="Login yoki telefon"
                placeholder="901234567 yoki s901234567"
                autocomplete="username"
                autofocus
                :error="form.errors.login"
            />
            <TextField
                v-model="form.password"
                type="password"
                label="Parol"
                autocomplete="current-password"
                :error="form.errors.password"
            />
            <div class="flex justify-end">
                <button
                    type="button"
                    class="text-sm font-medium text-brand hover:text-brand-strong"
                    @click="forgotPasswordOpen = true"
                >
                    Parolni unutdingizmi?
                </button>
            </div>
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Kirish
            </AppButton>
        </form>
    </div>

    <p class="mt-6 text-center text-sm text-ink-muted">
        Do'koningiz bormi?
        <Link
            :href="register()"
            class="font-medium text-brand hover:text-brand-strong"
        >
            Ro'yxatdan o'ting
        </Link>
    </p>

    <ForgotPasswordModal
        v-model:open="forgotPasswordOpen"
        @reset="onPasswordReset"
    />
</template>
