<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Exception;

use Fratily\Core\Exception;

/**
 * 
 */
class MethodUndefinedException extends Exception implements LogicException{

    const MSG   = "Call to undefined method {class.name}::{method.name}() {call.in}.";

    /**
     * Constructor
     *
     * @param   string  $class
     * @param   string  $method
     * @param   mixed[] $args
     *
     * @return  void
     */
    public function __construct(string $class, string $method, array $args = []){
        $this->setData("class.name", $class);
        $this->setData("method.name", $method);

        parent::__construct();
    }
}