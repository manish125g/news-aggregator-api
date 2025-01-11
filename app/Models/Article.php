<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'author', 'title', 'source', 'source_url', 'description', 'keywords', 'category', 'image_url', 'content', 'published_at'
    ];

    public static function getArticles(Request $request, string $cacheKey)
    {
        $query = Article::query();

        // Apply filters
        if ($request->has('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                ->orWhere('content', 'like', '%' . $request->keyword . '%')
                ->orWhere('keywords', 'like', '%' . $request->keyword . '%');
        }

        if ($request->has('category')) {
            $query->whereIn('category', $request->category);
        }

        if ($request->has('source')) {
            $query->whereIn('source', $request->source);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('published_at', [$startDate, $endDate]);
        } else if ($request->has('start_date')) {
            $query->whereDate('published_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } else if ($request->has('end_date')) {
            $query->whereDate('published_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        //End of applying filters

        $sortBy = $request->input('sort_by', 'published_at'); // Default to published_at
        $sortOrder = $request->input('sort_order', 'desc');  // Default to descending


        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $sortBy, $sortOrder) {
            return $query->orderBy($sortBy, $sortOrder)->paginate(10);
        });
    }

    public static function getUserPersonalizedFeeds(UserPreference $preference, string $cacheKey)
    {
        $query = Article::query();

        if ($preference->sources) {
            $query->whereIn('source', $preference->sources);
        }

        if ($preference->categories) {
            $query->whereIn('category', $preference->categories);
        }

        if ($preference->authors) {
            $query->whereIn('author', $preference->authors);
        }

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query) {
            return $query->orderBy('published_at', 'desc')->paginate(10);
        });
    }
}
