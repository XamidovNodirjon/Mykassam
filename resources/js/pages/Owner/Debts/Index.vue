<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FeatureLocked from '@/components/FeatureLocked.vue';
import PageHeader from '@/components/PageHeader.vue';
import ShopTabs from '@/components/ShopTabs.vue';
import StatCard from '@/components/StatCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import SelectField from '@/components/ui/SelectField.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import TextField from '@/components/ui/TextField.vue';
import {
    formatMoney,
    formatNumber,
    formatPhone,
    formatShortDate,
} from '@/lib/format';
import { index as shopsIndex } from '@/routes/owner/shops';
import { index, pay, store } from '@/routes/owner/shops/debts';
import type { Customer, Debt, DebtStatus, Paginated, Shop } from '@/types';

const props = defineProps<{
    shop: Shop;
    debts: Paginated<Debt>;
    summary: { outstanding: number; open: number; overdue: number };
    customers?: Customer[];
    filters: { search: string | null; status: DebtStatus | null };
}>();

const enabled = computed(() => props.shop.features?.includes('debts'));

const tabs: { value: DebtStatus | null; label: string }[] = [
    { value: null, label: 'Barchasi' },
    { value: 'open', label: "To'lanmagan" },
    { value: 'overdue', label: "Muddati o'tgan" },
    { value: 'paid', label: "To'langan" },
];

const statusTone = {
    open: 'warning',
    overdue: 'danger',
    paid: 'success',
} as const;

const today = new Date().toISOString().slice(0, 10);

const createOpen = ref(false);
const createForm = useForm({
    customer_id: null as number | null,
    amount: null as number | null,
    due_date: today,
    note: '',
});

const customerOptions = computed(() =>
    (props.customers ?? []).map((customer) => ({
        value: customer.id,
        label: customer.phone
            ? `${customer.name} · ${formatPhone(customer.phone)}`
            : customer.name,
    })),
);

function openCreate(): void {
    createForm.reset();
    createForm.clearErrors();
    createOpen.value = true;

    if (!props.customers) {
        router.reload({ only: ['customers'] });
    }
}

function submitCreate(): void {
    createForm.submit(store(props.shop.id), {
        preserveScroll: true,
        onSuccess: () => (createOpen.value = false),
    });
}

const paying = ref<Debt | null>(null);
const payOpen = ref(false);
const payForm = useForm({ amount: null as number | null });

function openPay(debt: Debt): void {
    paying.value = debt;
    payForm.clearErrors();
    payForm.amount = debt.remaining;
    payOpen.value = true;
}

function submitPay(): void {
    if (!paying.value) {
        return;
    }

    payForm.submit(pay({ shop: props.shop.id, debt: paying.value.id }), {
        preserveScroll: true,
        onSuccess: () => (payOpen.value = false),
    });
}
</script>

<template>
    <Head :title="`${shop.name} — qarz daftari`" />

    <PageHeader
        :title="shop.name"
        description="Qarz daftari: to'lov kuni mijozga SMS eslatma boradi"
        :back-href="shopsIndex.url()"
    >
        <template v-if="enabled" #actions>
            <AppButton icon="plus" @click="openCreate">Qarz yozish</AppButton>
        </template>
    </PageHeader>

    <ShopTabs :shop="shop" active="debts" />

    <FeatureLocked
        v-if="!enabled"
        feature="Qarz daftari va SMS eslatma"
        :plan-name="shop.plan?.name"
    />

    <div class="mb-6 grid grid-cols-3 gap-3">
        <StatCard
            highlight
            label="Jami qarz"
            :value="formatMoney(summary.outstanding)"
            icon="wallet"
        />
        <StatCard
            label="To'lanmagan"
            :value="formatNumber(summary.open)"
            icon="book"
        />
        <StatCard
            label="Muddati o'tgan"
            :value="formatNumber(summary.overdue)"
            icon="alert"
        />
    </div>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <nav
            class="flex gap-1 overflow-x-auto rounded-full bg-surface-muted p-1"
            aria-label="Holat bo'yicha"
        >
            <Link
                v-for="tab in tabs"
                :key="tab.label"
                :href="
                    index(shop.id, {
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
                placeholder="Mijoz ismi yoki telefoni"
            />
        </div>
    </div>

    <EmptyState
        v-if="debts.data.length === 0"
        icon="book"
        title="Qarzlar yo'q"
        description="Nasiyaga sotilgan savdolar va qo'lda yozilgan qarzlar shu yerda ko'rinadi."
    />

    <ul v-else class="grid gap-3 md:grid-cols-2">
        <li
            v-for="debt in debts.data"
            :key="debt.id"
            class="flex flex-col gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-medium text-ink">
                        {{ debt.customer?.name }}
                    </p>
                    <p class="text-sm text-ink-muted">
                        {{ formatPhone(debt.customer?.phone) }}
                        <template v-if="debt.note">· {{ debt.note }}</template>
                    </p>
                </div>
                <StatusBadge :tone="statusTone[debt.status]">
                    {{ debt.status_label }}
                </StatusBadge>
            </div>

            <div class="flex items-end justify-between gap-3">
                <div class="text-sm">
                    <p class="text-lg font-semibold text-ink tabular-nums">
                        {{ formatMoney(debt.remaining) }}
                    </p>
                    <p class="text-ink-muted">
                        {{ formatMoney(debt.amount) }} dan ·
                        {{ formatShortDate(debt.due_date) }} gacha
                    </p>
                </div>
                <AppButton
                    v-if="enabled && debt.status !== 'paid'"
                    size="sm"
                    icon="check"
                    @click="openPay(debt)"
                >
                    To'lov
                </AppButton>
            </div>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="debts" />
    </div>

    <AppModal v-model:open="createOpen" title="Qarz yozish">
        <form class="flex flex-col gap-4" @submit.prevent="submitCreate">
            <SelectField
                v-model="createForm.customer_id"
                label="Mijoz"
                placeholder="Mijozni tanlang"
                hint="Yangi mijozni «Mijozlar» bo'limida qo'shing"
                :options="customerOptions"
                :error="createForm.errors.customer_id"
            />
            <MoneyField
                v-model="createForm.amount"
                label="Summa"
                :error="createForm.errors.amount"
            />
            <TextField
                v-model="createForm.due_date"
                type="date"
                label="To'lov sanasi"
                :error="createForm.errors.due_date"
            />
            <TextField
                v-model="createForm.note"
                label="Izoh"
                placeholder="Masalan: 2 qop un"
                :error="createForm.errors.note"
            />
            <AppButton
                type="submit"
                size="lg"
                block
                :loading="createForm.processing"
            >
                Saqlash
            </AppButton>
        </form>
    </AppModal>

    <AppModal
        v-model:open="payOpen"
        :title="`To'lov: ${paying?.customer?.name ?? ''}`"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submitPay">
            <p class="text-sm text-ink-soft">
                Qolgan qarz:
                <span class="font-semibold text-ink">
                    {{ formatMoney(paying?.remaining) }}
                </span>
            </p>
            <MoneyField
                v-model="payForm.amount"
                label="To'langan summa"
                :error="payForm.errors.amount"
            />
            <AppButton
                type="submit"
                size="lg"
                block
                :loading="payForm.processing"
            >
                Qabul qilish
            </AppButton>
        </form>
    </AppModal>
</template>
