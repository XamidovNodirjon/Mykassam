<?php

use App\Models\Plan;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\UploadedFile;

/**
 * Build a minimal .xlsx (first sheet, inline strings) the way Excel stores it.
 *
 * @param  list<list<string|int>>  $rows
 */
function xlsxUpload(array $rows): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'xlsx');
    $sheetRows = '';

    foreach ($rows as $rowIndex => $row) {
        $cells = '';

        foreach ($row as $columnIndex => $value) {
            $reference = chr(65 + $columnIndex).($rowIndex + 1);
            $cells .= is_int($value)
                ? "<c r=\"{$reference}\"><v>{$value}</v></c>"
                : "<c r=\"{$reference}\" t=\"inlineStr\"><is><t>{$value}</t></is></c>";
        }

        $sheetRows .= '<row r="'.($rowIndex + 1)."\">{$cells}</row>";
    }

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::OVERWRITE);
    $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$sheetRows.'</sheetData></worksheet>');
    $zip->close();

    return new UploadedFile($path, 'mahsulotlar.xlsx', test: true);
}

it('creates new products and updates existing ones from an Excel file', function () {
    $shop = Shop::factory()->create();
    $existing = Product::factory()->for($shop)->create(['barcode' => '4780000000011', 'stock' => 3]);

    $response = $this->actingAs($shop->owner)->post(route('owner.shops.products.import', $shop), [
        'file' => xlsxUpload([
            ['Nomi', 'Shtrix-kod', 'Tan narx', 'Minimal narx', 'Sotuv narxi', 'Qoldiq'],
            ['Coca-Cola 1L', '4780000000011', 9_000, 10_000, 12_000, 24],
            ['Non', '', 3_000, 3_500, 4_000, 50],
        ]),
    ]);

    $response->assertRedirect()->assertInertiaFlash('importResult', ['created' => 1, 'updated' => 1, 'errors' => []]);
    expect($existing->fresh())->name->toBe('Coca-Cola 1L')->stock->toBe(24)
        ->and($shop->products()->where('name', 'Non')->sole()->price)->toBe(4_000);
});

it('imports a CSV saved by Excel and reports the rows it skipped', function () {
    $shop = Shop::factory()->create();
    $csv = "\xEF\xBB\xBFNomi;Shtrix-kod;Tan narx;Minimal narx;Sotuv narxi;Qoldiq\n"
        ."Choy;;12 000;13 000;15 000;10\n"
        ."Shakar;;8000;9000;8500;5\n"
        .";;1;1;1;1\n";

    $response = $this->actingAs($shop->owner)->post(route('owner.shops.products.import', $shop), [
        'file' => UploadedFile::fake()->createWithContent('mahsulotlar.csv', $csv),
    ]);

    $response->assertInertiaFlash('importResult.created', 1)
        ->assertInertiaFlash('importResult.errors', [
            "3-qator: sotuv narxi minimal narxdan kam bo'lishi mumkin emas",
            '4-qator: nomi kiritilishi shart.',
        ]);
    expect($shop->products()->sole()->cost_price)->toBe(12_000);
});

it('stops an import that would go over the plan product limit', function () {
    $plan = Plan::factory()->create(['max_products' => 2, 'features' => ['product_import']]);
    $shop = Shop::factory()->onPlan($plan)->create();
    Product::factory()->for($shop)->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.products.import', $shop), [
        'file' => xlsxUpload([['A', '', 1, 1, 1, 1], ['B', '', 1, 1, 1, 1]]),
    ])->assertRedirect();

    expect($shop->products()->count())->toBe(1);
});

it('needs a plan with Excel import', function () {
    $shop = Shop::factory()->subscribed('start')->create();

    $this->actingAs($shop->owner)->post(route('owner.shops.products.import', $shop), [
        'file' => xlsxUpload([['A', '', 1, 1, 1, 1]]),
    ])->assertRedirect();

    expect($shop->products()->count())->toBe(0);
});

it('downloads a template with the expected columns', function () {
    $shop = Shop::factory()->create();

    $response = $this->actingAs($shop->owner)->get(route('owner.shops.products.import-template', $shop));

    $response->assertOk()->assertDownload('mahsulotlar-shablon.csv');
    expect($response->streamedContent())->toContain('Nomi;Shtrix-kod;"Tan narx";"Minimal narx";"Sotuv narxi";Qoldiq');
});
