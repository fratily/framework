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
namespace FratilyTest\Utility;

use Fratily\Utility\Reflector;

/**
 * PHPのリフレクション拡張クラス
 */
class ReflectorTest extends \PHPUnit\Framework\TestCase{

    const UNDEFINE_CLASS    = "\\Fratily\\UndefineClass";
    const UNDEFINE_FUNC     = "\\Fratily\\UndefineFunc";

    /**
     * クラスを取得する
     */
    public function testGetClass(){
        $this->assertInstanceOf(
            \ReflectionClass::class,
            Reflector::getClass(\Exception::class)
        );
        $this->assertInstanceOf(
            \ReflectionClass::class,
            Reflector::getClass(new \Exception())
        );
        $this->assertInstanceOf(
            \ReflectionClass::class,
            Reflector::getClass(new class{})
        );
    }

    /**
     * 未定義クラスを取得する
     *
     * @expectedException   \Fratily\Exception\ClassUndefinedException
     */
    public function testGetUndefineClass(){
        Reflector::getClass(self::UNDEFINE_CLASS);
    }

    /**
     * メソッドを取得する
     */
    public function testGetMethod(){
        $this->assertInstanceOf(
            \ReflectionMethod::class,
            Reflector::getMethod(\Exception::class, "getMessage")
        );
        $this->assertInstanceOf(
            \ReflectionMethod::class,
            Reflector::getMethod(new \Exception(), "getMessage")
        );
    }

    /**
     * 未定義クラスのメソッドを取得する
     *
     * @expectedException   \Fratily\Exception\ClassUndefinedException
     */
    public function testGetUndefineClassMethod(){
        Reflector::getMethod(self::UNDEFINE_CLASS, "__construct");
    }

    /**
     * 未定義メソッドを取得する
     *
     * @expectedException   \Fratily\Exception\MethodUndefinedException
     */
    public function testGetUndefineMethod(){
        Reflector::getMethod(\Exception::class, "undefine_123");
    }

    /**
     *　プロパティを取得する
     */
    public function testGetProperty(){
        $this->assertInstanceOf(
            \ReflectionProperty::class,
            Reflector::getProperty(\Exception::class, "message")
        );
        $this->assertInstanceOf(
            \ReflectionProperty::class,
            Reflector::getProperty(new \Exception(), "message")
        );
    }

    /**
     * 未定義クラスのプロパティを取得する
     *
     * @expectedException   \Fratily\Exception\ClassUndefinedException
     */
    public function testGetUndefineClassProperty(){
        Reflector::getProperty(self::UNDEFINE_CLASS, "message");
    }

    /**
     * 未定義プロパティを取得する
     *
     * @expectedException   \Fratily\Exception\PropertyUndefinedException
     */
    public function testGetUndefineProperty(){
        Reflector::getProperty(\Exception::class, "undefine_123");
    }

    /**
     * 関数を取得する
     */
    public function testGetFunction(){
        $this->assertInstanceOf(
            \ReflectionFunction::class,
            Reflector::getFunction("base64_encode")
        );
        $this->assertInstanceOf(
            \ReflectionFunction::class,
            Reflector::getFunction(function(int $v){return $v * $v;})
        );
    }

    /**
     * 関数や無名関数以外のcallableを取得する
     *
     * @dataProvider     provideNotFunction
     * @expectedException   \InvalidArgumentException
     */
    public function testGetNotFunction($val){
        Reflector::getFunction($val);
    }

    public function provideNotFunction(){
        return [
            [["Reflector", "getClass"]],
            [Reflector::class . "::getClass"],
            [new class{public function __invoke(){}}],
            [[new class{function foo(){}}, "foo"]]
        ];
    }
}