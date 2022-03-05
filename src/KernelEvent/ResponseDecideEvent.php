<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use Fratily\Framework\KernelEvent;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * An event that fires after the response has been decide by the middleware.
 */
class ResponseDecideEvent extends KernelEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    /**
     * @param ServerRequestInterface $request
     */
    public function __construct(
        ServerRequestInterface $request,
        private ResponseInterface $response
    ) {
        parent::__construct($request);
    }

    /**
     * Returns the response given to this event.
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the response to this event.
     *
     * @param ResponseInterface $response
     * @return $this
     */
    public function setResponse(ResponseInterface $response): static
    {
        $this->response = $response;
        return $this;
    }
}
