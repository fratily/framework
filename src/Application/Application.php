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
namespace Fratily\Application;

use Fratily\Router\RouteCollector;
use Fratily\Router\Dispatcher;
use Fratily\Http\Server\RequestHandler;
use Fratily\Http\Server\RequestHandlerInterface;
use Fratily\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 *
 * @property-read   ContainerInterface  $container
 */
class App implements MiddlewareInterface{

    const NS_CTRL   = "App\\Controller\\";

    /**
     * @var float
     */
    private $startedAt;

    /**
     * @var bool
     */
    private $isDebug;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RouteCollector
     */
    private $routes;

    /**
     * @var MiddlewareInterface[]
     */
    private $middleware         = [];

    /**
     * @var MiddlewareInterface[]
     */
    private $beforeMiddleware   = [];

    /**
     * @var MiddlewareInterface[]
     */
    private $afterMiddleware    = [];

    /**
     * Constructor
     *
     * @param   bool    $debug
     */
    public function __construct(bool $debug){
        $this->startedAt        = microtime(true);
        $this->isDebug          = $debug;
        $this->container        = Configure::getContainer();
        $this->routes           = new RouteCollector();
    }

    /**
     * アプリケーションを実行してレスポンスを生成する
     *
     * @param   ServerRequestInterface  $request
     * @param   ResponseInterface   $response
     *
     *
     * @return  Response
     */
    public function handle(
        ServerRequestInterface $request,
        ResponseInterface $response = null
    ): Response{
        try{
            return new Response(
                $this->getHandler($response)->handle($request)
            );
        }catch(\Fratily\Http\Status\HttpStatus $e){
            
        }catch(\Throwable $e){

        }
    }

    //  Middleware

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $localHandler   = new RequestHandler();
        $dispatcher     = new Dispatcher($this->routeCollector);
        $result         = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch($result[]){}
        foreach($this->beforeMiddleware as $middleware){
            $localHandler->append($middleware);
        }

        $localHandler->append();

    }

    //  Request Handler

    /**
     * ミドルウェアを末尾に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function append(MiddlewareInterface $middleware){
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * ミドルウェアをアクションミドルウェアの直前に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function addBeforeAction(MiddlewareInterface $middleware){
        $this->beforeMiddleware[]   = $middleware;
    }

    /**
     * ミドルウェアをアクションミドルウェアの直後に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function addAfterAction(MiddlewareInterface $middleware){
        $this->afterMiddleware[]    = $middleware;
    }

    /**
     * ミドルウェアを実行するハンドラを作成する
     *
     * @return  RequestHandlerInterface
     */
    public function getHandler(ResponseInterface $response = null){
        $handler    = new RequestHandler($response);

        foreach(){

        }
    }

    public function getController(string $name){
        $class  = Configure::getControllerNamespace()
            . strtr(ucwords(strtr($name, ["-" => " "])), [" " => ""])
            . "Controller";

        if(class_exists($class)){
            $object = new $class($this->container);

            if($object instanceof Controller){
                return $object;
            }
        }

        return null;
    }

    public function getErrorController(string $name){
        $class  = static::NS_CTRL
            . strtr(ucwords(strtr($name, ["-" => " "])), [" " => ""])
            . "Controller";

        if(class_exists($class)){
            $object = new $class($this->container);

            if($object instanceof ErrorController){
                return $object;
            }
        }

        return null;
    }
}