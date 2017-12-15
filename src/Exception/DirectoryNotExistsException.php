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
class DirectoryNotExistsException extends Exception implements RuntimeException{

    const MSG   = "Directory '{path}' not exists {call.in}.";
    
    /**
     * Constructor
     * 
     * @param   string  $path
     * 
     * @return  void
     */
    public function __construct(string $path){
        $this->setData("path", $path);
        
        parent::__construct();
    }
}