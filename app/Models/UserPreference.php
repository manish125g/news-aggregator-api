<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id', 'sources', 'categories', 'authors'
    ];

    protected $casts = [
        'authors' => 'array',
        'sources' => 'array',
        'categories' => 'array',
    ];
}
