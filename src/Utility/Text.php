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
namespace Fratily\Utility;

use Fratily\Utility\Hash;
use Fratily\Exception\InvalidArgumentException;

/**
 * 
 */
class Text{
        
    const PLACEHOLDER   = "([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)";
    
    public static function format(
        string $text,
        array $context = [],
        string $ldel = "{",
        string $rdel = "}",
        string $unknown = ":unknown:"
    ): string{
        if($ldel === "" || $rdel === ""){
            throw new InvalidArgumentException();
        }
        
        if(!(bool)preg_match_all(
            "/" . preg_quote($ldel) . self::PLACEHOLDER . preg_quote($rdel) . "/",
            $text, $matches, PREG_PATTERN_ORDER
        )){
            return $text;
        }
        
        $matches    = array_unique($matches[1]);
        $search     = [];
        $replace    = [];

        foreach($matches as $match){
            $data       = Hash::get($context, $match);
            $search[]   = "{{$match}}";
            
            if(is_scalar($data) || (is_object($data) && method_exists($data, "__toString"))){
                $replace[]  = (string)$data;
            }else{
                $replace[]  = $unknown;
            }
        }
        
        return str_replace($search, $replace, $text);
    }
}