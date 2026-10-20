<script setup lang="ts">
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import CustomerPicker from '@/components/pos/CustomerPicker.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { inputClass } from '@/components/ui/input';
import MoneyField from '@/components/ui/MoneyField.vue';
import TextField from '@/components/ui/TextField.vue';
import { formatMoney, formatNumber } from '@/lib/format';
import type { CartLine } from '@/components/pos/types';
import type { Customer, Option } from '@/types';

/**
 * Cart body shared by the desktop side panel and the mobile bottom sheet.
 */
const props = defineProps<{
    lines: CartLine[];
    paymentMethods: Option[];
    /** Goods total before the bonus is taken off. */
    total: number;
    processing: boolean;
    canSubmit: boolean;
    lineError: (index: number) => string | undefined;
    generalError?: string;
    customersEnabled: boolean;
    cashbackPercent: number;
    errors: Partial<Record<'customer_id' | 'bonus_used' | 'due_date', string>>;
}>();

const paymentMethod = defineModel<string>('paymentMethod', { required: true });
const customer = defineModel<Customer | null>('customer', { required: true });
const bonusUsed = defineModel<number | null>('bonusUsed', { required: true });
const dueDate = defineModel<string>('dueDate', { required: true });

const isCredit = computed(() => paymentMethod.value === 'debt');
const payable = computed(() => props.total - (bonusUsed.value ?? 0));
const cashback = computed(() =>
    customer.value && !isCredit.value
        ? Math.floor((payable.value * props.cashbackPercent) / 100)
        : 0,
);
const today = new Date().toISOString().slice(0, 10);

const emit = defineEmits<{
    quantity: [line: CartLine, delta: number];
    price: [line: CartLine, value: string];
    remove: [line: CartLine];
    submit: [];
}>();
</script>

<template>
    <div class="flex flex-col gap-4">
        <p
            v-if="lines.length === 0"
            class="flex flex-col items-center gap-2 py-8 text-center text-sm text-ink-muted"
        >
            <AppIcon name="cart" :size="28" />
            Mahsulot tanlang
        </p>

        <ul v-else class="flex flex-col gap-3">
            <li
                v-for="(line, index) in lines"
                :key="line.product.id"
                class="rounded-xl border p-3"
                :class="
                    line.unitPrice < line.product.min_price || lineError(index)
                        ? 'border-danger'
                        : 'border-line'
                "
            >
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-medium text-ink">
                        {{ line.product.name }}
                    </p>
                    <button
                        type="button"
                        class="-mt-1 -mr-1 flex size-8 shrink-0 items-center justify-center rounded-lg text-ink-muted hover:text-danger"
                        aria-label="Olib tashlash"
                        @click="emit('remove', line)"
                    >
                        <AppIcon name="x" :size="16" />
                    </button>
                </div>

                <div class="mt-2 flex items-center gap-2">
                    <div
                        class="flex items-center rounded-xl border border-line"
                    >
                        <button
                            type="button"
                            class="flex size-10 items-center justify-center text-ink-soft hover:text-ink"
                            aria-label="Kamaytirish"
                            @click="emit('quantity', line, -1)"
                        >
                            <AppIcon name="minus" :size="16" />
                        </button>
                        <span
                            class="w-8 text-center text-sm font-semibold text-ink tabular-nums"
                        >
                            {{ line.quantity }}
                        </span>
                        <button
                            type="button"
                            class="flex size-10 items-center justify-center text-ink-soft hover:text-ink disabled:opacity-30"
                            aria-label="Ko'paytirish"
                            :disabled="line.quantity >= line.product.stock"
                            @click="emit('quantity', line, 1)"
                        >
                            <AppIcon name="plus" :size="16" />
                        </button>
                    </div>
                    <div class="relative flex-1">
                        <input
                            :value="formatNumber(line.unitPrice)"
                            type="text"
                            inputmode="numeric"
                            :aria-label="`${line.product.name} narxi`"
                            :class="
                                inputClass(
                                    line.unitPrice < line.product.min_price,
                                    'h-10 pr-12 text-right tabular-nums',
                                )
                            "
                            @input="
                                emit(
                                    'price',
                                    line,
                                    ($event.target as HTMLInputElement).value,
                                )
                            "
                        />
                        <span
                            class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-xs text-ink-muted"
                        >
                            so'm
                        </span>
                    </div>
                </div>

                <p
                    v-if="line.unitPrice < line.product.min_price"
                    class="mt-2 text-xs text-danger"
                >
                    Minimal narx: {{ formatMoney(line.product.min_price) }}
                </p>
                <p
                    v-else-if="lineError(index)"
                    class="mt-2 text-xs text-danger"
                >
                    {{ lineError(index) }}
                </p>
                <p v-else class="mt-2 text-right text-xs text-ink-muted">
                    = {{ formatMoney(line.quantity * line.unitPrice) }}
                </p>
            </li>
        </ul>

        <template v-if="customersEnabled">
            <CustomerPicker v-model="customer" />
            <p v-if="errors.customer_id" class="-mt-2 text-sm text-danger">
                {{ errors.customer_id }}
            </p>
            <MoneyField
                v-if="customer && (customer.bonus_balance ?? 0) > 0"
                v-model="bonusUsed"
                label="Bonusdan to'lash"
                :hint="`Mavjud: ${formatMoney(customer.bonus_balance)}`"
                :error="errors.bonus_used"
            />
        </template>

        <fieldset>
            <legend class="mb-2 text-sm font-medium text-ink-soft">
                To'lov turi
            </legend>
            <div
                class="grid gap-2"
                :class="
                    paymentMethods.length > 3 ? 'grid-cols-2' : 'grid-cols-3'
                "
            >
                <label
                    v-for="method in paymentMethods"
                    :key="method.value"
                    class="flex cursor-pointer items-center justify-center rounded-xl border px-2 py-2.5 text-sm font-medium transition"
                    :class="
                        paymentMethod === method.value
                            ? 'border-brand bg-brand-soft text-brand-strong'
                            : 'border-line text-ink-soft'
                    "
                >
                    <input
                        v-model="paymentMethod"
                        type="radio"
                        name="payment_method"
                        class="sr-only"
                        :value="method.value"
                    />
                    {{ method.label }}
                </label>
            </div>
        </fieldset>

        <TextField
            v-if="isCredit"
            v-model="dueDate"
            type="date"
            label="Qarzni to'lash sanasi"
            :hint="`Shu kuni mijozga SMS eslatma boradi (bugun: ${today})`"
            :error="errors.due_date"
        />

        <p v-if="generalError" class="text-sm text-danger" role="alert">
            {{ generalError }}
        </p>

        <div class="flex flex-col gap-1 border-t border-line pt-4">
            <div
                v-if="bonusUsed"
                class="flex justify-between text-sm text-ink-muted"
            >
                <span>Bonus</span>
                <span class="tabular-nums">-{{ formatMoney(bonusUsed) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-ink-muted">
                    {{ isCredit ? 'Nasiyaga' : 'Jami' }}
                </span>
                <span class="text-2xl font-semibold text-ink tabular-nums">
                    {{ formatMoney(payable) }}
                </span>
            </div>
            <p v-if="cashback > 0" class="text-right text-xs text-brand">
                Mijozga keshbek: +{{ formatMoney(cashback) }}
            </p>
        </div>

        <AppButton
            size="lg"
            block
            icon="check"
            :disabled="!canSubmit"
            :loading="processing"
            @click="emit('submit')"
        >
            Sotish
        </AppButton>
    </div>
</template>
