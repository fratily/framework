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
namespace Fratily\Utility;

/**
 * 値バリデーション
 */
class Validator{
    
    const REQUIRED  = 1;
    
    private static $prevResult  = true;
    
    public static function varidate($value, bool $empty = false, string $rules = "", array $contexts = []){
        if($empty){
            if($value === null || $value === ""
                || (is_array($value) && empty($value))
            ){
                self::$prevResult   = self::REQUIRED;
                return false;
            }
        }
        
        return true;
    }
    
    protected static function parseRules(string $rules, array $contexts){
        return explode("|", $rules);
    }
}