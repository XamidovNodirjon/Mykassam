<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import type { IconName } from '@/lib/icons';
import { index as customersIndex } from '@/routes/owner/shops/customers';
import { index as debtsIndex } from '@/routes/owner/shops/debts';
import { index as productsIndex } from '@/routes/owner/shops/products';
import { index as sellersIndex } from '@/routes/owner/shops/sellers';
import { index as receiptsIndex } from '@/routes/owner/shops/stock-receipts';
import type { PlanFeature, Shop } from '@/types';

export type ShopTab =
    | 'products'
    | 'sellers'
    | 'customers'
    | 'debts'
    | 'receipts';

const props = defineProps<{
    shop: Shop;
    active: ShopTab;
}>();

/**
 * Tabs of modules outside the plan stay visible with a lock, so the owner sees what an upgrade adds.
 */
const tabs: {
    value: ShopTab;
    label: string;
    icon: IconName;
    href: (shopId: number) => string;
    feature?: PlanFeature;
}[] = [
    {
        value: 'products',
        label: 'Mahsulotlar',
        icon: 'cube',
        href: productsIndex.url,
    },
    {
        value: 'sellers',
        label: 'Sotuvchilar',
        icon: 'users',
        href: sellersIndex.url,
    },
    {
        value: 'customers',
        label: 'Mijozlar',
        icon: 'gift',
        href: customersIndex.url,
        feature: 'customers',
    },
    {
        value: 'debts',
        label: 'Qarz daftari',
        icon: 'book',
        href: debtsIndex.url,
        feature: 'debts',
    },
    {
        value: 'receipts',
        label: 'Kirim',
        icon: 'inbox',
        href: receiptsIndex.url,
        feature: 'stock_control',
    },
];

function isLocked(feature?: PlanFeature): boolean {
    return feature !== undefined && !props.shop.features?.includes(feature);
}
</script>

<template>
    <nav
        class="-mx-4 mb-4 overflow-x-auto px-4 sm:mx-0 sm:px-0"
        aria-label="Do'kon bo'limlari"
    >
        <div class="inline-flex gap-1 rounded-full bg-surface-muted p-1">
            <Link
                v-for="tab in tabs"
                :key="tab.value"
                :href="tab.href(shop.id)"
                class="flex items-center gap-2 rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap transition"
                :class="
                    active === tab.value
                        ? 'bg-surface-raised text-ink shadow-sm'
                        : 'text-ink-soft hover:text-ink'
                "
                :aria-current="active === tab.value ? 'page' : undefined"
            >
                <AppIcon :name="tab.icon" :size="18" />
                {{ tab.label }}
                <AppIcon
                    v-if="isLocked(tab.feature)"
                    name="lock"
                    :size="14"
                    class="text-ink-muted"
                />
            </Link>
        </div>
    </nav>
</template>
