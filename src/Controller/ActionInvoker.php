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
namespace Fratily\Framework\Controller;

use Fratily\Reflection\ReflectionCallable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class ActionInvoker{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * @var callable
     */
    private $action;

    /**
     * @var \ReflectionParameter[]
     */
    private $params;

    /**
     * メソッドがアクションか確認する
     *
     * @param   Controller  $controller
     * @param   string  $method
     *
     * @return  bool
     */
    protected static function isAction(Controller $controller, string $method){
        if(method_exists($controller, $method)){
            $ref    = new \ReflectionMethod($controller, $method);

            if(!$ref->isStatic() && $ref->isPublic()
                && !method_exists(Controller::class, $method)
            ){
                return true;
            }
        }

        return false;
    }

    /**
     * Constructor
     *
     * @param   \Closure|string[]   $action
     * @param   ContainerInterface  $container
     * @param   ResponseFactoryInterfafce   $factory
     *
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        $action,
        ContainerInterface $container,
        ResponseFactoryInterface $factory
    ){
        $this->container    = $container;
        $this->factory      = $factory;

        if($action instanceof \Closure){
            $this->action   = $action;
            $this->params   = (new \ReflectionFunction($action))->getParameters();
        }else{
            if(!is_array($action) || !isset($action[0]) || !isset($action[1])
                || !is_string($action[0] || !is_string($action[1]))
            ){
                throw new \InvalidArgumentException();
            }

            $this->controller   = $this->newController($action[0]);

            if(!self::isAction($this->controller, $action[1])){
                throw new \InvalidArgumentException();
            }

            $this->action   = [$this->controller, $action[1]];
            $this->params   = (new \ReflectionMethod($action[0], $action[1]))
                ->getParameters();
        }
    }

    /**
     * コントローラーのインスタンスを生成する
     *
     * @param   string  $class
     *
     * @return  Controller
     *
     * @throws  \InvalidArgumentException
     */
    protected function newController(string $class){
        if(!Controller::isController($action[0])){
            throw new \InvalidArgumentException();
        }

        $paramNames = (new \ReflectionMethod($class, "__construct"))->getParameters();
        $args       = [];

        foreach($paramNames as $param){
            $name   = $param->getName();

            if($name === "container"){
                $args[] = $this->container;
            }else if($name === "factory"){
                $args[] = $this->factory;
            }else if($this->container->has($name)){
                $args[] = $this->container->get($name);
            }else{
                $args[] = null;
            }
        }

        return (new \ReflectionClass($class))->newInstanceArgs($args);
    }

    public function invoke(
        ServerRequestInterface $request,
        array $params
    ){
        $args   = [];
        $params = $params + [
            "_request"   => $request,
            "_params"    => $params
        ];

        foreach($this->params as $param){
            $name   = $param->getName();

            if(array_key_exists($params[$name])){
                $args[] = $params[$param->getName()];
            }else if($param->isDefaultValueAvailable()){
                $args[] = $param->getDefaultValue();
            }
        }

        $response   = call_user_func_array($this->action, $args);

        if(is_scalar($response)){
        }else if($contents instanceof ResponseInterface){
            $response   = $contents;
        }else{
            throw new \UnexpectedValueException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $contents   = $this->action->invokeMapedArgs(
            $this->object,
            [
                "_request"   => $request,
                "_params"    => $this->params
            ] + $this->params
        );

        $response   = $handler->handle($request);

        if($contents === null){
            $contents   = "";
        }

        if(is_scalar($contents) && $response->getBody()->isWritable()){
            $response->getBody()->write($contents);
        }else if($contents instanceof ResponseInterface){
            $response   = $contents;
        }else{
            throw new \UnexpectedValueException;
        }

        return $response;
    }
}