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
namespace Fratily\Core;

use Fratily\Exception\PropertyInaccessibleException;
use Fratily\Exception\PropertyUndefinedException;
use Fratily\Exception\MethodInaccessibleException;
use Fratily\Exception\MethodUndefinedException;

/**
 * 使っていない(PHPがErrorを投げ出し、個人で実装する必要がなくなりつつあるため)
 */
trait StdClassTrait{

    /**
     * Called when reading data from an inaccessible property.
     *
     * @param   string  $key
     *
     * @throws  PropertyInaccessibleException
     * @throws  PropertyUndefineException
     *
     * @return  mixed
     */
    public function __get(string $key){
        if(property_exists($this, $key)){
            throw new PropertyInaccessibleException(get_class(), $key);
        }

        throw new PropertyUndefinedException(get_class(), $key);
    }

    /**
     * Called when executing isset(), empty() on an inaccessible property.
     *
     * @param   string  $key
     *
     * @return  bool
     */
    public function __isset(string $key): bool{
        return property_exists($this, $key);
    }

    /**
     * Called when writing data to an inaccessible property.
     *
     * @param   string  $key
     * @param   mixed   $val
     *
     * @throws  PropertyInaccessibleException
     * @throws  PropertyUndefineException
     *
     * @return  void
     */
    public function __set(string $key, $val): void{
        if(property_exists($this, $key)){
            throw new PropertyInaccessibleException(get_class(), $key);
        }

        throw new PropertyUndefinedException(get_class(), $key);
    }

    /**
     * Called when executing unset() on an inaccessible property.
     *
     * @param   string  $key
     *
     * @throws  PropertyInaccessibleException
     * @throws  PropertyUndefineException
     *
     * @return  void
     */
    public function __unset(string $key): void{
        if(property_exists($this, $key)){
            throw new PropertyInaccessibleException(get_class(), $key);
        }

        throw new PropertyUndefinedException(get_class(), $key);
    }

    /**
     * Called when invoking inaccessible method in an object context.
     *
     * @param   string  $key
     * @param   mixed[] $args
     *
     * @throws  MethodInaccessibleException
     * @throws  MethodUndefineException
     *
     * @return  void
     */
    public function __call(string $key, array $args){
        if(method_exists($this, $key)){
            throw new MethodInaccessibleException(get_class(), $key, $args);
        }

        throw new MethodUndefinedException(get_class(), $key, $args);
    }

    /**
     * Called when invoking inaccessible method in an static context.
     *
     * @param   string  $key
     * @param   mixed[] $args
     *
     * @throws  MethodInaccessibleException
     * @throws  MethodUndefineException
     *
     * @return  void
     */
    public static function __callStatic(string $key, array $args = []){
        if(method_exists(static::class, $key)){
            throw new MethodInaccessibleException(static::class, $key, $args);
        }

        throw new MethodUndefinedException(static::class, $key, $args);
    }
}