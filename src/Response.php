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

use Fratily\Http\Message\Response\EmitterInterface;
use Fratily\EventManager\EventManagerInterface;
use Fratily\EventManager\Event;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
class Response{

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
     * @var EventManagerInterface
     */
    protected $eventMng;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var bool
     */
    private $send;

    /**
     * @var \Throwable|null
     */
    private $error;

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
        EmitterInterface $emmiter,
        EventManagerInterface $eventMng
    ){
        $this->request  = $request;
        $this->handler  = $handler;
        $this->emitter  = $emmiter;
        $this->eventMng = $eventMng;
        $this->send     = false;
        $this->error    = null;
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
     * @
     */
    public function send(){
        if(!$this->send){
            $response   = $this->handle();

            if($response !== null){
                $this->emit($response);
            }

            $this->send = true;
        }

        return $this->error instanceof \Throwable;
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
            $params["start"]    = time();
            $params["response"] = $this->handler->handle($this->request);
        }catch(\Throwable $e){
            $params["error"]    = $e;
            $this->error        = $e;
        }finally{
            $params["finish"]   = time();
        }

        $this->eventMng->trigger(new Event("response.handler.finish", $params));

        $this->response = $params["response"];

        return $this->response;
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
            $params["start"]    = time();

            $this->emitter->emit($response);
        }catch(\Throwable $e){
            $params["error"]    = $e;
            $this->error        = $e;
        }finally{
            $params["finish"]   = time();
        }

        $this->eventMng->trigger(new Event("response.emit.finish", $params));
    }
}