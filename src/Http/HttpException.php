<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use Exception;
use Throwable;

class HttpException extends Exception
{
    public function __construct(string $message, int $status, Throwable|null $prev = null)
    {
        parent::__construct($message, $status, $prev);
    }

    public function getStatusCode(): int
    {
        return $this->getCode();
    }
}
