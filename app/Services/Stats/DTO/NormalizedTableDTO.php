<?php

namespace App\Services\Stats\DTO;

class NormalizedTableDTO
{
    /**
     * @param  NormalizedRowDTO[]  $rows
     */
    public function __construct(
        public string $name,
        public array $columns, // Definice klíčů a popisků
        public array $rows,
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'columns' => $this->columns,
            'rows' => array_map(fn ($row) => $row->toArray(), $this->rows),
            'metadata' => $this->metadata,
        ];
    }
}
