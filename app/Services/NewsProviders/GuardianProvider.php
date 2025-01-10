<?php

namespace App\Services\NewsProviders;


use App\Contracts\NewsProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GuardianProvider implements NewsProvider
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => config('services.guardian.key'),
            'show-fields' => 'all',
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from The Guardian');
        }

        return collect($response->json('response.results'))->map(function ($article) {
            return [
                'author' => $article['fields']['byline'] ?? 'Unknown',
                'title' => $article['webTitle'],
                'source' => 'The Guardian',
                'source_url' => $article['webUrl'] ?? null,
                'description' => $article['fields']['trailText'] ?? '',
                'keywords' => '', // No keywords available
                'category' => $article['sectionName'] ?? '',
                'image_url' => $article['fields']['thumbnail'] ?? null,
                'content' => $article['fields']['body'] ?? '',
                'published_at' => Carbon::create($article['publishedAt'])->toDateTimeString(),
                'status' => 'published',
            ];
        })->toArray();
    }
}
