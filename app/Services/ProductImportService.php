<?php

namespace App\Services;

use App\DTOs\ProductImportResult;
use App\Models\Product;
use App\Models\Shop;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Support\SpreadsheetReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Bulk create or update products from a spreadsheet. A row matches an existing product by
 * barcode (or by name when it has none); invalid rows are skipped and reported.
 */
class ProductImportService
{
    /**
     * Column order of the template; the header row is optional.
     */
    public const COLUMNS = ['Nomi', 'Shtrix-kod', 'Tan narx', 'Minimal narx', 'Sotuv narxi', 'Qoldiq'];

    private const MAX_ROWS = 2000;

    public function __construct(
        private ProductRepositoryInterface $products,
        private ShopPlanGuard $guard,
        private SpreadsheetReader $reader,
    ) {}

    /**
     * @throws ValidationException When the file cannot be read or is empty.
     */
    public function import(Shop $shop, UploadedFile $file): ProductImportResult
    {
        $rows = $this->readRows($file);
        $errors = [];
        /** @var list<array<string, mixed>> $creates */
        $creates = [];
        /** @var list<array{product: Product, attributes: array<string, mixed>}> $updates */
        $updates = [];
        $seenKeys = [];

        foreach ($rows as $line => $row) {
            $validator = Validator::make($this->toAttributes($row), [
                'name' => ['required', 'string', 'max:255'],
                'barcode' => ['nullable', 'string', 'max:64'],
                'cost_price' => ['required', 'integer', 'min:0'],
                'min_price' => ['required', 'integer', 'min:0'],
                'price' => ['required', 'integer', 'gte:min_price'],
                'stock' => ['required', 'integer', 'min:0', 'max:10000000'],
            ], [
                'price.gte' => "sotuv narxi minimal narxdan kam bo'lishi mumkin emas",
            ], [
                'name' => 'nomi',
                'cost_price' => 'tan narx',
                'min_price' => 'minimal narx',
                'price' => 'sotuv narxi',
                'stock' => 'qoldiq',
            ]);

            if ($validator->fails()) {
                $errors[] = "{$line}-qator: ".$validator->errors()->first();

                continue;
            }

            /** @var array{name: string, barcode: string|null, cost_price: int, min_price: int, price: int, stock: int} $attributes */
            $attributes = $validator->validated();
            $key = $attributes['barcode'] ?? 'name:'.mb_strtolower($attributes['name']);

            if (isset($seenKeys[$key])) {
                $errors[] = "{$line}-qator: bu mahsulot faylda {$seenKeys[$key]}-qatorda ham bor.";

                continue;
            }

            $seenKeys[$key] = $line;
            $existing = $this->products->findForImport($shop, $attributes['barcode'], $attributes['name']);

            if ($existing === null) {
                $creates[] = $attributes;
            } else {
                $updates[] = ['product' => $existing, 'attributes' => $attributes];
            }
        }

        $this->guard->ensureCanAddProducts($shop, count($creates));

        DB::transaction(function () use ($shop, $creates, $updates): void {
            foreach ($creates as $attributes) {
                $this->products->create($shop, [...$attributes, 'is_active' => true]);
            }

            foreach ($updates as $update) {
                $this->products->update($update['product'], $update['attributes']);
            }
        });

        return new ProductImportResult(count($creates), count($updates), $errors);
    }

    /**
     * Data rows keyed by their line number in the file (1-based, as Excel shows them).
     *
     * @return array<int, list<string>>
     *
     * @throws ValidationException
     */
    private function readRows(UploadedFile $file): array
    {
        try {
            $rows = $this->reader->read($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['file' => $exception->getMessage()]);
        }

        $numbered = [];

        foreach ($rows as $index => $row) {
            $isHeader = $index === 0 && ! is_numeric($this->number($row[2] ?? ''));

            if (! $isHeader && implode('', $row) !== '') {
                $numbered[$index + 1] = $row;
            }
        }

        if ($numbered === []) {
            throw ValidationException::withMessages(['file' => 'Faylda mahsulotlar topilmadi.']);
        }

        if (count($numbered) > self::MAX_ROWS) {
            throw ValidationException::withMessages(['file' => 'Bir faylda '.self::MAX_ROWS." tagacha mahsulot bo'lishi mumkin."]);
        }

        return $numbered;
    }

    /**
     * @param  list<string>  $row
     * @return array<string, string|null>
     */
    private function toAttributes(array $row): array
    {
        $barcode = trim($row[1] ?? '');

        return [
            'name' => trim($row[0] ?? ''),
            'barcode' => $barcode === '' ? null : $barcode,
            'cost_price' => $this->number($row[2] ?? ''),
            'min_price' => $this->number($row[3] ?? ''),
            'price' => $this->number($row[4] ?? ''),
            'stock' => $this->number($row[5] ?? ''),
        ];
    }

    /**
     * "12 500", "12500.00" or "12,5" written by Excel -> "12500" / "13". Non-numbers stay as is for validation.
     */
    private function number(string $value): string
    {
        $normalized = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], trim($value));

        return is_numeric($normalized) ? (string) (int) round((float) $normalized) : trim($value);
    }
}
