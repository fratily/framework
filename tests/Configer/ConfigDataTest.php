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
namespace FratilyTest\Configer;

class ConfigDataTest extends \PHPUnit\Framework\TestCase{
    
    /**
     * @return \Fratily\Configer\ConfigData
     */
    protected function generateData(){
        return new \Fratily\Configer\ConfigData();
    }
    
    /**
     * コンフィグを上書きする
     * 
     * @expectedException   \Fratily\Configer\Exception\KeyExistsException
     */
    public function testAddExistsConfig(){
        return $this->generateData()
            ->withValue("abc")
            ->withValue("abc");
    }
    
    /**
     * 未操作データのデフォルト値を取得する
     */
    public function testGetDefaultValue(){
        $data   = $this->generateData()
            ->withValue("null")
            ->withValue("string", "abc");
        
        $this->assertSame(null, $data->get("null"));
        $this->assertSame("abc", $data->get("string"));
    }
    
    /**
     * 未定義データを取得する
     */
    public function testGetUndefineValue(){
        $data   = $this->generateData();
        
        $this->assertEquals(null, $data->get("undefine"));
    }
    
    /**
     * 値を設定する
     */
    public function testSetValue(){
        $data   = $this->generateData()
            ->withValue("key")
            ->withValue("name");
        
        $data->set("key", "abc");
        $data->set("name", "def");
        
        $this->assertEquals("abc", $data->get("key"));
        $this->assertEquals("def", $data->get("name"));
    }
    
    /**
     * バリデーションした値を追加
     */
    public function testSetValidValue(){
        $data   = $this->generateData()
            ->withValue("string", null, "is_string")
            ->withValue("string_len5", null, function($v){
                return is_string($v) && strlen($v) === 5;
            })
            ->withValue("int", null, "is_int");
        
        $this->assertTrue($data->set("string", "abc"));
        $this->assertTrue($data->set("string_len5", "abcde"));
        $this->assertFalse($data->set("int", "this is not integer"));
        
        $this->assertEquals("abc", $data->get("string"));
        $this->assertEquals("abcde", $data->get("string_len5"));
        $this->assertEquals(null, $data->get("int"));
    }
    
    /**
     * フォーマットした値を取得する
     */
    public function testGetFormatedValue(){
        $data   = $this->generateData()
            ->withValue("scalar1", null, "is_scalar", function($v){return (string)$v;})
            ->withValue("scalar2", null, "is_scalar", function($v){return (string)$v;})
            ->withValue("scalar3", null, "is_scalar", function($v){return (string)$v;});
        
        $data->set("scalar1", "string");
        $data->set("scalar2", 12345);
        $data->set("scalar3", 123.45);
        
        $this->assertSame("string", $data->get("scalar1"));
        $this->assertSame("12345", $data->get("scalar2"));
        $this->assertSame("123.45", $data->get("scalar3"));
    }
}