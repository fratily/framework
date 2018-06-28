<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Framework;

use Fratily\Framework\Container as ContainerConfig;
use Fratily\Container\ContainerFactory;
use Fratily\Container\Container;
use Fratily\Router\RouteCollector;
use Fratily\Router\Route;
use Fratily\Http\Server\RequestHandler;
use Fratily\EventManager\EventManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
class Application{

    use Traits\DebugTrait;
    use Traits\TimelineTrait;
    use Traits\LogTrait;

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
     * アプリケーションインスタンスを生成する
     *
     * @param   mixed[] $containerConfig
     *  コンテナ構築クラスの配列
     * @param   bool    $debug
     *  アプリケーションをデバッグモードで起動するか
     *
     * @return  static
     *
     * @throws  LogicException
     */
    public static function create(array $containerConfig, bool $debug){
        $startedAt    = microtime(true);
        $timeline           = [];
        $containerConfig    = array_merge(
            [new ContainerConfig\AppConfig($debug)],
            $containerConfig,
            [
                new ContainerConfig\TraitConfig(),
                new ContainerConfig\TypeConfig(),
                new ContainerConfig\ActionConfig(),
                new ContainerConfig\MiddlewareConfig()
            ],
            $debug ? [new ContainerConfig\DebugConfig($startedAt)] : [],
            [new ContainerConfig\CoreConfig()]
        );

        $start      = microtime(true);
        $container  = (new ContainerFactory())->createWithConfig($containerConfig, true);
        $end        = microtime(true);

        $timeline["container.factory"]  = [$start, $end];

        $start  = microtime(true);
        $app    = $container->get("app");
        $end    = microtime(true);

        $timeline["app.construct"]  = [$start, $end];

        if(!($app instanceof static)){
            throw new \LogicException;
        }

        if($debug){
            foreach($timeline as $name => $time){
                $container->get("core.debugbar.timeline")->addLine($name, $time[0], $time[1]);
            }
        }

        return $app;
    }

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
     *  アプリケーションで使用するDIコンテナインスタンス
     * @param   EventManagetInterface   $eventMng
     *  アプリケーションで使用するイベントマネージャインスタンス
     * @param   RouteCollector  $routes
     *  アプリケーションで使用するルーティングルール定義インスタンス
     */
    public function __construct(
        Container $container,
        EventManagerInterface $eventMng,
        RouteCollector $routes
    ){
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
     * @param   string  $host
     *  許容するホスト名。ワイルドカード構文を用いる。
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
    public function get(
        string $path,
        $action,
        string $host = "*",
        string $name = null,
        array $data = []
    ){
        return $this->addRoute($path, $action, "GET", $host, $name, $data);
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
     * @param   string  $host
     *  許容するホスト名。ワイルドカード構文を用いる。
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
    public function post(
        string $path,
        $action,
        string $host = "*",
        string $name = null,
        array $data = []
    ){
        return $this->addRoute($path, $action, "POST", $host, $name, $data);
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
     * @param   string  $host
     *  許容するホスト名。ワイルドカード構文を用いる。
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
    public function put(
        string $path,
        $action,
        string $host = "*",
        string $name = null,
        array $data = []
    ){
        return $this->addRoute($path, $action, "PUT", $host, $name, $data);
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
     * @param   string  $host
     *  許容するホスト名。ワイルドカード構文を用いる。
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
    public function delete(
        string $path,
        $action,
        string $host = "*",
        string $name = null,
        array $data = []
    ){
        return $this->addRoute($path, $action, "DELETE", $host, $name, $data);
    }

    /**
     * ルーティングルールを追加する
     *
     * @param   string  $path
     *  ルーティングルール
     * @param   string|\Closure $action
     *  アクションを示す。クロージャーの場合はそれをアクションとし、
     *  文字列の場合は「コントローラークラス:アクションメソッド」として理解される。
     *  コントローラークラスはクラス名はもちろんDIコンテナに追加したサービス名も指定できる。
     * @param   string[]|string $allows
     *  許容するHTTPメソッドリスト。
     * @param   string  $host
     *  許容するホスト名。ワイルドカード構文を用いる。
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
    protected function addRoute(
        string $path,
        $action,
        $allows = "GET",
        string $host = "*",
        $name = null,
        array $data = []
    ){
        if(is_string($action)){
            $action = $this->parseActionString($action);
        }else if(!($action instanceof \Closure)){
            throw new \InvalidArgumentException();
        }

        $data["_action"]    = $action;

        $this->routes->add(
            Route::newInstance($path, $host, $allows, $data)->withName($name)
        );

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
        $this->startTimeline("handler.generate");

        $this->startTimeline("router.generate");
        $router = $this->routes
            ->router($request->getUri()->getHost(), $request->getMethod())
        ;
        $this->endTimeline("router.generate");

        $this->startTimeline("router.routing");
        $result = $router->search($request->getUri()->getPath());
        $this->endTimeline("router.routing");

        if($result->found){
            $this->info("Match on route {$result->name}.");

            $action = $result->data["_action"];
        }else{
            $this->info("There were no matching routes");

            $action = function(){
                throw new \Fratily\Http\Message\Status\NotFound();
            };
        }

        $middlewares    = array_merge(
            $this->createWrapperMiddlewares(),
            $this->middlewares["before"],
            self::normalizeMiddlewares($result->data["middleware.before"] ?? []),
            $this->createActionMiddleware($action, $result->params),
            self::normalizeMiddlewares($result->data["middleware.before"] ?? []),
            $this->middlewares["after"]
        );

        $handler    = $this->container->newInstance(RequestHandler::class);

        foreach($middlewares as $middleware){
            $handler->append($middleware);
        }

        $this->endTimeline("handler.generate");

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
    private function createWrapperMiddlewares(){
        $middlewares    = [];

        $middlewares[]  = $this->container->get("core.middleware.error");

        if($this->isDebug()){
            $middlewares[]  = $this->container->get("core.middleware.debug");
        }

        return $middlewares;
    }
}