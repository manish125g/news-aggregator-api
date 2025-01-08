<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponder
{
    /**
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function sendSuccess(string $message, $data = [], $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'meta' => $this->metaDefinition(),
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }
        return response()->json($response, $statusCode);
    }

    /**
     * @param $error
     * @param array $errorMessages
     * @param int $statusCode
     * @return Response
     */
    protected function sendError($error, array $errorMessages = [], int $statusCode = Response::HTTP_NOT_FOUND): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
            'meta' => $this->metaDefinition()
        ];

        if (!empty($errorMessages)) {
            $response['error'] = $errorMessages;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * @return array
     */
    protected function metaDefinition(): array
    {
        return [
            'timestamp' => Carbon::now(),
            'version' => env('API_VERSION')
        ];
    }
}
