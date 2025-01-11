<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    public function test_user_can_set_preferences()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        Article::factory()->create(['category' => 'Technology', 'source' => 'NewsAPI']);

        $response = $this->postJson('/api/user/preferences', [
            'sources' => ['NewsAPI', 'New York Times', 'The Guardian'],
            'categories' => ['Technology'],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Preference(s) updated successfully.']);
    }

    public function test_user_can_retrieve_preferences()
    {
        $user = User::factory()->create();
        UserPreference::factory()->create([
            'user_id' => $user->id,
            'sources' => ['NewsAPI', 'New York Times'],
            'categories' => ['Technology'],
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJsonFragment(['sources' => ['NewsAPI', 'New York Times'], 'categories' => ['Technology']]);
    }

    public function test_personalized_news_feed()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $uniqueCategory = Str::random();

        Article::factory()->create(['category' => $uniqueCategory]);

        UserPreference::factory()->create([
            'user_id' => $user->id,
            'categories' => [$uniqueCategory],
        ]);

        $response = $this->getJson('/api/user/personalized-feed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

}
