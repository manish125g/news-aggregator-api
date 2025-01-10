<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'author', 'title', 'source', 'source_url', 'description', 'keywords', 'category', 'image_url', 'content', 'published_at'
    ];
}
