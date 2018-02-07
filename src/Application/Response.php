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

use Fratily\Http\Message\Response\EmitterInterface;
use Fratily\Http\Message\Response\Emitter;
use Psr\Http\Message\ResponseInterface;

/**
 * {@inheritdoc}
 */
class Response{
    
    /**
     * Response instance.
     * 
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * Constructor
     * 
     * @param   ResponseInterface   $response
     * 
     * @return  void
     */
    public function __construct(ResponseInterface $response){
        $this->response = $response;
    }
    
    /**
     * レスポンスを送信する
     * 
     * @param   EmitterInterface    $emitter
     * 
     * @return  void
     */
    public function send(EmitterInterface $emitter = null){
        $emitter    = ($emitter ?? self::$emitter ?? new Emitter());
        
        $emitter->emit($this->response);
    }
}