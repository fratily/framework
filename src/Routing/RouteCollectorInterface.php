<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

interface RouteCollectorInterface
{
    public function build(): RouterInterface;
}
