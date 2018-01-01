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
namespace Fratily\Controller;

use Fratily\Http\ResponseFactoryInterface;
use Fratily\Renderer\RendererInterface;
use Fratily\Utility\Reflector;
use Psr\Container\ContainerInterface;

/**
 *
 */
class ControllerFactory implements ControllerFactoryInterface{

    const CTRL_NS   = "\\App\\Controller\\";

    /**
     * DIコンテナ
     *
     * @var CotnainerInterface
     */
    private $container;

    /**
     * レスポンスファクトリー
     *
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * レンダラ
     *
     * @var RendererInterface
     */
    private $renderer;

    /**
     * エラーコントローラーのクラス名
     *
     * @var string
     */
    private $errClass;

    /**
     * コントローラーのネームスペース
     *
     * @param   string
     */
    private $ctrlNs;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     * @param   ResponseFactoryInterface    $responseFactory
     * @param   RendererInterface   $renderer
     */
    public function __construct(
        ContainerInterface $container,
        ResponseFactoryInterface $responseFactory,
        RendererInterface $renderer
    ){
        $this->container        = $container;
        $this->responseFactory  = $responseFactory;
        $this->renderer         = $renderer;
    }

    /**
     * コントローラーを返す
     *
     * @param   string  $controller
     *      コントローラー名
     *
     * @throws  EXception\ControllerNotFoundException
     *
     * @return  Controller
     */
    public function getController(string $controller): Controller{
        $class  = ($this->ctrlNs ?? self::CTRL_NS)
            . strtr(ucwords(strtr($controller, ["-" => " "])), [" " => ""])
            . "Controller";

        if(!$this->isController($class)){
            throw new Exception\ControllerNotFoundException($class);
        }

        return new $class($this->container, $this->responseFactory, $this->renderer);
    }

    /**
     * エラーコントローラーを返す
     *
     * @return  ErrorControllerInterface
     */
    public function getErrorController(): ErrorControllerInterface{
        $class  = $this->errClass ?? ErrorController::class;
        return new $class($this->container, $this->responseFactory, $this->renderer);
    }

    /**
     * コントローラークラスのネームスペースをセットする
     *
     * @param   string  $ns
     */
    public function setControllerNamespace(string $ns){
        $this->ctrlNs  = $ns;
    }

    /**
     * エラーコントローラークラスを設定する
     *
     * @param   string  $class
     *      エラーコントローラークラス名
     *
     * @throws  \InvalidArgumentException
     */
    public function setErrorController(string $class){
        if(!$this->isErrorController($class)){
            throw new \InvalidArgumentException(
                "{$class} is not error controller."
            );
        }

        $this->errClass = $class;
    }

    /**
     * 指定クラスがコントローラーか調べる
     *
     * @param   string  $class
     *      調べるクラス名
     *
     * @return  bool
     */
    protected function isController(string $class){
        if(($class = Reflector::getClass($class, false)) === false){
            return false;
        }

        return $class->isSubclassOf(Controller::class);
    }

    /**
     * 指定クラスがエラーコントローラーか調べる
     *
     * @param   string  $class
     *      調べるクラス名
     *
     * @return  bool
     */
    protected function isErrorController(string $class){
        if(($class = Reflector::getClass($class, false)) === false){
            return false;
        }

        return $class->isSubclassOf(Controller::class)
            && $class->implementsInterface(ErrorControllerInterface::class);
    }
}
