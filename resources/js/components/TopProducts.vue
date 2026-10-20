<script setup lang="ts">
import { formatMoney, formatNumber } from '@/lib/format';
import type { TopProduct } from '@/types';

defineProps<{
    products: TopProduct[];
}>();
</script>

<template>
    <p
        v-if="products.length === 0"
        class="px-4 py-8 text-center text-sm text-ink-muted sm:px-5"
    >
        Bu davrda savdo bo'lmagan.
    </p>
    <ol v-else class="mt-3 divide-y divide-line">
        <li
            v-for="(product, index) in products"
            :key="product.product_name"
            class="flex items-center gap-3 px-4 py-3 sm:px-5"
        >
            <span class="w-5 text-sm font-semibold text-ink-muted tabular-nums">
                {{ index + 1 }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-ink">
                    {{ product.product_name }}
                </p>
                <p class="text-xs text-ink-muted">
                    {{ formatNumber(product.quantity) }} dona
                </p>
            </div>
            <p class="text-sm font-semibold text-ink tabular-nums">
                {{ formatMoney(product.revenue) }}
            </p>
        </li>
    </ol>
</template>
