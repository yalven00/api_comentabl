<?php

namespace App\Repositories;

use App\Models\VideoPost;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class VideoPostRepository extends BaseRepository
{
    public function __construct(VideoPost $model)
    {
        parent::__construct($model);
    }

    public function getAll(int $perPage = 15): CursorPaginator
    {
        $query = $this->model->latest();
        return $this->cursorPaginate($query, $perPage);
    }

    public function create(array $data): VideoPost
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    public function update(VideoPost $videoPost, array $data): VideoPost
    {
        $videoPost->update($data);
        return $videoPost;
    }

    public function delete(VideoPost $videoPost): bool
    {
        return DB::transaction(function () use ($videoPost) {
            $videoPost->comments()->delete();    
            return $videoPost->delete();
        });
    }
   
   public function getWithComments(string $slug): ?VideoPost
   {
    return $this->model
        ->with(['comments' => function ($query) {
            $query->whereNull('parent_id')
                ->with(['user:id,name', 'replies' => function ($query) {
                    $query->with(['user:id,name'])
                        ->orderBy('cursor_sort')
                        ->limit(3);
                }])
                ->orderBy('cursor_sort')
                ->limit(10);
        }])
        ->where('slug', $slug)
        ->first();
   }
}