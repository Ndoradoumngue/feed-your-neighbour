<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MessageException extends HttpException
{
    public function __construct(string $message, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct(400, $message, $previous, $headers, $code);
    }
}
