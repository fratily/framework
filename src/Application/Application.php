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
     * @var RequestHandlerInterface|null
     */
    private $handler;

    /**
     * @var RouteCollector
     */
    private $routeCollector;
    
    /**
     * @var Dispatcher
     */
    
    /**
     * Constructor
     *
     * @param   bool    $debug
     *
     * @return  void
     */
    public function __construct(
        RouteCollector $collector,
        RequestHandlerInterface $handler
    ){
        $this->startedAt        = microtime(true);
        $this->isDebug          = Configure::isDebug();
        $this->container        = Configure::getContainer();
        $this->routeCollector   = $collector;
        $this->router           = new Dispatcher($this->routeCollector);
        $this->handler          = $handler;
    }

    /**
     * アプリケーションを実行してレスポンスを生成する
     *
     * @param   ServerRequestInterface  $request
     *
     *
     * @return  Response
     */
    public function handle(ServerRequestInterface $request): Response{
        return new Response($this->handler->handle($request));
    }
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $localHandler   = new RequestHandler;
    }
    
}