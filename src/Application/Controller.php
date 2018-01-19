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

use Fratily\Http\ResponseFactoryInterface;
use Fratily\Renderer\RendererInterface;
use Fratily\Utility\Reflector;
use Fratily\Exception\PropertyUndefinedException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 *
 *
 * @property-read   ContainerInterface  $container
 * @property-read   ResponseInterface   $response
 * @property-read   RendererInterface   $renderer
 */
abstract class Controller{

    /**
     * DIコンテナ
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container){
        $this->container        = $container;
    }

    /**
     * Get property
     *
     * @param   string  $key
     *
     * @throws  Exception\PropertyUndefinedException
     *
     * @return  mixed
     */
    protected function __get($key){
        switch($key){
            case "container":
                return $this->container;
            case "response":
                return $this->responseFactory->createResponse();
            case "renderer":
                return $this->renderer;
        }

        throw new PropertyUndefinedException(static::class, $key);
    }

    /**
     * アクションを実行する
     *
     * @param   string  $action
     *      実行するアクション名
     * @param   ServerRequestInterface  $request
     *      リクエストオブジェクト
     * @param   mixed[] $params
     *      パラメーター
     *
     * @throws  Exception\ActionImplementException
     * @throws  Exception\ActionUndefinedException
     *
     * @return  ResponseInterface
     */
    public function execute(
        string $action,
        ServerRequestInterface $request,
        array $params = []
    ): ResponseInterface{
        //  アクションメソッド名解決
        $action = lcfirst(strtr(ucwords(strtr($action, ["-" => " "])), [" " => ""]));

        //  アクションメソッドがユーザー実装コントローラーで定義されているか
        if(method_exists(self::class, $action) || !method_exists($this, $action)){
            throw new Exception\ActionUndefinedException(static::class, $action);
        }

        $method = Reflector::getMethod(static::class, $action);

        //  アクションメソッドがpulicかつ非staticか
        if(!$method->isPublic()){
            throw new Exception\ActionImplementException(static::class, $action,
                "Visibility of action {class.name}::{method.name}() is not public."
            );
        }else if($method->isStatic()){
            throw new Exception\ActionImplementException(static::class, $action,
                "Action {class.name}::{method.name}() is static method."
            );
        }

        //  アクションメソッド実行
        $return = $method->invokeArgs(
            $this,
            Reflector::bindParams2Args(
                $method,
                [
                    "params"    => $params,
                    "request"   => $request
                ] + $params
            )
        );

        if($return instanceof ResponseInterface){
            return $return;
        }else if(is_scalar($return)){
            $response   = $this->response;
            $response->getBody()->write((string)$return);

            return $response;
        }

        throw new Exception\ActionImplementException(static::class, $action,
            "Action {class.name}::{method.name} dose not returnd ResponseInterface or scalar value."
        );
    }
}