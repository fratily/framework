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
namespace Fratily\Router;

use Fratily\Exception\PropertyUndefinedException;

/**
 * 
 * 
 * @property-read   mixed       $type
 * @property-read   mixed[]     $params
 */
class Result{
    
    private $type       = RouterInterface::NOT_FOUND;
    
    private $params     = [];
    
    /**
     * Property get
     * 
     * @param   string  $key
     * 
     * @throws  PropertyUndefinedException
     * 
     * @return  mixed
     */
    public function __get($key){
        switch($key){
            case "type":
            case "allowed":
            case "params":
                return $this->$key;
        }
        
        throw new PropertyUndefinedException(static::class, $key);
    }
    
    /**
     * 
     * 
     * @param   mixed   $type
     * 
     * @return  static
     */
    public function withType($type){
        if(!in_array($type, [
            RouterInterface::FOUND,
            RouterInterface::NOT_FOUND,
            RouterInterface::METHOD_NOT_ALLOWED
        ])){
            throw new \InvalidArgumentException;
        }
        
        $clone          = clone $this;
        $clone->type    = $type;
        return $clone;
    }
    
    /**
     * 
     * 
     * @param   mixed[] $params
     * 
     * @return  static
     */
    public function withParams(array $params){
        if(!isset($params["controller"])){
            throw new Exception\ControllerNotAssignedException();
        }
        
        $clone          = clone $this;
        $clone->params  = $params;
        return $clone;
    }

    /**
     * 一致するルールがあった
     * 
     * @param   mixed[] $params
     * 
     * @return  self
     */
    public static function found(array $params){
        return (new static())
            ->withType(RouterInterface::FOUND)
            ->withParams($params);
    }
    
    /**
     * 一致するルールがなかった
     * 
     * @return  self
     */
    public static function notFound(){
        return (new static());
    }
    
    /**
     * メソッドが一致しなかった
     * 
     * @return  self
     */
    public static function methodNotAllowed(){
        return (new static())
            ->withType(RouterInterface::METHOD_NOT_ALLOWED);
    }
}