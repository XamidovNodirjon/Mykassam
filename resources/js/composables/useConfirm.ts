import { reactive } from 'vue';

type ConfirmOptions = {
    title: string;
    message?: string;
    confirmText?: string;
    danger?: boolean;
};

type ConfirmState = ConfirmOptions & {
    open: boolean;
    resolve: ((confirmed: boolean) => void) | null;
};

/**
 * Single app-wide confirmation dialog, rendered once by ConfirmDialog in the layout.
 */
export const confirmState = reactive<ConfirmState>({
    open: false,
    title: '',
    message: undefined,
    confirmText: undefined,
    danger: false,
    resolve: null,
});

export function settleConfirm(confirmed: boolean): void {
    confirmState.resolve?.(confirmed);
    confirmState.resolve = null;
    confirmState.open = false;
}

export function useConfirm(): (options: ConfirmOptions) => Promise<boolean> {
    return (options) =>
        new Promise<boolean>((resolve) => {
            settleConfirm(false);
            Object.assign(confirmState, {
                danger: false,
                message: undefined,
                confirmText: undefined,
                ...options,
                open: true,
                resolve,
            });
        });
}
