<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportController extends Controller
{
    public function store(Request $request, Shop $shop, ProductImportService $import): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'extensions:xlsx,csv'],
        ], [
            'file.extensions' => 'Faqat .xlsx yoki .csv fayl yuklang.',
        ], [
            'file' => 'fayl',
        ]);

        $result = $import->import($shop, $request->file('file'));

        Inertia::flash('importResult', $result->toArray());
        $this->toast("Yuklandi: {$result->created} ta yangi, {$result->updated} ta yangilandi.");

        return back();
    }

    /**
     * CSV template that opens in Excel; the BOM keeps Cyrillic and O'zbek letters readable.
     * The shop is bound so the route's "can:manage,shop" check sees the model.
     */
    public function template(Shop $shop): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'w');

            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ProductImportService::COLUMNS, ';', escape: '');
            fputcsv($output, ['Coca-Cola 1L', '4780000000011', '9000', '10000', '12000', '24'], ';', escape: '');
            fclose($output);
        }, 'mahsulotlar-shablon.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
