<?php

namespace App\Services\Stats\DTO;

class NormalizedRowDTO
{
    public function __construct(
        public array $values,
        public ?int $playerId = null,
        public ?int $teamId = null,
        public ?string $rowLabel = null,
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'values' => $this->values,
            'player_id' => $this->playerId,
            'team_id' => $this->teamId,
            'row_label' => $this->rowLabel,
            'metadata' => $this->metadata,
        ];
    }
}
