<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

interface RouteLoaderInterface
{
    public function load(): RouteCollectorInterface;
}
