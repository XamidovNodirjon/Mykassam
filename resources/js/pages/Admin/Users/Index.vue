<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import StatCard from '@/components/StatCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { useConfirm } from '@/composables/useConfirm';
import {
    formatDateTime,
    formatNumber,
    formatPhone,
    formatShortDate,
} from '@/lib/format';
import { destroy, update } from '@/routes/admin/users';
import type { Paginated, User, UserRole } from '@/types';

const props = defineProps<{
    users: Paginated<User>;
    summary: {
        total: number;
        owners: number;
        sellers: number;
        blocked: number;
        online: number;
    };
    filters: { search: string | null; role: UserRole | null };
}>();

const roles: { value: UserRole | null; label: string }[] = [
    { value: null, label: 'Hammasi' },
    { value: 'owner', label: 'Egalar' },
    { value: 'seller', label: 'Sotuvchilar' },
    { value: 'admin', label: 'Adminlar' },
];

const roleTone = {
    admin: 'warning',
    owner: 'success',
    seller: 'neutral',
} as const;

/** Matches UserService::ONLINE_WITHIN_SECONDS. */
const onlineWithinMs = 5 * 60 * 1000;

function isOnline(user: User): boolean {
    return (
        user.last_seen_at != null &&
        Date.now() - Date.parse(user.last_seen_at) < onlineWithinMs
    );
}

const confirm = useConfirm();
const editing = ref<User | null>(null);
const editOpen = ref(false);

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '' as string | null,
    login: '',
    password: '',
    is_active: true,
});

function edit(user: User): void {
    editing.value = user;
    form.defaults({
        first_name: user.first_name,
        last_name: user.last_name ?? '',
        phone: user.phone ?? '',
        login: user.login ?? '',
        password: '',
        is_active: user.is_active ?? true,
    });
    form.reset();
    form.clearErrors();
    editOpen.value = true;
}

function save(): void {
    if (editing.value === null) {
        return;
    }

    form.submit(update(editing.value.id), {
        preserveScroll: true,
        onSuccess: () => (editOpen.value = false),
        onFinish: () => form.reset('password'),
    });
}

async function remove(user: User): Promise<void> {
    const confirmed = await confirm({
        title: "Foydalanuvchini o'chirish",
        message:
            user.role === 'owner'
                ? `${user.full_name} va uning ${user.shops_count ?? 0} ta do'koni butunlay o'chiriladi: savdolar, mahsulotlar, mijozlar, qarzlar va sotuvchilar. Bu amalni qaytarib bo'lmaydi.`
                : `${user.full_name} va uning ${user.sales_count ?? 0} ta savdosi butunlay o'chiriladi. Bu amalni qaytarib bo'lmaydi.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy(user.id), { preserveScroll: true });
    }
}

function selectRole(role: UserRole | null): void {
    if (role === props.filters.role) {
        return;
    }

    router.reload({
        data: { role: role ?? undefined, page: undefined },
        only: ['users', 'filters'],
        replace: true,
    });
}
</script>

<template>
    <Head title="Foydalanuvchilar" />

    <PageHeader
        title="Foydalanuvchilar"
        description="Tizimdagi barcha hisoblar: egalar, sotuvchilar va adminlar"
    />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <StatCard
            highlight
            label="Jami"
            :value="formatNumber(summary.total)"
            :hint="`Bloklangan: ${formatNumber(summary.blocked)}`"
            icon="users"
        />
        <StatCard
            label="Do'kon egalari"
            :value="formatNumber(summary.owners)"
            icon="store"
        />
        <StatCard
            label="Sotuvchilar"
            :value="formatNumber(summary.sellers)"
            icon="user"
        />
        <StatCard
            label="Hozir onlayn"
            :value="formatNumber(summary.online)"
            hint="So'nggi 5 daqiqada"
            icon="monitor"
        />
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div
            class="flex gap-1 overflow-x-auto rounded-full bg-surface-muted p-1"
            role="tablist"
            aria-label="Rol"
        >
            <button
                v-for="role in roles"
                :key="role.label"
                type="button"
                role="tab"
                :aria-selected="filters.role === role.value"
                class="flex-1 rounded-full px-3 py-2 text-sm font-medium whitespace-nowrap transition sm:flex-none"
                :class="
                    filters.role === role.value
                        ? 'bg-surface-raised text-ink shadow-sm'
                        : 'text-ink-soft hover:text-ink'
                "
                @click="selectRole(role.value)"
            >
                {{ role.label }}
            </button>
        </div>
        <div class="flex-1">
            <SearchInput
                :model-value="filters.search"
                placeholder="Ism, telefon yoki login bo'yicha qidirish"
                :only="['users', 'filters']"
            />
        </div>
    </div>

    <EmptyState
        v-if="users.data.length === 0"
        icon="users"
        title="Foydalanuvchilar topilmadi"
    />

    <ul
        v-else
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li
            v-for="user in users.data"
            :key="user.id"
            class="flex flex-col gap-3 px-4 py-3.5 sm:flex-row sm:items-center"
        >
            <div class="flex min-w-0 flex-1 items-start gap-3">
                <span
                    class="relative flex size-10 shrink-0 items-center justify-center rounded-full bg-surface-muted font-semibold text-ink-soft"
                >
                    {{ user.first_name.charAt(0) }}
                    <span
                        v-if="isOnline(user)"
                        class="absolute right-0 bottom-0 size-3 rounded-full border-2 border-surface-raised bg-brand"
                        title="Onlayn"
                    />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="truncate font-medium text-ink">
                            {{ user.full_name }}
                        </p>
                        <StatusBadge
                            v-if="user.role"
                            :tone="roleTone[user.role]"
                        >
                            {{ user.role_label }}
                        </StatusBadge>
                    </div>
                    <p class="mt-0.5 text-sm text-ink-muted">
                        <span v-if="user.phone" class="tabular-nums">
                            {{ formatPhone(user.phone) }}
                        </span>
                        <span v-if="user.phone && user.login !== user.phone">
                            ·
                        </span>
                        <span v-if="user.login !== user.phone">
                            Login: {{ user.login }}
                        </span>
                    </p>
                    <p
                        v-if="user.role === 'owner'"
                        class="mt-0.5 text-xs text-ink-muted"
                    >
                        {{ user.shops_count }} ta do'kon
                    </p>
                    <p
                        v-else-if="user.role === 'seller' && user.shop"
                        class="mt-0.5 text-xs text-ink-muted"
                    >
                        Do'kon: {{ user.shop.name }}
                        <template v-if="user.shop.owner">
                            (egasi: {{ user.shop.owner.full_name }})
                        </template>
                        · {{ formatNumber(user.sales_count) }} ta savdo
                    </p>
                </div>
            </div>
            <div
                class="flex items-center justify-between gap-3 pl-13 sm:flex-col sm:items-end sm:gap-1 sm:pl-0"
            >
                <StatusBadge :tone="user.is_active ? 'success' : 'danger'">
                    {{ user.is_active ? 'Faol' : 'Bloklangan' }}
                </StatusBadge>
                <div class="text-right text-xs text-ink-muted">
                    <p>
                        {{
                            isOnline(user)
                                ? 'Hozir onlayn'
                                : user.last_seen_at
                                  ? `Oxirgi faollik: ${formatDateTime(user.last_seen_at)}`
                                  : 'Faollik qayd etilmagan'
                        }}
                    </p>
                    <p v-if="user.created_at">
                        Ro'yxatdan o'tgan:
                        {{ formatShortDate(user.created_at) }}
                        {{ user.created_at.slice(0, 4) }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2 pl-13 sm:pl-0">
                <AppButton
                    variant="secondary"
                    size="sm"
                    icon="pencil"
                    @click="edit(user)"
                >
                    Tahrirlash
                </AppButton>
                <button
                    v-if="user.role !== 'admin'"
                    type="button"
                    class="flex size-9 items-center justify-center rounded-full text-ink-muted transition hover:bg-danger-soft hover:text-danger"
                    :aria-label="`${user.full_name}ni o'chirish`"
                    @click="remove(user)"
                >
                    <AppIcon name="trash" :size="18" />
                </button>
            </div>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="users" />
    </div>

    <AppModal v-model:open="editOpen" title="Foydalanuvchini tahrirlash">
        <form class="flex flex-col gap-4" @submit.prevent="save">
            <div class="grid gap-4 sm:grid-cols-2">
                <TextField
                    v-model="form.first_name"
                    label="Ism"
                    :error="form.errors.first_name"
                />
                <TextField
                    v-model="form.last_name"
                    label="Familiya"
                    :error="form.errors.last_name"
                />
            </div>
            <PhoneField
                v-model="form.phone"
                :hint="
                    editing?.role === 'owner'
                        ? 'Do\'kon egasi shu raqam bilan tizimga kiradi'
                        : undefined
                "
                :error="form.errors.phone"
            />
            <TextField
                v-if="editing?.role !== 'owner'"
                v-model="form.login"
                label="Login"
                autocomplete="off"
                :error="form.errors.login"
            />
            <TextField
                v-model="form.password"
                type="password"
                label="Yangi parol"
                hint="Bo'sh qoldirilsa, parol o'zgarmaydi. Kamida 6 ta belgi."
                autocomplete="new-password"
                :error="form.errors.password"
            />
            <ToggleField
                v-if="editing?.role !== 'admin'"
                v-model="form.is_active"
                label="Faol"
                description="O'chirilsa, foydalanuvchi tizimga kira olmaydi"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
