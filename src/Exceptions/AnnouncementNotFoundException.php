<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnnouncementNotFoundException extends NotFoundHttpException
{
    public function __construct(int $announcementId, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        parent::__construct(
            sprintf('Aucune annonce trouvée pour l\'ID %d', $announcementId),
            $previous,
            $code,
            $headers
        );
    }
}