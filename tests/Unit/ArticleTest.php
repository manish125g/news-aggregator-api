<?php

namespace Tests\Unit;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;

class ArticleTest extends TestCase
{
    public function test_fetch_articles_with_pagination()
    {
        Article::factory()->count(15)->create();

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    }

    public function test_filter_articles_by_keyword()
    {
        Article::factory()->create(['title' => 'Test News Test Tata']);
        Article::factory()->create(['title' => 'Other News']);

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/articles?keyword=Test News Test Tata');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_fetch_single_article()
    {
        $article = Article::factory()->create();

        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson(['data' => [
                'id' => $article->id
            ]]);
    }
}