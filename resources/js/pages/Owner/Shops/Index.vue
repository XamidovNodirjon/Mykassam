<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatPhone, formatShortDate } from '@/lib/format';
import { create, destroy, edit } from '@/routes/owner/shops';
import { index as productsIndex } from '@/routes/owner/shops/products';
import { index as sellersIndex } from '@/routes/owner/shops/sellers';
import type { Shop } from '@/types';

defineProps<{
    shops: Shop[];
}>();

const confirm = useConfirm();

async function remove(shop: Shop): Promise<void> {
    const confirmed = await confirm({
        title: "Do'konni o'chirish",
        message: `"${shop.name}" va uning barcha mahsulotlari, sotuvchilari o'chiriladi.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy(shop.id));
    }
}
</script>

<template>
    <Head title="Do'konlar" />

    <PageHeader title="Do'konlarim" :description="`${shops.length} ta do'kon`">
        <template #actions>
            <AppButton :href="create.url()" icon="plus">Yangi do'kon</AppButton>
        </template>
    </PageHeader>

    <EmptyState
        v-if="shops.length === 0"
        icon="store"
        title="Hali do'kon yo'q"
        description="Birinchi do'koningizni yarating, so'ng mahsulot va sotuvchilarni qo'shing."
    >
        <AppButton :href="create.url()" icon="plus">Do'kon yaratish</AppButton>
    </EmptyState>

    <ul v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <li
            v-for="shop in shops"
            :key="shop.id"
            class="flex flex-col rounded-2xl border border-line bg-surface shadow-card"
        >
            <div class="flex items-start gap-3 p-4">
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-brand-strong"
                >
                    <AppIcon name="store" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate font-semibold text-ink">
                        {{ shop.name }}
                    </p>
                    <p class="truncate text-sm text-ink-muted">
                        {{ shop.type?.name }}
                        <template v-if="shop.address">
                            · {{ shop.address }}
                        </template>
                    </p>
                    <p v-if="shop.phone" class="text-sm text-ink-muted">
                        {{ formatPhone(shop.phone) }}
                    </p>
                    <p
                        v-if="shop.plan"
                        class="mt-1 text-xs font-medium"
                        :class="
                            shop.subscription_status === 'expired'
                                ? 'text-danger'
                                : 'text-ink-muted'
                        "
                    >
                        {{ shop.plan.name }} ·
                        {{ shop.subscription_status_label }}
                        <template v-if="shop.subscription_ends_at">
                            · {{ formatShortDate(shop.subscription_ends_at) }}
                            gacha
                        </template>
                    </p>
                </div>
                <StatusBadge :tone="shop.is_active ? 'success' : 'neutral'">
                    {{ shop.is_active ? 'Faol' : 'Nofaol' }}
                </StatusBadge>
            </div>

            <div class="grid grid-cols-2 gap-2 px-4">
                <Link
                    :href="productsIndex.url(shop.id)"
                    class="flex items-center justify-between rounded-xl bg-surface-muted px-3 py-2.5 transition hover:bg-brand-soft"
                >
                    <span class="flex items-center gap-2 text-sm text-ink-soft">
                        <AppIcon name="cube" :size="18" />
                        Mahsulotlar
                    </span>
                    <span class="text-sm font-semibold text-ink">
                        {{ shop.products_count }}
                    </span>
                </Link>
                <Link
                    :href="sellersIndex.url(shop.id)"
                    class="flex items-center justify-between rounded-xl bg-surface-muted px-3 py-2.5 transition hover:bg-brand-soft"
                >
                    <span class="flex items-center gap-2 text-sm text-ink-soft">
                        <AppIcon name="users" :size="18" />
                        Sotuvchilar
                    </span>
                    <span class="text-sm font-semibold text-ink">
                        {{ shop.sellers_count }}
                    </span>
                </Link>
            </div>

            <div class="mt-auto flex justify-end gap-1 p-2 pt-3">
                <AppButton
                    variant="ghost"
                    size="sm"
                    icon="pencil"
                    :href="edit.url(shop.id)"
                >
                    Tahrirlash
                </AppButton>
                <AppButton
                    variant="ghost"
                    size="sm"
                    icon="trash"
                    class="hover:text-danger"
                    @click="remove(shop)"
                >
                    O'chirish
                </AppButton>
            </div>
        </li>
    </ul>
</template>
