<?php

declare(strict_types=1);

namespace Fratily\Framework\Routing;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @param string $path
     * @param int $method
     * @param string|null $name
     *
     * @phpstan-param non-empty-string $path
     * @phpstan-param positive-int $method
     * @phpstan-param non-empty-string|null $name
     */
    public function __construct(
        public string $path,
        public int $method = Method::GET,
        public string|null $name = null
    ) {
        if (!str_starts_with($path, '/')) {
            throw new InvalidArgumentException();
        }

        // @phpstan-ignore-next-line non-empty-string and '' will always evaluate to false.
        if ($name !== null && $name === '') {
            throw new InvalidArgumentException();
        }
    }
}
