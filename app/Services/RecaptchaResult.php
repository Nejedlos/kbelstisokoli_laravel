<?php

namespace App\Services;

class RecaptchaResult
{
    public function __construct(
        public bool $passed,
        public ?float $score = null,
        public array $errorCodes = [],
        public array $raw = []
    ) {}
}
