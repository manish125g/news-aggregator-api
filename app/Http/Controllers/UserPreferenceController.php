<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\UserPreferenceResource;
use App\Models\Article;
use App\Models\UserPreference;
use App\Traits\ApiResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="User Preferences",
 *     description="Operations related to user preferences and Personalized Feeds"
 * )
 */
class UserPreferenceController extends Controller
{
    use ApiResponder;

    /**
     * @OA\Post(
     *     path="/api/user/preferences",
     *     summary="Store user preferences",
     *     description="Store the user's preferences for sources, categories, and authors.",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="sources", type="array", items=@OA\Items(type="string"), example={"source1", "source2"}),
     *             @OA\Property(property="categories", type="array", items=@OA\Items(type="string"), example={"category1", "category2"}),
     *             @OA\Property(property="authors", type="array", items=@OA\Items(type="string"), example={"author1", "author2"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferences updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Preference(s) updated successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/UserPreferenceResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or missing preferences",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="At least one source, authors or categories are required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Basic validation
            $request->validate([
                'sources' => 'nullable|array|exists:articles,source',
                'categories' => 'nullable|array|exists:articles,category',
                'authors' => 'nullable|array|exists:articles,author',
            ]);

            // Check if at least one preference is passed
            if (!$request->hasAny(['sources', 'categories', 'authors'])) {
                return $this->sendError("At least one source, authors or categories are required", [], 422);
            }

            // Updating preferences
            $preference = UserPreference::updateUserPreference($request);

            return $this->sendSuccess('Preference(s) updated successfully.', new UserPreferenceResource($preference));
        } catch (ValidationException $exception) {
            return $this->sendError('Validation error.', $exception->errors(), 422);
        } catch (\Exception $exception) {
            return $this->sendError('Something went wrong.', [$exception->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/user/preferences",
     *     summary="Get user preferences",
     *     description="Retrieve the preferences of the authenticated user.",
     *     tags={"User Preferences"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User preferences retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Preference found."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/UserPreferenceResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Preference not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Preference not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function show(Request $request): JsonResponse
    {
        // Get user preference
        $preference = UserPreference::getUserPreference($request);

        // Handled empty preference
        if (!$preference) {
            return $this->sendError('Preference not found.', [], 404);
        }
        return $this->sendSuccess('Preference found.', new UserPreferenceResource($preference));
    }

    /**
     * @OA\Get(
     *     path="/api/user/personalized-feed",
     *     summary="Get personalized feed for the user",
     *     description="Retrieve a personalized feed of articles based on the user's preferences.",
     *     tags={"User Feed"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Personalized feed retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Personalized feed found."),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ArticleResource"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No personalized feeds found or preference not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No feeds found for your preferences, please adjust your preferences.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function personalizedFeed(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
    {
        // Get user preference
        $preference = UserPreference::getUserPreference($request);

        // Handled empty preference
        if (!$preference) {
            return $this->sendError('Preference not found.', [], 404);
        }

        // creating caching key
        $cacheKey = "user_personalized_feeds_{$request->user()->id}_" . md5(serialize($request->all()));

        // Checking if cache has result for similar request of the user
        if (Cache::has($cacheKey)) {
            $personalizedFeeds = Cache::get($cacheKey);
        } else {
            // If cache has no result then fetch it from DB along with saving into the Cache
            $personalizedFeeds = Article::getUserPersonalizedFeeds($preference, $cacheKey);
        }

        // Checked if there is any personalized feed available
        if ($personalizedFeeds->count() > 0) {
            return ArticleResource::collection($personalizedFeeds);
        } else {
            return $this->sendError('No feeds found for your preferences, please adjust your preferences.', [], 404);
        }
    }

}
