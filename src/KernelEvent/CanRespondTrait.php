<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use Psr\Http\Message\ResponseInterface;

trait CanRespondTrait
{
    private ResponseInterface|null $response = null;

    /**
     * Returns the response given to this event.
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ResponseInterface|null
    {
        return $this->response;
    }

    /**
     * Set the response to this event.
     *
     * Many events return the response specified here to the client.
     *
     * @param ResponseInterface|null $response
     * @return $this
     */
    public function setResponse(ResponseInterface|null $response): static
    {
        $this->response = $response;
        return $this;
    }
}
