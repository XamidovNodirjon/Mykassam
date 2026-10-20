<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { inputClass } from '@/components/ui/input';
import type { Option, Shop, StatisticsFilters } from '@/types';

/**
 * Period chips (+ custom range) and an optional shop selector that reload the page's statistics.
 */
const props = defineProps<{
    filters: StatisticsFilters;
    periods: Option[];
    shops?: Shop[];
}>();

const state = reactive({
    period: props.filters.period,
    from: props.filters.from,
    to: props.filters.to,
    shop_id: props.filters.shop_id,
});

watch(
    () => props.filters,
    (filters) => Object.assign(state, filters),
);

function apply(): void {
    const isCustom = state.period === 'custom';

    router.reload({
        data: {
            period: state.period,
            from: isCustom ? state.from : undefined,
            to: isCustom ? state.to : undefined,
            shop_id: state.shop_id ?? undefined,
        },
        replace: true,
    });
}

function selectPeriod(period: string): void {
    state.period = period as StatisticsFilters['period'];

    if (period !== 'custom') {
        apply();
    }
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div
                class="flex gap-1 overflow-x-auto rounded-full bg-surface-muted p-1"
                role="tablist"
                aria-label="Davr"
            >
                <button
                    v-for="period in periods"
                    :key="period.value"
                    type="button"
                    role="tab"
                    :aria-selected="state.period === period.value"
                    class="flex-1 rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap transition sm:flex-none"
                    :class="
                        state.period === period.value
                            ? 'bg-surface-raised text-ink shadow-sm'
                            : 'text-ink-soft hover:text-ink'
                    "
                    @click="selectPeriod(period.value)"
                >
                    {{ period.label }}
                </button>
            </div>

            <select
                v-if="shops && shops.length > 1"
                v-model="state.shop_id"
                aria-label="Do'kon"
                :class="inputClass(false, 'sm:w-56')"
                @change="apply"
            >
                <option :value="null">Barcha do'konlar</option>
                <option v-for="shop in shops" :key="shop.id" :value="shop.id">
                    {{ shop.name }}
                </option>
            </select>
        </div>

        <div
            v-if="state.period === 'custom'"
            class="grid grid-cols-[1fr_1fr_auto] items-center gap-2"
        >
            <input
                v-model="state.from"
                type="date"
                aria-label="Boshlanish sanasi"
                :class="inputClass()"
            />
            <input
                v-model="state.to"
                type="date"
                aria-label="Tugash sanasi"
                :class="inputClass()"
            />
            <button
                type="button"
                class="h-11 rounded-full bg-linear-to-b from-brand-hi to-brand px-5 text-sm font-medium text-on-brand shadow-glow transition hover:brightness-110 active:scale-[0.97]"
                @click="apply"
            >
                Ko'rsatish
            </button>
        </div>
    </div>
</template>
