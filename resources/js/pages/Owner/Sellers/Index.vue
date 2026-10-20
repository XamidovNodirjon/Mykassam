<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import CredentialsCard from '@/components/CredentialsCard.vue';
import PageHeader from '@/components/PageHeader.vue';
import ShopTabs from '@/components/ShopTabs.vue';
import AppButton from '@/components/ui/AppButton.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { formatMoney, formatNumber, formatPhone } from '@/lib/format';
import { index as shopsIndex } from '@/routes/owner/shops';
import { create, edit } from '@/routes/owner/shops/sellers';
import type { Credentials, Shop, User } from '@/types';

defineProps<{
    shop: Shop;
    sellers: User[];
}>();

const page = usePage();
const credentials = ref<Credentials | null>(page.flash.credentials ?? null);

watch(
    () => page.flash.credentials,
    (value) => {
        if (value) {
            credentials.value = value;
        }
    },
);
</script>

<template>
    <Head :title="`${shop.name} — sotuvchilar`" />

    <PageHeader
        :title="shop.name"
        :description="`${sellers.length} ta sotuvchi`"
        :back-href="shopsIndex.url()"
    >
        <template #actions>
            <AppButton :href="create.url(shop.id)" icon="plus">
                Sotuvchi
            </AppButton>
        </template>
    </PageHeader>

    <ShopTabs :shop="shop" active="sellers" />

    <CredentialsCard
        v-if="credentials"
        class="mb-4"
        :credentials="credentials"
        @close="credentials = null"
    />

    <EmptyState
        v-if="sellers.length === 0"
        icon="users"
        title="Sotuvchilar yo'q"
        description="Sotuvchi qo'shing — login va parol avtomatik yaratiladi."
    >
        <AppButton :href="create.url(shop.id)" icon="plus">
            Sotuvchi qo'shish
        </AppButton>
    </EmptyState>

    <ul v-else class="grid gap-3 md:grid-cols-2">
        <li v-for="seller in sellers" :key="seller.id">
            <Link
                :href="edit.url({ shop: shop.id, seller: seller.id })"
                class="flex items-center gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card transition hover:border-brand/40"
            >
                <span
                    class="flex size-11 shrink-0 items-center justify-center rounded-full bg-surface-muted font-semibold text-ink-soft"
                >
                    {{ seller.first_name.charAt(0) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-2 font-medium text-ink">
                        <span class="truncate">{{ seller.full_name }}</span>
                        <StatusBadge v-if="!seller.is_active" tone="neutral">
                            Nofaol
                        </StatusBadge>
                    </p>
                    <p class="truncate text-sm text-ink-muted">
                        <span class="font-mono">{{ seller.login }}</span>
                        · {{ formatPhone(seller.phone) }}
                    </p>
                    <p class="mt-1 text-xs text-ink-muted">
                        {{ formatNumber(seller.sales_count) }} ta savdo ·
                        {{ formatMoney(seller.revenue) }}
                    </p>
                </div>
                <AppIcon name="chevron-right" class="text-ink-muted" />
            </Link>
        </li>
    </ul>
</template>
