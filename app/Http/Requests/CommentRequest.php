<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'content' => ['required', 'string', 'min:1', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
            'commentable_type' => ['required', 'string', Rule::in(['news', 'video_posts', 'comments'])],
            'commentable_id' => ['required', 'integer'],
        ];

        if ($this->has(['commentable_type', 'commentable_id'])) {
            $type = $this->input('commentable_type');
            $id = $this->input('commentable_id');
            
            if ($type === 'news') {
                $rules['commentable_id'][] = 'exists:news,id';
            } elseif ($type === 'video_posts') {
                $rules['commentable_id'][] = 'exists:video_posts,id';
            } elseif ($type === 'comments') {
                $rules['commentable_id'][] = 'exists:comments,id';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'content.max' => 'Comment can not being more then 5000 символов',
            'commentable_type.in' => 'Bad type of commentable type',
        ];
    }
}