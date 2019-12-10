<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base aggregator controller.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Controller extends BaseController
{
    /**
     * Standard 200 OK json output.
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function respond($data = [], int $status = 200, array $headers = []): JsonResponse
    {
        $data = [
            'status' => $status,
            'data' => $data,
            'error' => null,
        ];

        return (new JsonResponse($data, $status, $headers))
            ->setEncodingOptions(static::getJsonEncodingOptions());
    }

    /**
     * Standard json output with error(s).
     * @param mixed $error
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function respondWithError($error, int $status = 500, array $headers = []): JsonResponse
    {
        if (is_string($error)) {
            $error = ['message' => $error];
        }

        $data = [
            'status' => $status,
            'data' => null,
            'error' => $error,
        ];

        return (new JsonResponse($data, $status, $headers))
            ->setEncodingOptions(static::getJsonEncodingOptions());
    }

    /**
     * PLain text response.
     * @param string $txt
     * @param int $status
     * @param array $headers
     * @return Response
     */
    public static function respondWithText(string $txt, int $status = 200, array $headers = []): Response
    {
        return new Response($txt, $status, array_merge($headers, ['Content-Type' => 'text/plain']));
    }

    /**
     * Health check endpoint method.
     * @return Response
     */
    public function health(): Response
    {
        return $this->respondWithText('OK');
    }

    /**
     * Set the JSON encoding based on the current env.
     * @return int
     */
    protected static function getJsonEncodingOptions(): int
    {
        return app()->environment('production')
            ? JSON_UNESCAPED_SLASHES
            : JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
    }
}
