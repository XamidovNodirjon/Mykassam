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

const appearance = ref<Appearance>(current());

/**
 * App-wide light / dark theme. Light is the default; "system" follows the device setting.
 * The choice is kept per browser in localStorage.
 */
export function useAppearance() {
    function setAppearance(value: Appearance): void {
        appearance.value = value;
        document.documentElement.dataset.theme = value;

        try {
            localStorage.setItem(storageKey, value);
        } catch {
            // Storage can be blocked (private mode); the theme still applies for this visit.
        }
    }

    function cycleAppearance(): void {
        const index = appearanceOptions.findIndex(
            (option) => option.value === appearance.value,
        );

        setAppearance(
            appearanceOptions[(index + 1) % appearanceOptions.length].value,
        );
    }

    return { appearance, setAppearance, cycleAppearance };
}
