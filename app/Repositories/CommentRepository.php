<?php

namespace App\Repositories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

class CommentRepository extends BaseRepository
{
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }

    public function getCommentsForContent(string $type, int $id, int $perPage = 15): CursorPaginator
    {
        $query = $this->model
            ->with(['user:id,name', 'replies' => function ($query) {
                $query->with(['user:id,name'])
                    ->orderBy('cursor_sort')
                    ->limit(5);
            }])
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->root()
            ->orderBy('cursor_sort');

        return $this->cursorPaginate($query, $perPage);
    }

    public function getRepliesForComment(int $commentId, int $perPage = 15): CursorPaginator
    {
        $query = $this->model
            ->with(['user:id,name', 'replies' => function ($query) {
                $query->with(['user:id,name'])
                    ->orderBy('cursor_sort')
                    ->limit(2);
            }])
            ->where('parent_id', $commentId)
            ->orderBy('cursor_sort');

        return $this->cursorPaginate($query, $perPage);
    }

    public function create(array $data): Comment
    {
        $commentableType = $data['commentable_type'];
        $commentableId = $data['commentable_id'];
    
        if ($commentableType === 'comments') {
            $parentComment = Comment::findOrFail($commentableId);
            $commentableType = $parentComment->commentable_type;
            $commentableId = $parentComment->commentable_id;
            $data['parent_id'] = $commentableId;
            $data['commentable_type'] = 'comments';
            $data['commentable_id'] = $parentComment->id;
        }

        return DB::transaction(function () use ($data) {
            $comment = $this->model->create($data);
            $comment->load(['user:id,name', 'parent']);
            
            return $comment;
        });
    }

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update($data);
        $comment->load(['user:id,name']);
        
        return $comment;
    }

    public function delete(Comment $comment): bool
    {
        return DB::transaction(function () use ($comment) {
            if ($comment->replies_count > 0) {
                $this->deleteRepliesRecursive($comment);
            }
            
            return $comment->delete();
        });
    }

    private function deleteRepliesRecursive(Comment $comment): void
    {
        foreach ($comment->replies as $reply) {
            if ($reply->replies_count > 0) {
                $this->deleteRepliesRecursive($reply);
            }
            $reply->delete();
        }
    }

    public function getCommentTree(int $commentId, int $depth = 3): ?Comment
    {
        return $this->model
            ->with(['user:id,name', 'replies' => function ($query) use ($depth) {
                if ($depth > 1) {
                    $query->withReplies($depth - 1);
                }
            }])
            ->find($commentId);
    }
}