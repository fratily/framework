<?php

declare(strict_types=1);

namespace Fratily\Framework\Http\Exception;

use Throwable;

class NotFoundException extends HttpException
{
    /**
     * @param string $message The exception message.
     * @param Throwable|null $prev The previous exception used for the exception chaining.
     * @param int $code The exception code.
     */
    public function __construct(string $message, Throwable|null $prev = null, int $code = 0)
    {
        parent::__construct($message, 404, $prev, $code);
    }
}
