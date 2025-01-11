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
use Psy\Util\Json;

class UserPreferenceController extends Controller
{
    use ApiResponder;

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
