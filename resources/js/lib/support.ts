/**
 * "Nodirjon10_01" -> "https://t.me/Nodirjon10_01"
 */
export function telegramUrl(username: string): string {
    return `https://t.me/${username.replace(/^@/, '')}`;
}
