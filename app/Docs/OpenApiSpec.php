<?php

namespace App\Docs;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="News Aggregator API",
 *         description="API documentation for the News Aggregator project.",
 *         @OA\Contact(
 *             email="manish011994@gmail.com"
 *         ),
 *         @OA\License(
 *             name="Apache 2.0",
 *             url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *         )
 *     ),
 *  @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="JWT Bearer token authentication"
 *     ),
 *  ),
 *     @OA\Server(
 *         url="http://localhost:8000",
 *         description="Local development server"
 *     )
 * )
 */
final class OpenApiSpec
{

}
