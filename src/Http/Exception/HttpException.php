<?php

declare(strict_types=1);

namespace Fratily\Framework\Http\Exception;

use Exception;
use Throwable;

class HttpException extends Exception
{
    /**
     * @param string $message The exception message.
     * @param int $status_code The expected HTTP response status code.
     * @param Throwable|null $prev The previous exception used for the exception chaining.
     * @param int $code The exception code.
     *
     * @phpstan-param int<100,599> $status_code
     */
    public function __construct(
        string $message,
        private int $status_code,
        Throwable|null $prev = null,
        int $code = 0
    ) {
        parent::__construct($message, $code, $prev);
    }

    /**
     * Returns the http response status code.
     *
     * @return int
     *
     * @phpstan-return int<100,599>
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }
}
