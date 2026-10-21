<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    formatCompactMoney,
    formatMoney,
    formatNumber,
    formatShortDate,
} from '@/lib/format';
import type { DailyPoint } from '@/types';

/**
 * Daily revenue as a single-series bar chart. Hover (or tap on phones) a bar to read the day;
 * the readout row above the plot replaces a floating tooltip so it never covers the bars.
 */
const props = defineProps<{
    points: DailyPoint[];
}>();

const selectedIndex = ref<number | null>(null);

const max = computed(() =>
    Math.max(1, ...props.points.map((point) => point.revenue)),
);

const selected = computed(() =>
    selectedIndex.value === null ? null : props.points[selectedIndex.value],
);

const totals = computed(() =>
    props.points.reduce(
        (sum, point) => ({
            revenue: sum.revenue + point.revenue,
            sales_count: sum.sales_count + point.sales_count,
        }),
        { revenue: 0, sales_count: 0 },
    ),
);

const xLabels = computed(() => {
    const count = props.points.length;

    if (count === 0) {
        return [];
    }

    const indexes = Array.from(
        new Set([0, Math.floor((count - 1) / 2), count - 1]),
    );

    return indexes.map((index) => ({
        index,
        label: formatShortDate(props.points[index].date),
    }));
});

function barHeight(point: DailyPoint): string {
    if (point.revenue === 0) {
        return '0%';
    }

    return `${Math.max(2, (point.revenue / max.value) * 100)}%`;
}
</script>

<template>
    <div>
        <div
            class="mb-4 flex min-h-12 flex-wrap items-end justify-between gap-x-4 gap-y-1"
            aria-live="polite"
        >
            <div>
                <p class="text-xs text-ink-muted">
                    {{
                        selected
                            ? formatShortDate(selected.date)
                            : 'Davr bo‘yicha jami'
                    }}
                </p>
                <p class="text-lg font-semibold text-ink tabular-nums">
                    {{ formatMoney(selected?.revenue ?? totals.revenue) }}
                </p>
            </div>
            <div class="flex gap-4 text-right text-xs text-ink-muted">
                <span>
                    Savdolar:
                    <b class="font-semibold text-ink tabular-nums">
                        {{
                            formatNumber(
                                selected?.sales_count ?? totals.sales_count,
                            )
                        }}
                    </b>
                </span>
                <span v-if="selected && selected.profit !== undefined">
                    Foyda:
                    <b class="font-semibold text-ink tabular-nums">
                        {{ formatMoney(selected.profit) }}
                    </b>
                </span>
            </div>
        </div>

        <div class="relative flex gap-2">
            <div
                class="flex h-44 w-12 shrink-0 flex-col justify-between text-right text-[10px] text-ink-muted tabular-nums"
                aria-hidden="true"
            >
                <span>{{ formatCompactMoney(max) }}</span>
                <span>{{ formatCompactMoney(Math.round(max / 2)) }}</span>
                <span>0</span>
            </div>

            <div class="relative h-44 flex-1">
                <div
                    class="pointer-events-none absolute inset-0 flex flex-col justify-between"
                    aria-hidden="true"
                >
                    <span class="border-t border-dashed border-line" />
                    <span class="border-t border-dashed border-line" />
                    <span class="border-t border-line" />
                </div>

                <div
                    class="absolute inset-0 flex items-end gap-[2px]"
                    role="list"
                    aria-label="Kunlik tushum"
                    @mouseleave="selectedIndex = null"
                >
                    <button
                        v-for="(point, index) in points"
                        :key="point.date"
                        type="button"
                        role="listitem"
                        class="group flex h-full min-w-0 flex-1 items-end focus-visible:outline-none"
                        :aria-label="`${formatShortDate(point.date)}: ${formatMoney(point.revenue)}`"
                        @mouseenter="selectedIndex = index"
                        @focus="selectedIndex = index"
                        @click="
                            selectedIndex =
                                selectedIndex === index ? null : index
                        "
                    >
                        <span
                            class="w-full origin-bottom animate-grow-up rounded-t-[4px] transition-[background-color,height] duration-500 ease-spring"
                            :class="
                                selectedIndex === null ||
                                selectedIndex === index
                                    ? 'bg-chart'
                                    : 'bg-chart-muted'
                            "
                            :style="{
                                height: barHeight(point),
                                animationDelay: `${index * 12}ms`,
                            }"
                        />
                    </button>
                </div>
            </div>
        </div>

        <div
            class="relative mt-2 ml-14 h-4 text-[10px] text-ink-muted"
            aria-hidden="true"
        >
            <span
                v-for="label in xLabels"
                :key="label.index"
                class="absolute -translate-x-1/2 whitespace-nowrap first:translate-x-0 last:-translate-x-full"
                :style="{
                    left: `${(label.index + 0.5) * (100 / points.length)}%`,
                }"
            >
                {{ label.label }}
            </span>
        </div>
    </div>
</template>
