const numberFormatter = new Intl.NumberFormat('ru-RU', {
    maximumFractionDigits: 0,
});

const MONTHS = [
    'yan',
    'fev',
    'mar',
    'apr',
    'may',
    'iyn',
    'iyl',
    'avg',
    'sen',
    'okt',
    'noy',
    'dek',
];

/**
 * 1250000 -> "1 250 000"
 */
export function formatNumber(value: number | null | undefined): string {
    return numberFormatter.format(value ?? 0).replace(/ /g, ' ');
}

/**
 * 1250000 -> "1 250 000 so'm"
 */
export function formatMoney(value: number | null | undefined): string {
    return `${formatNumber(value)} so'm`;
}

/**
 * 1250000 -> "1.3 mln", used where space is tight (chart axes, tiles on phones).
 */
export function formatCompactMoney(value: number): string {
    const abs = Math.abs(value);

    if (abs >= 1_000_000_000) {
        return `${trimZero(value / 1_000_000_000)} mlrd`;
    }

    if (abs >= 1_000_000) {
        return `${trimZero(value / 1_000_000)} mln`;
    }

    if (abs >= 1_000) {
        return `${trimZero(value / 1_000)} ming`;
    }

    return String(value);
}

function trimZero(value: number): string {
    return value.toFixed(1).replace(/\.0$/, '');
}

/**
 * "2026-10-13" -> "13 okt"
 */
export function formatShortDate(value: string): string {
    const [, month, day] = value.slice(0, 10).split('-').map(Number);

    return `${day} ${MONTHS[month - 1]}`;
}

/**
 * ISO date-time -> "13 okt 2026, 14:05"
 */
export function formatDateTime(value: string): string {
    const date = new Date(value);
    const time = date.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });

    return `${date.getDate()} ${MONTHS[date.getMonth()]} ${date.getFullYear()}, ${time}`;
}

/**
 * "998901234567" -> "+998 90 123 45 67"
 */
export function formatPhone(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    const digits = value.replace(/\D/g, '');

    if (digits.length !== 12) {
        return value;
    }

    return `+${digits.slice(0, 3)} ${digits.slice(3, 5)} ${digits.slice(5, 8)} ${digits.slice(8, 10)} ${digits.slice(10, 12)}`;
}

/**
 * Local digits of a phone (without the 998 prefix): "998901234567" -> "901234567".
 */
export function localPhoneDigits(value: string | null | undefined): string {
    const digits = (value ?? '').replace(/\D/g, '');

    return digits.startsWith('998') ? digits.slice(3, 12) : digits.slice(0, 9);
}
