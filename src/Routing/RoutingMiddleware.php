<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

use Fratily\Framework\Http\Exception\NotFoundException;
use Fratily\Framework\Http\RequestAttribute;
use Fratily\Framework\Routing\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface
{
    public const NOT_FOUND_MODE_CONTINUE = 'continue';
    public const NOT_FOUND_MODE_EXCEPTION = 'exception';

    public function __construct(
        private RouterInterface $router,
        private string $not_found_mode = self::NOT_FOUND_MODE_EXCEPTION,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->router->match($request->getMethod(), $request->getUri());

        if ($match === null) {
            if ($this->not_found_mode === self::NOT_FOUND_MODE_EXCEPTION) {
                throw new NotFoundException('todo: write message');
            }
        } else {
            $request = $request
                ->withAttribute(RequestAttribute::ROUTING_MATCH_ROUTE_CALLBACK, $match['action'])
                ->withAttribute(RequestAttribute::ROUTING_MATCH_ROUTE_PARAMS, $match['params']);
        }

        return $handler->handle($request);
    }
}
