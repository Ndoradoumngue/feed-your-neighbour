<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AnnouncementServiceException extends HttpException
{
    public function __construct($message = 'Erreur lors du traitement de la demande', \Exception $previous = null, $code = 0, array $headers = [])
    {
        parent::__construct(500, $message, $previous, $headers, $code);
    }
}