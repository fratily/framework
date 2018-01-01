<?php
/**
 * FratilyPHP
 *
 * Basic exception class group.
 *
 * @see https://github.com/kento-oka/agilephp
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) 2017 Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Exception;

use Fratily\Core\Exception;

/**
 * 
 */
class ClassUndefinedException extends Exception implements LogicException{

    const MSG   = "Call to undefined class {class.name} {call.in}.";

    /**
     * Constructor
     *
     * @param   string  $class
     *
     * @return  void
     */
    public function __construct(string $class){
        $this->setData("class.name", $class);

        parent::__construct();
    }
}