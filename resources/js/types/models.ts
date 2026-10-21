import type { User } from './auth';

export type Option<T = string> = {
    value: T;
    label: string;
};

export type ShopType = {
    id: number;
    name: string;
    is_active?: boolean;
    shops_count?: number;
};

export type Shop = {
    id: number;
    name: string;
    address?: string | null;
    phone?: string | null;
    is_active?: boolean;
    shop_type_id?: number;
    type?: ShopType;
    owner?: User;
    products_count?: number;
    sellers_count?: number;
    sales_count?: number;
    revenue?: number;
    profit?: number;
    cashback_percent?: number;
    plan?: Plan;
    features?: PlanFeature[];
    subscription_status?: SubscriptionStatus;
    subscription_status_label?: string;
    subscription_ends_at?: string | null;
};

export type PlanFeature =
    | 'customers'
    | 'debts'
    | 'product_import'
    | 'stock_control';

export type Plan = {
    id: number;
    slug: string;
    name: string;
    price: number;
    max_sellers: number | null;
    max_products: number | null;
    features: PlanFeature[];
    feature_labels: string[];
    is_active: boolean;
    sort_order: number;
    shops_count?: number;
};

export type SubscriptionStatus = 'trial' | 'active' | 'expired';

/**
 * Shared on every page while a shop the user works with has an expired subscription.
 */
export type SubscriptionNotice = {
    shops: { id: number; name: string }[];
    locked: boolean;
    plans: { id: number; name: string; price: number; features: string[] }[];
};

/**
 * Contacts for payments and plan upgrades, shared on every page.
 */
export type Support = {
    telegram: string | null;
    phone: string | null;
};

export type Product = {
    id: number;
    shop_id: number;
    name: string;
    barcode: string | null;
    cost_price: number;
    min_price: number;
    price: number;
    stock: number;
    min_stock: number;
    is_active: boolean;
    shop?: Shop;
};

export type SellableProduct = Pick<
    Product,
    'id' | 'name' | 'barcode' | 'min_price' | 'price' | 'stock'
>;

export type SaleItem = {
    id: number;
    product_id: number | null;
    product_name: string;
    quantity: number;
    unit_price: number;
    cost_price?: number;
    total: number;
};

export type Sale = {
    id: number;
    total_amount: number;
    total_cost?: number;
    profit?: number;
    items_count: number;
    payment_method: string;
    payment_method_label: string;
    bonus_used: number;
    bonus_earned: number;
    customer?: Customer;
    created_at: string;
    seller?: User;
    shop?: Shop;
    items?: SaleItem[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type StatisticsTotals = {
    revenue: number;
    cost?: number;
    profit?: number;
    sales_count: number;
    items_count: number;
    average_check: number;
};

export type DailyPoint = {
    date: string;
    revenue: number;
    profit?: number;
    sales_count: number;
};

export type TopProduct = {
    product_name: string;
    quantity: number;
    revenue: number;
};

export type StatisticsFilters = {
    period: 'today' | 'week' | 'month' | 'custom';
    from: string;
    to: string;
    shop_id: number | null;
};

export type Customer = {
    id: number;
    name: string;
    phone: string | null;
    bonus_balance?: number;
    debt?: number;
};

export type DebtStatus = 'open' | 'overdue' | 'paid';

export type Debt = {
    id: number;
    customer?: Customer;
    sale_id: number | null;
    amount: number;
    paid_amount: number;
    remaining: number;
    due_date: string;
    status: DebtStatus;
    status_label: string;
    note: string | null;
    reminded_at: string | null;
    created_at: string;
};

export type StockReceipt = {
    id: number;
    product_id: number | null;
    product_name: string;
    quantity: number;
    cost_price: number;
    total: number;
    user?: User;
    created_at: string;
};

export type ProductImportResult = {
    created: number;
    updated: number;
    errors: string[];
};

export type SmsMessage = {
    id: number;
    phone: string;
    purpose_label: string;
    status: 'pending' | 'sent' | 'delivered' | 'failed';
    status_label: string;
    error: string | null;
    created_at: string;
};

export type Credentials = {
    login: string;
    password: string;
};
