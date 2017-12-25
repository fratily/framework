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
namespace FratilyTest\Configer;

use Fratily\Configer\Configure as Config;

class ConfiguerTest extends \PHPUnit\Framework\TestCase{
    
    public function setup(){
        $ref    = new \ReflectionClass(Config::class);
        $prop   = $ref->getProperty("config");
        $prop->setAccessible(true);
        $prop->setValue([1 => []]);
        Config::set("app.dir.root", "APP_ROOT");
        Config::set("app.dir.temp", "APP_TEMP");
        Config::set("app.dir.var", "APP_VAR");
        Config::set("app.file.bootstrap", "APP_BOOTSTRAP");
        Config::set("app.file.routes", "APP_ROUTES");
        Config::set("core.dir.root", "CORE_ROOT");
        Config::set("core.dir.config", "CORE_CONFIG");
        Config::set("test.overwrite.allowed", "Allowed", Config::ALLOW_OVERWRITE);
        Config::set("test.overwrite.notallowed", "NotAllowed");
        Config::set("test.null", null);
    }
    
    public function testGetConfig(){
        $this->assertEquals("APP_ROOT", Config::get("app.dir.root"));
    }
    
    public function testAllowedOverwrite(){
        Config::set("test.overwrite.allowed", "overwrite");
        $this->assertEquals("overwrite", Config::get("test.overwrite.allowed"));
    }
    
    /**
     * @expectedException   \Fratily\Configer\Exception\CanNotOverwriteException
     */
    public function testNotAllowedOverwrite(){
        Config::set("test.overwrite.notallowed", "overwrite");
    }
    
    public function testGetWild(){
        $this->assertArraySubset(
            ["APP_ROOT", "APP_TEMP", "APP_VAR"],
            Config::get("app.dir.*")
        );
        $this->assertArraySubset(
            ["APP_ROOT", "APP_TEMP", "APP_VAR", "CORE_ROOT", "CORE_CONFIG"],
            Config::get("*.dir.*")
        );
    }
    
    public function testHasValue(){
        $this->assertTrue(Config::has("app.dir.root"));
        $this->assertTrue(Config::has("test.null"));
    }
}