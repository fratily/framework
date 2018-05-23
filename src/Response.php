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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 *
 */
class Response{

    /**
     * @var RequestInterface
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
        RequestInterface $request,
        RequestHandlerInterface $handler,
        EmitterInterface $emmiter
    ){
        $this->request  = $request;
        $this->handler  = $handler;
        $this->emitter  = $emmiter;
        $this->send     = false;
    }

    /**
     * リクエストハンドラを実行し生成されたレスポンスを送信する
     *
     * @
     */
    public function send(){
        if(!$this->send){
            try{
                $this->emitter->emit($this->handler->handle($this->request));
            }catch(\Throwable $e){
                $this->error    = $e;
            }

            $this->send = true;
        }

        return $this->error instanceof \Throwable;
    }

    /**
     * 送信時に発生した例外もしくはエラーを取得する
     *
     * @return  \Throwable|null
     */
    public function getError(){
        return $this->error;
    }

}