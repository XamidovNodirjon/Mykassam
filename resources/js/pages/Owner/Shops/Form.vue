<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppCard from '@/components/ui/AppCard.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import SelectField from '@/components/ui/SelectField.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { index, store, update } from '@/routes/owner/shops';
import type { Shop, ShopType } from '@/types';

const props = defineProps<{
    shop: Shop | null;
    shopTypes: ShopType[];
}>();

const form = useForm({
    name: props.shop?.name ?? '',
    shop_type_id: props.shop?.shop_type_id ?? null,
    address: props.shop?.address ?? '',
    phone: props.shop?.phone ?? '',
    cashback_percent: (props.shop?.cashback_percent ?? 0) as number | null,
    is_active: props.shop?.is_active ?? true,
});

/**
 * Keep the current type selectable even if the administrator has deactivated it.
 */
const typeOptions = computed(() => {
    const options = props.shopTypes.map((type) => ({
        value: type.id as number | null,
        label: type.name,
    }));
    const currentType = props.shop?.type;

    if (currentType && !options.some((o) => o.value === currentType.id)) {
        options.push({ value: currentType.id, label: currentType.name });
    }

    return options;
});

function submit(): void {
    form.submit(props.shop ? update(props.shop.id) : store());
}
</script>

<template>
    <Head :title="shop ? 'Do\'konni tahrirlash' : 'Yangi do\'kon'" />

    <PageHeader
        :title="shop ? shop.name : 'Yangi do\'kon'"
        :description="
            shop
                ? 'Do\'kon ma\'lumotlarini tahrirlash'
                : 'Do\'kon tarmog\'ingizga yangi do\'kon qo\'shing'
        "
        :back-href="index.url()"
    />

    <form class="max-w-xl" @submit.prevent="submit">
        <AppCard>
            <div class="flex flex-col gap-4">
                <TextField
                    v-model="form.name"
                    label="Do'kon nomi"
                    placeholder="Masalan: Chilonzor Market"
                    autofocus
                    :error="form.errors.name"
                />
                <SelectField
                    v-model="form.shop_type_id"
                    label="Do'kon turi"
                    placeholder="Turini tanlang"
                    :options="typeOptions"
                    :error="form.errors.shop_type_id"
                />
                <TextField
                    v-model="form.address"
                    label="Manzil"
                    placeholder="Ixtiyoriy"
                    :error="form.errors.address"
                />
                <PhoneField
                    v-model="form.phone"
                    label="Do'kon telefoni"
                    hint="Ixtiyoriy"
                    :error="form.errors.phone"
                />
                <MoneyField
                    v-model="form.cashback_percent"
                    label="Keshbek"
                    suffix="%"
                    hint="Mijoz har to'langan xariddan shuncha foizni bonus sifatida oladi (Mijozlar bo'limi bor tariflarda)"
                    :error="form.errors.cashback_percent"
                />
                <ToggleField
                    v-model="form.is_active"
                    label="Faol"
                    description="Nofaol do'kon sotuvchilari tizimga kira olmaydi"
                />
            </div>
        </AppCard>

        <div
            class="sticky bottom-(--tabbar-clearance) mt-4 flex gap-3 lg:static lg:bottom-auto"
        >
            <AppButton variant="secondary" size="lg" :href="index.url()">
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
