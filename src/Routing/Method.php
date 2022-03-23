<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

class Method
{
    public const GET     = 0b00000001;
    public const HEAD    = 0b00000010;
    public const POST    = 0b00000100;
    public const PUT     = 0b00001000;
    public const PATCH   = 0b00010000;
    public const DELETE  = 0b00100000;
    public const OPTIONS = 0b01000000;
}
