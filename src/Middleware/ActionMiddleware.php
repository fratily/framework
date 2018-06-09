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
namespace Fratily\Framework\Middleware;

use Fratily\Framework\Controller\Controller;
use Fratily\Container\Container;
use Fratily\Reflection\ReflectionCallable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 *
 */
class ActionMiddleware implements MiddlewareInterface{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ReflectionCallable
     */
    private $action;

    /**
     * @var mixed[]
     */
    private $params;

    /**
     * コントローラーのクラス名とアクションメソッド名からアクションミドルウェアを作成
     *
     * @param   ContainerInterface  $container
     * @param   string  $controller
     * @param   string  $method
     * @param   mixed[] $params
     *
     * @return  static
     *
     * @throws  \InvalidArgumentException
     *
     * @todo    ここは修正の余地あり。仕様を確定させないといけない
     */
    public static function getInstanceWithController(
        ContainerInterface $container,
        string $controller,
        string $method,
        array $params = []
    ){
        if(!class_exists($controller)){
            throw new \InvalidArgumentException();
        }

        $controller = $container->has($controller)
            ? $container->get($controller)
            : new $controller($container)
        ;

        if(!($controller instanceof Controller)){
            throw new \InvalidArgumentException();
        }

        if(!is_callable([$controller, $method])){
            throw new \InvalidArgumentException();
        }

        return new static($container, [$controller, $method], $params);
    }

    /**
     * Constructor
     *
     * @param   Container   $container
     * @param   callable    $action
     * @param   mixed[] $params
     */
    public function __construct(
        Container $container,
        $action,
        array $params = []
    ){
        $this->container    = $container;
        $this->action       = $action;
        $this->params       = $params;
    }

    /**
     * アクションを設定する
     *
     * @param   mixed   $action
     *
     * @return  $this
     */
    public function setAction($action){
        $this->action   = $action;

        return $this;
    }

    /**
     * パラメータを設定する
     *
     * @param   mixed[] $params
     *
     * @return  $this
     */
    public function setParams(array $params){
        $this->params   = $params;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $action = $this->container->lazy(
            $this->action,
            array_merge([
                "_request"  => $request,
                "_params"   => $this->params,
            ], $this->params)
        );

        $response   = $handler->handle($request);
        $_response  = $action->load();

        if($_response instanceof ResponseInterface){
            $response   = $_response;
        }else if(is_scalar($_response) || $_response === null){
            if(!$response->getBody()->isWritable()){
                throw new \LogicException;
            }

            $response->getBody()->write($_response ?? "");
        }else{
            throw new \UnexpectedValueException;
        }

        return $response;
    }
}