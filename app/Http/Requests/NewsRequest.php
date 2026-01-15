<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'slug' => ['required', 'string', 'max:255', 'unique:news,slug'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:news,slug,' . $this->route('news')];
        }

        return $rules;
    }
}