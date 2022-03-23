<?php

declare(strict_types=1);

namespace Fratily\Framework\Http;

class RequestAttribute
{
    public const ROUTING_MATCH_ROUTE_PARAMS = 'fratily.routing.match.params';

    public const CONTROLLER_CALLBACK = 'fratily.controller.callback';
    public const CONTROLLER_ARGS = 'fratily.controller.args';
}
