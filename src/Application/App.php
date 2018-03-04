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
use Fratily\Router\Router;
use Fratily\Reflection\ReflectionCallable;
use Fratily\Http\Message\Response as HttpResponse;
use Fratily\Http\Server\RequestHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
final class App implements MiddlewareInterface{

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
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var RouteCollector
     */
    private $routes;

    /**
     * @var RequestHandler
     */
    private $handler;

    /**
     * レスポンスファクトリの値を正しくする
     *
     * @param   ResponseFactoryInterface|string|null    $factory
     *
     * @return  ResponseFactoryInterface|string|null
     */
    private static function normalizeResponseFactory($factory){
        static $implements  = [];

        if($factory instanceof ResponseFactoryInterface){
            return $factory;
        }else if(is_string($factory)){
            if(class_exists($factory)){
                if(!isset($implements[$factory])){
                    $implements[$factory]   = (new \ReflectionClass($factory))
                        ->implementsInterface(ResponseFactoryInterface::class);
                }

                if($implements[$factory]){
                    return $factory;
                }
            }else{
                return $factory;
            }
        }

        return null;
    }

    /**
     * ミドルウェアリストを正しくする
     *
     * @param   MiddlewareInterface[]|MiddlewareInterface|null  $middlewares
     *
     * @return  MiddlewareInterface[]|null
     */
    private static function normalizeMiddlewares($middlewares){
        $return = [];

        foreach((array)$middlewares as $middleware){
            if(!($middleware instanceof MiddlewareInterface)){
                $return[]   = $middleware;
            }
        }

        return empty($return) ? null : $return;
    }

    /**
     * Constructor
     *
     * @param   ConteinerInterface  $container
     * @param   ResponseFactoryInterface    $responseFactory
     * @param   bool    $debug
     */
    public function __construct(
        ContainerInterface $container,
        ResponseFactoryInterface $responseFactory,
        bool $debug = false
    ){
        $this->startedAt        = microtime(true);
        $this->isDebug          = $debug;
        $this->container        = $container;
        $this->responseFactory  = $responseFactory;
        $this->routes           = new RouteCollector();
        $this->handler          = new RequestHandler($responseFactory);
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
            $result = $this->routes
                ->createRouter($request->getMethod())
                ->search($request->getUri()->getPath());

            if($result[0] === Router::NOT_FOUND){
                throw new \Fratily\Http\Status\NotFound();
            }
            
            $this->constructHandler(
                $this->createActionMiddleware(
                    $result[2]["action"],
                    $result[1]
                ),
                $result[2]["middleware.before"],
                $result[2]["middleware.after"],
                $result[2]["factory.response"]
            );

            return new Response($this->handler->handle($request));
        }catch(\Throwable $e){
            $method = "throwable";
            $params = [
                "debug"     => $this->isDebug,
                "status"    => 500,
                "e"         => $e
            ];

            if($e instanceof \Fratily\Http\Status\HttpStatus){
                $method             = "http{$e->getStatus()}";
                $params["status"]   = $e->getStatus();

                if($e instanceof \Fratily\Http\Status\MethodNotAllowed){
                    $params["allowed"]  = $e->getAllowed();
                }
            }
        }

        $controller = $this->getErrorController();
        $method     = method_exists($controller, $method) ? $method : "status";
        $action     = new ReflectionCallable([$controller, $method]);

        $contents   = $action->invokeMapedArgs($controller, [
            "_request"  => $request,
            "_params"   => $params,
        ] + $params) ?? "";

        $response   = $response ?? new HttpResponse();
        $response   = $response->withStatus($status);

        if(is_scalar($contents) && $response->getBody()->isWritable()){
            $response->getBody()->write($contents);
        }else if($contents instanceof ResponseInterface){
            $response   = $contents;
        }else{
            throw new \UnexpectedValueException;
        }

        return new Response($response);
    }

    //  Middleware

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $result = $this->routes
            ->createRouter($request->getMethod())
            ->search($request->getUri()->getPath());

        if($result[0] === Router::NOT_FOUND){
            throw new \Fratily\Http\Status\NotFound();
        }

        //  ハンドラを実行してレスポンスを取得(レスポンスの指定があれば書き換え)
        $response   = $handler->handle($request);

        if(isset($data["response"])){
            if($data["response"] instanceof ResponseInterface){
                $response   = $data["response"];
            }else{
                $response   = $data["response"]->createResponse();
            }
        }

        return $this->createActionHandler(
            $this->createActionMiddleware(
                $data["action"],
                $result[1]
            ),
            $response,
            $data["middleware.before"] ?? null,
            $data["middleware.after"] ?? null
        )->handle($request);
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
        if($middleware === $this && !$this->handler->hasClass(self::class)
            || $middleware !== $this
        ){
            $this->handler->append($middleware);
        }

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
        $this->handler->insertBeforeObject($this, $middleware);

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
        $this->handler->insertAfterObject($this, $middleware);

        return $this;
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
            return new ActionMiddleware($action, $params);
        }

        return new ActionMiddleware(
            new $action[0]($this->container), $action[1], $params
        );
    }
    
    /**
     * リクエストハンドラをアクションミドルウェアを追加し再構成する
     * 
     * @param   MiddlewareInterface $action
     * @param   MiddlewareInterface[]   $beforeMiddlewares  [optional]
     * @param   MiddlewareInterface[]   $afterMiddlewares   [optional]
     * @param   ResponseFactoryInterface    $factory    [optional]
     * 
     * @return  void
     */
    private function constructHandler(
        MiddlewareInterface $action,
        array $beforeMiddlewares = null,
        array $afterMiddlewares = null,
        ResponseFactoryInterface $factory = null
    ){
        if(!$this->handler->hasObject($this)){
            $this->handler->append($this);
        }
        
        $this->handler->replaceObject($this, $action);
        
        if($beforeMiddlewares !== null){
            foreach($beforeMiddlewares as $middleware){
                $this->handler->insertBeforeObject($action, $middleware);
            }
        }
        
        if($afterMiddlewares !== null){
            foreach(array_reverse($afterMiddlewares) as $middleware){
                $this->handler->insertAfterObject($action, $middleware);
            }
        }
        
        if($factory !== null){
            $this->handler->setResponseFactory($factory);
        }
    }

    //  Router

    /**
     * ルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     * @param   string  $methods
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function route(string $name, string $path, $action, array $options = []){
        $data       = [];
        $options    = $options + [
            "allow" => null,
            "middleware.before" => null,
            "middleware.after"  => null,
            "factory.response"  => null
        ];

        //  Resolve action
        if(is_callable($action)){
            $data["action"] = $action;
        }else{
            $action = is_string($action) ? [$action, "index"] : $action;

            if(!is_array($action)){
                throw new \InvalidArgumentException();
            }else if(!isset($action[0]) || !isset($action[0])
                || !is_string($action[0] || !is_string($action[1]))
                || $action[0] === "" || $action[1] === ""
            ){
                throw new \InvalidArgumentException();
            }else if(!Controller\Controller::isController($action[0])){
                throw new \InvalidArgumentException();
            }

            $data["action"] = [$action[0], $action[1]];
        }

        $data["middleware.before"]  = self::normalizeMiddlewares(
            $options["middleware.before"]
        );
        $data["middleware.after"]   = self::normalizeMiddlewares(
            $options["middleware.after"]
        );
        $data["factory.response"]   = self::normalizeResponseFactory(
            $options["factory.response"]
        );

        $this->routes->addRoute($name, $path, $options["allow"], $data);
    }

    /**
     * GETメソッドを許容するルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function get(string $name, string $path, $action){
        $this->route($name, $path, $action, ["GET"]);
    }

    /**
     * POSTメソッドを許容するルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function post(string $name, string $path, $action){
        $this->route($name, $path, $action, ["POST"]);
    }

    /**
     * PUTメソッドを許容するルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function put(string $name, string $path, $action){
        $this->route($name, $path, $action, ["PUT"]);
    }

    /**
     * PATCHメソッドを許容するルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function patch(string $name, string $path, $action){
        $this->route($name, $path, $action, ["PATCH"]);
    }

    /**
     * DELETEメソッドを許容するルートを追加する
     *
     * @param   string  $name
     * @param   string  $path
     * @param   callabl|string[]|string   $action
     *
     * @return  void
     *
     * @throws  \InvalidArgumentException
     */
    public function delete(string $name, string $path, $action){
        $this->route($name, $path, $action, ["DELETE"]);
    }

    /**
     * エラーコントローラーのインスタンスを返す
     *
     * @return  Controller\ErrorControllerInterface
     */
    private function getErrorController(){
        $class  = Configure::getErrorController();

        return new $class($this->container);
    }
}