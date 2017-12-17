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
namespace FratilyTest\Controller;

use Fratily\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 *
 *
 * @property-read   ContainerInterface  $container
 * @property-read   ResponseInterface   $response
 * @property-read   RendererInterface   $renderer
 */
class ControllerTest extends \PHPUnit\Framework\TestCase{

    /**
     * @var \Fratily\Controller\Controller
     */
    private $controller;

    public function setup(){
    }

    /**
     * __get で定義されているプロパティへのアクセス
     */
    public function testGetProperty(){
        $this->assertInstanceOf(ContainerInterface::class, $this->controller->container);
        $this->assertInstanceOf(RendererInterface::class, $this->controller->renderer);
        $this->assertInstanceOf(ResponseInterface::class, $this->controller->response);
    }

    /**
     * 存在しないプロパティへのアクセス
     *
     * @expectedException   \Fratily\Exception\PropertyUndefinedException
     */
    public function testGetUndefineProperty(){
        $this->controller->undefine;
    }
}