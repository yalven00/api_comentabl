<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function cursorPaginate(Builder $query, int $perPage = 15, array $columns = ['*'], string $cursorName = 'cursor'): CursorPaginator
    {
        return $query->cursorPaginate($perPage, $columns, $cursorName);
    }
}