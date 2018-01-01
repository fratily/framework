<?php
/**
 * FratilyPHP
 * 
 * @see https://github.com/kento-oka/agilephp
 * 
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) 2017 Kento Oka
 * @license     MIT
 */
namespace Fratily\Http;

use Zend\Diactoros\Response\EmitterInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 
 */
class Emitter implements EmitterInterface{
    
    /**
     * Response instance.
     * 
     * @var ResponseInterface 
     */
    private $response;
    
    /**
     * レスポンスを発行する
     * 
     * @param   ResponseInterface   $response
     * @param   int                 $bufferSize
     * 
     * @throws  HeaderSendedException
     * 
     * @return  $this
     */
    public function emit(ResponseInterface $response, int $bufferSize = 4096){
        $this->response = $response;
        $file           = null;
        $line           = null;
        
        if(headers_sent($file, $line)){
            //  どちらにするべきか
            if(true){
                throw new Exception\HeaderSendedException();
            }else{
                $this->emitBody($bufferSize);
                return;
            }
        }
        
        $this->emitHttpStatus();
        $this->emitHeaders();
        if($this->response->getBody()->getSize() > 0){
            $this->emitBody($bufferSize);
        }
    }
    
    /**
     * HTTPステータスヘッダを送信する
     * 
     * @return  void
     */
    protected function emitHttpStatus(){
        header(sprintf("HTTP/%s %d%s",
            $this->response->getProtocolVersion(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase()
        ));
    }
    
    /**
     * ヘッダーを送信する
     * 
     * @return  void
     */
    protected function emitHeaders(){
        foreach($this->response->getHeaders() as $name => $values){
            if(strtolower($name) === "set-cookie"){
                foreach($values as $value){
                    header(sprintf("Set-Cookie: %s", $value));
                }
            }else{
                header(sprintf("%s: %s", $name, implode(", ", $values)));
            }
        }
    }
    
    /**
     * メッセージボディを送信する
     *
     * @param   int $bufferSize
     * 
     * @return  void
     */
    protected function emitBody(int $bufferSize){
        //  NO Content と Not Modified はボディが必要ない
        if(in_array($this->response->getStatusCode(), [204, 304])){
            return;
        }
        
        $body   = $this->response->getBody();

        //  シークできるならバッファサイズに合わせてリードする
        if($body->isSeekable()){
            $body->rewind();
            while(!$body->eof()){
                echo $body->read($bufferSize);
            }
        }else{
            echo $body;
        }
    }
}