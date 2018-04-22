# FratilyPHP

Fratily is a web application framework being developed with PHP, for studying.

# 現状での使い方

```php

/**
 * Aura DI コンテナ
 */
class DefaultConfig extends Aura\Di\ContainerConfig{

    public function define(Aura\Di\Container $di){
        $di->set(Interop\Http\Factory\ResponseFactoryInterface::class, $di->lazyNew(Fratily\Http\Factory\ResponseFactory::class));
    }
}

/**
 * コントローラークラス
 */
class IndexController extends Fratily\Framework\Controller\Controller{

    public function index(){
        return "This is index page";
    }

    public function page($page){
        return "This is page$page page";
    }
}

/*
 * アクセスログのようなものを出力するミドルウェア
 */
class AccessMiddleware implements Psr\Http\Server\MiddlewareInterface{

    private $dir;

    public function __construct(string $dir){
        if(!is_dir($dir)){
            throw new InvalidArgumentException();
        }

        $this->dir  = realpath($dir);
    }

    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface{
        $fp = fopen($this->dir . DIRECTORY_SEPARATOR . "log", "a+");

        fwrite($fp, date("Y-m-d H:i:s") . " " . $_SERVER["REQUEST_METHOD"] . "\t" . $_SERVER["REQUEST_URI"] . PHP_EOL);

        return $handler->handle($request);
    }
}

// DIコンテナを生成
$c  = (new Aura\Di\ContainerBuilder)->newConfiguredInstance([
    DefaultConfig::class
]);

// ルート定義
$routes = new Fratily\Router\RouteCollector();

$routes->get("index", "/", [
    "action"    => [IndexController::class, "index"]
]);

$routes->get("page", "/page/{page:[1-9][0-9]*}", [
    "action"    => [IndexController::class, "page"]
]);

$routes->get("func", "/func", [
    "action"    => function(){
        return "This is function page.";
    }
]);

// アプリケーションインスタンスを生成
$app    = new Fratily\Framework\Application($c, $routes, true);
// ミドルウェアを追加
$app->append(new AccessMiddleware(__DIR__));

// リクエストインスタンスを生成
$request    = (new Fratily\Http\Factory\ServerRequestFactory())->createServerRequestFromArray($_SERVER);

// ミドルウェアハンドラを実行
$handler    = $app->generateHandler($request);

// レスポンスを送信
$emitter    = new Fratily\Http\Message\Response\Emitter();
$emitter->emit($handler->handle($request));
```