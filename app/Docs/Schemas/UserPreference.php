<?php

namespace App\Docs\Schemas;
/**
 * @OA\Schema(
 *     schema="UserPreferenceResource",
 *     type="object",
 *     title="User Preference",
 *     description="Schema representing a user's preferences",
 *     @OA\Property(property="id", type="integer", description="ID of the user preference", example=1),
 *     @OA\Property(property="user_id", type="integer", description="ID of the associated user", example=42),
 *     @OA\Property(
 *         property="sources",
 *         type="array",
 *         description="Preferred sources of the user",
 *         @OA\Items(type="string", example="source_1")
 *     ),
 *     @OA\Property(
 *         property="categories",
 *         type="array",
 *         description="Preferred categories of the user",
 *         @OA\Items(type="string", example="category_1")
 *     ),
 *     @OA\Property(
 *         property="authors",
 *         type="array",
 *         description="Preferred authors of the user",
 *         @OA\Items(type="string", example="author_1")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the preference was created", example="2025-01-10T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the preference was last updated", example="2025-01-11T12:00:00Z")
 * )
 */
final class UserPreference
{

}
