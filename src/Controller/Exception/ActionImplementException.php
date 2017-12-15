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
namespace Fratily\Controller\Exception;

use Fratily\Core\Exception;
use Fratily\Exception\LogicException;

/**
 *
 */
class ActionImplementException extends Exception implements ControllerException, LogicException{

    const MSG   = "Implementation of action {class.name}::{method.name}() is incorrect.";

    /**
     * Constructor
     *
     * @param   string  $class
     *      Class name.
     * @param   string  $method
     *      Method name.
     * @param   string  $msg
     *      The exception message to throw.
     *
     * @return  void
     */
    public function __construct(string $class, string $method, string $msg = null){
        $this->setData("class.name", $class);
        $this->setData("method.name", $method);

        parent::__construct($msg);
    }
}