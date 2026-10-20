<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/components/AppIcon.vue';
import type { IconName } from '@/lib/icons';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
        size?: 'sm' | 'md' | 'lg';
        href?: string;
        type?: 'button' | 'submit';
        icon?: IconName;
        loading?: boolean;
        disabled?: boolean;
        block?: boolean;
        class?: string;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        href: undefined,
        type: 'button',
        icon: undefined,
        class: undefined,
    },
);

const classes = computed(() =>
    cn(
        'inline-flex items-center justify-center gap-2 rounded-full font-medium transition duration-200 select-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand active:scale-[0.97] disabled:pointer-events-none disabled:opacity-50',
        {
            'bg-linear-to-b from-brand-hi to-brand text-on-brand shadow-glow hover:brightness-110':
                props.variant === 'primary',
            'border border-line bg-surface-raised/70 text-ink shadow-card backdrop-blur-md hover:bg-surface-raised':
                props.variant === 'secondary',
            'bg-linear-to-b from-danger-hi to-danger text-on-danger shadow-card hover:brightness-110':
                props.variant === 'danger',
            'text-ink-soft hover:bg-surface-muted hover:text-ink':
                props.variant === 'ghost',
            'h-9 px-3 text-sm': props.size === 'sm',
            'h-11 px-4 text-sm': props.size === 'md',
            'h-13 px-5 text-base': props.size === 'lg',
            'w-full': props.block,
        },
        props.class,
    ),
);
</script>

<template>
    <Link v-if="href" :href="href" :class="classes">
        <AppIcon v-if="icon" :name="icon" :size="18" />
        <slot />
    </Link>
    <button
        v-else
        :type="type"
        :class="classes"
        :disabled="disabled || loading"
    >
        <svg
            v-if="loading"
            class="size-4 animate-spin"
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
        >
            <circle
                cx="12"
                cy="12"
                r="9"
                stroke="currentColor"
                stroke-opacity="0.25"
                stroke-width="3"
            />
            <path
                d="M21 12a9 9 0 0 0-9-9"
                stroke="currentColor"
                stroke-width="3"
                stroke-linecap="round"
            />
        </svg>
        <AppIcon v-else-if="icon" :name="icon" :size="18" />
        <slot />
    </button>
</template>
