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
namespace FratilyTest\Controller;

use Fratily\Renderer\RendererInterface;
use Fratily\Http\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Container\ContainerInterface;

/**
 *
 */
class ControllerTest extends \PHPUnit\Framework\TestCase{

    /**
     * @var \Fratily\Controller\Controller
     */
    private $controller;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * {@inheritdoc}
     */
    public function setup(){
        $container  = $this->getMockForAbstractClass(ContainerInterface::class);
        $resFactory = $this->getMockForAbstractClass(ResponseFactoryInterface::class);
        $renderer   = $this->getMockForAbstractClass(RendererInterface::class);
        $response   = $this->getMockForAbstractClass(ResponseInterface::class);
        $stream     = $this->getMockForAbstractClass(StreamInterface::class);

        $stream->expects($this->any())
            ->method("write")
            ->with($this->isType("string"))
            ->willReturn(1);

        $response->expects($this->any())
            ->method("getBody")
            ->willReturn($stream);

        $resFactory->expects($this->any())
            ->method("createResponse")
            ->willReturn($response);

        $this->controller   = $this->getMockForAbstractClass(TestController::class, [
            $container, $resFactory, $renderer
        ]);

        $this->request      = $this->getMockForAbstractClass(ServerRequestInterface::class);
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

    /**
     * 存在しないアクション実行
     *
     * @expectedException   \Fratily\Controller\Exception\ActionUndefinedException
     */
    public function testExecuteUndefineAction(){
        $this->controller->execute("undefineAction", $this->request);
    }

    /**
     * staticなアクション実行
     *
     * @expectedException   \Fratily\Controller\Exception\ActionImplementException
     */
    public function testExecuteStaticAction(){
        $this->controller->execute("staticAction", $this->request);
    }

    /**
     * privateなアクション実行
     *
     * @expectedException   \Fratily\Controller\Exception\ActionImplementException
     */
    public function testExecutePrivateAction(){
        $this->controller->execute("privateAction", $this->request);
    }

    /**
     * protectedなアクション実行
     *
     * @expectedException   \Fratily\Controller\Exception\ActionImplementException
     */
    public function testExecuteProtectedAction(){
        $this->controller->execute("protectedAction", $this->request);
    }

    /**
     * 不正な値を返すアクション実行
     *
     * @expectedException   \Fratily\Controller\Exception\ActionImplementException
     */
    public function testExecuteReturnsUnexpectValueAction(){
        $this->controller->execute("returnArray", $this->request);
    }

    /**
     * 正しい値を返すアクション実行
     */
    public function testExecuteValidAction(){
        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->controller->execute("returnString", $this->request)
        );
        $this->assertInstanceOf(
            ResponseInterface::class,
            $this->controller->execute(
                "returnResponse",
                $this->request, [
                    "response" => $this->controller->response
                ]
            )
        );
    }
}