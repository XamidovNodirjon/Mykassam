<?php

namespace App\Enums;

/**
 * Extra modules a plan may unlock. Selling, products, sellers and statistics are always included.
 */
enum PlanFeature: string
{
    case Customers = 'customers';
    case Debts = 'debts';
    case ProductImport = 'product_import';
    case StockControl = 'stock_control';

    public function label(): string
    {
        return match ($this) {
            self::Customers => 'Mijozlar bazasi va keshbek',
            self::Debts => 'Qarz daftari va SMS eslatma',
            self::ProductImport => "Excel'dan mahsulot yuklash",
            self::StockControl => 'Kam qolgan mahsulot va kirim tarixi',
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $feature): array => ['value' => $feature->value, 'label' => $feature->label()],
            self::cases(),
        );
    }
}
