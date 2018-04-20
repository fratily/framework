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
     * @var ContainerInterface
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
     * コントローラーのクラスメイとアクションメソッドからアクションミドルウェアを作成
     *
     * @param   ContainerInterface  $container
     * @param   string  $controller
     * @param   string  $method
     * @param   mixed[] $params
     *
     * @return  static
     *
     * @throws  \InvalidArgumentException
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

        if(!($controller instanceof Controller\Controller)){
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
     * @param   ContainerInterface  $container
     * @param   callable    $action
     * @param   mixed[] $params
     */
    public function __construct(
        ContainerInterface $container,
        callable $action,
        array $params = []
    ){
        $this->container    = $container;
        $this->action       = $action;
        $this->params       = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        if(is_array($this->action) && $this->action[0] instanceof Controller\Controller){
            $object = $this->action[0];
        }else{
            $object = null;
        }

        $reflection = new ReflectionCallable($this->action);
        $args       = [];

        foreach($reflection->getReflection()->getParameters() as $parameter){
            $name   = $parameter->getName();

            if($name === "_request"){
                $args[] = $request;
            }elseif($name === "_params"){
                $args[] = $this->params;
            }else if(array_key_exists($name, $this->params)){
                $args[] = $this->params[$name];
            }else if($this->container->has("action.params." . $name)){
                $args[] = $this->container->get("action.params." . $name);
            }else if($parameter->hasType() && class_exists($parameter->getType())
                && $this->container->has($parameter->getType())
            ){
                $args[] = $this->container->get($parameter->getType());
            }else if($parameter->isDefaultValueAvailable()){
                $args[] = $parameter->getDefaultValue();
            }else if($parameter->allowsNull()){
                $args[] = null;
            }else{
                throw new \LogicException();
            }
        }

        $response   = $handler->handle($request);
        $_response  = $reflection->invokeArgs($object, $args);

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