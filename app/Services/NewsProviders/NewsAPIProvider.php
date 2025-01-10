<?php

namespace App\Services\NewsProviders;


use App\Contracts\NewsProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class NewsAPIProvider implements NewsProvider
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => config('services.newsapi.key'),
            'country' => 'us',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from NewsAPI');
        }

        return collect($response->json('articles'))->map(function ($article) {
            return [
                'author' => $article['author'] ?? 'Unknown',
                'title' => $article['title'],
                'source' => 'NewsAPI',
                'source_url' => $article['url'] ?? null,
                'description' => $article['description'] ?? '',
                'keywords' => '', // No keywords available
                'category' => '', // No category available
                'image_url' => $article['urlToImage'] ?? null,
                'content' => $article['content'] ?? '',
                'published_at' => Carbon::create($article['publishedAt'])->toDateTimeString(),
                'status' => 'published',
            ];
        })->toArray();
    }
}
