<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PageHeader from '@/components/PageHeader.vue';
import ShopTabs from '@/components/ShopTabs.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import AppPagination from '@/components/ui/AppPagination.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import MoneyField from '@/components/ui/MoneyField.vue';
import SearchInput from '@/components/ui/SearchInput.vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import { useConfirm } from '@/composables/useConfirm';
import { formatMoney, formatNumber } from '@/lib/format';
import { index as shopsIndex } from '@/routes/owner/shops';
import {
    create,
    destroy,
    edit,
    importMethod as importProducts,
    importTemplate,
    index,
} from '@/routes/owner/shops/products';
import { store as storeReceipt } from '@/routes/owner/shops/products/receipts';
import type { Paginated, Product, ProductImportResult, Shop } from '@/types';

const props = defineProps<{
    shop: Shop;
    products: Paginated<Product>;
    lowStockCount: number;
    filters: { search: string | null; low_stock: boolean };
}>();

const confirm = useConfirm();
const page = usePage();
const canImport = computed(() =>
    props.shop.features?.includes('product_import'),
);
const canReceive = computed(() =>
    props.shop.features?.includes('stock_control'),
);

function stockTone(product: Product): 'danger' | 'warning' | 'neutral' {
    if (product.stock === 0) {
        return 'danger';
    }

    return product.stock <= product.min_stock ? 'warning' : 'neutral';
}

const importOpen = ref(false);
const importResult = ref<ProductImportResult | null>(null);
const importForm = useForm({ file: null as File | null });

function openImport(): void {
    importForm.reset();
    importForm.clearErrors();
    importResult.value = null;
    importOpen.value = true;
}

function submitImport(): void {
    importForm.submit(importProducts(props.shop.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importResult.value = page.flash.importResult ?? null;
            importForm.reset();
        },
    });
}

const receiving = ref<Product | null>(null);
const receiveOpen = ref(false);
const receiveForm = useForm({
    quantity: null as number | null,
    cost_price: null as number | null,
});

function openReceive(product: Product): void {
    receiving.value = product;
    receiveForm.clearErrors();
    receiveForm.quantity = null;
    receiveForm.cost_price = product.cost_price;
    receiveOpen.value = true;
}

const receiveTitle = computed(() => `Kirim: ${receiving.value?.name ?? ''}`);

function onFileChange(event: Event): void {
    importForm.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitReceive(): void {
    if (!receiving.value) {
        return;
    }

    receiveForm.submit(
        storeReceipt({ shop: props.shop.id, product: receiving.value.id }),
        {
            preserveScroll: true,
            onSuccess: () => (receiveOpen.value = false),
        },
    );
}

async function remove(product: Product): Promise<void> {
    const confirmed = await confirm({
        title: "Mahsulotni o'chirish",
        message: `"${product.name}" o'chiriladi. Savdolar tarixida u saqlanib qoladi.`,
        confirmText: "O'chirish",
        danger: true,
    });

    if (confirmed) {
        router.visit(destroy({ shop: props.shop.id, product: product.id }), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head :title="`${shop.name} — mahsulotlar`" />

    <PageHeader
        :title="shop.name"
        :description="`${products.meta.total} ta mahsulot`"
        :back-href="shopsIndex.url()"
    >
        <template #actions>
            <AppButton
                v-if="canImport"
                variant="secondary"
                icon="upload"
                @click="openImport"
            >
                Excel
            </AppButton>
            <AppButton :href="create.url(shop.id)" icon="plus">
                Mahsulot
            </AppButton>
        </template>
    </PageHeader>

    <ShopTabs :shop="shop" active="products" />

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="flex-1">
            <SearchInput
                :model-value="filters.search"
                placeholder="Nomi yoki shtrix-kod"
            />
        </div>
        <Link
            v-if="canReceive"
            :href="
                index(shop.id, {
                    query: {
                        search: filters.search ?? undefined,
                        low_stock: filters.low_stock ? undefined : 1,
                    },
                })
            "
            preserve-scroll
            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 text-sm font-medium transition"
            :class="
                filters.low_stock
                    ? 'border-warning bg-warning-soft text-warning'
                    : 'border-line bg-surface text-ink-soft hover:text-ink'
            "
        >
            <AppIcon name="alert" :size="18" />
            Kam qolgan: {{ lowStockCount }}
        </Link>
    </div>

    <EmptyState
        v-if="products.data.length === 0"
        icon="cube"
        :title="filters.search ? 'Hech narsa topilmadi' : 'Mahsulotlar yo\'q'"
        :description="
            filters.search
                ? undefined
                : 'Sotuvchilar sota olishi uchun mahsulot qo\'shing.'
        "
    >
        <AppButton
            v-if="!filters.search"
            :href="create.url(shop.id)"
            icon="plus"
        >
            Mahsulot qo'shish
        </AppButton>
    </EmptyState>

    <template v-else>
        <!-- Phones: cards -->
        <ul class="flex flex-col gap-2 md:hidden">
            <li
                v-for="product in products.data"
                :key="product.id"
                class="rounded-2xl border border-line bg-surface shadow-card"
            >
                <Link
                    :href="edit.url({ shop: shop.id, product: product.id })"
                    class="block p-4"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-ink">
                                {{ product.name }}
                            </p>
                            <p
                                v-if="product.barcode"
                                class="font-mono text-xs text-ink-muted"
                            >
                                {{ product.barcode }}
                            </p>
                        </div>
                        <p class="font-semibold text-ink tabular-nums">
                            {{ formatMoney(product.price) }}
                        </p>
                    </div>
                    <div
                        class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted"
                    >
                        <span>Tan: {{ formatMoney(product.cost_price) }}</span>
                        <span>Min: {{ formatMoney(product.min_price) }}</span>
                        <StatusBadge :tone="stockTone(product)">
                            {{ formatNumber(product.stock) }} dona
                        </StatusBadge>
                        <StatusBadge v-if="!product.is_active" tone="neutral">
                            Nofaol
                        </StatusBadge>
                    </div>
                </Link>
                <div v-if="canReceive" class="border-t border-line px-2 py-1">
                    <AppButton
                        variant="ghost"
                        size="sm"
                        icon="inbox"
                        @click="openReceive(product)"
                    >
                        Kirim
                    </AppButton>
                </div>
            </li>
        </ul>

        <!-- Tablets and desktop: table -->
        <div
            class="hidden overflow-hidden rounded-2xl border border-line bg-surface shadow-card md:block"
        >
            <table class="w-full text-sm">
                <thead
                    class="bg-surface-muted text-left text-xs text-ink-muted"
                >
                    <tr>
                        <th class="px-4 py-3 font-medium">Mahsulot</th>
                        <th class="px-4 py-3 text-right font-medium">
                            Tan narx
                        </th>
                        <th class="px-4 py-3 text-right font-medium">
                            Minimal
                        </th>
                        <th class="px-4 py-3 text-right font-medium">Narx</th>
                        <th class="px-4 py-3 text-right font-medium">Qoldiq</th>
                        <th class="px-4 py-3 font-medium">Holat</th>
                        <th class="px-4 py-3">
                            <span class="sr-only">Amallar</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="px-4 py-3">
                            <p class="font-medium text-ink">
                                {{ product.name }}
                            </p>
                            <p
                                v-if="product.barcode"
                                class="font-mono text-xs text-ink-muted"
                            >
                                {{ product.barcode }}
                            </p>
                        </td>
                        <td
                            class="px-4 py-3 text-right text-ink-soft tabular-nums"
                        >
                            {{ formatMoney(product.cost_price) }}
                        </td>
                        <td
                            class="px-4 py-3 text-right text-ink-soft tabular-nums"
                        >
                            {{ formatMoney(product.min_price) }}
                        </td>
                        <td
                            class="px-4 py-3 text-right font-semibold text-ink tabular-nums"
                        >
                            {{ formatMoney(product.price) }}
                        </td>
                        <td
                            class="px-4 py-3 text-right tabular-nums"
                            :class="{
                                'text-danger': stockTone(product) === 'danger',
                                'text-warning':
                                    stockTone(product) === 'warning',
                                'text-ink': stockTone(product) === 'neutral',
                            }"
                        >
                            {{ formatNumber(product.stock) }}
                        </td>
                        <td class="px-4 py-3">
                            <StatusBadge
                                :tone="
                                    product.is_active ? 'success' : 'neutral'
                                "
                            >
                                {{ product.is_active ? 'Faol' : 'Nofaol' }}
                            </StatusBadge>
                        </td>
                        <td class="px-2 py-3">
                            <div class="flex justify-end">
                                <AppButton
                                    v-if="canReceive"
                                    variant="ghost"
                                    size="sm"
                                    icon="inbox"
                                    class="px-2"
                                    aria-label="Kirim"
                                    title="Kirim"
                                    @click="openReceive(product)"
                                />
                                <AppButton
                                    variant="ghost"
                                    size="sm"
                                    icon="pencil"
                                    class="px-2"
                                    aria-label="Tahrirlash"
                                    :href="
                                        edit.url({
                                            shop: shop.id,
                                            product: product.id,
                                        })
                                    "
                                />
                                <AppButton
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    class="px-2 hover:text-danger"
                                    aria-label="O'chirish"
                                    @click="remove(product)"
                                />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </template>

    <div class="mt-4">
        <AppPagination :paginator="products" />
    </div>

    <AppModal v-model:open="importOpen" title="Excel'dan yuklash">
        <form class="flex flex-col gap-4" @submit.prevent="submitImport">
            <p class="text-sm text-ink-soft">
                Ustunlar tartibi: Nomi, Shtrix-kod, Tan narx, Minimal narx,
                Sotuv narxi, Qoldiq. Shtrix-kodi (yoki nomi) bir xil mahsulot
                yangilanadi, qolganlari qo'shiladi.
            </p>
            <a
                :href="importTemplate.url(shop.id)"
                class="inline-flex items-center gap-2 text-sm font-medium text-brand"
            >
                <AppIcon name="download" :size="18" />
                Shablonni yuklab olish
            </a>
            <label
                class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border border-dashed px-4 py-6 text-center text-sm"
                :class="
                    importForm.errors.file ? 'border-danger' : 'border-line'
                "
            >
                <AppIcon name="upload" :size="24" class="text-ink-muted" />
                <span class="font-medium text-ink">
                    {{
                        importForm.file?.name ??
                        '.xlsx yoki .csv faylni tanlang'
                    }}
                </span>
                <input
                    type="file"
                    accept=".xlsx,.csv"
                    class="sr-only"
                    @change="onFileChange"
                />
            </label>
            <p
                v-if="importForm.errors.file"
                class="text-sm text-danger"
                role="alert"
            >
                {{ importForm.errors.file }}
            </p>

            <div
                v-if="importResult"
                class="rounded-xl bg-surface-muted px-4 py-3 text-sm"
            >
                <p class="font-medium text-ink">
                    {{ importResult.created }} ta qo'shildi,
                    {{ importResult.updated }} ta yangilandi.
                </p>
                <ul
                    v-if="importResult.errors.length"
                    class="mt-2 flex max-h-40 flex-col gap-1 overflow-y-auto text-xs text-danger"
                >
                    <li v-for="error in importResult.errors" :key="error">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <AppButton
                type="submit"
                size="lg"
                block
                icon="upload"
                :disabled="!importForm.file"
                :loading="importForm.processing"
            >
                Yuklash
            </AppButton>
        </form>
    </AppModal>

    <AppModal v-model:open="receiveOpen" :title="receiveTitle">
        <form class="flex flex-col gap-4" @submit.prevent="submitReceive">
            <p class="text-sm text-ink-soft">
                Hozirgi qoldiq:
                <span class="font-semibold text-ink">
                    {{ formatNumber(receiving?.stock) }} dona
                </span>
            </p>
            <MoneyField
                v-model="receiveForm.quantity"
                label="Kelgan miqdor"
                suffix="dona"
                :error="receiveForm.errors.quantity"
            />
            <MoneyField
                v-model="receiveForm.cost_price"
                label="Tan narx (bir dona)"
                hint="O'zgarsa, mahsulotning tan narxi ham yangilanadi"
                :error="receiveForm.errors.cost_price"
            />
            <AppButton
                type="submit"
                size="lg"
                block
                :loading="receiveForm.processing"
            >
                Omborga qo'shish
            </AppButton>
        </form>
    </AppModal>
</template>
