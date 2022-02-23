<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use Psr\Http\Message\ServerRequestInterface;

class KernelEvent
{
    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(
        private ServerRequestInterface $request
    ) {
    }

    /**
     * Returns the current request instance.
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
