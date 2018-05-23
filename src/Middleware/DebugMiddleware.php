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
    ResponseInterface,
    StreamInterface
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
     * @var Environment
     */
    private $twig;

    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * Constructor
     *
     * @param   Environment $twig
     * @param   ResponseFactoryInterface    $factory
     */
    public function __construct(Environment $twig, ResponseFactoryInterface $factory){
        $this->twig     = $twig;
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

            $response->withBody($this->addDebugToolBar($response->getBody()));
        }catch(\Throwable $e){
            $response   = $this->createErrorPage($e);
        }

        return $response;
    }

    /**
     * デバッグ用のツールバーを追加する
     *
     * レスポンスがhtml形式の場合のみ、
     * bodyの閉じタグ直前にツールバーのブロック要素を追加する。
     *
     * @param   StreamInterface $body
     *
     * @return  StreamInterface
     */
    private function addDebugToolBar(StreamInterface $body){
        return $body;
    }

    /**
     * エラーページ描画用のレスポンスインスタンスを生成する
     *
     * @param   \Throwable  $e
     *
     * @return  ResponseInterface
     */
    private function createErrorPage(\Throwable $e){
        $status = 500;
        $phrase = HttpStatus::PHRASES[$status] ?? "Undefine";

        if($e instanceof HttpStatus){
            $status = $e->getStatusCode();
            $phrase = $e->getStatusPhrase();

            if($e instanceof \Fratily\Http\Message\Status\MethodNotAllowed){
                $allow  = $e->getAllowed();
            }
        }

        $twig       = new Environment(new FilesystemLoader($this->path));
        $response   = $this->factory->createResponse($status);

        $context    = [
            "error" => [
                "class"     => get_class($e),
                "object"    => $e,
                "prev"      => [],
            ],
        ];

        while(($prev = $e->getPrevious()) !== null){
            $context["error"]["prev"][] = [
                "class"     => get_class($prev),
                "object"    => $prev,
            ];
        }

        $response->getBody()->write($twig->render("error.twig", $context));

        return $response;
    }
}