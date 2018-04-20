<?php
/**
 * FratilyPHP Http Server Middleware
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Http\Server\Middleware;

use Fratily\Framework\Render\TwigRender;
use Fratily\Http\Message\Status\HttpStatus;
use Twig\{
    Environment,
    Loader\FilesystemLoader
};
use Psr\Http\{
    Message\ResponseInterface,
    Message\ServerRequestInterface,
    Server\MiddlewareInterface,
    Server\RequestHandlerInterface
};
use \Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class ErrorHandleMiddleware implements MiddlewareInterface{

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var RenderInterface
     */
    private $render;

    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * Constructor
     *
     * @param   REnderInterface $render
     */
    public function __construct(TwigRender $render, ResponseFactoryInterface $factory, bool $debug = false){
        $this->render   = $render;
        $this->factory  = $factory;
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{
        try{
            return $handler->handle($request);
        }catch(\Throwable $e){
            $file   = "error";
            $status = 500;
            $phrase = HttpStatus::PHRASES[$status] ?? "Undefine";

            if($e instanceof HttpStatus){
                $file   = $e->getStatusCode();
                $status = $e->getStatusCode();
                $phrase = $e->getStatusPhrase();

                if($e instanceof \Fratily\Http\Message\Status\MethodNotAllowed){
                    $allow  = $e->getAllowed();
                }
            }
        }

        $context    = [
            "e"         => $e,
            "status"    => $status,
            "phrase"    => $phrase,
            "debug"     => $this->debug,
        ];

        try{
            $body   = $this->render->render($file . ".twig", $context);
        }catch(\Exception $e){
            $body   = null;
        }

        $response   = $this->factory->createResponse($status);

        if(isset($allow) && !empty($allow)){
            $response   = $response->withAddedHeader("Allow", implode(",", $allow));
        }

        if($body !== null){
            $response->getBody()->write($body);
        }
    }
}