<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Autorizace bude řešena přes role/oprávnění (Spatie). Zde ponecháno povolené.
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('post');

        return [
            'category_id' => ['nullable', 'exists:post_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug,'.($id ?? 'NULL')],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable'],
            'status' => ['required', 'in:draft,published'],
            'is_visible' => ['boolean'],
            'publish_at' => ['nullable', 'date'],
            'featured_image' => ['nullable', 'string', 'max:255'],
        ];
    }
}
