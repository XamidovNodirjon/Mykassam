<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/components/PageHeader.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import TextField from '@/components/ui/TextField.vue';
import ToggleField from '@/components/ui/ToggleField.vue';
import { useConfirm } from '@/composables/useConfirm';
import { destroy, store, update } from '@/routes/admin/shop-types';
import type { ShopType } from '@/types';

defineProps<{
    shopTypes: ShopType[];
}>();

const confirm = useConfirm();
const modalOpen = ref(false);
const editing = ref<ShopType | null>(null);

const form = useForm({
    name: '',
    is_active: true,
});

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(shopType: ShopType): void {
    editing.value = shopType;
    form.clearErrors();
    form.name = shopType.name;
    form.is_active = shopType.is_active ?? true;
    modalOpen.value = true;
}

function submit(): void {
    const target = editing.value ? update(editing.value.id) : store();

    form.submit(target, {
        preserveScroll: true,
        onSuccess: () => {
            modalOpen.value = false;
            form.reset();
        },
    });
}

async function remove(shopType: ShopType): Promise<void> {
    const confirmed = await confirm({
        title: "Turni o'chirish",
        message: `"${shopType.name}" turini o'chirmoqchimisiz?`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy(shopType.id), { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Do'kon turlari" />

    <PageHeader
        title="Do'kon turlari"
        description="Egalar do'kon yaratishda shu ro'yxatdan tanlaydi"
    >
        <template #actions>
            <AppButton icon="plus" @click="openCreate">Yangi tur</AppButton>
        </template>
    </PageHeader>

    <EmptyState
        v-if="shopTypes.length === 0"
        icon="grid"
        title="Hali turlar yo'q"
        description="Birinchi do'kon turini qo'shing."
    />

    <ul
        v-else
        class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface shadow-card"
    >
        <li
            v-for="shopType in shopTypes"
            :key="shopType.id"
            class="flex items-center gap-3 px-4 py-3"
        >
            <div class="min-w-0 flex-1">
                <p class="truncate font-medium text-ink">
                    {{ shopType.name }}
                </p>
                <p class="text-xs text-ink-muted">
                    {{ shopType.shops_count }} ta do'kon
                </p>
            </div>
            <StatusBadge :tone="shopType.is_active ? 'success' : 'neutral'">
                {{ shopType.is_active ? 'Faol' : 'Nofaol' }}
            </StatusBadge>
            <AppButton
                variant="ghost"
                size="sm"
                icon="pencil"
                class="px-2"
                aria-label="Tahrirlash"
                @click="openEdit(shopType)"
            />
            <AppButton
                variant="ghost"
                size="sm"
                icon="trash"
                class="px-2 hover:text-danger"
                aria-label="O'chirish"
                @click="remove(shopType)"
            />
        </li>
    </ul>

    <AppModal
        v-model:open="modalOpen"
        :title="editing ? 'Turni tahrirlash' : 'Yangi tur'"
    >
        <form class="flex flex-col gap-4" @submit.prevent="submit">
            <TextField
                v-model="form.name"
                label="Nomi"
                placeholder="Masalan: Oziq-ovqat"
                autofocus
                :error="form.errors.name"
            />
            <ToggleField
                v-model="form.is_active"
                label="Faol"
                description="Nofaol turlar yangi do'konlar uchun ko'rinmaydi"
            />
            <AppButton type="submit" size="lg" block :loading="form.processing">
                Saqlash
            </AppButton>
        </form>
    </AppModal>
</template>
