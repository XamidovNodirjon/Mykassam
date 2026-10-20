<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import { formatMoney, formatNumber } from '@/lib/format';
import type { IconName } from '@/lib/icons';
import { index as ownersIndex } from '@/routes/admin/owners';
import { index as plansIndex } from '@/routes/admin/plans';
import { index as shopTypesIndex } from '@/routes/admin/shop-types';
import { index as shopsIndex } from '@/routes/admin/shops';
import { index as smsIndex } from '@/routes/admin/sms';
import { index as subscriptionsIndex } from '@/routes/admin/subscriptions';

defineProps<{
    overview: {
        owners: number;
        sellers: number;
        shops: number;
        products: number;
        customers: number;
        outstanding_debts: number;
        today_revenue: number;
        today_sales: number;
        month_revenue: number;
        month_profit: number;
    };
    subscriptions: {
        trial: number;
        active: number;
        expired: number;
        monthly_revenue: number;
    };
}>();

const shortcuts: {
    label: string;
    description: string;
    href: string;
    icon: IconName;
}[] = [
    {
        label: 'Tariflar',
        description: "Do'konlar obunasi va muddatlar",
        href: subscriptionsIndex.url(),
        icon: 'card',
    },
    {
        label: 'Tarif rejalari',
        description: "Narx, limit va bo'limlarni sozlash",
        href: plansIndex.url(),
        icon: 'grid',
    },
    {
        label: "Do'kon egalari",
        description: "Egalarni ko'rish va bloklash",
        href: ownersIndex.url(),
        icon: 'users',
    },
    {
        label: "Do'konlar",
        description: "Barcha do'konlar va tushumlar",
        href: shopsIndex.url(),
        icon: 'store',
    },
    {
        label: "Do'kon turlari",
        description: "Turlarni qo'shish va tahrirlash",
        href: shopTypesIndex.url(),
        icon: 'tag',
    },
    {
        label: 'SMS xabarnomalar',
        description: 'USMS balansi va yuborilgan SMSlar',
        href: smsIndex.url(),
        icon: 'chat',
    },
];
</script>

<template>
    <Head title="Admin panel" />

    <PageHeader
        title="Boshqaruv paneli"
        description="Platforma, obunalar va do'konlar bo'yicha umumiy ko'rsatkichlar"
    />

    <h2 class="mb-3 text-sm font-medium text-ink-muted">Obunalar</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <StatCard
            highlight
            label="Oylik obuna tushumi"
            :value="formatMoney(subscriptions.monthly_revenue)"
            icon="wallet"
        />
        <Link :href="subscriptionsIndex.url({ query: { status: 'active' } })">
            <StatCard
                label="To'lagan"
                :value="formatNumber(subscriptions.active)"
                icon="check-circle"
            />
        </Link>
        <Link :href="subscriptionsIndex.url({ query: { status: 'trial' } })">
            <StatCard
                label="Sinovda"
                :value="formatNumber(subscriptions.trial)"
                icon="trend"
            />
        </Link>
        <Link :href="subscriptionsIndex.url({ query: { status: 'expired' } })">
            <StatCard
                label="Muddati tugagan"
                :value="formatNumber(subscriptions.expired)"
                hint="To'lov kutilmoqda"
                icon="alert"
            />
        </Link>
    </div>

    <h2 class="mt-6 mb-3 text-sm font-medium text-ink-muted">Savdo</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <StatCard
            label="Bugungi tushum"
            :value="formatMoney(overview.today_revenue)"
            :hint="`${formatNumber(overview.today_sales)} ta savdo`"
            icon="trend"
        />
        <StatCard
            label="30 kunlik tushum"
            :value="formatMoney(overview.month_revenue)"
            icon="chart"
        />
        <StatCard
            label="30 kunlik foyda"
            :value="formatMoney(overview.month_profit)"
            icon="tag"
        />
        <StatCard
            label="Nasiyadagi qarzlar"
            :value="formatMoney(overview.outstanding_debts)"
            icon="book"
        />
    </div>

    <h2 class="mt-6 mb-3 text-sm font-medium text-ink-muted">Platforma</h2>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <StatCard
            label="Do'kon egalari"
            :value="formatNumber(overview.owners)"
            icon="user"
        />
        <StatCard
            label="Do'konlar"
            :value="formatNumber(overview.shops)"
            icon="store"
        />
        <StatCard
            label="Sotuvchilar"
            :value="formatNumber(overview.sellers)"
            icon="users"
        />
        <StatCard
            label="Mahsulotlar"
            :value="formatNumber(overview.products)"
            icon="cube"
        />
        <StatCard
            label="Mijozlar"
            :value="formatNumber(overview.customers)"
            icon="gift"
        />
    </div>

    <h2 class="mt-6 mb-3 text-sm font-medium text-ink-muted">Boshqaruv</h2>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <Link
            v-for="shortcut in shortcuts"
            :key="shortcut.href"
            :href="shortcut.href"
            class="flex items-center gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card transition hover:border-brand/40"
        >
            <span
                class="flex size-11 items-center justify-center rounded-xl bg-brand-soft text-brand-strong"
            >
                <AppIcon :name="shortcut.icon" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block font-medium text-ink">
                    {{ shortcut.label }}
                </span>
                <span class="block truncate text-sm text-ink-muted">
                    {{ shortcut.description }}
                </span>
            </span>
            <AppIcon name="chevron-right" class="text-ink-muted" />
        </Link>
    </div>
</template>
