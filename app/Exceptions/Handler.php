<?php

namespace App\Exceptions;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handle thrown exception.
 *
 * @copyright 2019 Brightfish
 * @author Arnaud Coolsaet <a.coolsaet@brightfish.be>
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected function prepareJsonResponse($request, Exception $e)
    {
        return Controller::respondWithError(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareResponse($request, Exception $e)
    {
        if (app()->environment('production')) {
            $content = $e->getMessage();
        } else {
            $content = $e->getMessage() . PHP_EOL
                . $e->getFile() . ' (' . $e->getLine() . ')' . PHP_EOL
                . $e->getTraceAsString();
        }

        $status = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        $headers = $this->isHttpException($e) ? $e->getHeaders() : [];

        return Controller::respondWithText($content, $status, $headers);
    }
}
