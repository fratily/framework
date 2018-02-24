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
     * @var RequestHandler
     */
    private $handler;

    private static function normalizeRouteData(array $data){
        $data["action"] = self::normalizeRouteAction(
            $data["action"] ?? null
        );

        $data["response"]   = self::normalizeRouteResponse(
            $data["response"] ?? null
        );

        $data["middleware.before"]  = self::normalizeRouteMiddleware(
            $data["middleware.befoer"] ?? null
        );

        $data["middleware.after"]   = self::normalizeRouteMiddleware(
            $data["middleware.after"] ?? null
        );
    }

    private static function normalizeRouteAction($action){
        if(is_callable($action)
            || (is_string($action) && strpos($action, "@") > 0)
        ){
            return $action;
        }

        return null;
    }

    private static function normalizeRouteResponse($response){
        if($response instanceof ResponseInterface
            || $response instanceof ResponseFactoryInterface
        ){
            return $response;
        }

        return null;
    }

    private static function normalizeRouteMiddleware($middlewares){
        $return = [];

        foreach((array)$middlewares as $middleware){
            if(!($middleware instanceof MiddlewareInterface)){
                $return[]   = $middleware;
            }
        }

        return empty($return) ? null : $return;
    }

    /**
     * ルーティング結果から取得したデータをバリデーションする
     *
     * @param   mixed[] $data
     *
     * @return  bool
     */
    private static function validDispatchData(array $data){
        return self::validAction($data["action"] ?? null)
            && self::validMiddleware($data["response"] ?? null)
            && self::validMiddleware($data["middleware.before"] ?? null)
            && self::validResponse($data["middleware.after"] ?? null);
    }

    /**
     * アクションをバリデーションする
     *
     * 1文字以上の文字列、もしくはコーラブルな値を許容する
     *
     * @param   mixed   $action
     *
     * @return  bool
     */
    private static function validAction($action){
        return is_callable($action) || is_string($action) && $action !== "";
    }

    /**
     * ミドルウェアをバリデーションする
     *
     * 単一のミドルウェアインスタンス、もしくはミドルウェアのリストを許容する
     *
     * @param   mixed   $middlewares
     *
     * @return  bool
     */
    private static function validMiddleware($middlewares){
        if($middlewares !== null){
            foreach((array)$middlewares as $middleware){
                if(!($middleware instanceof MiddlewareInterface)){
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * レスポンスをバリデーションする
     *
     * レスポンスインスタンス、もしくはレスポンスファクトリを許容する
     *
     * @param   mixed   $response
     *
     * @return  bool
     */
    private static function validResponse($response){
        return $response === null
            || ($response instanceof ResponseInterface)
            || ($response instanceof ResponseFactoryInterface);
    }

    /**
     * Constructor
     *
     * @param   ConteinerInterface  $container
     * @param   bool    $debug
     */
    public function __construct(ContainerInterface $container, bool $debug){
        $this->startedAt    = microtime(true);
        $this->isDebug      = $debug;
        $this->container    = $container;
        $this->routes       = new RouteCollector();
        $this->handler      = new RequestHandler();
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
            if(!$this->handler->hasClass(self::class)){
                $this->handler->append($this);
            }

            if($response !== null){
                $this->handler->setResponse($response);
            }

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

        $data   = self::normalizeRouteData($result[2]);

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
     * アクション実行ハンドラを作成する
     *
     * @param   ActionMiddleware    $action
     * @param   ResponseInterface|null  $response
     * @param   MidlewareInterface|MiddlewareInterface[]|null   $before
     * @param   MidlewareInterface|MiddlewareInterface[]|null   $after
     *
     * @return  RequestHandler
     */
    private function createActionHandler(
        ActionMiddleware $action,
        ResponseInterface $response = null,
        $before = null,
        $after = null
    ){
        $handler    = new RequestHandler();

        $handler->setResponse($response);

        if($before !== null){
            foreach((array)$before as $middleware){
                $handler->append($middleware);
            }
        }

        $handler->append($action);

        if($after !== null){
            foreach((array)$after as $middleware){
                $handler->append($middleware);
            }
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
            return new ActionMiddleware($action, $params);
        }

        if(($pos = strpos($action, "@")) !== false){
            $controller = substr($action, 0, $pos);
            $method     = substr($action, $pos + 1);
        }else{
            $controller = $action;
            $method     = "index";
        }

        $object = $this->getController($controller);

        if($object === null){
            throw new \LogicException;
        }else if(!method_exists($object, $method)){
            throw new \LogicException;
        }

        return new ActionMiddleware($object, $method, $params);
    }


    /**
     * 指定名のコントローラーインスタンスを返す
     *
     * @param   string  $name
     *
     * @return  Controller\Controller|null
     */
    private function getController(string $name){
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
    private function getErrorController(){
        $class  = Configure::getErrorController();

        return new $class($this->container);
    }
}