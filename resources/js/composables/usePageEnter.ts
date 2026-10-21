import { usePage } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { computed, onMounted, watch } from 'vue';

const staggeredBlocks = 8;

/**
 * Lets the top-level blocks of a page rise in one after another when a page opens. Only a new
 * path replays it: filtering, searching or paginating (query changes) keeps the page still.
 */
export function usePageEnter(container: Ref<HTMLElement | null>): void {
    const page = usePage();

    const path = computed(() => page.url.split('?')[0]);

    function play(): void {
        const element = container.value;

        if (
            element === null ||
            window.matchMedia('(prefers-reduced-motion: reduce)').matches
        ) {
            return;
        }

        Array.from(element.children)
            .slice(0, staggeredBlocks)
            .forEach((block, index) => {
                block.animate(
                    [
                        { opacity: 0, transform: 'translateY(0.75rem)' },
                        { opacity: 1, transform: 'none' },
                    ],
                    {
                        duration: 420,
                        delay: index * 50,
                        easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                        fill: 'backwards',
                    },
                );
            });
    }

    onMounted(play);
    watch(path, play, { flush: 'post' });
}
