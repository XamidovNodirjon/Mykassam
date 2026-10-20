<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import SaleList from '@/components/SaleList.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { inputClass } from '@/components/ui/input';
import { show } from '@/routes/seller/sales';
import type { Paginated, Sale } from '@/types';

const props = defineProps<{
    sales: Paginated<Sale>;
    filters: { from: string | null; to: string | null };
}>();

const state = reactive({ from: props.filters.from, to: props.filters.to });

function apply(): void {
    router.reload({
        data: {
            from: state.from || undefined,
            to: state.to || undefined,
            page: undefined,
        },
        replace: true,
    });
}
</script>

<template>
    <Head title="Savdolar tarixi" />

    <PageHeader
        title="Savdolar tarixi"
        :description="`${sales.meta.total} ta savdo`"
    />

    <div class="mb-4 grid grid-cols-2 gap-2 sm:max-w-md">
        <input
            v-model="state.from"
            type="date"
            aria-label="Boshlanish sanasi"
            :class="inputClass()"
            @change="apply"
        />
        <input
            v-model="state.to"
            type="date"
            aria-label="Tugash sanasi"
            :class="inputClass()"
            @change="apply"
        />
    </div>

    <EmptyState
        v-if="sales.data.length === 0"
        icon="receipt"
        title="Savdolar yo'q"
    />

    <SaleList
        v-else
        :sales="sales.data"
        :show-url="(sale) => show.url(sale.id)"
    />

    <div class="mt-4">
        <AppPagination :paginator="sales" />
    </div>
</template>
