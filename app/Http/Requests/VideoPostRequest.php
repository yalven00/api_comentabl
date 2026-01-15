<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoPostRequest extends FormRequest
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
            'video_url' => ['required', 'url'],
            'slug' => ['required', 'string', 'max:255', 'unique:video_posts,slug'],
            'duration' => ['required', 'integer', 'min:1'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['slug'] = ['required', 'string', 'max:255', 'unique:video_posts,slug,' . $this->route('video_post')];
        }

        return $rules;
    }
}