<?php

namespace App\DTO;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Crée une réponse de succès
     */
    public static function success(mixed $data = null, string $message = '', int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => true,
            'code' => $statusCode,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return new JsonResponse($response, $statusCode);
    }

    /**
     * Crée une réponse d'erreur
     */
    public static function error(string $message, int $statusCode = Response::HTTP_BAD_REQUEST, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'code' => $statusCode,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return new JsonResponse($response, $statusCode);
    }

    /**
     * Crée une réponse avec du HTML (pour les modaux)
     */
    public static function html(string $html, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse([
            'code' => $statusCode,
            'message' => $html,
        ], $statusCode);
    }
}
