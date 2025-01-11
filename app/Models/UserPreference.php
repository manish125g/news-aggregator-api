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

    /**
     * @param $request
     * @return mixed
     */
    public static function updateUserPreference($request)
    {
        return UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->all()
        );
    }

    public static function getUserPreference($request)
    {
        // Get user preference
        return UserPreference::where('user_id', $request->user()->id)->first();
    }
}
