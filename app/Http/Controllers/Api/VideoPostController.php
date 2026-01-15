<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\VideoPostRequest;
use App\Http\Resources\VideoPostResource;
use App\Models\VideoPost;
use App\Repositories\VideoPostRepository;
use Illuminate\Http\JsonResponse;

class VideoPostController extends ApiController
{
    public function __construct(
        private VideoPostRepository $videoPostRepository
    ) {}

    public function index(): JsonResponse
    {
        $videoPosts = $this->videoPostRepository->getAll();
        return $this->paginated($videoPosts, VideoPostResource::class);
    }

    public function store(VideoPostRequest $request): JsonResponse
    {
        $videoPost = $this->videoPostRepository->create($request->validated());
        return $this->success(new VideoPostResource($videoPost), 'Video post created successfully', 201);
    }

    public function show(string $slug): JsonResponse
    {
        $videoPost = $this->videoPostRepository->getWithComments($slug);
        
        if (!$videoPost) {
            return $this->error('Video post not found', 404);
        }

        return $this->success(new VideoPostResource($videoPost));
    }

    public function update(VideoPostRequest $request, VideoPost $videoPost): JsonResponse
    {
        $videoPost = $this->videoPostRepository->update($videoPost, $request->validated());
        return $this->success(new VideoPostResource($videoPost), 'Video post updated successfully');
    }

    public function destroy(VideoPost $videoPost): JsonResponse
    {
        $this->videoPostRepository->delete($videoPost);
        return $this->success(null, 'Video post deleted successfully');
    }
}