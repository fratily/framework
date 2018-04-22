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

use Fratily\Framework\Render\RenderInterface;
use Psr\Container\ContainerInterface;
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
     * クラスがコントローラーか確認する
     *
     * @param   string  $class
     *
     * @return  bool
     */
    public static function isController(string $class){
        static $result  = [];

        if(!isset($result[$class])){
            $result[$class] = false;

            if(class_exists($class)){
                $ref    = new \ReflectionClass($class);

                if($ref->implementsInterface(self::class)){
                    $result[$class] = true;
                }
            }
        }

        return $result[$class];
    }

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
     */
    public function __get($id){
        return $this->container->get($id);
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
     * @throws  \LogicException
     */
    protected function response(int $code = 200){
        if($this->factory === null){
            if(!$this->container->has(ResponseFactoryInterface::class)){
                throw new \LogicException;
            }

            $this->factory  = $this->container->get(ResponseFactoryInterface::class);
        }

        return $this->factory->createResponse($code);
    }

    /**
     * テンプレとエンジンの結果を取得
     *
     * @param   string  $path
     * @param   mixed[] $context
     *
     * @return  string
     *
     * @throws  \LogicException
     */
    protected function render(string $path, array $context = []){
        if($this->render === null){
            if(!$this->container->has(RenderInterface::class)){
                throw new \LogicException;
            }

            $this->render   = $this->container->get(RenderInterface::class);
        }

        return $this->render->render($path, $context);
    }
}