<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import AppLogo from '@/components/AppLogo.vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import SubscriptionNotice from '@/components/SubscriptionNotice.vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import ToastHost from '@/components/ToastHost.vue';
import { useConfirm } from '@/composables/useConfirm';
import { activeHref, navigationFor } from '@/layouts/navigation';
import { logout } from '@/routes';

const page = usePage();
const confirm = useConfirm();

const user = computed(() => page.props.auth.user);
const items = computed(() => navigationFor(user.value?.role));
const active = computed(() => activeHref(items.value, page.url));

const subtitle = computed(() =>
    user.value?.role === 'seller' && user.value.shop
        ? user.value.shop.name
        : user.value?.role_label,
);

async function signOut(): Promise<void> {
    const confirmed = await confirm({
        title: 'Tizimdan chiqish',
        message: 'Haqiqatan ham chiqmoqchimisiz?',
        confirmText: 'Chiqish',
        danger: true,
    });

    if (confirmed) {
        router.post(logout.url());
    }
}
</script>

<template>
    <div class="min-h-dvh lg:flex">
        <!-- Desktop sidebar: a floating glass panel -->
        <aside class="sticky top-0 hidden h-dvh w-72 shrink-0 p-3 lg:block">
            <div class="flex h-full flex-col rounded-[2rem] glass">
                <div class="px-5 pt-6 pb-5">
                    <AppLogo />
                </div>
                <nav
                    class="flex flex-1 flex-col gap-1 overflow-y-auto px-3"
                    aria-label="Asosiy"
                >
                    <Link
                        v-for="item in items"
                        :key="item.href"
                        :href="item.href"
                        prefetch
                        class="flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-medium transition duration-200"
                        :class="
                            active === item.href
                                ? 'bg-surface-raised font-semibold text-brand-strong shadow-card'
                                : 'text-ink-soft hover:bg-surface-muted hover:text-ink'
                        "
                        :aria-current="
                            active === item.href ? 'page' : undefined
                        "
                    >
                        <span
                            class="flex size-8 items-center justify-center rounded-xl transition"
                            :class="
                                active === item.href
                                    ? 'bg-linear-to-b from-brand-hi to-brand text-on-brand shadow-glow'
                                    : 'bg-surface-muted'
                            "
                        >
                            <AppIcon :name="item.icon" :size="18" />
                        </span>
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="flex flex-col gap-2 p-3">
                    <ThemeToggle />
                    <div
                        class="flex items-center gap-3 rounded-2xl bg-surface-muted px-2.5 py-2"
                    >
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-linear-to-b from-brand-hi to-brand text-sm font-semibold text-on-brand shadow-glow"
                        >
                            {{ user?.first_name?.charAt(0) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink">
                                {{ user?.full_name }}
                            </p>
                            <p class="truncate text-xs text-ink-muted">
                                {{ subtitle }}
                            </p>
                        </div>
                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-full text-ink-muted transition hover:bg-danger-soft hover:text-danger"
                            aria-label="Chiqish"
                            @click="signOut"
                        >
                            <AppIcon name="logout" :size="18" />
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <!-- Mobile top bar: a floating glass capsule -->
            <header
                class="sticky top-0 z-30 px-3 pt-[max(0.75rem,env(safe-area-inset-top))] lg:hidden"
            >
                <div
                    class="flex items-center justify-between gap-3 rounded-full glass py-1.5 pr-1.5 pl-2"
                >
                    <AppLogo compact />
                    <div class="flex min-w-0 items-center gap-0.5">
                        <div class="mr-1.5 min-w-0 text-right">
                            <p
                                class="truncate text-sm leading-tight font-medium text-ink"
                            >
                                {{ user?.full_name }}
                            </p>
                            <p
                                class="truncate text-xs leading-tight text-ink-muted"
                            >
                                {{ subtitle }}
                            </p>
                        </div>
                        <ThemeToggle compact />
                        <button
                            type="button"
                            class="flex size-10 items-center justify-center rounded-full text-ink-muted transition hover:bg-danger-soft hover:text-danger"
                            aria-label="Chiqish"
                            @click="signOut"
                        >
                            <AppIcon name="logout" :size="20" />
                        </button>
                    </div>
                </div>
            </header>

            <main
                class="mx-auto w-full max-w-6xl flex-1 px-4 pt-4 pb-[calc(var(--tabbar-clearance)+1.5rem)] sm:px-6 lg:px-8 lg:pt-8 lg:pb-12"
            >
                <SubscriptionNotice />
                <slot />
            </main>
        </div>

        <!-- Mobile tab bar: a floating glass capsule like iOS -->
        <nav
            class="fixed inset-x-3 bottom-(--tabbar-bottom) z-30 mx-auto max-w-md rounded-full glass p-1.5 lg:hidden"
            aria-label="Asosiy"
        >
            <div
                class="grid"
                :style="{
                    gridTemplateColumns: `repeat(${items.length}, minmax(0, 1fr))`,
                }"
            >
                <Link
                    v-for="item in items"
                    :key="item.href"
                    :href="item.href"
                    prefetch
                    class="flex min-w-0 flex-col items-center gap-0.5 rounded-full px-1 py-1.5 text-[10px] font-medium transition duration-200 active:scale-95"
                    :class="
                        active === item.href
                            ? 'bg-brand-soft text-brand-strong'
                            : 'text-ink-muted'
                    "
                    :aria-current="active === item.href ? 'page' : undefined"
                >
                    <AppIcon :name="item.icon" :size="22" />
                    <span class="w-full truncate text-center">
                        {{ item.label }}
                    </span>
                </Link>
            </div>
        </nav>

        <ToastHost />
        <ConfirmDialog />
    </div>
</template>
