<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';
import PeriodFilter from '@/components/PeriodFilter.vue';
import RevenueChart from '@/components/RevenueChart.vue';
import SaleList from '@/components/SaleList.vue';
import StatCard from '@/components/StatCard.vue';
import TopProducts from '@/components/TopProducts.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppCard from '@/components/ui/AppCard.vue';
import { formatMoney, formatNumber } from '@/lib/format';
import { create, index, show } from '@/routes/seller/sales';
import type {
    DailyPoint,
    Option,
    Sale,
    StatisticsFilters,
    StatisticsTotals,
    TopProduct,
} from '@/types';

defineProps<{
    filters: StatisticsFilters;
    periods: Option[];
    totals: StatisticsTotals;
    daily: DailyPoint[];
    topProducts: TopProduct[];
    latestSales: Sale[];
}>();
</script>

<template>
    <Head title="Mening statistikam" />

    <PageHeader
        title="Mening statistikam"
        description="Faqat sizning savdolaringiz"
    >
        <template #actions>
            <AppButton :href="create.url()" icon="cart">Yangi savdo</AppButton>
        </template>
    </PageHeader>

    <div class="flex flex-col gap-4 lg:gap-6">
        <PeriodFilter :filters="filters" :periods="periods" />

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard
                highlight
                label="Tushum"
                :value="formatMoney(totals.revenue)"
                icon="trend"
            />
            <StatCard
                label="Savdolar"
                :value="formatNumber(totals.sales_count)"
                icon="receipt"
            />
            <StatCard
                label="Sotilgan"
                :value="`${formatNumber(totals.items_count)} dona`"
                icon="cube"
            />
            <StatCard
                label="O'rtacha chek"
                :value="formatMoney(totals.average_check)"
                icon="cart"
            />
        </div>

        <AppCard v-if="daily.length > 1" title="Kunlik tushum">
            <RevenueChart :points="daily" />
        </AppCard>

        <div class="grid gap-4 lg:grid-cols-2 lg:gap-6">
            <AppCard title="Ko'p sotganlarim" :padded="false">
                <TopProducts :products="topProducts" />
            </AppCard>

            <AppCard title="Oxirgi savdolar" :padded="false">
                <template #actions>
                    <Link
                        :href="index.url()"
                        class="text-sm font-medium text-brand hover:text-brand-strong"
                    >
                        Barchasi
                    </Link>
                </template>
                <p
                    v-if="latestSales.length === 0"
                    class="px-4 py-8 text-center text-sm text-ink-muted sm:px-5"
                >
                    Hali savdo yo'q.
                </p>
                <div v-else class="p-3">
                    <SaleList
                        :sales="latestSales"
                        :show-url="(sale) => show.url(sale.id)"
                    />
                </div>
            </AppCard>
        </div>
    </div>
</template>
