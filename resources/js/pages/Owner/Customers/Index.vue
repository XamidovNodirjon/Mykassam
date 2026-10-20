<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import FeatureLocked from '@/components/FeatureLocked.vue';
import PageHeader from '@/components/PageHeader.vue';
import ShopTabs from '@/components/ShopTabs.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import PhoneField from '@/components/ui/PhoneField.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import TextField from '@/components/ui/TextField.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatMoney, formatPhone } from '@/lib/format';
import { index as shopsIndex } from '@/routes/owner/shops';
import { destroy, store, update } from '@/routes/owner/shops/customers';
import type { Customer, Paginated, Shop } from '@/types';

const props = defineProps<{
    shop: Shop;
    customers: Paginated<Customer>;
    filters: { search: string | null };
}>();

const confirm = useConfirm();
const enabled = computed(() => props.shop.features?.includes('customers'));
const modalOpen = ref(false);
const editing = ref<Customer | null>(null);

const form = useForm({
    name: '',
    phone: '' as string | null,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(customer: Customer): void {
    editing.value = customer;
    form.clearErrors();
    form.name = customer.name;
    form.phone = customer.phone ?? '';
    modalOpen.value = true;
}

function submit(): void {
    const target = editing.value
        ? update({ shop: props.shop.id, customer: editing.value.id })
        : store(props.shop.id);

    form.submit(target, {
        preserveScroll: true,
        onSuccess: () => {
            modalOpen.value = false;
            form.reset();
        },
    });
}

async function remove(customer: Customer): Promise<void> {
    const confirmed = await confirm({
        title: "Mijozni o'chirish",
        message: `"${customer.name}" va uning bonuslari o'chiriladi.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy({ shop: props.shop.id, customer: customer.id }), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="`${shop.name} — mijozlar`" />

    <PageHeader
        :title="shop.name"
        :description="`${customers.meta.total} ta mijoz · keshbek ${shop.cashback_percent ?? 0}%`"
        :back-href="shopsIndex.url()"
    >
        <template v-if="enabled" #actions>
            <AppButton icon="plus" @click="openCreate">Mijoz</AppButton>
        </template>
    </PageHeader>

    <ShopTabs :shop="shop" active="customers" />

    <FeatureLocked
        v-if="!enabled"
        feature="Mijozlar bazasi va keshbek"
        :plan-name="shop.plan?.name"
    />

    <div class="mb-4">
        <SearchInput
            :model-value="filters.search"
            placeholder="Ism yoki telefon"
            :only="['customers', 'filters']"
        />
    </div>

    <EmptyState
        v-if="customers.data.length === 0"
        icon="gift"
        :title="filters.search ? 'Hech kim topilmadi' : 'Hali mijozlar yo\'q'"
        description="Doimiy mijozlarni qo'shing: ular har xariddan keshbek yig'adi va nasiyaga olishi mumkin."
    />

    <ul
        v-else
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li
            v-for="customer in customers.data"
            :key="customer.id"
            class="flex items-center gap-3 px-4 py-3"
        >
            <div class="min-w-0 flex-1">
                <p class="truncate font-medium text-ink">{{ customer.name }}</p>
                <p class="text-sm text-ink-muted">
                    {{ formatPhone(customer.phone) || "Telefon yo'q" }}
                </p>
                <div class="mt-1 flex flex-wrap gap-2">
                    <StatusBadge tone="success">
                        Bonus: {{ formatMoney(customer.bonus_balance) }}
                    </StatusBadge>
                    <StatusBadge v-if="customer.debt" tone="danger">
                        Qarz: {{ formatMoney(customer.debt) }}
                    </StatusBadge>
                </div>
            </div>
            <template v-if="enabled">
                <AppButton
                    variant="ghost"
                    size="sm"
                    icon="pencil"
                    class="px-2"
                    aria-label="Tahrirlash"
                    @click="openEdit(customer)"
                />
                <AppButton
                    variant="ghost"
                    size="sm"
                    icon="trash"
                    class="px-2 hover:text-danger"
                    aria-label="O'chirish"
                    @click="remove(customer)"
                />
            </template>
        </li>
    </ul>

    <div class="mt-4">
        <AppPagination :paginator="customers" />
    </div>

    <AppModal
        v-model:open="modalOpen"
        :title="editing ? 'Mijozni tahrirlash' : 'Yangi mijoz'"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <TextField
                v-model="form.name"
                label="Ism"
                autofocus
                :error="form.errors.name"
            />
            <PhoneField
                v-model="form.phone"
                hint="Qarz eslatmasi SMS shu raqamga boradi"
                :error="form.errors.phone"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
