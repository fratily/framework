<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Http;

use Fratily\Controller\ControllerFactory;
use Fratily\Router\RouterInterface;
use Fratily\Middleware\MiddlewareHandlerInterface;
use Fratily\Middleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 *
 */
class Kernel implements MiddlewareHandlerInterface{

    /**
     * コントローラーファクトリー
     * 
     * @var ControllerFactory
     */
    private $ctrlFactory;

    /**
     * URIルーター
     * 
     * @var RouterInterface
     */
    private $router;
    
    /**
     * ミドルウェアキュー
     * 
     * @var \SplQueue
     */
    private $queue;

    /**
     * Constructor
     *
     * @param   Application $app
     *
     * @return  void
     */
    public function __construct(ControllerFactory $factory, RouterInterface $router){
        $this->ctrlFactory  = $factory;
        $this->router       = $router;
        $this->queue        = new \SplQueue();
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface{
        try{
            if($this->queue->isEmpty()){
                return $this->process($request);
            }

            return $this->queue->dequeue()->process($request, $this);
        }catch(Status\HttpStatus $s){
            return $this->ctrlFactory
                ->getErrorController()
                ->execute("status", $request, ["code" => $s->getCode()]);
        }catch(\Throwable $e){
            return $this->ctrlFactory
                ->getErrorController()
                ->execute("throwable", $request, ["e" => $e]);
        }
    }

    /**
     * コントローラーアクションを実行する
     * 
     * @param   ServerRequestInterface  $request
     * 
     * @return  ResponseInterface
     */
    public function process(ServerRequestInterface $request): ResponseInterface{
        $result = $this->router->search($request->getMethod(), $request->getUri()->getPath());

        switch($result->type){
            case RouterInterface::NOT_FOUND:
                throw new Status\NotFound();

            case RouterInterface::METHOD_NOT_ALLOWED:
                throw new Status\MethodNotAllowed();

            case RouterInterface::FOUND:
                return $this->ctrlFactory
                    ->getController($result->params["controller"])
                    ->execute(
                        $result->params["action"] ?? "index",
                        $request,
                        $result->params
                    );
        }
        
        throw new \UnexpectedValueException("この例外は出現しない");
    }

    /**
     * ミドルウェアを末尾に追加する
     * 
     * @param   MiddlewareInterface $middleware
     */
    public function append(MiddlewareInterface $middleware){
        $this->queue->push($middleware);
        return $this;
    }

    /**
     * ミドルウェアを先頭に追加する
     *
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function prepend(MiddlewareInterface $middleware){
        $this->unshift($middleware);
        return $this;
    }

    /**
     * 指定したミドルウェアの前にミドルウェアを追加する
     *
     * @param   string  $name
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function insertPrev(string $name, MiddlewareInterface $middleware){
        $find   = false;

        foreach($this as $key => $val){
            if(get_class($val) === $name){
                $find   = $key;
                break;
            }
        }

        if($find !== false){
            $this->add($find, $middleware);
        }

        return $this;
    }

    /**
     * 指定したミドルウェアの後にミドルウェアを追加する
     *
     * @param string $name
     * @param   MiddlewareInterface $middleware
     *
     * @return  $this
     */
    public function insertNext(string $name, MiddlewareInterface $middleware){
        $find   = false;

        foreach($this as $key => $val){
            $valClass   = get_class($val);

            if($valClass === $name){
                $find   = $key;
                break;
            }elseif($valClass === MiddlewareWrapper::class
                && $valClass === $val->getMiddlewareName()
            ){
                $find   = $key;
                break;
            }
        }

        if($find !== false){
            if(isset($this[++$find])){
                $this->add($key, $middleware);
            }else{
                $this->push($middleware);
            }
        }

        return $this;
    }
}