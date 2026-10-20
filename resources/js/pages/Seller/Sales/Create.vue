<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import PosCart from '@/components/pos/PosCart.vue';
import type { CartLine } from '@/components/pos/types';
import AppModal from '@/components/ui/AppModal.vue';
import { inputClass } from '@/components/ui/input';
import { formatMoney, formatNumber } from '@/lib/format';
import { show, store } from '@/routes/seller/sales';
import type { Customer, Option, SellableProduct } from '@/types';

const props = defineProps<{
    products: SellableProduct[];
    search: string | null;
    paymentMethods: Option[];
    customersEnabled: boolean;
    cashbackPercent: number;
}>();

const page = usePage();
const cart = ref<CartLine[]>([]);
const cartOpen = ref(false);
const term = ref(props.search ?? '');
const lastSaleId = ref<number | null>(page.flash.lastSaleId ?? null);

const customer = ref<Customer | null>(null);

const form = useForm({
    payment_method: props.paymentMethods[0]?.value ?? 'cash',
    items: [] as { product_id: number; quantity: number; unit_price: number }[],
    customer_id: null as number | null,
    bonus_used: null as number | null,
    due_date: '',
});

watch(customer, () => {
    form.bonus_used = null;
});

const total = computed(() =>
    cart.value.reduce((sum, line) => sum + line.quantity * line.unitPrice, 0),
);
const itemsCount = computed(() =>
    cart.value.reduce((sum, line) => sum + line.quantity, 0),
);
const canSubmit = computed(
    () =>
        cart.value.length > 0 &&
        cart.value.every((line) => line.unitPrice >= line.product.min_price) &&
        (form.payment_method !== 'debt' ||
            (customer.value !== null && form.due_date !== '')),
);

function findLine(productId: number): CartLine | undefined {
    return cart.value.find((line) => line.product.id === productId);
}

function quantityInCart(productId: number): number {
    return findLine(productId)?.quantity ?? 0;
}

function add(product: SellableProduct): void {
    const line = findLine(product.id);

    if (!line) {
        cart.value.push({ product, quantity: 1, unitPrice: product.price });
    } else if (line.quantity < product.stock) {
        line.quantity++;
    }

    form.clearErrors();
}

function changeQuantity(line: CartLine, delta: number): void {
    const quantity = line.quantity + delta;

    if (quantity <= 0) {
        removeLine(line);

        return;
    }

    line.quantity = Math.min(quantity, line.product.stock);
}

function changePrice(line: CartLine, value: string): void {
    const digits = value.replace(/\D/g, '');
    line.unitPrice = digits === '' ? 0 : Number(digits);
}

function removeLine(line: CartLine): void {
    cart.value = cart.value.filter((item) => item !== line);
    form.clearErrors();
}

function lineError(index: number): string | undefined {
    const errors = form.errors as Record<string, string | undefined>;

    return (
        errors[`items.${index}.unit_price`] ??
        errors[`items.${index}.quantity`] ??
        errors[`items.${index}.product_id`]
    );
}

let searchTimer: ReturnType<typeof setTimeout> | undefined;

watch(term, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.reload({
            data: { search: value || undefined },
            only: ['products', 'search'],
            replace: true,
        });
    }, 250);
});

onBeforeUnmount(() => clearTimeout(searchTimer));

/**
 * Enter adds the only match — this also makes USB/Bluetooth barcode scanners work.
 */
function onSearchEnter(): void {
    if (props.products.length === 1) {
        add(props.products[0]);
        term.value = '';
    }
}

function submit(): void {
    form.items = cart.value.map((line) => ({
        product_id: line.product.id,
        quantity: line.quantity,
        unit_price: line.unitPrice,
    }));
    form.customer_id = customer.value?.id ?? null;

    form.submit(store(), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            cartOpen.value = false;
            term.value = '';
            lastSaleId.value = page.flash.lastSaleId ?? null;
            customer.value = null;
            form.reset('items', 'customer_id', 'bonus_used', 'due_date');
        },
        onError: () => {
            cartOpen.value = true;
        },
    });
}
</script>

<template>
    <Head title="Sotish" />

    <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
        <section class="min-w-0">
            <div
                v-if="lastSaleId"
                class="mb-4 flex items-center justify-between gap-3 rounded-2xl bg-brand-soft px-4 py-3 text-sm"
            >
                <span class="flex items-center gap-2 text-brand-strong">
                    <AppIcon name="check-circle" :size="20" />
                    Savdo #{{ lastSaleId }} saqlandi
                </span>
                <Link
                    :href="show.url(lastSaleId)"
                    class="font-medium text-brand-strong underline underline-offset-4"
                >
                    Ko'rish
                </Link>
            </div>

            <div class="relative mb-4">
                <span
                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-ink-muted"
                >
                    <AppIcon name="search" :size="18" />
                </span>
                <input
                    v-model="term"
                    type="search"
                    inputmode="search"
                    autocomplete="off"
                    placeholder="Mahsulot nomi yoki shtrix-kod"
                    aria-label="Mahsulot qidirish"
                    :class="inputClass(false, 'h-12 pl-10')"
                    @keydown.enter.prevent="onSearchEnter"
                />
            </div>

            <p
                v-if="products.length === 0"
                class="rounded-2xl border border-dashed border-line bg-surface px-4 py-10 text-center text-sm text-ink-muted"
            >
                {{
                    search
                        ? 'Hech narsa topilmadi.'
                        : 'Sotuvda mahsulot yo‘q. Do‘kon egasiga murojaat qiling.'
                }}
            </p>

            <ul
                v-else
                class="grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4"
            >
                <li v-for="product in products" :key="product.id">
                    <button
                        type="button"
                        class="relative flex h-full w-full flex-col gap-1 rounded-2xl border bg-surface p-3 text-left shadow-card transition active:scale-[0.98] disabled:opacity-50"
                        :class="
                            quantityInCart(product.id) > 0
                                ? 'border-brand ring-2 ring-brand/15'
                                : 'border-line hover:border-brand/40'
                        "
                        :disabled="quantityInCart(product.id) >= product.stock"
                        @click="add(product)"
                    >
                        <span
                            v-if="quantityInCart(product.id) > 0"
                            class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-full bg-brand text-xs font-semibold text-on-brand"
                        >
                            {{ quantityInCart(product.id) }}
                        </span>
                        <span
                            class="line-clamp-2 pr-6 text-sm font-medium text-ink"
                        >
                            {{ product.name }}
                        </span>
                        <span
                            class="mt-auto pt-1 text-base font-semibold text-ink tabular-nums"
                        >
                            {{ formatMoney(product.price) }}
                        </span>
                        <span class="text-xs text-ink-muted">
                            {{ formatNumber(product.stock) }} dona bor
                        </span>
                    </button>
                </li>
            </ul>
        </section>

        <!-- Desktop: cart as a side panel -->
        <aside class="hidden lg:block">
            <div
                class="sticky top-8 max-h-[calc(100dvh-4rem)] overflow-y-auto rounded-2xl border border-line bg-surface shadow-card"
            >
                <h2
                    class="flex items-center justify-between border-b border-line px-5 py-4 font-semibold text-ink"
                >
                    Savatcha
                    <span class="text-sm font-normal text-ink-muted">
                        {{ formatNumber(itemsCount) }} dona
                    </span>
                </h2>
                <div class="p-5">
                    <PosCart
                        v-model:payment-method="form.payment_method"
                        v-model:customer="customer"
                        v-model:bonus-used="form.bonus_used"
                        v-model:due-date="form.due_date"
                        :customers-enabled="customersEnabled"
                        :cashback-percent="cashbackPercent"
                        :errors="form.errors"
                        :lines="cart"
                        :payment-methods="paymentMethods"
                        :total="total"
                        :processing="form.processing"
                        :can-submit="canSubmit"
                        :line-error="lineError"
                        :general-error="form.errors.items"
                        @quantity="changeQuantity"
                        @price="changePrice"
                        @remove="removeLine"
                        @submit="submit"
                    />
                </div>
            </div>
        </aside>
    </div>

    <!-- Phones and tablets: sticky cart bar + bottom sheet -->
    <div
        v-if="cart.length > 0"
        class="fixed inset-x-0 bottom-[calc(var(--tabbar-clearance)-0.25rem)] z-20 px-3 lg:hidden"
    >
        <button
            type="button"
            class="mx-auto flex w-full max-w-md items-center justify-between gap-3 rounded-full bg-linear-to-b from-brand-hi to-brand px-5 py-3.5 text-on-brand shadow-glow transition active:scale-[0.98]"
            @click="cartOpen = true"
        >
            <span class="flex items-center gap-2 text-sm font-medium">
                <AppIcon name="cart" :size="20" />
                {{ formatNumber(itemsCount) }} dona
            </span>
            <span class="flex items-center gap-1 text-base font-semibold">
                {{ formatMoney(total) }}
                <AppIcon name="chevron-right" :size="18" />
            </span>
        </button>
    </div>

    <AppModal v-model:open="cartOpen" title="Savatcha">
        <PosCart
            v-model:payment-method="form.payment_method"
            v-model:customer="customer"
            v-model:bonus-used="form.bonus_used"
            v-model:due-date="form.due_date"
            :customers-enabled="customersEnabled"
            :cashback-percent="cashbackPercent"
            :errors="form.errors"
            :lines="cart"
            :payment-methods="paymentMethods"
            :total="total"
            :processing="form.processing"
            :can-submit="canSubmit"
            :line-error="lineError"
            :general-error="form.errors.items"
            @quantity="changeQuantity"
            @price="changePrice"
            @remove="removeLine"
            @submit="submit"
        />
    </AppModal>
</template>
