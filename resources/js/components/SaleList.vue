<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import { formatDateTime, formatMoney } from '@/lib/format';
import type { Sale } from '@/types';

/**
 * Compact sale rows used by owner and seller sale lists.
 */
withDefaults(
    defineProps<{
        sales: Sale[];
        showUrl: (sale: Sale) => string;
        showSeller?: boolean;
        showShop?: boolean;
    }>(),
    { showSeller: false, showShop: false },
);
</script>

<template>
    <ul
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li v-for="sale in sales" :key="sale.id">
            <Link
                :href="showUrl(sale)"
                class="flex items-center gap-3 px-4 py-3 transition hover:bg-surface-muted"
            >
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-surface-muted text-ink-soft"
                >
                    <AppIcon name="receipt" :size="20" />
                </span>
                <div class="min-w-0 flex-1">
                    <p
                        class="flex items-center gap-2 text-sm font-medium text-ink"
                    >
                        <span>#{{ sale.id }}</span>
                        <span class="text-ink-muted">·</span>
                        <span class="truncate text-ink-soft">
                            {{ sale.payment_method_label }},
                            {{ sale.items_count }} dona
                        </span>
                    </p>
                    <p class="truncate text-xs text-ink-muted">
                        {{ formatDateTime(sale.created_at) }}
                        <template v-if="showSeller && sale.seller">
                            · {{ sale.seller.full_name }}
                        </template>
                        <template v-if="showShop && sale.shop">
                            · {{ sale.shop.name }}
                        </template>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-ink tabular-nums">
                        {{ formatMoney(sale.total_amount) }}
                    </p>
                    <p
                        v-if="sale.profit !== undefined"
                        class="text-xs tabular-nums"
                        :class="sale.profit >= 0 ? 'text-brand' : 'text-danger'"
                    >
                        {{ sale.profit >= 0 ? '+' : ''
                        }}{{ formatMoney(sale.profit) }}
                    </p>
                </div>
            </Link>
        </li>
    </ul>
</template>
