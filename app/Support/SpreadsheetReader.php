<?php

namespace App\Support;

use InvalidArgumentException;
use SimpleXMLElement;
use SplFileObject;
use ZipArchive;

/**
 * Reads the first sheet of an .xlsx file, or a .csv file, into rows of cell strings.
 * Covers what product imports need without an extra package.
 */
class SpreadsheetReader
{
    /**
     * @return list<list<string>>
     *
     * @throws InvalidArgumentException When the file cannot be read.
     */
    public function read(string $path, string $extension): array
    {
        return match (strtolower($extension)) {
            'xlsx' => $this->readXlsx($path),
            'csv', 'txt' => $this->readCsv($path),
            default => throw new InvalidArgumentException('Faqat .xlsx yoki .csv fayl yuklang.'),
        };
    }

    /**
     * @return list<list<string>>
     */
    private function readXlsx(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException("Excel faylni ochib bo'lmadi.");
        }

        try {
            $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');

            if ($sheet === false) {
                throw new InvalidArgumentException('Excel faylda varaq topilmadi.');
            }

            $sharedStrings = $this->sharedStrings($zip->getFromName('xl/sharedStrings.xml') ?: null);
        } finally {
            $zip->close();
        }

        $rows = [];

        foreach ($this->parseXml($sheet)->sheetData->row ?? [] as $row) {
            $cells = [];

            foreach ($row->c as $cell) {
                $cells[$this->columnIndex((string) $cell['r'])] = $this->cellValue($cell, $sharedStrings);
            }

            $rows[] = $this->fillGaps($cells);
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private function sharedStrings(?string $xml): array
    {
        if ($xml === null) {
            return [];
        }

        $strings = [];

        foreach ($this->parseXml($xml)->si as $item) {
            // Rich text keeps its pieces in <r><t>, plain text in <t>.
            $strings[] = isset($item->t) ? (string) $item->t : implode('', array_map('strval', $item->xpath('.//*[local-name()="t"]') ?: []));
        }

        return $strings;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): string
    {
        return trim(match ((string) $cell['t']) {
            's' => $sharedStrings[(int) $cell->v] ?? '',
            'inlineStr' => (string) $cell->is->t,
            default => (string) $cell->v,
        });
    }

    /**
     * "C12" -> 2 (zero-based column of the cell reference).
     */
    private function columnIndex(string $reference): int
    {
        $letters = preg_replace('/\d+/', '', strtoupper($reference)) ?? '';
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = $index * 26 + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    /**
     * @param  array<int, string>  $cells
     * @return list<string>
     */
    private function fillGaps(array $cells): array
    {
        if ($cells === []) {
            return [];
        }

        return array_values(array_replace(array_fill(0, max(array_keys($cells)) + 1, ''), $cells));
    }

    private function parseXml(string $xml): SimpleXMLElement
    {
        $element = simplexml_load_string($xml, options: LIBXML_NONET);

        if ($element === false) {
            throw new InvalidArgumentException('Excel fayl buzilgan.');
        }

        return $element;
    }

    /**
     * Excel saves CSV with ";" in many locales, so the delimiter is taken from the first line.
     *
     * @return list<list<string>>
     */
    private function readCsv(string $path): array
    {
        $file = new SplFileObject($path);
        $firstLine = (string) $file->fgets();
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        $file->rewind();

        $rows = [];

        while (($row = $file->fgetcsv($delimiter, escape: '')) !== false) {
            if ($row === [null]) {
                continue;
            }

            $rows[] = array_values(array_map(fn (?string $value): string => trim((string) $value), $row));
        }

        if ($rows !== []) {
            $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]) ?? $rows[0][0];
        }

        return $rows;
    }
}
