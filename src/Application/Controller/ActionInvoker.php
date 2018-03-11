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
namespace Fratily\Application\Controller;

use Fratily\Reflection\ReflectionCallable;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class ActionInvoker{
    
    /**
     * @var callable
     */
    private $action;
    
    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     *
     * @example new ActionMiddleware(function(){}, []);
     * @example new ActionMiddleware(new Controller(), "index", []);
     */
    public function __construct(callable $action, ResponseFactoryInterface $factory){
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