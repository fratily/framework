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
 * PHPの配列操作拡張クラス
 */
class Hash{
    
    /**
     * 値が配列もしくは配列と同等の機能を有するかどうか
     * 
     * @param   mixed   $data
     *      確認対象
     * 
     * @return  bool
     */
    public static function isArray($data): bool{
        return (
            is_array($data)
            || (
                $data instanceof \ArrayAccess
                && $data instanceof \Traversable
                && $data instanceof \Serializable
                && $data instanceof \Countable
            )
        );
    }
    
    /**
     * 値が配列アクセス可能かどうか
     * 
     * @param   mixed   $data
     *      確認対象
     * 
     * @return  bool
     */
    public static function isArrayAccessible($data): bool{
        return (is_array($data) || $data instanceof \ArrayAccess);
    }
    
    /**
     * 値がiterableかどうか(PHP7.1からは is_iterable() を使える)
     * 
     * @param   mixed   $data
     *      確認対象
     * 
     * @return  bool
     */
    public static function isIterable($data): bool{
        return (is_array($data) || $data instanceof \Traversable);
    }
    
    /**
     * Checks if the given key exists in the array.
     * 
     * @param   array|ArrayAccess   $data
     *      Value to check.
     * @param   string  $key
     *      Key to check.
     * @param   bool    $throw
     *      Specify whether to throw exception if the passed data is not array.
     * 
     * @return  bool
     */
    public static function keyExists($data, string $key, bool $throw = true): bool{
        if(is_array($data)){
            return array_key_exists($key, $data);
        }elseif($data instanceof ArrayAccess){
            return isset($data[$key]);
        }elseif($throw){
            throw new \InvalidArgumentException();
        }
        
        return false;
    }
    
    /**
     * Retrieve a value from an array.
     * 
     * @param   array|ArrayAccess   $data
     *      The data to search.
     * @param   string  $key
     *      The key to retrieve.
     * @param   mixed   $default
     *      Value to return if not found.
     * 
     * @return  mixed
     */
    public static function get($data, string $key, $default = null){
        if(!static::isArrayAccessible($data)){
            throw new \InvalidArgumentException();
        }
        
        foreach(explode(".", $key) as $key){
            if(!static::keyExists($data, $key, false)){
                $data   = $default;
                break;
            }
            
            $data   = $data[$key];
        }
        
        return $data;
    }
    
    /**
     * Set values in array.
     * 
     * @param   array|ArrayAccess   $data
     *      The data to insert into.
     * @param   string  $key
     *      The key to insert at.
     * @param   mixed   $val
     *      The values to insert.
     * 
     * @return  array|\ArrayAccess
     */
    public static function set($data, string $key, $val){
        if(!static::isArrayAccessible($data)){
            throw new \InvalidArgumentException();
        }
        
        $origin = $data;
        $temp   = &$data;
        
        foreach(explode(".", $key) as $key){
            if(!static::isArrayAccessible($temp)){
                return $origin;
            }elseif(!static::keyExists($temp, $key)){
                $temp[$key] = [];
            }
            
            $temp   = &$temp[$key];
        }

        if(static::isArrayAccessible($temp) && !empty($temp)){
            if(static::isIterable($val)){
                foreach($val as $k => $v){
                    $temp[$k]   = $v;
                }
            }elseif(static::isArrayAccessible($val)){
                $temp   = $val;
            }
        }else{
            $temp   = $val;
        }
        
        return $data;
    }
    
    /**
     * 
     * 
     * @param   array|ArrayAccess   $data
     * @param   string  ...$keys
     * 
     * @return  array
     */
    public static function getValues($data, string ...$keys): array{
        if(!static::isArrayAccessible($data)){
            throw new \InvalidArgumentException();
        }
        
        $return = [];
        
        foreach($keys as $key){
            $return[$key]   = static::keyExists($data, $key) ? $data[$key] : null;
        }
        
        return $return;
    }
    
    /**
     * 
     * 
     * @param   array|ArrayAccess   $data
     * @param   string  ...$keys
     * 
     * @return  array|\ArrayAccess
     */
    public static function unsetValues($data, string ...$keys){
        if(!static::isArrayAccessible($data)){
            throw new \InvalidArgumentException();
        }
        
        foreach($keys as $key){
            if(static::keyExists($data, $key)){
                unset($data[$key]);
            }
        }
        
        return $data;
    }
}