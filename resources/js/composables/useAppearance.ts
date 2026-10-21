import { ref } from 'vue';
import type { IconName } from '@/lib/icons';

export type Appearance = 'light' | 'dark' | 'system';

export const appearanceOptions: {
    value: Appearance;
    label: string;
    icon: IconName;
}[] = [
    { value: 'light', label: "Yorug'", icon: 'sun' },
    { value: 'dark', label: "Qorong'i", icon: 'moon' },
    { value: 'system', label: 'Tizim', icon: 'monitor' },
];

const storageKey = 'appearance';

function isAppearance(value: unknown): value is Appearance {
    return value === 'light' || value === 'dark' || value === 'system';
}

/**
 * The theme the inline script in app.blade.php already put on <html data-theme>.
 */
function current(): Appearance {
    if (typeof document === 'undefined') {
        return 'light';
    }

    const theme = document.documentElement.dataset.theme;

    return isAppearance(theme) ? theme : 'light';
}

function schemeOf(value: Appearance): 'light' | 'dark' {
    return value === 'dark' ||
        (value === 'system' &&
            window.matchMedia('(prefers-color-scheme: dark)').matches)
        ? 'dark'
        : 'light';
}

const appearance = ref<Appearance>(current());

/**
 * Puts the theme on <html>. The scheme is set here as well (the inline script also resolves it)
 * so the colours are final before a view transition takes its snapshot.
 */
function apply(value: Appearance): void {
    const root = document.documentElement;

    appearance.value = value;
    root.dataset.theme = value;
    root.dataset.scheme = schemeOf(value);
}

/**
 * Flips the theme with every CSS transition paused, so all surfaces change in the same frame.
 * With an origin point and View Transitions support, the new theme grows out of that point.
 */
function switchTheme(
    value: Appearance,
    origin?: { x: number; y: number },
): void {
    const root = document.documentElement;
    const unchanged = schemeOf(value) === root.dataset.scheme;
    const reducedMotion = window.matchMedia(
        '(prefers-reduced-motion: reduce)',
    ).matches;

    root.dataset.themeSwitching = '';

    const resume = (): void => {
        requestAnimationFrame(() =>
            requestAnimationFrame(() => delete root.dataset.themeSwitching),
        );
    };

    if (
        unchanged ||
        reducedMotion ||
        origin === undefined ||
        typeof document.startViewTransition !== 'function'
    ) {
        apply(value);
        resume();

        return;
    }

    const radius = Math.hypot(
        Math.max(origin.x, window.innerWidth - origin.x),
        Math.max(origin.y, window.innerHeight - origin.y),
    );
    const transition = document.startViewTransition(() => apply(value));

    void transition.ready.then(() => {
        root.animate(
            {
                clipPath: [
                    `circle(0px at ${origin.x}px ${origin.y}px)`,
                    `circle(${radius}px at ${origin.x}px ${origin.y}px)`,
                ],
            },
            {
                duration: 500,
                easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                pseudoElement: '::view-transition-new(root)',
            },
        );
    });
    void transition.finished.finally(resume);
}

if (typeof window !== 'undefined') {
    // Another tab changed the theme: follow it here too.
    window.addEventListener('storage', (event) => {
        if (event.key === storageKey) {
            switchTheme(
                isAppearance(event.newValue) ? event.newValue : 'light',
            );
        }
    });
}

/**
 * The centre of the element that was clicked, where the theme reveal starts.
 */
function originOf(event?: MouseEvent): { x: number; y: number } | undefined {
    if (!(event?.currentTarget instanceof HTMLElement)) {
        return undefined;
    }

    const rect = event.currentTarget.getBoundingClientRect();

    return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
}

/**
 * App-wide light / dark theme. Light is the default; "system" follows the device setting.
 * The choice is kept per browser in localStorage.
 */
export function useAppearance() {
    function setAppearance(value: Appearance, event?: MouseEvent): void {
        if (value === appearance.value) {
            return;
        }

        switchTheme(value, originOf(event));

        try {
            localStorage.setItem(storageKey, value);
        } catch {
            // Storage can be blocked (private mode); the theme still applies for this visit.
        }
    }

    function cycleAppearance(event?: MouseEvent): void {
        const index = appearanceOptions.findIndex(
            (option) => option.value === appearance.value,
        );

        setAppearance(
            appearanceOptions[(index + 1) % appearanceOptions.length].value,
            event,
        );
    }

    return { appearance, setAppearance, cycleAppearance };
}
