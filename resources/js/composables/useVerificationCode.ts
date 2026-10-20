import { useHttp } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import type { RouteDefinition } from '@/wayfinder';

type CodeSentResponse = {
    data: { resend_after: number };
};

/**
 * Texts a verification code to a phone number and counts down until another one may be requested.
 */
export function useVerificationCode(endpoint: RouteDefinition<'post'>) {
    const http = useHttp<{ phone: string }, CodeSentResponse>({ phone: '' });
    const sent = ref(false);
    const resendIn = ref(0);
    let timer: ReturnType<typeof setInterval> | undefined;

    function startCountdown(seconds: number): void {
        clearInterval(timer);
        resendIn.value = seconds;
        timer = setInterval(() => {
            resendIn.value -= 1;

            if (resendIn.value <= 0) {
                clearInterval(timer);
            }
        }, 1000);
    }

    function send(phone: string): void {
        http.phone = phone;
        http.submit(endpoint, {
            onSuccess: (response) => {
                sent.value = true;
                startCountdown(response.data.resend_after);
            },
            onHttpException: (response) => {
                http.setError(
                    'phone',
                    response.status === 429
                        ? "Juda ko'p urinish. Birozdan so'ng qayta urinib ko'ring."
                        : "SMS yuborib bo'lmadi. Qayta urinib ko'ring.",
                );
            },
            onNetworkError: () => {
                http.setError('phone', 'Internet aloqasini tekshiring.');
            },
        }).catch(() => undefined);
    }

    /**
     * Back to the phone step, e.g. to fix a mistyped number.
     */
    function reset(): void {
        sent.value = false;
        http.clearErrors();
    }

    onBeforeUnmount(() => clearInterval(timer));

    return { http, sent, resendIn, send, reset };
}
