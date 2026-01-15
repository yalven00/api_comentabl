<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'comments' => $this->whenLoaded('comments', function () use ($request) {
                $comments = $this->comments;
                
                if ($request->has('cursor')) {
                    return CommentResource::collection($comments);
                }
                
                return [
                    'data' => CommentResource::collection($comments),
                    'meta' => [
                        'total' => $this->comments()->count(),
                        'root_comments' => $this->comments()->root()->count(),
                    ],
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}