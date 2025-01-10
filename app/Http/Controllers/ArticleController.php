<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class ArticleController extends Controller
{
    use ApiResponder;

    public function index(Request $request): \Illuminate\Http\Response|JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $request->validate([
                'keyword' => 'nullable|string|max:255',
                'category' => 'nullable|string|exists:articles,category',
                'source' => 'nullable|string|exists:articles,source',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'sort_by' => 'nullable|in:title,published_at,category',
                'sort_order' => 'nullable|in:asc,desc',
            ]);

            // Additional validation logic for handling conditional `start_date` and `end_date`
            if ($request->has('start_date') && $request->has('end_date')) {
                $request->validate([
                    'start_date' => ['sometimes', 'date', 'before_or_equal:end_date'],
                    'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
                ]);
            }

            // Create caching key
            $cacheKey = 'articles_' . md5(serialize($request->all()));

            // Check if caching has the data
            if (Cache::has($cacheKey)) {
                $articles = Cache::get($cacheKey);
            } else {
                // Caching does not have data, query it and store in cache
                $articles = Article::getArticles($request, $cacheKey);
            }
            return ArticleResource::collection($articles);
        } catch (ValidationException $exception) {
            return $this->sendError('Validation Error.', [$exception->errors()], 422);
        } catch (\Exception $e) {
            return $this->sendError('An unexpected error occurred', [$e->getMessage()], 500);
        }
    }

    public function show(Request $request, $id): JsonResponse
    {
        if (Cache::has('article_' . $id)) {
            $article = Cache::get('article_' . $id);
        } else {
            $article = Cache::remember('articles_' . $id, now()->addMinutes(10), function () use ($id) {
                return Article::findOrFail($id);
            });
        }
        return $this->sendSuccess('Article found.', ArticleResource::make($article));
    }
}
