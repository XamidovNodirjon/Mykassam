import type { IconName } from '@/lib/icons';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as ownersIndex } from '@/routes/admin/owners';
import { index as shopTypesIndex } from '@/routes/admin/shop-types';
import { index as adminShopsIndex } from '@/routes/admin/shops';
import { index as smsIndex } from '@/routes/admin/sms';
import { index as subscriptionsIndex } from '@/routes/admin/subscriptions';
import { dashboard as ownerDashboard } from '@/routes/owner';
import { index as ownerSalesIndex } from '@/routes/owner/sales';
import { index as ownerShopsIndex } from '@/routes/owner/shops';
import { dashboard as sellerDashboard } from '@/routes/seller';
import {
    create as posCreate,
    index as sellerSalesIndex,
} from '@/routes/seller/sales';
import type { UserRole } from '@/types';

export type NavItem = {
    label: string;
    href: string;
    icon: IconName;
    /** Exact match only (dashboards), otherwise any nested URL activates the item. */
    exact?: boolean;
};

export function navigationFor(role: UserRole | undefined): NavItem[] {
    switch (role) {
        case 'admin':
            return [
                {
                    label: 'Statistika',
                    href: adminDashboard.url(),
                    icon: 'chart',
                    exact: true,
                },
                { label: 'Egalar', href: ownersIndex.url(), icon: 'users' },
                {
                    label: "Do'konlar",
                    href: adminShopsIndex.url(),
                    icon: 'store',
                },
                {
                    label: 'Turlar',
                    href: shopTypesIndex.url(),
                    icon: 'grid',
                },
                {
                    label: 'Tariflar',
                    href: subscriptionsIndex.url(),
                    icon: 'card',
                },
                { label: 'SMS', href: smsIndex.url(), icon: 'chat' },
            ];
        case 'owner':
            return [
                {
                    label: 'Statistika',
                    href: ownerDashboard.url(),
                    icon: 'chart',
                    exact: true,
                },
                {
                    label: "Do'konlar",
                    href: ownerShopsIndex.url(),
                    icon: 'store',
                },
                {
                    label: 'Savdolar',
                    href: ownerSalesIndex.url(),
                    icon: 'receipt',
                },
            ];
        case 'seller':
            return [
                { label: 'Sotish', href: posCreate.url(), icon: 'cart' },
                {
                    label: 'Statistika',
                    href: sellerDashboard.url(),
                    icon: 'chart',
                    exact: true,
                },
                {
                    label: 'Tarix',
                    href: sellerSalesIndex.url(),
                    icon: 'receipt',
                },
            ];
        default:
            return [];
    }
}

function matches(item: NavItem, path: string): boolean {
    if (item.exact) {
        return path === item.href;
    }

    return path === item.href || path.startsWith(`${item.href}/`);
}

/**
 * The most specific item matching the URL, so "/seller/sales/create" activates
 * "Sotish" rather than "Tarix" ("/seller/sales").
 */
export function activeHref(
    items: NavItem[],
    currentUrl: string,
): string | null {
    const path = currentUrl.split('?')[0];

    return (
        items
            .filter((item) => matches(item, path))
            .sort((a, b) => b.href.length - a.href.length)[0]?.href ?? null
    );
}
