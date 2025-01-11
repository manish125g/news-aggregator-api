<?php

namespace App\Services\NewsProviders;


use App\Contracts\NewsProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NYTProvider implements NewsProvider
{
    public function fetchArticles(): array
    {
        $response = Http::get('https://api.nytimes.com/svc/topstories/v2/home.json', [
            'api-key' => config('services.nyt.key'),
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch articles from NYT');
        }

        return collect($response->json('results'))->map(function ($article) {
            return [
                'author' => !empty($article['byline']) ? Str::replaceStart("By ", "", $article['byline']) : 'Unknown',
                'title' => $article['title'],
                'source' => 'New York Times',
                'source_url' => $article['url'] ?? null,
                'description' => $article['abstract'] ?? '',
                'keywords' => Str::limit(implode(',', $article['des_facet'] ?? []), 255, '', true), // Added for limiting the data in DB
                'category' => $article['section'] ?? '',
                'image_url' => $article['multimedia'][0]['url'] ?? null,
                'content' => $article['abstract'] ?? '',
                'published_at' => $article['published_date'],
                'status' => 'published',
            ];
        })->toArray();
    }
}
