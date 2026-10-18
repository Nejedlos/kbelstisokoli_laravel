<?php

namespace App\Exceptions;

use RuntimeException;

class DuplicateMatchException extends RuntimeException
{
    public function __construct(public readonly int $matchId)
    {
        parent::__construct(__('matches.validation.import_conflict', ['id' => $matchId]));
    }
}
