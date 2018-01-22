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

use Fratily\Http\Server\MiddlewareInterface;
use Fratily\Http\Server\RequestHandlerInterface;
use Fratily\Reflection\ReflectionCallable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 */
class ActionMiddleware implements MiddlewareInterface{

    /**
     * @var object|null
     */
    private $object;

    /**
     * @var ReflectionCallable
     */
    private $action;

    /**
     * @var mixed[]
     */
    private $params;

    /**
     *
     * @example new ActionMiddleware(function(){}, []);
     * @example new ActionMiddleware(new Controller(), "index", []);
     */
    public function __construct(...$args){
        if(isset($args[0], $args[1])
            && is_callable($args[0]) && is_array($args[1])
        ){
            //  action function, params
            $this->action   = new ReflectionCallable($args[0]);
            $this->params   = $args[1];
        }else if(isset($args[0], $args[1], $args[2])
            && ($args[0] instanceof Controller\Controller)
            && is_string($args[1]) && is_array($args[2])
        ){
            //  controller object, action method name, params
            if(!method_exists($args[0], $args[1])){
                throw new \InvalidArgumentException();
            }

            $this->object   = $args[0];
            $this->action   = new ReflectionCallable([$args[0], $args[1]]);
            $this->params   = $args[2];

            if(!$this->action->getReflection()->isPublic()
                || $this->action->getReflection()->isStatic()
            ){
                throw new \InvalidArgumentException();
            }
        }else{
            throw new \InvalidArgumentException();
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