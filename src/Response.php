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
namespace Fratily\Framework;

use Fratily\Http\Message\Response\EmitterInterface;
use Fratily\EventManager\Event;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
class Response{

    use Traits\EventTrait;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var RequestHandlerInterface
     */
    protected $handler;

    /**
     * @var EmitterInterface
     */
    protected $emitter;

    /**
     * @var bool
     */
    private $send   = false;

    /**
     * @var \Throwable|null
     */
    private $error  = null;

    /**
     * Constructor
     *
     * @param   RequestInterface    $request
     * @param   RequestHandlerInterface $handler
     * @param   EmitterInterface    $emmiter
     */
    public function __construct(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        EmitterInterface $emmiter
    ){
        $this->request  = $request;
        $this->handler  = $handler;
        $this->emitter  = $emmiter;
    }

    /**
     * 送信時に発生した例外もしくはエラーを取得する
     *
     * @return  \Throwable|null
     */
    public function getError(){
        return $this->error;
    }

    /**
     * リクエストハンドラを実行し生成されたレスポンスを送信する
     *
     * @return  bool
     */
    public function send(){
        if(!$this->send){
            $response   = $this->handle();

            if($response !== null){
                $this->emit($response);
            }

            $this->send = true;

            $this->event(new Event("response.send.after"));
        }

        return !($this->error instanceof \Throwable);
    }

    /**
     * ミドルウェアハンドラを実行してレスポンスを生成する
     *
     * @return  ResponseInterface|null
     */
    private function handle(){
        $params = [
            "start"     => null,
            "finish"    => null,
            "response"  => null,
            "error"     => null,
        ];

        try{
            $params["start"]    = microtime(true);
            $params["response"] = $this->handler->handle($this->request);
        }catch(\Throwable $e){
            $params["error"]    = $e;
            $this->error        = $e;
        }finally{
            $params["finish"]   = microtime(true);
        }

        $this->event(new Event("response.handle.after", $params));

        return $params["response"];
    }

    /**
     * レスポンスを送信する
     *
     * @param   ResponseInterface   $response
     *
     * @return  void
     */
    private function emit(ResponseInterface $response){
        $params = [
            "start"     => null,
            "finish"    => null,
            "error"     => null,
        ];

        try{
            $params["start"]    = microtime(true);

            $this->emitter->emit($response);
        }catch(\Throwable $e){
            $params["error"]    = $e;
            $this->error        = $e;
        }finally{
            $params["finish"]   = microtime(true);
        }

        $this->event(new Event("response.emit.after", $params));
    }
}