<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use Throwable;

class NotFoundException extends HttpException
{
    public function __construct(string $message, Throwable|null $prev = null)
    {
        parent::__construct($message, 404, $prev);
    }
}
