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
namespace Fratily\Framework;

use Fratily\Router\{RouteCollector, Router};
use Fratily\Http\Message\Status\NotFound;
use Fratily\Http\Server\RequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\{
    MiddlewareInterface,
    RequestHandlerInterface
};
use \Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class Application{

    /**
     * @var int
     */
    private $startedAt;

    private $debug;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var RouteCollector
     */
    private $routes;

    /**
     * @var MiddlewareInterface[][]
     */
    private $middlewares    = [
        "before"    => [],
        "after"     => [],
    ];

    /**
     * ミドルウェアリストを正しくする
     *
     * @param   MiddlewareInterface[]|MiddlewareInterface|null  $middlewares
     *
     * @return  MiddlewareInterface[]
     */
    private static function normalizeMiddlewares($middlewares){
        $return = [];

        foreach((array)$middlewares as $middleware){
            if(!($middleware instanceof MiddlewareInterface)){
                $return[]   = $middleware;
            }
        }

        return $return;
    }

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     * @param   RouteCollector  $routes
     */
    public function __construct(ContainerInterface $container, RouteCollector $routes, bool $debug = false){
        $this->startedAt    = time();
        $this->debug        = $debug;
        $this->container    = $container;
        $this->routes       = $routes;
    }

    /**
     * ミドルウェアを末尾に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function append(MiddlewareInterface $middleware){
        $this->middlewares["after"][]   = $middleware;

        return $this;
    }

    /**
     * ミドルウェアを先頭に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function prepend(MiddlewareInterface $middleware){
        array_unshift($this->middlewares["before"], $middleware);

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
        $this->middlewares["before"][]  = $middleware;

        return $this;
    }

    /**
     * ミドルウェアをアクションミドルウェアの直後に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function addAfterAction(MiddlewareInterface $middleware){
        array_unshift($this->middlewares["after"], $middleware);

        return $this;
    }

    /**
     * ミドルウェアハンドラを生成する
     *
     * @param   ServerRequestInterface  $request
     *
     * @return  RequestHandlerInterface
     */
    public function generateHandler(ServerRequestInterface $request){
        $result = $this->routes
            ->createRouter($request->getMethod())
            ->search($request->getUri()->getPath());

        if($result[0] === Router::NOT_FOUND){
            $action = function(){throw new NotFound();};
        }else{
            $action = $result[2]["action"];
        }

        $middlewares    = array_merge(
            $this->middlewares["before"],
            self::normalizeMiddlewares($result[2]["middleware.before"] ?? []),
            [$this->createActionMiddleware($action, $result[1])],
            self::normalizeMiddlewares($result[2]["middleware.before"] ?? []),
            $this->middlewares["after"]
        );

        if(!$this->container->has(ResponseFactoryInterface::class)){
            throw new \LogicException();
        }

        $factory    = $this->container->get(ResponseFactoryInterface::class);

        if(!($factory instanceof ResponseFactoryInterface)){
            throw new \LogicException();
        }

        $handler    = new RequestHandler($factory);

        foreach($middlewares as $middleware){
            $handler->append($middleware);
        }

        return $handler;
    }

    /**
     * アクション実行ミドルウェアを作成する
     *
     * @param   callable|string $action
     * @param   mixed[] $params
     *
     * @return  ActionMiddleware
     *
     * @throws \LogicException
     */
    private function createActionMiddleware($action, array $params = []){
        if(is_callable($action)){
            if(is_array($action) && is_string($action[0])){
                $reflection = new \ReflectionMethod($action[0], $action[1]);

                if($reflection->isStatic()){
                    return new Middleware\ActionMiddleware($this->container, $action, $params);
                }
            }else{
                return new Middleware\ActionMiddleware($this->container, $action, $params);
            }
        }

        return Middleware\ActionMiddleware::getInstanceWithController(
            $this->container,
            $action[0],
            $action[1],
            $params
        );
    }
}