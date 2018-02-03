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
use Fratily\Reflection\ReflectionCallable;
use Fratily\Http\Message\Response as HttpResponse;
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

    private function resolveParams($params){
        $return = [
            "action"        => $this->resolveAction($params["action"] ?? null),
            "middleware"    => [
                "before"    => $this->resolveMiddleware($params["middleware.before"] ?? []),
                "after"     => $this->resolveMiddleware($params["middleware.after"] ?? [])
            ],
            "response"      => $this->resolveResponseFactory($params["response.factory"])
        ];
        
        unset(
            $params["action"],
            $params["middleware.before"],
            $params["middleware.after"]
        );
        
        $return["params"]   = $params;
        
        return $return;
    }
    
    private function resolveAction($action){
        if(is_callable($action)){
            return $action;
        }else if(is_string($action) && ($pos = strpos($action, "@")) !== false){
            $controller = $this->getController(substr($action, 0, $pos));
            $method     = substr($action, $pos + 1);
            
            if($controller !== null && method_exists($controller, $method)){
                return [
                    "controller"    => $controller,
                    "method"        => $method
                ];
            }
        }
        
        return null;
    }
    
    private function resolveMiddleware($middlewares){
        return array_filter((array)$middlewares, function($v){
            return $v instanceof MiddlewareInterface;
        });
    }

    private function resolveResponseFactory($factory){
        if(is_object($factory)){
            if($factory ){
                
            }
        }
    }

    /**
     * Constructor
     *
     * @param   bool    $debug
     */
    public function __construct(bool $debug){
        $this->startedAt        = microtime(true);
        $this->isDebug          = $debug;
        $this->container        = Configure::getContainer();
        $this->routes           = Configure::getRoutes();
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
            $method = "http{$e->getStatus()}";
            $status = $e->getStatus();
            $params = [];
            
            if($e instanceof \Fratily\Http\Status\MethodNotAllowed){
                $params["allowed"]  = $e->getAllowed();
            }
        }catch(\Throwable $e){
            $method = "throwable";
            $status = 500;
            $params = [
                "e" => $e
            ];
        }

        $controller = $this->getErrorController();
        $method     = method_exists($controller, $method) ? $method : "status";
        $action     = new ReflectionCallable([$controller, $method]);
        
        $contents   = $action->invokeMapedArgs($controller, [
            "_request"  => $request,
            "_params"   => $params,
            "status"    => $status
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
        $localHandler   = new RequestHandler($handler->handle($request));
        $dispatcher     = new Dispatcher($this->routes);
        $result         = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch($result[0]){
            case Dispatcher::NOT_FOUND:
                throw new \Fratily\Http\Status\NotFound();
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new \Fratily\Http\Status\MethodNotAllowed($result[1]);
        }
        
        $params = $this->resolveParams($result[1]);
        
        if($params["action"] === null){
            throw new \LogicException;
        }
        
        foreach($params["middleware"]["before"] as $middleware){
            $localHandler->append($middleware);
        }

        if(is_array($params["action"]) && isset($params["action"]["controller"])){
            $localHandler->append(
                new ActionMiddleware(
                    $params["action"]["controller"],
                    $params["action"]["method"],
                    $params["params"]
                )
            );
        }else{
            $localHandler->append(
                new ActionMiddleware($params["action"], $params["params"])
            );
        }

        foreach($params["middleware"]["after"] as $middleware){
            $localHandler->append($middleware);
        }
        
        return $localHandler->handle($request);
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
        $find       = false;
        
        foreach($this->middleware as $middleware){
            if($middleware === $this){
                $find   = true;
            }
            
            $handler->append($middleware);
        }
        
        if(!$find){
            $handler->append($this);
        }
        
        return $handler;
    }

    /**
     * 指定名のコントローラーインスタンスを返す
     * 
     * @param   string  $name
     * 
     * @return  Controller\Controller|null
     */
    public function getController(string $name){
        $class  = Configure::getControllerNamespace()
            . strtr(ucwords(strtr($name, ["-" => " "])), [" " => ""])
            . "Controller";

        if(class_exists($class)){
            $ref    = new \ReflectionClass($class);
            
            if($ref->isSubclassOf(Controller\Controller::class)){
                return new $class($this->container);
            }
        }

        return null;
    }

    /**
     * エラーコントローラーのインスタンスを返す
     * 
     * @return  Controller\ErrorControllerInterface
     */
    public function getErrorController(){
        $class  = Configure::getErrorController();
        
        return new $class($this->container);
    }
}