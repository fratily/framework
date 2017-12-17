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
namespace Fratily\Controller;

use Fratily\Http\ResponseFactoryInterface;
use Fratily\Renderer\RendererInterface;
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
     * Get property
     *
     * @param   string  $key
     *
     * @throws  Exception\PropertyUndefinedException
     *
     * @return  mixed
     */
    public function __get($key){
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

        $method = new \ReflectionMethod($this, $action);

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

        $args   = [];

        //  アクションメソッド呼び出し時の引数解決
        foreach($method->getParameters() as $param){
            $value  = null;

            if($param->getName() === "params"){
                $value  = $params;
            }else if($param->getName() === "request"){
                $value  = $request;
            }else if(!isset($params[$param->getName()])){
                if($param->isDefaultValueAvailable()){
                    $value  = $param->getDefaultValue();
                }
            }else{
                $value  = $params[$param->getName()];
            }

            $args[]   = $value;
        }

        //  アクションメソッド実行
        $return = $method->invokeArgs($this, $args);

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