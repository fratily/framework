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
namespace Fratily\Framework\Controller;

use Fratily\Framework\{
    Render\RenderInterface,
    Exception\ContainerNotFoundException
};
use Psr\Container\{
    ContainerInterface,
    NotFoundExceptionInterface
};
use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
abstract class Controller{

    /**
     * DIコンテナ
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ResponseFactoryInterface|null
     */
    private $factory;

    /**
     * @var RenderInterface|null
     */
    private $render;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     */
    public function __construct(
        ContainerInterface $container,
        ResponseFactoryInterface $factory = null,
        RenderInterface $render = null
    ){
        $this->container    = $container;
        $this->factory      = $factory;
        $this->render       = $render;
    }

    /**
     * Undefine paremater access (get)
     *
     * DIコンテナから取得してくる
     *
     * @param   string  $id
     *
     * @return  mixed
     *
     * @throws  ContainerNotFoundException
     */
    public function __get($id){
        try{
            return $this->container->get($id);
        }catch(NotFoundExceptionInterface $e){
            throw new ContainerNotFoundException(ResponseFactoryInterface::class, 0, $e);
        }
    }

    /**
     * Undefine parameter access (has)
     *
     * DIコンテナに確認する
     *
     * @param   string  $id
     *
     * @return  bool
     */
    public function __isset($id){
        return $this->container->has($id);
    }

    /**
     * レスポンスを生成する
     *
     * @param   int $code   HTTPレスポンスステータスコード
     *
     * @return  ResponseInterface
     *
     * @throws  ContainerNotFoundException
     */
    protected function response(int $code = 200){
        if($this->factory === null){
            if(!$this->container->has(ResponseFactoryInterface::class)){
                throw new ContainerNotFoundException(ResponseFactoryInterface::class);
            }

            try{
                $this->factory  = $this->container->get(ResponseFactoryInterface::class);
            }catch(NotFoundExceptionInterface $e){
                throw new ContainerNotFoundException(ResponseFactoryInterface::class, 0, $e);
            }
        }

        return $this->factory->createResponse($code);
    }

    /**
     * テンプレートエンジンのレンダリング結果を取得
     *
     * @param   string  $path
     * @param   mixed[] $context
     *
     * @return  string
     *
     * @throws  ContainerNotFoundException
     */
    protected function render(string $path, array $context = []){
        if($this->render === null){
            if(!$this->container->has(RenderInterface::class)){
                throw new ContainerNotFoundException(RenderInterface::class);
            }

            try{
                $this->render   = $this->container->get(RenderInterface::class);
            }catch(NotFoundExceptionInterface $e){
                throw new ContainerNotFoundException(RenderInterface::class, 0, $e);
            }
        }

        return $this->render->render($path, $context);
    }
}