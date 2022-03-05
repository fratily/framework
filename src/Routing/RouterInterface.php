<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

use Psr\Http\Message\UriInterface;

interface RouterInterface
{
    /**
     * Returns route information that matches the request.
     *
     * @param string $method
     * @param UriInterface $uri
     * @return array|null Route information. Returns null if the route not found.
     *
     * @phpstan-return array{controller:callable,params:array<string,mixed>}
     */
    public function match(string $method, UriInterface $uri): array|null;
}
