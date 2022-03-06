<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use Fratily\Framework\KernelEvent;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This event fires after the response output is complete.
 */
class TerminateEvent extends KernelEvent
{
    public function __construct(
        ServerRequestInterface $request,
        private ResponseInterface $response,
    ) {
        parent::__construct($request);
    }

    /**
     * Returns the output response instance.
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
