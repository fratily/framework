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

/**
 * PHPのリフレクション拡張クラス
 */
class Reflector{

    /**
     * @var \ReflectionClass[]
     */
    private static $classes = [];

    /**
     * @var \ReflectionMethod[][]
     */
    private static $methods = [];

    /**
     * @var \ReflectionProperty[][]
     */
    private static $properties  = [];

    /**
     * @var \ReflectionFunction
     */
    private static $functions   = [];

    /**
     * クラスのリフレクションオブジェクトを返す
     *
     * @param   string|object   $class
     *
     * @throws  \Fratily\Exception\ClassUndefinedException
     *
     * @return  \ReflectionClass
     */
    public static function getClass($class){
        if(is_object($class)){
            $class  = get_class($class);
        }else if(!is_string($class)){
            throw new \InvalidArgumentException;
        }

        $key  = hash("md5", $class);

        if(!isset(self::$classes[$key])){
            if(!class_exists($class)){
                throw new \Fratily\Exception\ClassUndefinedException($class);
            }

            self::$classes[$key]  = new \ReflectionClass($class);
        }

        return self::$classes[$key];
    }

    /**
     * メソッドのリフレクションオブジェクトを返す
     *
     * @param   string|object   $class
     * @param   string  $method
     *
     * @throws  \Fratily\Exception\MethodUndefinedException
     *
     * @return  \ReflectionMethod
     */
    public static function getMethod($class, string $method){
        $class  = self::getClass($class);

        if(!isset(self::$methods[$class->getName()][$method])){
            if(!$class->hasMethod($method)){
                throw new \Fratily\Exception\MethodUndefinedException($class->getName(), $method);
            }

            self::$methods[$class->getName()][$method]  = $class->getMethod($method);
        }

        return self::$methods[$class->getName()][$method];
    }

    /**
     * クラスが持つすべてのメソッドのリフレクションオブジェクトを返す
     *
     * @param   string|object   $class
     * @param   int $filter
     * @throws  \Fratily\Exception\ClassUndefinedException
     *
     * @return  \ReflectionMethod[]
     */
    public static function getMethods($class, int $filter = null){
        $class      = self::getClass($class);
        $methods    = $class->getMethods($filter);

        foreach($methods as $method){
            if(!isset(self::$methods[$class->getName()][$method->getName()])){
                self::$methods[$class->getName()][$method->getName()]   = $method;
            }
        }

        return $methods;
    }

    /**
     * プロパティのリフレクションオブジェクトを返す
     *
     * @param   string|object   $class
     * @param   string  $property
     *
     * @throws  \Fratily\Exception\PropertyUndefinedException
     *
     * @return  \ReflectionProperty
     */
    public static function getProperty($class, string $property){
        $class  = self::getClass($class);

        if(!isset(self::$properties[$class->getName()][$property])){
            if(!$class->hasProperty($property)){
                throw new \Fratily\Exception\PropertyUndefinedException($class->getName(), $property);
            }

            self::$properties[$class->getName()][$property]  = $class->getProperty($property);
        }

        return self::$properties[$class->getName()][$property];
    }

    /**
     * クラスが持つすべてのプロパティのリフレクションオブジェクトを返す
     *
     * @param   string|object   $class
     * @param   int $filter
     * @throws  \Fratily\Exception\ClassUndefinedException
     *
     * @return  \ReflectionProperty[]
     */
    public static function getProperties($class, int $filter = null){
        $class      = self::getClass($class);
        $properties = $class->getProperties($filter);

        foreach($properties as $property){
            if(!isset(self::$properties[$class->getName()][$property->getName()])){
                self::$properties[$class->getName()][$property->getName()]  = $property;
            }
        }

        return $properties;
    }

    /**
     * 関数のリフレクションオブジェクトを返す
     *
     * @param   callable    $function
     *
     * @return  \ReflectionFunction
     */
    public static function getFunction($function){
        if(!is_string($function) && !($function instanceof \Closure)){
            throw new \InvalidArgumentException;
        }else if(is_string($function) && strpos($function, ":") !== false){
            throw new \InvalidArgumentException;
        }

        $key    = is_string($function) ? $function : "-".spl_object_hash($function);

        if(!isset(self::$functions[$key])){
            try{
                self::$functions[$key]  = new \ReflectionFunction($function);
            }catch(\ReflectionException $e){
                throw new \InvalidArgumentException;
            }
        }

        return self::$functions[$key];
    }

    /**
     * 関数の引数にパラメータをバインドする
     *
     * @param   \ReflectionFunctionAbstract $func
     * @param   mixed[] $params
     *
    * @return   mixed[]
     */
    public static function bindParams2Args(
        \ReflectionFunctionAbstract $func,
        array $params,
        $default = null
    ){
        $args   = [];

        foreach($func->getParameters() as $param){
            $value  = $default;

            if(isset($params[$param->getName()])){
                $value  = $params[$param->getName()];
            }else if($param->isDefaultValueAvailable()){
                $value  = $param->getDefaultValue();
            }

            $args[] = $value;
        }

        return $args;
    }
}