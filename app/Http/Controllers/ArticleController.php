<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @OA\Tag(
 *     name="Article",
 *     description="Operations related to articles"
 * )
 */
class ArticleController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Get(
     *     path="/api/articles",
     *     summary="Fetch articles with optional filters and pagination",
     *     description="Retrieve a list of articles based on various filters like keyword, category, source, date range, sorting, and ordering. Caching is used for optimization.",
     *     tags={"Articles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         description="Search keyword to filter articles by title or content",
     *         required=false,
     *         @OA\Schema(type="string", maxLength=255)
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter articles by category (array of category values)",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         description="Filter articles by source (array of source values)",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter articles published after or on this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter articles published before or on this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort articles by a specific field. Possible values: title, published_at, category",
     *         required=false,
     *         @OA\Schema(type="string", enum={"title", "published_at", "category"})
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order for the articles. Possible values: asc, desc",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ArticleResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No articles found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No articles found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Validation Error."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse|AnonymousResourceCollection
    {
        try {
            $request->validate([
                'keyword' => 'nullable|string|max:255',
                'category' => 'nullable|array|exists:articles,category',
                'source' => 'nullable|array|exists:articles,source',
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
            } else { // Caching does not have data, query it and store in cache
                $articles = Article::getArticles($request, $cacheKey);
            }
            if ($articles->isEmpty()) {
                return $this->sendError("No articles found.", [], 404);
            }
            return ArticleResource::collection($articles);
        } catch (ValidationException $exception) {
            return $this->sendError('Validation Error.', [$exception->errors()], 422);
        } catch (\Exception $e) {
            return $this->sendError('An unexpected error occurred', [$e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     summary="Fetch a single article by ID",
     *     description="Retrieve the details of a specific article by its ID. The response is cached for 10 minutes to optimize performance.",
     *     tags={"Articles"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the article to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Article found."),
     *             @OA\Property(property="data", ref="#/components/schemas/ArticleResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Article not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No query results for model [Article] with ID {id}.")
     *         )
     *     )
     * )
     */
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
