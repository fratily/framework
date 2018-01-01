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
namespace Fratily\Debug;

/**
 * 
 */
class Dumper{
    
    /**
     * 簡易的な変数ダンプ
     * 
     * @param   mixed   $value
     *      ダンプする値
     * @param   bool    $echo
     *      出力するか
     * @param   bool    $cl
     *      出力する場合、最後の改行を挿入するか
     * 
     * @return  string
     */
    public static function dumpSimple($value, bool $echo = false, bool $cl = false){
        $type   = "unknown";
        $dump   = null;
        
        if(is_scalar($value)){
            $type   = gettype($value);
            $dump   = var_export($value, true);
        }else if(is_array($value)){
            $type   = "array";
            $dump   = count($value);
        }else if(is_object($value)){
            $type   = "object";
            $dump   = get_class($value);
        }else if(is_resource($value)){
            $type   = "resource";
            $dump   = get_resource_type($value);
        }else if($value === null){
            $type   = "null";
            $dump   = null;
        }
        
        $return = ucfirst($type) . ($dump !== null ? "({$dump})" : "");
        
        if($echo){
            echo $return . $cl ? PHP_EOL : "";
        }
        
        return $return;
    }
}