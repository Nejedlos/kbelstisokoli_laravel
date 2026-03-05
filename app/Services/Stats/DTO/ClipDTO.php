<?php

namespace App\Services\Stats\DTO;

/**
 * Representuje vyříznutý fragment (clip) z HTML stránky pro AI analýzu.
 */
class ClipDTO
{
    public function __construct(
        public readonly string $id,           // team_header, roster_table, matches_list, boxscore_home, etc.
        public readonly string $htmlFragment, // Sanitizovaný HTML výřez (subtree)
        public readonly string $textHint,     // Popis (např. "Roster table")
        public readonly array $links = [],    // Extrahované relevantní linky ['href' => '/hrac/123', 'text' => 'Jan Novák']
        public readonly array $evidence = [], // Metadata (selectors, headers, row count, etc.)
        public readonly ?string $hash = null, // SHA256 fragmentu
    ) {}

    /**
     * Převede DTO na pole pro logování nebo API.
     */
    public function toArray(): array
    {
        return [
            'clip_id' => $this->id,
            'html_fragment' => $this->htmlFragment,
            'text_hint' => $this->textHint,
            'extracted_links' => $this->links,
            'evidence' => $this->evidence,
            'fragment_hash' => $this->hash ?? hash('sha256', $this->htmlFragment),
        ];
    }
}
