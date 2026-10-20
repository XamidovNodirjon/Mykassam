<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import { telegramUrl } from '@/lib/support';

/**
 * Shown on a module page when the shop's plan does not include it. Existing records stay visible.
 */
defineProps<{
    feature: string;
    planName?: string;
}>();

const support = computed(() => usePage().props.support);
</script>

<template>
    <div
        class="mb-4 flex flex-col gap-3 rounded-2xl border border-warning/30 bg-warning-soft px-4 py-3 text-sm sm:flex-row sm:items-center"
    >
        <AppIcon name="lock" :size="20" class="shrink-0 text-warning" />
        <p class="flex-1 text-ink-soft">
            <span class="font-medium text-ink">"{{ feature }}"</span>
            {{ planName ? `${planName} tarifiga` : 'joriy tarifga' }} kirmaydi.
            Avvalgi ma'lumotlar saqlangan va ko'rinadi, yangisini qo'shish uchun
            tarifni yangilang.
        </p>
        <a
            v-if="support.telegram"
            :href="telegramUrl(support.telegram)"
            target="_blank"
            rel="noopener"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-full bg-linear-to-b from-brand-hi to-brand px-4 py-2 font-medium text-on-brand shadow-glow transition hover:brightness-110"
        >
            <AppIcon name="send" :size="16" />
            @{{ support.telegram }}
        </a>
    </div>
</template>
