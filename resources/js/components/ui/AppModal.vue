<script setup lang="ts">
import { onBeforeUnmount, watch } from 'vue';
import AppIcon from '@/components/AppIcon.vue';

/**
 * Bottom sheet on phones, centered dialog on larger screens.
 * A non-dismissible modal has no close button and ignores Escape and backdrop clicks.
 */
const props = withDefaults(
    defineProps<{
        title: string;
        dismissible?: boolean;
    }>(),
    { dismissible: true },
);

const open = defineModel<boolean>('open', { default: false });

function close(): void {
    if (props.dismissible) {
        open.value = false;
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        close();
    }
}

watch(open, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';

    if (isOpen) {
        window.addEventListener('keydown', onKeydown);
    } else {
        window.removeEventListener('keydown', onKeydown);
    }
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-400 ease-spring"
            enter-from-class="opacity-0 [&>[role=dialog]]:translate-y-8 sm:[&>[role=dialog]]:translate-y-0 sm:[&>[role=dialog]]:scale-95"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0 [&>[role=dialog]]:translate-y-8 sm:[&>[role=dialog]]:translate-y-0 sm:[&>[role=dialog]]:scale-95"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-end justify-center bg-(--backdrop) backdrop-blur-[2px] sm:items-center sm:p-4"
                @click.self="close"
            >
                <div
                    role="dialog"
                    aria-modal="true"
                    :aria-label="props.title"
                    class="glass-strong pb-safe max-h-[92dvh] w-full overflow-y-auto rounded-t-[2rem] transition duration-400 ease-spring sm:max-w-lg sm:rounded-[2rem]"
                >
                    <div
                        class="mx-auto mt-2 h-1.5 w-10 rounded-full bg-ink-muted/30 sm:hidden"
                        aria-hidden="true"
                    />
                    <div
                        class="sticky top-0 z-10 flex items-center justify-between gap-3 border-b border-line px-5 py-4 backdrop-blur-xl"
                    >
                        <h2 class="text-base font-semibold text-ink">
                            {{ props.title }}
                        </h2>
                        <button
                            v-if="props.dismissible"
                            type="button"
                            class="-mr-1 flex size-8 items-center justify-center rounded-full bg-surface-muted text-ink-soft transition hover:text-ink"
                            aria-label="Yopish"
                            @click="close"
                        >
                            <AppIcon name="x" :size="20" />
                        </button>
                    </div>
                    <div class="p-5">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
