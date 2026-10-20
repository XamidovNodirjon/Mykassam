<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatPhone } from '@/lib/format';
import { status } from '@/routes/admin/owners';
import type { Paginated, User } from '@/types';

defineProps<{
    owners: Paginated<User>;
    filters: { search: string | null };
}>();

const confirm = useConfirm();

async function toggle(owner: User): Promise<void> {
    const confirmed = await confirm({
        title: owner.is_active ? 'Egani bloklash' : 'Egani faollashtirish',
        message: owner.is_active
            ? `${owner.full_name} va uning barcha sotuvchilari tizimga kira olmaydi.`
            : `${owner.full_name} yana tizimdan foydalana oladi.`,
        confirmText: owner.is_active ? 'Bloklash' : 'Faollashtirish',
        danger: owner.is_active,
    });

    if (confirmed) {
        router.visit(status(owner.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Do'kon egalari" />

    <PageHeader
        title="Do'kon egalari"
        :description="`Jami: ${owners.meta.total}`"
    />

    <div class="mb-4">
        <SearchInput
            :model-value="filters.search"
            placeholder="Ism yoki telefon bo'yicha qidirish"
        />
    </div>

    <EmptyState
        v-if="owners.data.length === 0"
        icon="users"
        title="Egalar topilmadi"
    />

    <ul v-else class="grid gap-3 md:grid-cols-2">
        <li
            v-for="owner in owners.data"
            :key="owner.id"
            class="flex items-center gap-3 rounded-2xl border border-line bg-surface p-4 shadow-card"
        >
            <span
                class="flex size-11 shrink-0 items-center justify-center rounded-full bg-surface-muted font-semibold text-ink-soft"
            >
                {{ owner.first_name.charAt(0) }}
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate font-medium text-ink">
                    {{ owner.full_name }}
                </p>
                <p class="text-sm text-ink-muted">
                    {{ formatPhone(owner.phone) }}
                </p>
                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                    <StatusBadge :tone="owner.is_active ? 'success' : 'danger'">
                        {{ owner.is_active ? 'Faol' : 'Bloklangan' }}
                    </StatusBadge>
                    <span class="text-xs text-ink-muted">
                        {{ owner.shops_count }} ta do'kon ·
                        {{ owner.created_at }}
                    </span>
                </div>
            </div>
            <AppButton
                size="sm"
                :variant="owner.is_active ? 'secondary' : 'primary'"
                @click="toggle(owner)"
            >
                {{ owner.is_active ? 'Bloklash' : 'Faollashtirish' }}
            </AppButton>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="owners" />
    </div>
</template>
