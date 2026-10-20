<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import SelectField from '@/components/ui/SelectField.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import {
    formatMoney,
    formatNumber,
    formatPhone,
    formatShortDate,
} from '@/lib/format';
import { index as plansIndex } from '@/routes/admin/plans';
import { index, update } from '@/routes/admin/subscriptions';
import type { Paginated, Plan, Shop, SubscriptionStatus } from '@/types';

const props = defineProps<{
    shops: Paginated<Shop>;
    summary: {
        trial: number;
        active: number;
        expired: number;
        monthly_revenue: number;
    };
    plans: Plan[];
    filters: { search: string | null; status: SubscriptionStatus | null };
}>();

const tabs: { value: SubscriptionStatus | null; label: string }[] = [
    { value: null, label: 'Barchasi' },
    { value: 'trial', label: 'Sinov' },
    { value: 'active', label: 'Faol' },
    { value: 'expired', label: 'Tugagan' },
];

const statusTone = {
    trial: 'warning',
    active: 'success',
    expired: 'danger',
} as const;

const periodOptions = [
    { value: 0, label: "Muddatni o'zgartirmaslik" },
    { value: 1, label: '1 oy' },
    { value: 3, label: '3 oy' },
    { value: 6, label: '6 oy' },
    { value: 12, label: '12 oy' },
];

const planOptions = computed(() =>
    props.plans.map((plan) => ({
        value: plan.id,
        label: `${plan.name} — ${formatMoney(plan.price)} / oy`,
    })),
);

const editing = ref<Shop | null>(null);
const modalOpen = ref(false);

const form = useForm<{ plan_id: number | null; months: number }>({
    plan_id: null,
    months: 1,
});

function daysLeft(date: string | null | undefined): number {
    if (!date) {
        return 0;
    }

    return Math.ceil((new Date(date).getTime() - Date.now()) / 86_400_000);
}

function openEdit(shop: Shop): void {
    editing.value = shop;
    form.clearErrors();
    form.plan_id = shop.plan?.id ?? props.plans[0]?.id ?? null;
    form.months = 1;
    modalOpen.value = true;
}

function submit(): void {
    if (!editing.value) {
        return;
    }

    form.submit(update(editing.value.id), {
        preserveScroll: true,
        onSuccess: () => (modalOpen.value = false),
    });
}
</script>

<template>
    <Head title="Tariflar" />

    <PageHeader
        title="Tariflar"
        description="Do'konlar obunasi: tarifni almashtirish va muddatni uzaytirish"
    >
        <template #actions>
            <AppButton variant="secondary" icon="grid" :href="plansIndex.url()">
                Tarif rejalari
            </AppButton>
        </template>
    </PageHeader>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <StatCard
            highlight
            label="Oylik tushum"
            :value="formatMoney(summary.monthly_revenue)"
            hint="Faol to'langan obunalar"
            icon="wallet"
        />
        <StatCard
            label="Faol"
            :value="formatNumber(summary.active)"
            icon="check-circle"
        />
        <StatCard
            label="Sinovda"
            :value="formatNumber(summary.trial)"
            icon="trend"
        />
        <StatCard
            label="Muddati tugagan"
            :value="formatNumber(summary.expired)"
            icon="alert"
        />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <nav
            class="flex gap-1 overflow-x-auto rounded-full bg-surface-muted p-1"
            aria-label="Holat bo'yicha"
        >
            <Link
                v-for="tab in tabs"
                :key="tab.label"
                :href="
                    index({
                        query: {
                            status: tab.value ?? undefined,
                            search: filters.search ?? undefined,
                        },
                    })
                "
                preserve-scroll
                class="rounded-full px-3 py-1.5 text-sm font-medium whitespace-nowrap transition"
                :class="
                    filters.status === tab.value
                        ? 'bg-surface-raised text-ink shadow-sm'
                        : 'text-ink-muted hover:text-ink'
                "
            >
                {{ tab.label }}
            </Link>
        </nav>
        <div class="flex-1">
            <SearchInput
                :model-value="filters.search"
                placeholder="Do'kon nomi bo'yicha qidirish"
            />
        </div>
    </div>

    <EmptyState
        v-if="shops.data.length === 0"
        icon="card"
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
                    <p class="truncate text-sm text-ink-muted">
                        {{ shop.owner?.full_name }} ·
                        {{ formatPhone(shop.owner?.phone) }}
                    </p>
                </div>
                <StatusBadge
                    v-if="shop.subscription_status"
                    :tone="statusTone[shop.subscription_status]"
                >
                    {{ shop.subscription_status_label }}
                </StatusBadge>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0 text-sm">
                    <p class="font-semibold text-ink">{{ shop.plan?.name }}</p>
                    <p class="text-ink-muted">
                        <template v-if="shop.subscription_ends_at">
                            {{ formatShortDate(shop.subscription_ends_at) }}
                            gacha
                            <template
                                v-if="daysLeft(shop.subscription_ends_at) > 0"
                            >
                                · {{ daysLeft(shop.subscription_ends_at) }} kun
                                qoldi
                            </template>
                        </template>
                        <template v-else>Muddat belgilanmagan</template>
                    </p>
                </div>
                <AppButton
                    size="sm"
                    :variant="
                        shop.subscription_status === 'expired'
                            ? 'primary'
                            : 'secondary'
                    "
                    @click="openEdit(shop)"
                >
                    {{
                        shop.subscription_status === 'expired'
                            ? 'Faollashtirish'
                            : "O'zgartirish"
                    }}
                </AppButton>
            </div>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="shops" />
    </div>

    <AppModal v-model:open="modalOpen" :title="editing?.name ?? 'Tarif'">
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <SelectField
                v-model="form.plan_id"
                label="Tarif"
                :options="planOptions"
                :error="form.errors.plan_id"
            />
            <SelectField
                v-model="form.months"
                label="To'langan muddat"
                hint="Muddat hali tugamagan bo'lsa, uning oxiriga qo'shiladi"
                :options="periodOptions"
                :error="form.errors.months"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
