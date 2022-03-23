<?php

declare(strict_types=1);

namespace Fratily\Framework\Controller;

use Fratily\Framework\Http\RequestAttribute;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ControllerMiddleware implements MiddlewareInterface
{
    public function __construct(private ResponseConverterInterface|null $converter = null)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $controller = $request->getAttribute(RequestAttribute::CONTROLLER_CALLBACK);
        $args = $request->getAttribute(RequestAttribute::CONTROLLER_ARGS, []);

        if ($controller === null) {
            $attribute_name = RequestAttribute::CONTROLLER_CALLBACK;
            throw new LogicException("The request attribute {$attribute_name} not found.");
        }

        if (!is_callable($controller)) {
            $attribute_name = RequestAttribute::CONTROLLER_CALLBACK;
            throw new LogicException("The request attribute {$attribute_name} is not callable.");
        }

        if (!is_array($args)) {
            $attribute_name = RequestAttribute::CONTROLLER_ARGS;
            throw new LogicException(
                "The request attribute {$attribute_name} must be an array that can be expanded as an argument."
            );
        }

        $response = $controller(...$args);

        if (!is_object($response) || !$response instanceof ResponseInterface) {
            if ($this->converter !== null) {
                return $this->converter->convert($request, $response);
            }

            throw new LogicException('todo: write message.');
        }

        return $response;
    }
}
