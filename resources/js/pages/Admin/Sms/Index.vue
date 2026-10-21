<script setup lang="ts">
import { Deferred, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import {
    formatDateTime,
    formatMoney,
    formatNumber,
    formatPhone,
} from '@/lib/format';
import type { Paginated, SmsMessage } from '@/types';

defineProps<{
    messages: Paginated<SmsMessage>;
    summary: { total: number; today: number; failed: number };
    balance?: { amount: number | null; error: string | null };
    isLive: boolean;
    filters: { search: string | null };
}>();

const statusTone = {
    sent: 'success',
    pending: 'warning',
    failed: 'danger',
} as const;

const refreshingBalance = ref(false);

function refreshBalance(): void {
    router.reload({
        only: ['balance'],
        onStart: () => (refreshingBalance.value = true),
        onFinish: () => (refreshingBalance.value = false),
    });
}
</script>

<template>
    <Head title="SMS xabarnomalar" />

    <PageHeader
        title="SMS xabarnomalar"
        description="USMS orqali yuborilgan tasdiqlash kodlari"
    >
        <template #actions>
            <AppButton
                variant="secondary"
                icon="refresh"
                :loading="refreshingBalance"
                @click="refreshBalance"
            >
                Balansni yangilash
            </AppButton>
        </template>
    </PageHeader>

    <div
        v-if="!isLive"
        class="mb-4 flex items-start gap-3 rounded-2xl bg-warning-soft px-4 py-3 text-sm text-warning"
    >
        <AppIcon name="alert" :size="20" class="shrink-0" />
        <p>
            Test rejimi: SMS'lar yuborilmaydi, kodlar faqat logga yoziladi.
            Haqiqiy yuborish uchun .env faylida SMS_DRIVER=usms qiling.
        </p>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <Deferred data="balance">
            <template #fallback>
                <div
                    class="h-full min-h-24 animate-pulse rounded-2xl bg-surface-muted"
                />
            </template>
            <StatCard
                highlight
                label="USMS balans"
                :value="
                    balance?.amount == null
                        ? 'Aniqlanmadi'
                        : formatMoney(balance.amount)
                "
                :hint="balance?.error ?? undefined"
                icon="wallet"
            />
        </Deferred>
        <StatCard
            label="Bugun"
            :value="formatNumber(summary.today)"
            icon="chat"
        />
        <StatCard
            label="Jami"
            :value="formatNumber(summary.total)"
            icon="chart"
        />
        <StatCard
            label="Xatoliklar"
            :value="formatNumber(summary.failed)"
            icon="alert"
        />
    </div>

    <div class="mt-6 mb-4">
        <SearchInput
            :model-value="filters.search"
            placeholder="Telefon raqam bo'yicha qidirish"
            :only="['messages', 'filters']"
        />
    </div>

    <EmptyState
        v-if="messages.data.length === 0"
        icon="chat"
        title="SMS'lar topilmadi"
    />

    <ul
        v-else
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li
            v-for="message in messages.data"
            :key="message.id"
            class="flex items-start gap-3 px-4 py-3"
        >
            <div class="min-w-0 flex-1">
                <p class="font-medium text-ink tabular-nums">
                    {{ formatPhone(message.phone) }}
                </p>
                <p class="text-xs text-ink-muted">
                    {{ message.purpose_label }} ·
                    {{ formatDateTime(message.created_at) }}
                </p>
                <p v-if="message.error" class="mt-1 text-xs text-danger">
                    {{ message.error }}
                </p>
            </div>
            <StatusBadge :tone="statusTone[message.status]">
                {{ message.status_label }}
            </StatusBadge>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="messages" />
    </div>
</template>
