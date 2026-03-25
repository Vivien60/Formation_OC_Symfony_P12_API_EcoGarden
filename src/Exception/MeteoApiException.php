<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MeteoApiException extends HttpException
{

    /**
     * @param string $content
     * @param int $statusCode
     */
    public function __construct(string $content, int $statusCode)
    {
        parent::__construct(statusCode:$statusCode, message:$content, code: $statusCode);
    }
}