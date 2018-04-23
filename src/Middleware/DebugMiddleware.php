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

use Fratily\Http\Message\Status\HttpStatus;
use Twig\{
    Loader\FilesystemLoader,
    Environment
};
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use Psr\Http\Server\{
    RequestHandlerInterface,
    MiddlewareInterface
};
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class DebugMiddleware implements MiddlewareInterface{

    /**
     * @var string
     */
    private $path;

    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     * @param   callable    $action
     * @param   mixed[] $params
     */
    public function __construct(string $path, ResponseFactoryInterface $factory){
        if(!is_dir($path)){
            throw new \InvalidArgumentException();
        }

        $this->path     = realpath($path);
        $this->factory  = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        try{
            $response   = $handler->handle($request->withAttribute("fratily.debug", true));
        }catch(\Throwable $e){
            $status = 500;
            $phrase = HttpStatus::PHRASES[$status] ?? "Undefine";

            if($e instanceof HttpStatus){
                $status = $e->getStatusCode();
                $phrase = $e->getStatusPhrase();

                if($e instanceof \Fratily\Http\Message\Status\MethodNotAllowed){
                    $allow  = $e->getAllowed();
                }
            }

            $context    = ["e" => $e];
            $twig       = new Environment(new FilesystemLoader($this->path));
            $response   = $this->factory->createResponse($status);

            $response->getBody()->write($twig->render("error.twig", $context));
        }

        return $response;
    }
}