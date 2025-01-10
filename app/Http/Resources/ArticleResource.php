<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "author" => $this->author,
            "title" => $this->title,
            "source" => $this->source,
            "source_url" => $this->source_url,
            "description" => $this->description,
            "keywords" => $this->keywords,
            "category" => $this->category,
            "image_url" => $this->image_url,
            "content" => $this->content,
            "published_at" => $this->published_at
        ];
    }
}
