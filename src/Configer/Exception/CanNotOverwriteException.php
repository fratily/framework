<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Configer\Exception;

use Fratily\Core\Exception;
use Fratily\Exception\LogicException;

/**
 * 
 */
class CanNotOverwriteException extends Exception implements LogicException, ConfigException{

    const MSG   = "Can not overwrite configure data '{key}' {call.in}.";
    
    /**
     * Constructor
     * 
     * @param   string  $key
     */
    public function __construct(string $key){
        $this->setData("key", $key);
        
        parent::__construct();
    }
}