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

use Fratily\Container\Container;
use Fratily\Router\RouteCollector;
use Fratily\Router\Router;
use Fratily\Http\Server\RequestHandler;
use Fratily\EventManager\EventManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * @var Container
     */
    private $container;

    /**
     * @var EventManagerInterface
     */
    private $eventMng;

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
     * @param   Container   $container
     * @param   EventManagetInterface   $eventMng
     * @param   RouteCollector  $routes
     * @param   bool    $debug
     */
    public function __construct(
        Container $container,
        EventManagerInterface $eventMng,
        RouteCollector $routes,
        bool $debug = false
    ){
        $this->startedAt    = time();
        $this->debug        = $debug;
        $this->container    = $container;
        $this->eventMng     = $eventMng;
        $this->routes       = $routes;
    }

    /**
     * GETメソッドで受けるルーティングルールを追加する
     *
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string  $name
     *  ルーティングルール名。指定しなかった場合は適当な文字が割り当てられる。
     * @param   mixed[] $data
     *  このルールに付随するデータ。
     *  今のところルール特有のミドルウェアの追加方法として利用される。
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    public function get(string $path, $action, string $name = null, array $data = []){
        return $this->addRoute("GET", $path, $action, $name, $data);
    }

    /**
     * POSTメソッドで受けるルーティングルールを追加する
     *
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string  $name
     *  ルーティングルール名。指定しなかった場合は適当な文字が割り当てられる。
     * @param   mixed[] $data
     *  このルールに付随するデータ。
     *  今のところルール特有のミドルウェアの追加方法として利用される。
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    public function post(string $path, $action, string $name = null, array $data = []){
        return $this->addRoute("POST", $path, $action, $name, $data);
    }

    /**
     * PUTメソッドで受けるルーティングルールを追加する
     *
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string  $name
     *  ルーティングルール名。指定しなかった場合は適当な文字が割り当てられる。
     * @param   mixed[] $data
     *  このルールに付随するデータ。
     *  今のところルール特有のミドルウェアの追加方法として利用される。
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    public function put(string $path, $action, string $name = null, array $data = []){
        return $this->addRoute("PUT", $path, $action, $name, $data);
    }

    /**
     * DELETEメソッドで受けるルーティングルールを追加する
     *
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string  $name
     *  ルーティングルール名。指定しなかった場合は適当な文字が割り当てられる。
     * @param   mixed[] $data
     *  このルールに付随するデータ。
     *  今のところルール特有のミドルウェアの追加方法として利用される。
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    public function delete(string $path, $action, string $name = null, array $data = []){
        return $this->addRoute("DELETE", $path, $action, $name, $data);
    }

    /**
     * ルーティングルールを追加する
     *
     * @param   string  $method
     *  許容するHTTPリクエストメソッド。
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string  $name
     *  ルーティングルール名。指定しなかった場合は適当な文字が割り当てられる。
     * @param   mixed[] $data
     *  このルールに付随するデータ。
     *  今のところルール特有のミドルウェアの追加方法として利用される。
     *
     * @return  $this
     *
     * @throws  \InvalidArgumentException
     */
    protected function addRoute(string $method, string $path, $action, string $name = null, array $data = []){
        $method = strtoupper($method);

        if(!in_array($method, ["GET", "POST", "PUT", "DELETE"])){
            throw new \InvalidArgumentException();
        }

        if(is_string($action)){
            $action = $this->parseActionString($action);
        }else if(!($action instanceof \Closure)){
            throw new \InvalidArgumentException();
        }

        if($name === null || $name === ""){
            $name   = "_rule_" . hash("md5", $path . bin2hex(random_bytes(2)));
        }

        $data["_action"]    = $action;

        $this->routes->addRoute($name, $path, (array)$method, $data);

        return $this;
    }

    /**
     * アクション指定文字列が正しいものか確認して成形する
     *
     * @param   string  $action
     *
     * @return  string[]
     *
     * @throws  \InvalidArgumentException
     */
    private function parseActionString(string $action){
        if(($pos = strpos($action, ":")) === false){
            throw new \InvalidArgumentException();
        }

        $controller = substr($action, 0, $pos);
        $method     = substr($action, $pos + 1);

        if(strlen($controller) === 0 || strlen($method) === 0){
            throw new \InvalidArgumentException();
        }

        if($this->container->has($controller)){
            $controller = $this->container->lazyGet($controller);
        }else if(class_exists($controller)){
            $controller = $this->container->lazyNew($controller);
        }else{
            throw new \InvalidArgumentException();
        }

        return [$controller, $method];
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
     * レスポンスを生成する
     *
     * @param   ServerRequestInterface  $request
     *
     * @return  Response
     */
    public function generateResponse(ServerRequestInterface $request){
        $request    = $request
            ->withAttribute("app.debug", $this->debug)
        ;

        return $this->container->newInstance(Response::class, [
            "request"   => $request,
            "handler"   => $this->generateHandler($request),
        ]);
    }

    /**
     * ミドルウェアハンドラを生成する
     *
     * @param   ServerRequestInterface  $request
     *
     * @return  RequestHandlerInterface
     */
    protected function generateHandler(ServerRequestInterface $request){
        $result = $this->routes
            ->createRouter($request->getMethod())
            ->search($request->getUri()->getPath());

        if($result[0] === Router::NOT_FOUND){
            $action = function(){
                throw new \Fratily\Http\Message\Status\NotFound();
            };
        }else{
            $action = $result[2]["_action"];
        }

        $middlewares    = array_merge(
            $this->createDebugMiddlewares(),
            $this->middlewares["before"],
            self::normalizeMiddlewares($result[2]["middleware.before"] ?? []),
            $this->createActionMiddleware($action, $result[1]),
            self::normalizeMiddlewares($result[2]["middleware.before"] ?? []),
            $this->middlewares["after"]
        );

        $handler    = $this->container->newInstance(RequestHandler::class);

        foreach($middlewares as $middleware){
            $handler->append($middleware);
        }

        return $handler;
    }

    /**
     * アクション実行用ミドルウェアリストを作成する
     *
     * @param   mixed   $action
     * @param   mixed[] $params
     *
     * @return  MiddlewareInterface[]
     */
    private function createActionMiddleware($action, array $params){
        $middlewares    = [];

        $middlewares[]  = $this->container->get("core.middleware.action")
            ->setAction($action)
            ->setParams($params)
        ;

        return $middlewares;
    }

    /**
     * デバッグ用ミドルウェアリストを作成する
     *
     * @return  MiddlewareInterface[]
     */
    private function createDebugMiddlewares(){
        $middlewares    = [];

        if($this->debug){
            $middlewares[]  = $this->container->get("core.middleware.debug");
        }

        return $middlewares;
    }
}