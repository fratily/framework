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

use Fratily\Exception\MethodInaccessibleException;

/**
 * 使っていない(DIコンテナで実現できるうえ、逃げに見えるため)
 */
trait Singleton{

    /**
     * Storage of generated instances.
     * 
     * @var object[] 
     */
    private static $__instances  = [];

    /**
     * Constructor
     * 
     * The visibility of the class constructor that implements the singleton
     * pattern must be private or protected.
     * Set to protected when executing the constructor from the child class,
     * and set it private if you don't extends it.
     * When set to public No error occurs when creating an instance.
     * 
     * @return  void
     */
    private function __construct(){}
    
    /**
     * Throw an exception because can not create a clone.
     * 
     * @throws  MethodInaccessibleException
     */
    final public function __clone(){
        throw new MethodInaccessibleException(static::class, "__clone");
    }

    /**
     * Retrieve an instance of a class.
     * 
     * @return  static
     */
    public static function instance(){
        $class  = get_called_class();

        if(array_key_exists($class, self::$__instances)){
            return self::$__instances[$class];
        }

        $reflection = new \ReflectionClass($class);
        $construct  = $reflection->getConstructor();
        $construct->setAccessible(true);
        
        $instance   = $reflection->newInstanceWithoutConstructor();
        $construct->invokeArgs($instance, func_get_args());

        self::$__instances[$class]    = $instance;

        return self::$__instances[$class];
    }
}