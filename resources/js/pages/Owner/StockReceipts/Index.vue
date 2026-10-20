<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import FeatureLocked from '@/components/FeatureLocked.vue';
import PageHeader from '@/components/PageHeader.vue';
import ShopTabs from '@/components/ShopTabs.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import { formatDateTime, formatMoney, formatNumber } from '@/lib/format';
import { index as shopsIndex } from '@/routes/owner/shops';
import type { Paginated, Shop, StockReceipt } from '@/types';

const props = defineProps<{
    shop: Shop;
    receipts: Paginated<StockReceipt>;
    filters: { search: string | null };
}>();

const enabled = computed(() => props.shop.features?.includes('stock_control'));
</script>

<template>
    <Head :title="`${shop.name} — kirim tarixi`" />

    <PageHeader
        :title="shop.name"
        description="Omborga kirim tarixi. Kirim qilish uchun mahsulotlar ro'yxatidagi «Kirim» tugmasini bosing."
        :back-href="shopsIndex.url()"
    />

    <ShopTabs :shop="shop" active="receipts" />

    <FeatureLocked
        v-if="!enabled"
        feature="Kam qolgan mahsulot va kirim tarixi"
        :plan-name="shop.plan?.name"
    />

    <div class="mb-4">
        <SearchInput
            :model-value="filters.search"
            placeholder="Mahsulot nomi"
        />
    </div>

    <EmptyState
        v-if="receipts.data.length === 0"
        icon="inbox"
        title="Kirimlar yo'q"
    />

    <ul
        v-else
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li
            v-for="receipt in receipts.data"
            :key="receipt.id"
            class="flex items-start justify-between gap-3 px-4 py-3"
        >
            <div class="min-w-0">
                <p class="truncate font-medium text-ink">
                    {{ receipt.product_name }}
                </p>
                <p class="text-xs text-ink-muted">
                    {{ formatDateTime(receipt.created_at) }}
                    <template v-if="receipt.user">
                        · {{ receipt.user.full_name }}
                    </template>
                </p>
            </div>
            <div class="shrink-0 text-right">
                <p class="font-semibold text-brand tabular-nums">
                    +{{ formatNumber(receipt.quantity) }} dona
                </p>
                <p class="text-xs text-ink-muted tabular-nums">
                    {{ formatMoney(receipt.cost_price) }} ×
                    {{ formatNumber(receipt.quantity) }} =
                    {{ formatMoney(receipt.total) }}
                </p>
            </div>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="receipts" />
    </div>
</template>
