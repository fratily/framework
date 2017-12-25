<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Core;

use Fratily\Configer\Configure;
use Fratily\Http\Response;
use Fratily\Http\Kernel;
use Fratily\Http\ResponseFactoryInterface;
use Fratily\Router\RouterInterface;
use Fratily\Renderer\RendererInterface;
use Fratily\Controller\ControllerFactory;
use Fratily\Controller\ControllerFactoryInterface;
use Fratily\Exception\PropertyUndefinedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 
 * 
 * @property-read   ContainerInterface  $container
 */
abstract class Application{

    /**
     * デバッグモード有効フラグ
     *
     * @var bool
     */
    private $isDebug;

    /**
     * アプリケーションの開始時間
     *
     * @var float
     */
    private $startedAt;

    /**
     * アプリケーションのルートディレクトリ
     *
     * @var string
     */
    private $rootDir;

    /**
     * DIコンテナ
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param   bool    $debug
     *
     * @return  void
     */
    public function __construct(bool $debug = false){
        $this->isDebug      = $debug;
        $this->rootDir      = $this->getRootDir();
        $this->startedAt    = microtime(true);

        Configure::set("app.dir.root", $this->getRootDir());
        Configure::set("app.dir.cache", $this->getCacheDir());
        Configure::set("app.dir.log", $this->getLogDir());
        Configure::set("app.dir.temp", $this->getTempDir());
        Configure::set("app.debug", $this->isDebug());

        $this->container    = $this->buildContainer();

        if(is_file($this->getConfigDir() . DS . "bootstrap.php")){
            require $this->getConfigDir() . DS . "bootstrap.php";
        }
        
        $this->bootstrap($this->container);
    }
    
    /**
     * Get property
     *
     * @param   string  $key
     * 
     * @throws  PropertyUndefinedException
     * 
     * @return  mixed
     */
    public function __get($key){
        switch($key){
            case "container":
                return $this->container;
        }

        throw new PropertyUndefinedException(static::class, $key);
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
        return new Response($this->buildKernel($this->container)->handle($request));
    }
    
    /**
     * DIコンテナを生成する
     *
     * @return  ContainerInterface
     */
    abstract protected function buildContainer(): ContainerInterface;

    /**
     * レスポンスファクトリーを生成する
     * 
     * @return  ResponseFactoryInterface
     */
    abstract protected function buildResponseFactory(): ResponseFactoryInterface;
    
    /**
     * URIルーターを生成する
     * 
     * @return  RouterInterface
     */
    abstract protected function buildRouter(): RouterInterface;
    
    /**
     * レンダラーを生成する
     * 
     * @return  RendererInterface
     */
    abstract protected function buildRenderer(): RendererInterface;
    
    /**
     * 任意の初期化処理を実行する
     * 
     * @return  void
     */
    protected function bootstrap(){
        
    }
    
    /**
     * コントローラーファクトリーを生成する
     * 
     * @return  ControllerFactory
     */
    protected function buildControllerFactory(): ControllerFactoryInterface{
        return new ControllerFactory($this->container, $this->buildResponseFactory(), $this->buildRenderer());
    }
    
    /**
     * カーネルを生成する
     *
     * @return  Kernel
     */
    protected function buildKernel(): Kernel{
        return new Kernel(
            $this->buildControllerFactory(),
            $this->buildRouter()
        );
    }

    /**
     * ルートディレクトリのパスを返す
     *
     * @return  string
     */
    public function getRootDir(): string{
        if($this->rootDir === null){
            $r  = new ReflectionObject($this);
            return realpath(dirname($r->getFileName()));
        }

        return $this->rootDir;
    }

    /**
     * コンフィグディレクトリのパスを返す
     *
     * @return  string
     */
    public function getConfigDir(): string{
        return $this->getRootDir() . DS . "config";
    }

    /**
     * キャッシュディレクトリのパスを返す
     *
     * @return  string
     */
    public function getCacheDir(): string{
        return $this->getRootDir() . DS . "var" . DS . "cache";
    }

    /**
     * ログディレクトリのパスを返す
     *
     * @return  string
     */
    public function getLogDir(): string{
        return $this->getRootDir() . DS . "var" . DS . "logs";
    }

    /**
     * テンポラリディレクトリのパスを返す
     *
     * @return  string
     */
    public function getTempDir(): string{
        return sys_get_temp_dir();
    }

    /**
     * デバッグモードが有効かを返す
     *
     * @return  bool
     */
    public function isDebug(){
        return $this->isDebug;
    }
}