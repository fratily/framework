<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

use Psr\Http\Message\UriInterface;

interface RouterInterface
{
    /**
     * Returns a callback of the action that matches the request.
     *
     * @param string $method
     * @param UriInterface $uri
     * @return callable|null Returns an action callback. Returns null if there is no matching route.
     */
    public function match(string $method, UriInterface $uri): ?callable;
}
