<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Framework\Middleware;

use Fratily\DebugBar\DebugBar;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 *
 */
class DebugMiddleware implements MiddlewareInterface{

    /**
     * @var DebugBar
     */
    private $debugbar;

    /**
     * @var debug
     */
    private $debug;

    /**
     * Constructor
     *
     * @param   Environment $twig
     * @param   ResponseFactoryInterface    $factory
     */
    public function __construct(DebugBar $debugbar, bool $debug){
        $this->debugbar = $debugbar;
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $response   = $handler->handle($request->withAttribute("fratily.debug", true));

        if($this->debug){
            $response   = $response->withBody(
                $this->addDebugToolBar($response->getBody())
            );
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
        $newBody    = new \Fratily\Http\Message\Stream\MemoryStream();

        $body->rewind();
        $newBody->write($this->debugbar->embed($body->getContents()));

        return $newBody;
    }
}