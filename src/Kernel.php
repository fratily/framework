<?php

declare(strict_types=1);

namespace Fratily\Framework;

use Fratily\Framework\Http\ResponseSenderInterface;
use Fratily\Framework\KernelEvent\RequestEvent;
use Fratily\Framework\KernelEvent\TerminateEvent;
use InvalidArgumentException;
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Kernel
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middleware_list = [];

    private MiddlewareInterface|null $fallback_middleware = null;

    private EventDispatcherInterface|null $event_dispatcher = null;

    /**
     * @param ResponseSenderInterface $response_sender
     */
    public function __construct(
        private ResponseSenderInterface $response_sender
    ) {
    }

    /**
     * @param MiddlewareInterface[] $middleware_list
     * @param MiddlewareInterface|null $fallback_middleware
     * @return $this
     */
    public function setMiddleware(array $middleware_list, MiddlewareInterface|null $fallback_middleware): static
    {
        foreach ($middleware_list as $middleware) {
            // @phpstan-ignore-next-line will always evaluate to true.
            if (!is_object($middleware) || !$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException();
            }
        }

        $this->middleware_list = $middleware_list;
        $this->fallback_middleware = $fallback_middleware;
        return $this;
    }

    /**
     * @param EventDispatcherInterface $event_dispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $event_dispatcher): static
    {
        $this->event_dispatcher = $event_dispatcher;
        return $this;
    }

    public function handle(ServerRequestInterface $request): void
    {
        $request_event = $this->dispatchEvent(new RequestEvent($request));

        if ($request_event->getResponse() !== null) {
            $response = $request_event->getResponse();
        } else {
            $response = $this->handleMiddleware($request);
        }

        $this->response_sender->send($response);

        $this->dispatchEvent(new TerminateEvent($request, $response));
    }

    private function handleMiddleware(ServerRequestInterface $request): ResponseInterface
    {
        $handler = new class ($this->middleware_list, $this->fallback_middleware) implements RequestHandlerInterface {
            /**
             * @param MiddlewareInterface[] $middleware_list
             */
            public function __construct(
                private array $middleware_list,
                private MiddlewareInterface|null $fallback_middleware,
                private bool $fallback_called = false
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (count($this->middleware_list) > 0) {
                    return array_shift($this->middleware_list)->process($request, $this);
                }

                if ($this->fallback_called) {
                    throw new LogicException();
                }

                if ($this->fallback_middleware === null) {
                    throw new LogicException();
                }

                $this->fallback_called = true;
                return $this->fallback_middleware->process($request, $this);
            }
        };

        return $handler->handle($request);
    }

    /**
     * Provide all relevant listeners with an event to process.
     *
     * @param object $event The object to process.
     * @return object The Event that was passed, now modified by listeners.
     *
     * @template T of object
     * @phpstan-param T $event
     * @phpstan-return T
     */
    private function dispatchEvent(object $event): object
    {
        if ($this->event_dispatcher !== null) {
            // @phpstan-ignore-next-line but returns object.
            return $this->event_dispatcher->dispatch($event);
        }

        return $event;
    }
}
