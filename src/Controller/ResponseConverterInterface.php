<?php

declare(strict_types=1);

namespace Fratily\Framework\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResponseConverterInterface
{
    /**
     * Converts the received value to ResponseInterface and returns it.
     *
     * @param ServerRequestInterface $request
     * @param mixed $response
     * @return ResponseInterface
     */
    public function convert(ServerRequestInterface $request, mixed $response): ResponseInterface;
}
