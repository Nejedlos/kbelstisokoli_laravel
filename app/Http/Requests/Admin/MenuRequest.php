<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('menu');

        return [
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255', 'unique:menus,location,'.($id ?? 'NULL')],
        ];
    }
}
