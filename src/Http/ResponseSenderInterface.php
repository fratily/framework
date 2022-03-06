<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

use Psr\Http\Message\ResponseInterface;

interface ResponseSenderInterface
{
    /**
     * Output the received response instance.
     *
     * @param ResponseInterface $response
     */
    public function send(ResponseInterface $response): void;
}
