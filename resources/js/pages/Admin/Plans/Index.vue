<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { formatMoney, formatNumber } from '@/lib/format';
import { store, update } from '@/routes/admin/plans';
import { index as subscriptionsIndex } from '@/routes/admin/subscriptions';
import type { Option, Plan, PlanFeature } from '@/types';

const props = defineProps<{
    plans: Plan[];
    features: Option<PlanFeature>[];
    trialPlanId: number;
}>();

const modalOpen = ref(false);
const editing = ref<Plan | null>(null);

const form = useForm({
    name: '',
    price: null as number | null,
    max_sellers: null as number | null,
    max_products: null as number | null,
    features: [] as PlanFeature[],
    is_active: true,
    sort_order: null as number | null,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.sort_order = props.plans.length + 1;
    modalOpen.value = true;
}

function openEdit(plan: Plan): void {
    editing.value = plan;
    form.clearErrors();
    form.name = plan.name;
    form.price = plan.price;
    form.max_sellers = plan.max_sellers;
    form.max_products = plan.max_products;
    form.features = [...plan.features];
    form.is_active = plan.is_active;
    form.sort_order = plan.sort_order;
    modalOpen.value = true;
}

function submit(): void {
    form.submit(editing.value ? update(editing.value.id) : store(), {
        preserveScroll: true,
        onSuccess: () => (modalOpen.value = false),
    });
}

function limit(value: number | null, unit: string): string {
    return value === null
        ? `Cheksiz ${unit}`
        : `${formatNumber(value)} ta ${unit}`;
}
</script>

<template>
    <Head title="Tarif rejalari" />

    <PageHeader
        title="Tarif rejalari"
        description="Narx, limitlar va bo'limlar. O'zgarish shu tarifdagi do'konlarga darhol tegishli."
        :back-href="subscriptionsIndex.url()"
    >
        <template #actions>
            <AppButton icon="plus" @click="openCreate">Yangi tarif</AppButton>
        </template>
    </PageHeader>

    <ul class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <li
            v-for="plan in plans"
            :key="plan.id"
            class="flex flex-col gap-4 rounded-2xl border bg-surface p-5 shadow-card"
            :class="plan.id === trialPlanId ? 'border-brand' : 'border-line'"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-lg font-semibold text-ink">
                        {{ plan.name }}
                    </p>
                    <p
                        class="text-2xl font-semibold tracking-tight text-ink tabular-nums"
                    >
                        {{ formatMoney(plan.price) }}
                        <span class="text-sm font-normal text-ink-muted"
                            >/ oy</span
                        >
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <StatusBadge :tone="plan.is_active ? 'success' : 'neutral'">
                        {{ plan.is_active ? 'Faol' : 'Nofaol' }}
                    </StatusBadge>
                    <StatusBadge v-if="plan.id === trialPlanId" tone="warning">
                        Bepul sinov
                    </StatusBadge>
                </div>
            </div>

            <ul class="flex flex-col gap-1.5 text-sm text-ink-soft">
                <li class="flex items-center gap-2">
                    <AppIcon name="users" :size="16" class="text-ink-muted" />
                    {{ limit(plan.max_sellers, 'sotuvchi') }}
                </li>
                <li class="flex items-center gap-2">
                    <AppIcon name="cube" :size="16" class="text-ink-muted" />
                    {{ limit(plan.max_products, 'mahsulot') }}
                </li>
                <li
                    v-for="feature in features"
                    :key="feature.value"
                    class="flex items-center gap-2"
                    :class="{
                        'text-ink-muted line-through': !plan.features.includes(
                            feature.value,
                        ),
                    }"
                >
                    <AppIcon
                        :name="
                            plan.features.includes(feature.value)
                                ? 'check'
                                : 'x'
                        "
                        :size="16"
                        :class="
                            plan.features.includes(feature.value)
                                ? 'text-brand'
                                : 'text-ink-muted'
                        "
                    />
                    {{ feature.label }}
                </li>
            </ul>

            <div
                class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-3"
            >
                <span class="text-sm text-ink-muted">
                    {{ plan.shops_count }} ta do'kon
                </span>
                <AppButton
                    variant="secondary"
                    size="sm"
                    icon="pencil"
                    @click="openEdit(plan)"
                >
                    Tahrirlash
                </AppButton>
            </div>
        </li>
    </ul>

    <AppModal
        v-model:open="modalOpen"
        :title="editing ? `Tarif: ${editing.name}` : 'Yangi tarif'"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <TextField
                v-model="form.name"
                label="Nomi"
                autofocus
                :error="form.errors.name"
            />
            <MoneyField
                v-model="form.price"
                label="Oylik narx (bir do'kon)"
                :error="form.errors.price"
            />
            <div class="grid grid-cols-2 gap-3">
                <MoneyField
                    v-model="form.max_sellers"
                    label="Sotuvchilar"
                    suffix="ta"
                    placeholder="Cheksiz"
                    :error="form.errors.max_sellers"
                />
                <MoneyField
                    v-model="form.max_products"
                    label="Mahsulotlar"
                    suffix="ta"
                    placeholder="Cheksiz"
                    :error="form.errors.max_products"
                />
            </div>

            <fieldset class="flex flex-col gap-2">
                <legend class="mb-1 text-sm font-medium text-ink-soft">
                    Qo'shimcha bo'limlar
                </legend>
                <label
                    v-for="feature in features"
                    :key="feature.value"
                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-line px-4 py-3 text-sm text-ink"
                >
                    <input
                        v-model="form.features"
                        type="checkbox"
                        :value="feature.value"
                        class="size-4 rounded accent-brand"
                    />
                    {{ feature.label }}
                </label>
            </fieldset>

            <MoneyField
                v-model="form.sort_order"
                label="Tartib raqami"
                suffix=""
                :error="form.errors.sort_order"
            />
            <ToggleField
                v-model="form.is_active"
                label="Faol"
                description="Nofaol tarifni yangi do'konlarga berib bo'lmaydi, eskilari unda qoladi"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
