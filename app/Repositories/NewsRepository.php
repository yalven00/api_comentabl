<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class NewsRepository extends BaseRepository
{
    public function __construct(News $model)
    {
        parent::__construct($model);
    }

    public function getAll(int $perPage = 15): CursorPaginator
    {
        $query = $this->model->latest();
        return $this->cursorPaginate($query, $perPage);
    }

    public function create(array $data): News
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    public function update(News $news, array $data): News
    {
        $news->update($data);
        return $news;
    }

    public function delete(News $news): bool
    {
        return DB::transaction(function () use ($news) {
            $news->comments()->delete();
            return $news->delete();
        });
    }



    public function getWithComments(string $slug): ?News 
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