<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,'.($id ?? 'NULL')],
            'content' => ['nullable'],
            'status' => ['required', 'in:draft,published'],
            'is_visible' => ['boolean'],
        ];
    }
}
