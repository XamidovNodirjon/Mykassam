import { cn } from '@/lib/utils';

/**
 * Shared look of text-like controls (inputs, selects). 16px text prevents iOS zoom on focus.
 */
export function inputClass(hasError = false, extra?: string): string {
    return cn(
        'h-11 w-full rounded-xl border bg-field px-3.5 text-base text-ink shadow-[inset_0_1px_2px_rgb(0_0_0/0.04)] placeholder:text-ink-muted transition outline-none read-only:bg-surface-muted focus:border-brand focus:bg-surface-raised focus:ring-4 focus:ring-brand/15',
        hasError ? 'border-danger' : 'border-line',
        extra,
    );
}
