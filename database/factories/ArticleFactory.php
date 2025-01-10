<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author' => $this->faker->name,
            'title' => $this->faker->sentence,
            'source' => $this->faker->company,
            'source_url' => $this->faker->url,
            'description' => $this->faker->paragraph,
            'keywords' => implode(',', $this->faker->words(5)), // Comma-separated keywords
            'category' => $this->faker->word,
            'image_url' => $this->faker->imageUrl(640, 480, 'articles'), // Placeholder image URL
            'content' => $this->faker->paragraphs(5, true), // Multi-paragraph content
            'published_at' => $this->faker->dateTimeBetween('-1 year'),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
        ];
    }
}
