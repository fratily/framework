<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Application\Exception;

/**
 * 
 */
class ConfigureException extends \Exception{
    
    public static function unexpectedValue(string $key, string $type){
        return new static("{$key} expected type of {$type}");
    }
    
    public static function overwrite(string $key){
        return new static("{$key} can not overwrite");
    }
}