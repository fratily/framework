<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Framework\Exception;

use Psr\Container\NotFoundExceptionInterface;

/**
 *
 */
class ContainerNotFoundException extends \LogicException implements NotFoundExceptionInterface{

    const MESSAGE           = "Not found {id} in dependency injection container.";

    /**
     * Constructor
     *
     * @param   string  $id
     * @param   int $code
     * @param   \Throwable  $previous
     */
    public function __construct(string $id, int $code = 0, \Throwable $previous = null){
        parent::__construct(str_replace("{id}", $id, self::MESSAGE), $code, $previous);
    }
}