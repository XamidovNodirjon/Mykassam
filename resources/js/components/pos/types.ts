import type { SellableProduct } from '@/types';

export type CartLine = {
    product: SellableProduct;
    quantity: number;
    unitPrice: number;
};
