<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import AppButton from '@/components/ui/AppButton.vue';
import { inputClass } from '@/components/ui/input';
import PhoneField from '@/components/ui/PhoneField.vue';
import TextField from '@/components/ui/TextField.vue';
import { formatMoney, formatPhone } from '@/lib/format';
import { index, store } from '@/routes/seller/customers';
import type { Customer } from '@/types';

/**
 * Find a regular customer by name or phone at the till, or sign a new one up on the spot.
 */
const customer = defineModel<Customer | null>({ default: null });

const term = ref('');
const results = ref<Customer[]>([]);
const creating = ref(false);

const lookup = useHttp<Record<string, never>, { data: Customer[] }>({});
const signUp = useHttp<
    { name: string; phone: string | null },
    { data: Customer }
>({
    name: '',
    phone: '',
});

let timer: ReturnType<typeof setTimeout> | undefined;

watch(term, (value) => {
    clearTimeout(timer);

    if (value.trim().length < 2) {
        results.value = [];

        return;
    }

    timer = setTimeout(async () => {
        const response = await lookup.get(
            index.url({ query: { search: value } }),
        );
        results.value = response?.data ?? [];
    }, 250);
});

onBeforeUnmount(() => clearTimeout(timer));

function choose(selected: Customer): void {
    customer.value = selected;
    term.value = '';
    results.value = [];
    creating.value = false;
}

function startCreating(): void {
    signUp.name = term.value;
    signUp.phone = '';
    signUp.clearErrors();
    creating.value = true;
}

async function create(): Promise<void> {
    const response = await signUp.post(store.url());

    if (response) {
        choose(response.data);
    }
}
</script>

<template>
    <div class="flex flex-col gap-2">
        <span class="text-sm font-medium text-ink-soft">Mijoz</span>

        <div
            v-if="customer"
            class="flex items-center gap-3 rounded-xl border border-brand bg-brand-soft px-3 py-2.5"
        >
            <AppIcon
                name="gift"
                :size="20"
                class="shrink-0 text-brand-strong"
            />
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-ink">
                    {{ customer.name }}
                </p>
                <p class="text-xs text-ink-soft">
                    {{ formatPhone(customer.phone) }} · bonus
                    {{ formatMoney(customer.bonus_balance) }}
                </p>
            </div>
            <button
                type="button"
                class="flex size-8 items-center justify-center rounded-lg text-ink-muted hover:text-danger"
                aria-label="Mijozni olib tashlash"
                @click="customer = null"
            >
                <AppIcon name="x" :size="16" />
            </button>
        </div>

        <form
            v-else-if="creating"
            class="flex flex-col gap-3 rounded-xl border border-line p-3"
            @submit.prevent="create"
        >
            <TextField
                v-model="signUp.name"
                label="Ism"
                autofocus
                :error="signUp.errors.name"
            />
            <PhoneField v-model="signUp.phone" :error="signUp.errors.phone" />
            <div class="grid grid-cols-2 gap-2">
                <AppButton variant="secondary" @click="creating = false">
                    Bekor qilish
                </AppButton>
                <AppButton type="submit" :loading="signUp.processing">
                    Qo'shish
                </AppButton>
            </div>
        </form>

        <div v-else class="relative">
            <input
                v-model="term"
                type="search"
                autocomplete="off"
                placeholder="Ism yoki telefon (ixtiyoriy)"
                aria-label="Mijozni qidirish"
                :class="inputClass(false, 'h-10 text-sm')"
            />
            <ul
                v-if="term.trim().length >= 2"
                class="absolute inset-x-0 top-full z-10 mt-1 overflow-hidden rounded-xl glass-strong"
            >
                <li v-for="result in results" :key="result.id">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm hover:bg-surface-muted"
                        @click="choose(result)"
                    >
                        <span class="truncate text-ink">{{ result.name }}</span>
                        <span class="shrink-0 text-xs text-ink-muted">
                            {{ formatPhone(result.phone) }}
                        </span>
                    </button>
                </li>
                <li>
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-brand hover:bg-surface-muted"
                        @click="startCreating"
                    >
                        <AppIcon name="plus" :size="16" />
                        Yangi mijoz qo'shish
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>
