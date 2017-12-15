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
class FunctionUndefinedException extends Exception implements LogicException{

    const MSG   = "Call to undefined function {func.name} {call.in}.";

    /**
     * Constructor
     *
     * @param   string  $func
     *
     * @return  void
     */
    public function __construct(string $func){
        $this->setData("func.name", $func);

        parent::__construct();
    }
}