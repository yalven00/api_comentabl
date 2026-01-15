<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\NewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Repositories\NewsRepository;
use Illuminate\Http\JsonResponse;

class NewsController extends ApiController
{
    public function __construct(
        private NewsRepository $newsRepository
    ) {}

    public function index(): JsonResponse
    {
        $news = $this->newsRepository->getAll();
        return $this->paginated($news, NewsResource::class);
    }

    public function store(NewsRequest $request): JsonResponse
    {
        $news = $this->newsRepository->create($request->validated());
        return $this->success(new NewsResource($news), 'News created successfully', 201);
    }

    public function show(string $slug): JsonResponse
    {
        $news = $this->newsRepository->getWithComments($slug);
        
        if (!$news) {
            return $this->error('News not found', 404);
        }

        return $this->success(new NewsResource($news));
    }

    public function update(NewsRequest $request, News $news): JsonResponse
    {
        $news = $this->newsRepository->update($news, $request->validated());
        return $this->success(new NewsResource($news), 'News updated successfully');
    }

    public function destroy(News $news): JsonResponse
    {
        $this->newsRepository->delete($news);
        return $this->success(null, 'News deleted successfully');
    }
}