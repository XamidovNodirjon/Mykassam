<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppModal from '@/components/ui/AppModal.vue';
import { formatMoney, formatPhone } from '@/lib/format';
import { telegramUrl } from '@/lib/support';
import { logout } from '@/routes';

/**
 * Payment reminder for expired subscriptions: a modal that cannot be closed when the user has
 * nothing left to work with, otherwise a banner above the page.
 */
const page = usePage();

const notice = computed(() => page.props.subscription);
const support = computed(() => page.props.support);
const isSeller = computed(() => page.props.auth.user?.role === 'seller');
const shopNames = computed(() =>
    notice.value?.shops.map((shop) => `"${shop.name}"`).join(', '),
);
</script>

<template>
    <div v-if="notice">
        <div
            v-if="!notice.locked"
            class="mb-4 flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3 text-sm text-danger backdrop-blur-md"
        >
            <AppIcon name="alert" :size="20" class="shrink-0" />
            <p>
                {{ shopNames }} obunasi tugagan, bu do'konda amallar bloklangan.
                <template v-if="support.telegram">
                    Tarifni tanlash uchun admin:
                    <a
                        :href="telegramUrl(support.telegram)"
                        target="_blank"
                        rel="noopener"
                        class="font-semibold underline"
                    >
                        @{{ support.telegram }}
                    </a>
                </template>
            </p>
        </div>

        <AppModal
            :open="notice.locked"
            :dismissible="false"
            title="Obuna muddati tugadi"
        >
            <div class="flex flex-col gap-4">
                <p class="text-sm text-ink-soft">
                    <template v-if="isSeller">
                        {{ shopNames }} do'konining obunasi tugagan. Savdoni
                        davom ettirish uchun do'kon egasiga murojaat qiling.
                    </template>
                    <template v-else>
                        {{ shopNames }} uchun muddat tugadi. Ma'lumotlaringiz
                        saqlangan. Ishni davom ettirish uchun
                        <span class="font-semibold text-ink"
                            >tarifni tanlang</span
                        >
                        va to'lov uchun admin bilan bog'laning.
                    </template>
                </p>

                <ul v-if="!isSeller" class="grid gap-2">
                    <li
                        v-for="plan in notice.plans"
                        :key="plan.id"
                        class="rounded-xl bg-surface-muted px-4 py-3"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-semibold text-ink">
                                {{ plan.name }}
                            </span>
                            <span class="text-sm font-medium text-ink-soft">
                                {{ formatMoney(plan.price) }} / oy
                            </span>
                        </div>
                        <p
                            v-if="plan.features.length"
                            class="mt-1 text-xs text-ink-muted"
                        >
                            + {{ plan.features.join(', ') }}
                        </p>
                    </li>
                </ul>

                <template v-if="!isSeller">
                    <a
                        v-if="support.telegram"
                        :href="telegramUrl(support.telegram)"
                        target="_blank"
                        rel="noopener"
                        class="flex h-13 items-center justify-center gap-2 rounded-full bg-linear-to-b from-brand-hi to-brand px-5 font-medium text-on-brand shadow-glow transition hover:brightness-110"
                    >
                        <AppIcon name="send" :size="18" />
                        Admin bilan bog'lanish: @{{ support.telegram }}
                    </a>
                    <a
                        v-if="support.phone"
                        :href="`tel:+${support.phone}`"
                        class="text-center text-sm font-medium text-brand"
                    >
                        yoki qo'ng'iroq: {{ formatPhone(support.phone) }}
                    </a>
                </template>

                <AppButton
                    variant="secondary"
                    icon="logout"
                    block
                    @click="router.post(logout.url())"
                >
                    Chiqish
                </AppButton>
            </div>
        </AppModal>
    </div>
</template>
