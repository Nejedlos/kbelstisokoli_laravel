<?php

namespace App\Services\Stats\DTO\CzBasketball;

class CzBasketballTeamPageDTO
{
    public function __construct(
        public array $team_header,
        public array $roster_table,
        public array $matches_table,
        public array $links,
        public array $warnings = []
    ) {}

    public function toArray(): array
    {
        return [
            'team_header' => $this->team_header,
            'roster_table' => $this->roster_table,
            'matches_table' => $this->matches_table,
            'links' => $this->links,
            'warnings' => $this->warnings,
        ];
    }
}
