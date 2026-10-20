export type UserRole = 'admin' | 'owner' | 'seller';

export type User = {
    id: number;
    first_name: string;
    last_name: string | null;
    full_name: string;
    phone?: string | null;
    login?: string;
    role?: UserRole;
    role_label?: string;
    is_active?: boolean;
    shop_id?: number | null;
    shop?: { id: number; name: string };
    shops_count?: number;
    sales_count?: number;
    revenue?: number;
    profit?: number;
    created_at?: string;
};

export type Auth = {
    user: User | null;
};
