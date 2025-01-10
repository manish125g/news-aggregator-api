<?php

namespace App\Services;


use App\Models\Article;
use Illuminate\Support\Facades\Log;

class NewsAggregatorService
{
    protected array $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    public function fetchAndStoreArticles(): void
    {
        foreach ($this->providers as $provider) {
            try {
                $articles = $provider->fetchArticles();

                foreach ($articles as $article) {
                    Article::updateOrCreate(
                        ['title' => $article['title']],
                        $article
                    );
                }

                Log::info('Successfully fetched articles from ' . get_class($provider));
            } catch (\Exception $e) {
                Log::error('Error fetching articles from ' . get_class($provider) . ': ' . $e->getMessage());
            }
        }
    }
}
