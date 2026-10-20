<script setup lang="ts">
import AppCard from '@/components/ui/AppCard.vue';
import { formatDateTime, formatMoney, formatNumber } from '@/lib/format';
import type { Sale } from '@/types';

defineProps<{
    sale: Sale;
}>();
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-[1fr_20rem]">
        <AppCard title="Mahsulotlar" :padded="false">
            <ul class="mt-3 divide-y divide-line">
                <li
                    v-for="item in sale.items"
                    :key="item.id"
                    class="flex items-start justify-between gap-3 px-4 py-3 sm:px-5"
                >
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-ink">
                            {{ item.product_name }}
                        </p>
                        <p class="text-xs text-ink-muted tabular-nums">
                            {{ formatNumber(item.quantity) }} ×
                            {{ formatMoney(item.unit_price) }}
                            <template v-if="item.cost_price !== undefined">
                                · tan narx {{ formatMoney(item.cost_price) }}
                            </template>
                        </p>
                    </div>
                    <p class="text-sm font-semibold text-ink tabular-nums">
                        {{ formatMoney(item.total) }}
                    </p>
                </li>
            </ul>
        </AppCard>

        <AppCard title="Ma'lumot">
            <dl class="flex flex-col gap-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Sana</dt>
                    <dd class="text-right text-ink">
                        {{ formatDateTime(sale.created_at) }}
                    </dd>
                </div>
                <div v-if="sale.shop" class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Do'kon</dt>
                    <dd class="text-right text-ink">{{ sale.shop.name }}</dd>
                </div>
                <div v-if="sale.seller" class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Sotuvchi</dt>
                    <dd class="text-right text-ink">
                        {{ sale.seller.full_name }}
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-ink-muted">To'lov</dt>
                    <dd class="text-right text-ink">
                        {{ sale.payment_method_label }}
                    </dd>
                </div>
                <div v-if="sale.customer" class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Mijoz</dt>
                    <dd class="text-right text-ink">
                        {{ sale.customer.name }}
                    </dd>
                </div>
                <div v-if="sale.bonus_used" class="flex justify-between gap-3">
                    <dt class="text-ink-muted">Bonusdan to'langan</dt>
                    <dd class="text-ink tabular-nums">
                        -{{ formatMoney(sale.bonus_used) }}
                    </dd>
                </div>
                <div
                    v-if="sale.bonus_earned"
                    class="flex justify-between gap-3"
                >
                    <dt class="text-ink-muted">Keshbek</dt>
                    <dd class="text-brand tabular-nums">
                        +{{ formatMoney(sale.bonus_earned) }}
                    </dd>
                </div>
                <div class="my-1 border-t border-line" />
                <div
                    v-if="sale.total_cost !== undefined"
                    class="flex justify-between gap-3"
                >
                    <dt class="text-ink-muted">Tan narx</dt>
                    <dd class="text-ink tabular-nums">
                        {{ formatMoney(sale.total_cost) }}
                    </dd>
                </div>
                <div
                    v-if="sale.profit !== undefined"
                    class="flex justify-between gap-3"
                >
                    <dt class="text-ink-muted">Sof foyda</dt>
                    <dd
                        class="font-medium tabular-nums"
                        :class="sale.profit >= 0 ? 'text-brand' : 'text-danger'"
                    >
                        {{ formatMoney(sale.profit) }}
                    </dd>
                </div>
                <div class="flex justify-between gap-3 text-base">
                    <dt class="font-medium text-ink">Jami</dt>
                    <dd class="font-semibold text-ink tabular-nums">
                        {{ formatMoney(sale.total_amount) }}
                    </dd>
                </div>
            </dl>
        </AppCard>
    </div>
</template>
