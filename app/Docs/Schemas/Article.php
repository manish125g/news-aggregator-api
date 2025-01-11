<?php

namespace App\Docs\Schemas;

/**
 * @OA\Schema(
 *     schema="ArticleResource",
 *     type="object",
 *     title="Article",
 *     description="Schema representing an article",
 *     @OA\Property(property="id", type="integer", description="ID of the article", example=1),
 *     @OA\Property(property="author", type="string", description="Author of the article", example="John Doe"),
 *     @OA\Property(property="title", type="string", description="Title of the article", example="Understanding OpenAPI"),
 *     @OA\Property(property="source", type="string", nullable=true, description="Source of the article", example="Tech News"),
 *     @OA\Property(property="source_url", type="string", format="uri", nullable=true, description="Source URL of the article", example="https://example.com/article-source"),
 *     @OA\Property(property="description", type="string", description="Description of the article", example="This article explains the basics of OpenAPI."),
 *     @OA\Property(property="keywords", type="string", description="Keywords associated with the article", example="OpenAPI, Swagger, API Documentation"),
 *     @OA\Property(property="category", type="string", description="Category of the article", example="Technology"),
 *     @OA\Property(property="image_url", type="string", format="uri", nullable=true, description="Image URL associated with the article", example="https://example.com/image.jpg"),
 *     @OA\Property(property="content", type="string", description="Full content of the article", example="This is the content of the article..."),
 *     @OA\Property(property="published_at", type="string", format="date-time", description="Published date and time of the article", example="2025-01-11T14:30:00Z"),
 *     @OA\Property(property="status", type="string", enum={"draft", "published", "archived"}, description="Status of the article", example="published"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date and time when the article was created", example="2025-01-10T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date and time when the article was last updated", example="2025-01-11T12:00:00Z")
 * )
 */
final class Article
{

}
