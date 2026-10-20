<?php

namespace App\DTOs;

final readonly class ProductImportResult
{
    /**
     * @param  list<string>  $errors  "N-qator: ..." messages of the rows that were skipped.
     */
    public function __construct(
        public int $created,
        public int $updated,
        public array $errors,
    ) {}

    /**
     * @return array{created: int, updated: int, errors: list<string>}
     */
    public function toArray(): array
    {
        return [
            'created' => $this->created,
            'updated' => $this->updated,
            'errors' => $this->errors,
        ];
    }
}
