<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppCard from '@/components/ui/AppCard.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatMoney } from '@/lib/format';
import { destroy, index, store, update } from '@/routes/owner/shops/products';
import type { Product, Shop } from '@/types';

const props = defineProps<{
    shop: Shop;
    product: Product | null;
}>();

const confirm = useConfirm();

const form = useForm({
    name: props.product?.name ?? '',
    barcode: props.product?.barcode ?? '',
    cost_price: props.product?.cost_price ?? null,
    min_price: props.product?.min_price ?? null,
    price: props.product?.price ?? null,
    stock: (props.product?.stock ?? 0) as number | null,
    min_stock: (props.product?.min_stock ?? 5) as number | null,
    is_active: props.product?.is_active ?? true,
});

const backUrl = computed(() => index.url(props.shop.id));

const minPriceBelowCost = computed(
    () =>
        form.cost_price !== null &&
        form.min_price !== null &&
        form.min_price < form.cost_price,
);

const profitPerUnit = computed(() =>
    form.price !== null && form.cost_price !== null
        ? form.price - form.cost_price
        : null,
);

function submit(): void {
    form.submit(
        props.product
            ? update({ shop: props.shop.id, product: props.product.id })
            : store(props.shop.id),
    );
}

async function remove(): Promise<void> {
    if (!props.product) {
        return;
    }

    const confirmed = await confirm({
        title: "Mahsulotni o'chirish",
        message: `"${props.product.name}" o'chiriladi.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(
            destroy({ shop: props.shop.id, product: props.product.id }),
        );
    }
}
</script>

<template>
    <Head :title="product ? product.name : 'Yangi mahsulot'" />

    <PageHeader
        :title="product ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot'"
        :description="shop.name"
        :back-href="backUrl"
    >
        <template v-if="product" #actions>
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

    <form class="max-w-2xl" @submit.prevent="submit">
        <div class="flex flex-col gap-4">
            <AppCard title="Asosiy">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <TextField
                            v-model="form.name"
                            label="Nomi"
                            placeholder="Masalan: Coca-Cola 1.5L"
                            autofocus
                            :error="form.errors.name"
                        />
                    </div>
                    <TextField
                        v-model="form.barcode"
                        label="Shtrix-kod"
                        inputmode="numeric"
                        placeholder="Ixtiyoriy"
                        :error="form.errors.barcode"
                    />
                    <MoneyField
                        v-model="form.stock"
                        label="Omborda"
                        suffix="dona"
                        :error="form.errors.stock"
                    />
                    <MoneyField
                        v-model="form.min_stock"
                        label="Kam qolganda ogohlantirish"
                        suffix="dona"
                        hint="Qoldiq shu songa tushsa, mahsulot «kam qolgan» bo'ladi"
                        :error="form.errors.min_stock"
                    />
                </div>
            </AppCard>

            <AppCard
                title="Narxlar"
                description="Sotuvchi minimal narxdan arzon sota olmaydi"
            >
                <div class="grid gap-4 sm:grid-cols-3">
                    <MoneyField
                        v-model="form.cost_price"
                        label="Tan narxi"
                        :error="form.errors.cost_price"
                    />
                    <MoneyField
                        v-model="form.min_price"
                        label="Minimal narx"
                        :error="form.errors.min_price"
                    />
                    <MoneyField
                        v-model="form.price"
                        label="Sotuv narxi"
                        :error="form.errors.price"
                    />
                </div>

                <div class="mt-4 flex flex-col gap-2 text-sm">
                    <p
                        v-if="minPriceBelowCost"
                        class="flex items-center gap-2 rounded-xl bg-warning-soft px-3 py-2 text-warning"
                    >
                        <AppIcon name="alert" :size="18" />
                        Minimal narx tan narxidan past — zarar bilan sotilishi
                        mumkin.
                    </p>
                    <p v-if="profitPerUnit !== null" class="text-ink-muted">
                        Standart narxda bir donadan foyda:
                        <b
                            class="font-semibold"
                            :class="
                                profitPerUnit >= 0
                                    ? 'text-brand'
                                    : 'text-danger'
                            "
                        >
                            {{ formatMoney(profitPerUnit) }}
                        </b>
                    </p>
                </div>
            </AppCard>

            <ToggleField
                v-model="form.is_active"
                label="Sotuvda"
                description="O'chirilsa sotuvchilar bu mahsulotni ko'rmaydi"
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
