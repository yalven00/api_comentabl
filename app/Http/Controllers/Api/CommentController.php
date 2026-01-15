<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Repositories\CommentRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CommentController extends ApiController
{
    public function __construct(
        private CommentRepository $commentRepository
    ) {}

    public function index(string $type, int $id): JsonResponse
    {
        $validTypes = ['news', 'video_posts'];
        
        if (!in_array($type, $validTypes)) {
            return $this->error('Invalid content type', 400);
        }

        $comments = $this->commentRepository->getCommentsForContent($type, $id);
        return $this->paginated($comments, CommentResource::class);
    }

    public function store(CommentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        
        $comment = $this->commentRepository->create($data);
        return $this->success(new CommentResource($comment), 'Comment created successfully', 201);
    }

    public function show(Comment $comment): JsonResponse
    {
        $comment->load(['user', 'replies.user']);
        return $this->success(new CommentResource($comment));
    }

    public function replies(Comment $comment): JsonResponse
    {
        $replies = $this->commentRepository->getRepliesForComment($comment->id);
        return $this->paginated($replies, CommentResource::class);
    }

    public function update(CommentRequest $request, Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return $this->error('Unauthorized', 403);
        }

        $comment = $this->commentRepository->update($comment, $request->validated());
        return $this->success(new CommentResource($comment), 'Comment updated successfully');
    }

    public function destroy(Comment $comment): JsonResponse
    {
        if ($comment->user_id !== Auth::id()) {
            return $this->error('Unauthorized', 403);
        }

        $this->commentRepository->delete($comment);
        return $this->success(null, 'Comment deleted successfully');
    }
}