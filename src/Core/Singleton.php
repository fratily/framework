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

use Fratily\Utility\Reflector;
use Fratily\Exception\MethodInaccessibleException;

/**
 * 使っていない(DIコンテナで実現できるうえ)
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
    public static function instance(...$args){
        if(!isset(self::$__instances[static::class])){
            $instance   = Reflector::getClass(static::class)->newInstanceWithoutConstructor();

            Reflector::getClass(static::class)
                ->getMethod("__construct")
                ->setAccessible(true)
                ->invokeArgs($instance, $args);

            self::$__instances[static::class]   = $instance;
        }

        return self::$__instances[static::class];
    }
}