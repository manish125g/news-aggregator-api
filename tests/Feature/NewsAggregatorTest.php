<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Services\NewsProviders\NewsAPIProvider;
use App\Services\NewsProviders\GuardianProvider;
use App\Services\NewsProviders\NYTProvider;
use App\Services\NewsAggregatorService;

class NewsAggregatorTest extends TestCase
{
    public function test_fetch_and_store_articles_from_newsapi()
    {
        Http::fake([
            'https://newsapi.org/*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Sample News from News API',
                        'content' => 'This is a sample news content.',
                        'category' => 'Technology',
                        'publishedAt' => now(),
                    ],
                ],
            ], 200),
        ]);

        $service = new NewsAggregatorService([
            new NewsAPIProvider()
        ]);
        $service->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Sample News from News API',
            'source' => 'NewsAPI',
        ]);
    }

    public function test_fetch_and_store_articles_from_guardianapi()
    {
        Http::fake([
            'https://content.guardianapis.com/*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Sample News from Guardian API',
                            'fields' => [
                                'body' => 'This is a sample news content.',
                            ],
                            'sectionName' => 'Technology',
                            'webPublicationDate' => now()->toISOString(),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new NewsAggregatorService([
            new GuardianProvider()
        ]);
        $service->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Sample News from Guardian API',
            'source' => 'The Guardian',
        ]);
    }

    public function test_fetch_and_store_articles_from_nytapi()
    {
        Http::fake([
            'https://api.nytimes.com/*' => Http::response([
                'results' => [
                    [
                        'title' => 'Sample News from New York Times API',
                        'abstract' => 'This is a sample news content.',
                        'section' => 'Technology',
                        'published_date' => now()->format('Y-m-d'),
                    ],
                ],
            ], 200),
        ]);

        $service = new NewsAggregatorService([
            new NYTProvider()
        ]);
        $service->fetchAndStoreArticles();

        $this->assertDatabaseHas('articles', [
            'title' => 'Sample News from New York Times API',
            'source' => 'New York Times',
        ]);
    }
}
