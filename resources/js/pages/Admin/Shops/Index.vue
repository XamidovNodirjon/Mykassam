<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatMoney, formatPhone } from '@/lib/format';
import { status } from '@/routes/admin/shops';
import type { Paginated, Shop } from '@/types';

defineProps<{
    shops: Paginated<Shop>;
    filters: { search: string | null };
}>();

const confirm = useConfirm();

async function toggle(shop: Shop): Promise<void> {
    const confirmed = await confirm({
        title: shop.is_active ? "Do'konni bloklash" : "Do'konni faollashtirish",
        message: shop.is_active
            ? `"${shop.name}" sotuvchilari tizimga kira olmaydi.`
            : `"${shop.name}" yana ishlay oladi.`,
        confirmText: shop.is_active ? 'Bloklash' : 'Faollashtirish',
        danger: shop.is_active,
    });

    if (confirmed) {
        router.visit(status(shop.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Do'konlar" />

    <PageHeader title="Do'konlar" :description="`Jami: ${shops.meta.total}`" />

    <div class="mb-4">
        <SearchInput
            :model-value="filters.search"
            placeholder="Do'kon nomi bo'yicha qidirish"
        />
    </div>

    <EmptyState
        v-if="shops.data.length === 0"
        icon="store"
        title="Do'konlar topilmadi"
    />

    <ul v-else class="grid gap-3 md:grid-cols-2">
        <li
            v-for="shop in shops.data"
            :key="shop.id"
            class="flex flex-col gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-medium text-ink">{{ shop.name }}</p>
                    <p class="text-sm text-ink-muted">
                        {{ shop.type?.name }}
                    </p>
                </div>
                <StatusBadge :tone="shop.is_active ? 'success' : 'danger'">
                    {{ shop.is_active ? 'Faol' : 'Bloklangan' }}
                </StatusBadge>
            </div>
            <dl class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-xl bg-surface-muted px-2 py-2">
                    <dt class="text-[11px] text-ink-muted">Mahsulot</dt>
                    <dd class="text-sm font-semibold text-ink">
                        {{ shop.products_count }}
                    </dd>
                </div>
                <div class="rounded-xl bg-surface-muted px-2 py-2">
                    <dt class="text-[11px] text-ink-muted">Sotuvchi</dt>
                    <dd class="text-sm font-semibold text-ink">
                        {{ shop.sellers_count }}
                    </dd>
                </div>
                <div class="rounded-xl bg-surface-muted px-2 py-2">
                    <dt class="text-[11px] text-ink-muted">Tushum</dt>
                    <dd class="truncate text-sm font-semibold text-ink">
                        {{ formatMoney(shop.revenue) }}
                    </dd>
                </div>
            </dl>
            <div class="flex items-center justify-between gap-3">
                <p class="min-w-0 truncate text-sm text-ink-soft">
                    {{ shop.owner?.full_name }}
                    <span class="text-ink-muted">
                        · {{ formatPhone(shop.owner?.phone) }}
                    </span>
                </p>
                <AppButton
                    size="sm"
                    :variant="shop.is_active ? 'secondary' : 'primary'"
                    @click="toggle(shop)"
                >
                    {{ shop.is_active ? 'Bloklash' : 'Faollashtirish' }}
                </AppButton>
            </div>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="shops" />
    </div>
</template>
