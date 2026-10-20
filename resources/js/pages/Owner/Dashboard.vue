<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import PeriodFilter from '@/components/PeriodFilter.vue';
import RevenueChart from '@/components/RevenueChart.vue';
import StatCard from '@/components/StatCard.vue';
import TopProducts from '@/components/TopProducts.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppCard from '@/components/ui/AppCard.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { formatMoney, formatNumber } from '@/lib/format';
import { create as createShop } from '@/routes/owner/shops';
import { index as productsIndex } from '@/routes/owner/shops/products';
import type {
    DailyPoint,
    Option,
    Product,
    Shop,
    StatisticsFilters,
    StatisticsTotals,
    TopProduct,
    User,
} from '@/types';

const props = defineProps<{
    filters: StatisticsFilters;
    periods: Option[];
    shopOptions: Shop[];
    totals: StatisticsTotals;
    daily: DailyPoint[];
    sellers: User[];
    shops: Shop[];
    topProducts: TopProduct[];
    lowStock: Product[];
}>();

const margin = computed(() =>
    props.totals.revenue > 0
        ? Math.round(((props.totals.profit ?? 0) / props.totals.revenue) * 100)
        : 0,
);

const bestSellerRevenue = computed(() =>
    Math.max(1, ...props.sellers.map((seller) => seller.revenue ?? 0)),
);
</script>

<template>
    <Head title="Statistika" />

    <PageHeader title="Statistika" description="Tushum, foyda va sotuvchilar" />

    <EmptyState
        v-if="shopOptions.length === 0"
        icon="store"
        title="Sizda hali do'kon yo'q"
        description="Statistika ko'rish uchun avval do'kon yarating."
    >
        <AppButton :href="createShop.url()" icon="plus">
            Do'kon yaratish
        </AppButton>
    </EmptyState>

    <div v-else class="flex flex-col gap-4 lg:gap-6">
        <PeriodFilter
            :filters="filters"
            :periods="periods"
            :shops="shopOptions"
        />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard
                highlight
                label="Tushum"
                :value="formatMoney(totals.revenue)"
                icon="trend"
            />
            <StatCard
                label="Sof foyda"
                :value="formatMoney(totals.profit)"
                :hint="`Marja ${margin}%`"
                icon="tag"
            />
            <StatCard
                label="Savdolar"
                :value="formatNumber(totals.sales_count)"
                :hint="`${formatNumber(totals.items_count)} dona mahsulot`"
                icon="receipt"
            />
            <StatCard
                label="O'rtacha chek"
                :value="formatMoney(totals.average_check)"
                icon="cart"
            />
        </div>

        <AppCard
            v-if="lowStock.length"
            title="Kam qolgan mahsulotlar"
            description="Qoldig'i ogohlantirish chegarasiga tushgan, kirim qilish vaqti keldi"
            :padded="false"
        >
            <ul class="mt-3 divide-y divide-line">
                <li v-for="product in lowStock" :key="product.id">
                    <Link
                        :href="
                            productsIndex.url(product.shop_id, {
                                query: { low_stock: 1 },
                            })
                        "
                        class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-surface-muted sm:px-5"
                    >
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-medium text-ink"
                            >
                                {{ product.name }}
                            </span>
                            <span class="block text-xs text-ink-muted">
                                {{ product.shop?.name }}
                            </span>
                        </span>
                        <span
                            class="flex shrink-0 items-center gap-1 text-sm font-semibold tabular-nums"
                            :class="
                                product.stock === 0
                                    ? 'text-danger'
                                    : 'text-warning'
                            "
                        >
                            <AppIcon name="alert" :size="16" />
                            {{ formatNumber(product.stock) }} dona
                        </span>
                    </Link>
                </li>
            </ul>
        </AppCard>

        <AppCard
            v-if="daily.length > 1"
            title="Kunlik tushum"
            description="Ustunga bosing yoki kursorni olib boring"
        >
            <RevenueChart :points="daily" />
        </AppCard>

        <div class="grid gap-4 lg:grid-cols-2 lg:gap-6">
            <AppCard
                title="Sotuvchilar"
                description="Tanlangan davr bo'yicha"
                :padded="false"
            >
                <EmptyState
                    v-if="sellers.length === 0"
                    class="m-4 border-0"
                    icon="users"
                    title="Sotuvchilar yo'q"
                />
                <ol v-else class="mt-3 divide-y divide-line">
                    <li
                        v-for="(seller, index) in sellers"
                        :key="seller.id"
                        class="px-4 py-3 sm:px-5"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-surface-muted text-xs font-semibold text-ink-soft"
                            >
                                {{ index + 1 }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p
                                    class="flex items-center gap-2 truncate text-sm font-medium text-ink"
                                >
                                    {{ seller.full_name }}
                                    <StatusBadge
                                        v-if="!seller.is_active"
                                        tone="neutral"
                                    >
                                        Nofaol
                                    </StatusBadge>
                                </p>
                                <p class="truncate text-xs text-ink-muted">
                                    {{ seller.shop?.name }} ·
                                    {{ formatNumber(seller.sales_count) }} ta
                                    savdo
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-sm font-semibold text-ink tabular-nums"
                                >
                                    {{ formatMoney(seller.revenue) }}
                                </p>
                                <p class="text-xs text-brand tabular-nums">
                                    foyda {{ formatMoney(seller.profit) }}
                                </p>
                            </div>
                        </div>
                        <div
                            class="mt-2 ml-11 h-1.5 overflow-hidden rounded-full bg-surface-muted"
                            aria-hidden="true"
                        >
                            <div
                                class="h-full rounded-full bg-chart"
                                :style="{
                                    width: `${((seller.revenue ?? 0) / bestSellerRevenue) * 100}%`,
                                }"
                            />
                        </div>
                    </li>
                </ol>
            </AppCard>

            <div class="flex flex-col gap-4 lg:gap-6">
                <AppCard title="Ko'p sotilgan mahsulotlar" :padded="false">
                    <TopProducts :products="topProducts" />
                </AppCard>

                <AppCard
                    v-if="shops.length > 1"
                    title="Do'konlar kesimida"
                    :padded="false"
                >
                    <ul class="mt-3 divide-y divide-line">
                        <li
                            v-for="shop in shops"
                            :key="shop.id"
                            class="flex items-center justify-between gap-3 px-4 py-3 sm:px-5"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate text-sm font-medium text-ink"
                                >
                                    {{ shop.name }}
                                </p>
                                <p class="text-xs text-ink-muted">
                                    {{ formatNumber(shop.sales_count) }} ta
                                    savdo
                                </p>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-sm font-semibold text-ink tabular-nums"
                                >
                                    {{ formatMoney(shop.revenue) }}
                                </p>
                                <p class="text-xs text-brand tabular-nums">
                                    foyda {{ formatMoney(shop.profit) }}
                                </p>
                            </div>
                        </li>
                    </ul>
                </AppCard>
            </div>
        </div>
    </div>
</template>
