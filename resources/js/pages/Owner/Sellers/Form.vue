<script setup lang="ts">
import { Head, router, useForm, useHttp } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppCard from '@/components/ui/AppCard.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { useConfirm } from '@/composables/useConfirm';
import { sellerCredentials } from '@/routes/owner';
import { destroy, index, store, update } from '@/routes/owner/shops/sellers';
import type { Credentials, Shop, User } from '@/types';

const props = defineProps<{
    shop: Shop;
    seller: User | null;
}>();

type CredentialsResponse = { success: boolean; data: Credentials };

const isEditing = computed(() => props.seller !== null);
const backUrl = computed(() => index.url(props.shop.id));
const confirm = useConfirm();

const form = useForm({
    first_name: props.seller?.first_name ?? '',
    last_name: props.seller?.last_name ?? '',
    phone: props.seller?.phone ?? '',
    login: props.seller?.login ?? '',
    password: '',
    is_active: props.seller?.is_active ?? true,
});

const credentialsRequest = useHttp<
    { phone: string; seller_id: number | null },
    CredentialsResponse
>({
    phone: '',
    seller_id: props.seller?.id ?? null,
});

const phoneComplete = computed(() => /^998\d{9}$/.test(form.phone ?? ''));

async function generate(fields: ('login' | 'password')[]): Promise<void> {
    if (!phoneComplete.value) {
        form.setError('phone', 'Avval telefon raqamni to‘liq kiriting.');

        return;
    }

    credentialsRequest.phone = form.phone;

    const response = await credentialsRequest.get(sellerCredentials.url());

    for (const field of fields) {
        form[field] = response.data[field];
        form.clearErrors(field);
    }
}

/**
 * New sellers get a login and password as soon as the phone number is complete.
 */
watch(
    () => form.phone,
    () => {
        if (!isEditing.value && phoneComplete.value) {
            void generate(['login', 'password']);
        }
    },
);

function submit(): void {
    form.submit(
        props.seller
            ? update({ shop: props.shop.id, seller: props.seller.id })
            : store(props.shop.id),
    );
}

async function remove(): Promise<void> {
    if (!props.seller) {
        return;
    }

    const confirmed = await confirm({
        title: "Sotuvchini o'chirish",
        message: `${props.seller.full_name} o'chiriladi. Savdolari bo'lsa, o'rniga nofaol qiling.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy({ shop: props.shop.id, seller: props.seller.id }));
    }
}
</script>

<template>
    <Head :title="seller ? seller.full_name : 'Yangi sotuvchi'" />

    <PageHeader
        :title="seller ? seller.full_name : 'Yangi sotuvchi'"
        :description="shop.name"
        :back-href="backUrl"
    >
        <template v-if="seller" #actions>
            <AppButton
                variant="ghost"
                icon="trash"
                class="hover:text-danger"
                @click="remove"
            >
                O'chirish
            </AppButton>
        </template>
    </PageHeader>

    <form class="max-w-xl" @submit.prevent="submit">
        <div class="flex flex-col gap-4">
            <AppCard title="Shaxsiy ma'lumotlar">
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-3">
                        <TextField
                            v-model="form.first_name"
                            label="Ism"
                            autofocus
                            :error="form.errors.first_name"
                        />
                        <TextField
                            v-model="form.last_name"
                            label="Familiya"
                            :error="form.errors.last_name"
                        />
                    </div>
                    <PhoneField
                        v-model="form.phone"
                        :hint="
                            isEditing
                                ? undefined
                                : 'Raqam kiritilishi bilan login va parol yaratiladi'
                        "
                        :error="form.errors.phone"
                    />
                </div>
            </AppCard>

            <AppCard
                title="Kirish ma'lumotlari"
                :description="
                    isEditing
                        ? 'Parolni o‘zgartirish uchun yangisini yarating'
                        : 'Sotuvchi shu login va parol bilan kiradi'
                "
            >
                <div class="flex flex-col gap-4">
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <TextField
                                v-model="form.login"
                                label="Login"
                                autocomplete="off"
                                placeholder="s901234567"
                                :error="form.errors.login"
                            />
                        </div>
                        <AppButton
                            variant="secondary"
                            class="w-11 px-0"
                            aria-label="Loginni qayta yaratish"
                            :loading="credentialsRequest.processing"
                            @click="generate(['login'])"
                        >
                            <AppIcon name="refresh" :size="18" />
                        </AppButton>
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <TextField
                                v-model="form.password"
                                label="Parol"
                                autocomplete="new-password"
                                :placeholder="
                                    isEditing ? 'O‘zgartirilmaydi' : undefined
                                "
                                :error="form.errors.password"
                            />
                        </div>
                        <AppButton
                            variant="secondary"
                            class="w-11 px-0"
                            aria-label="Yangi parol yaratish"
                            :loading="credentialsRequest.processing"
                            @click="generate(['password'])"
                        >
                            <AppIcon name="refresh" :size="18" />
                        </AppButton>
                    </div>
                </div>
            </AppCard>

            <ToggleField
                v-model="form.is_active"
                label="Faol"
                description="Nofaol sotuvchi tizimga kira olmaydi"
            />
        </div>

        <div
            class="sticky bottom-(--tabbar-clearance) mt-4 flex gap-3 lg:static"
        >
            <AppButton variant="secondary" size="lg" :href="backUrl">
                Bekor qilish
            </AppButton>
            <AppButton
                type="submit"
                size="lg"
                class="flex-1"
                :loading="form.processing"
            >
                Saqlash
            </AppButton>
        </div>
    </form>
</template>
