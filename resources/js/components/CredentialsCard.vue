<script setup lang="ts">
import { ref } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import type { Credentials } from '@/types';

/**
 * Shows freshly generated seller credentials once, with copy buttons.
 */
const props = defineProps<{
    credentials: Credentials;
}>();

defineEmits<{ close: [] }>();

const copied = ref<'login' | 'password' | 'all' | null>(null);

async function copy(
    kind: 'login' | 'password' | 'all',
    text: string,
): Promise<void> {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = kind;
        setTimeout(() => (copied.value = null), 1500);
    } catch {
        copied.value = null;
    }
}

function copyAll(): void {
    void copy(
        'all',
        `Login: ${props.credentials.login}\nParol: ${props.credentials.password}`,
    );
}
</script>

<template>
    <div
        class="rounded-2xl border border-brand/30 bg-brand-soft p-4"
        role="status"
    >
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="font-semibold text-brand-strong">
                    Sotuvchi uchun kirish ma'lumotlari
                </p>
                <p class="mt-0.5 text-xs text-ink-soft">
                    Parol faqat hozir ko'rinadi — uni sotuvchiga yetkazing.
                </p>
            </div>
            <button
                type="button"
                class="text-ink-muted hover:text-ink"
                aria-label="Yopish"
                @click="$emit('close')"
            >
                <AppIcon name="x" :size="18" />
            </button>
        </div>

        <dl class="mt-3 grid gap-2 sm:grid-cols-2">
            <div
                v-for="field in ['login', 'password'] as const"
                :key="field"
                class="flex items-center justify-between gap-2 rounded-xl bg-surface px-3 py-2"
            >
                <div class="min-w-0">
                    <dt class="text-xs text-ink-muted">
                        {{ field === 'login' ? 'Login' : 'Parol' }}
                    </dt>
                    <dd
                        class="truncate font-mono text-base font-semibold text-ink"
                    >
                        {{ credentials[field] }}
                    </dd>
                </div>
                <button
                    type="button"
                    class="flex size-9 shrink-0 items-center justify-center rounded-lg text-ink-muted hover:bg-surface-muted hover:text-ink"
                    :aria-label="`${field === 'login' ? 'Login' : 'Parol'}ni nusxalash`"
                    @click="copy(field, credentials[field])"
                >
                    <AppIcon
                        :name="copied === field ? 'check' : 'copy'"
                        :size="18"
                    />
                </button>
            </div>
        </dl>

        <button
            type="button"
            class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-brand-strong"
            @click="copyAll"
        >
            <AppIcon :name="copied === 'all' ? 'check' : 'copy'" :size="16" />
            {{ copied === 'all' ? 'Nusxalandi' : 'Hammasini nusxalash' }}
        </button>
    </div>
</template>
