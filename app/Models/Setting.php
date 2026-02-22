<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations, Auditable;

    protected $fillable = ['key', 'value'];

    public $translatable = ['value'];
}
