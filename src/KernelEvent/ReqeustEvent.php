<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use Fratily\Framework\KernelEvent;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An event that fires before the Request Handler is executed.
 */
class RequestEvent extends KernelEvent implements StoppableEventInterface
{
    use StoppableEventTrait;
    use CanRespondEventTrait;
}
