<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import SaleList from '@/components/SaleList.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { inputClass } from '@/components/ui/input';
import { show } from '@/routes/owner/sales';
import type { Paginated, Sale, Shop, User } from '@/types';

const props = defineProps<{
    sales: Paginated<Sale>;
    filters: {
        shop_id: number | null;
        seller_id: number | null;
        from: string | null;
        to: string | null;
    };
    shopOptions: Shop[];
    sellerOptions: User[];
}>();

const state = reactive({ ...props.filters });

const visibleSellers = computed(() =>
    state.shop_id
        ? props.sellerOptions.filter((s) => s.shop_id === state.shop_id)
        : props.sellerOptions,
);

const hasFilters = computed(() =>
    Object.values(props.filters).some((value) => value !== null),
);

function apply(): void {
    if (
        state.seller_id &&
        !visibleSellers.value.some((s) => s.id === state.seller_id)
    ) {
        state.seller_id = null;
    }

    router.reload({
        data: {
            shop_id: state.shop_id ?? undefined,
            seller_id: state.seller_id ?? undefined,
            from: state.from || undefined,
            to: state.to || undefined,
            page: undefined,
        },
        replace: true,
    });
}

function reset(): void {
    Object.assign(state, {
        shop_id: null,
        seller_id: null,
        from: null,
        to: null,
    });
    apply();
}
</script>

<template>
    <Head title="Savdolar" />

    <PageHeader
        title="Savdolar"
        :description="`${sales.meta.total} ta savdo`"
    />

    <div
        class="mb-4 grid grid-cols-2 gap-2 lg:grid-cols-[1fr_1fr_10rem_10rem_auto]"
    >
        <select
            v-if="shopOptions.length > 1"
            v-model="state.shop_id"
            aria-label="Do'kon"
            :class="inputClass()"
            @change="apply"
        >
            <option :value="null">Barcha do'konlar</option>
            <option v-for="shop in shopOptions" :key="shop.id" :value="shop.id">
                {{ shop.name }}
            </option>
        </select>
        <select
            v-model="state.seller_id"
            aria-label="Sotuvchi"
            :class="[
                inputClass(),
                { 'col-span-2 lg:col-span-1': shopOptions.length <= 1 },
            ]"
            @change="apply"
        >
            <option :value="null">Barcha sotuvchilar</option>
            <option
                v-for="seller in visibleSellers"
                :key="seller.id"
                :value="seller.id"
            >
                {{ seller.full_name }}
            </option>
        </select>
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
        <button
            v-if="hasFilters"
            type="button"
            class="col-span-2 h-11 rounded-xl px-4 text-sm font-medium text-ink-soft hover:bg-surface-muted lg:col-span-1"
            @click="reset"
        >
            Tozalash
        </button>
    </div>

    <EmptyState
        v-if="sales.data.length === 0"
        icon="receipt"
        title="Savdolar topilmadi"
        :description="
            hasFilters
                ? 'Filtrlarni o‘zgartirib ko‘ring.'
                : 'Sotuvchilar sotgan savdolar shu yerda ko‘rinadi.'
        "
    />

    <SaleList
        v-else
        :sales="sales.data"
        :show-url="(sale) => show.url(sale.id)"
        show-seller
        :show-shop="shopOptions.length > 1"
    />

    <div class="mt-4">
        <AppPagination :paginator="sales" />
    </div>
</template>
