<?php

declare(strict_types=1);

namespace Fratily\Framework\KernelEvent;

use LogicException;

trait StoppableEventTrait
{
    private bool $propagation_stopped = false;

    /**
     * Is propagation stopped?
     *
     * This will typically only be used by the Dispatcher to determine if the
     * previous listener halted propagation.
     *
     * @return bool
     *   True if the Event is complete and no further listeners should be called.
     *   False to continue calling listeners.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagation_stopped;
    }

    /**
     * Stop propagation.
     *
     * @return $this
     */
    public function stopPropagation(): static
    {
        $this->propagation_stopped = true;

        return $this;
    }
}
