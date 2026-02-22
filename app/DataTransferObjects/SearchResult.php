<?php

namespace App\DataTransferObjects;

class SearchResult
{
    public function __construct(
        public string $title,
        public string $snippet,
        public string $url,
        public string $type,
        public ?string $image = null,
        public ?string $date = null,
    ) {}
}
