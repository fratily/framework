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
namespace Fratily\Framework\Tests;

use Fratily\Framework\Configure;

class ConfigureuerTest extends \PHPUnit\Framework\TestCase{

    public function setup(){
        $ref    = new \ReflectionClass(Configure::class);
        $prop   = $ref->getProperty("config");
        $prop->setAccessible(true);
        $prop->setValue([1 => []]);
        Configure::set("app.dir.root", "APP_ROOT");
        Configure::set("app.dir.temp", "APP_TEMP");
        Configure::set("app.dir.var", "APP_VAR");
        Configure::set("app.file.bootstrap", "APP_BOOTSTRAP");
        Configure::set("app.file.routes", "APP_ROUTES");
        Configure::set("core.dir.root", "CORE_ROOT");
        Configure::set("core.dir.config", "CORE_CONFIG");
        Configure::set("test.overwrite.allowed", "Allowed", Configure::ALLOW_OVERWRITE);
        Configure::set("test.overwrite.notallowed", "NotAllowed");
        Configure::set("test.null", null);
    }

    public function testGetConfigure(){
        $this->assertEquals("APP_ROOT", Configure::get("app.dir.root"));
    }

    public function testAllowedOverwrite(){
        Configure::set("test.overwrite.allowed", "overwrite");
        $this->assertEquals("overwrite", Configure::get("test.overwrite.allowed"));
    }

    /**
     * @expectedException   \Fratily\Configureer\Exception\CanNotOverwriteException
     */
    public function testNotAllowedOverwrite(){
        Configure::set("test.overwrite.notallowed", "overwrite");
    }

    public function testGetWild(){
        $this->assertArraySubset(
            ["APP_ROOT", "APP_TEMP", "APP_VAR"],
            Configure::get("app.dir.*")
        );
        $this->assertArraySubset(
            ["APP_ROOT", "APP_TEMP", "APP_VAR", "CORE_ROOT", "CORE_CONFIG"],
            Configure::get("*.dir.*")
        );
    }

    public function testHasValue(){
        $this->assertTrue(Configure::has("app.dir.root"));
        $this->assertTrue(Configure::has("test.null"));
    }
}