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
namespace Fratily\Framework\Container;

use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;
use Fratily\Router\RouteCollector;
use Fratily\Http\Message\Response\EmitterInterface;
use Fratily\EventManager\EventManagerInterface;
use Fratily\DebugBar\DebugBar;
use Twig_Environment;
use Twig\Environment;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class CoreConfig extends ContainerConfig{

    /**
     * @var bool
     */
    private $debug;

    public function __construct(bool $debug){
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $this->defineTraits($container);
        $this->defineTypes($container);
        $this->defineControllerAndAction($container);
        $this->defineMiddleware($container);
        $this->defineDebug($container);

        // Twig
        $container->set("core.twig", $container->lazyNew(
            \Twig\Environment::class,
            [
                $container->lazyNew(
                    \Twig\Loader\FilesystemLoader::class,
                    [
                        FRATILY_FW_ROOT . "/resource/views"
                    ]
                )
            ]
        ));
    }

    private function defineDebug(Container $container){
        $container
            ->set("core.debugbar", $container->lazyNew(
                DebugBar::class,
                [
                    $container->lazyArray([
                        "message"   => $container->lazyGet("core.debugbar.message"),
                        "timeline"  => $container->lazyGet("core.debugbar.timeline"),
                        "dump"      => $container->lazyGet("core.debugbar.dump"),
                    ]),
                ]
            ))
            ->set("core.debugbar.message", $container->lazyNew(
                \Fratily\DebugBar\Collector\MessageCollector::class
            ))
            ->set("core.debugbar.timeline", $container->lazyNew(
                \Fratily\DebugBar\Collector\TimeCollector::class
            ))
            ->set("core.debugbar.dump", $container->lazyNew(
                \Fratily\DebugBar\Collector\VarCollector::class
            ))
        ;
    }

    /**
     * Fratily Frameworkで定義されているトレイトのセッターを登録する
     *
     * @param   Container   $container
     *
     * @return  void
     */
    private function defineTraits(Container $container){
        $container
            ->setters(
                \Fratily\Framework\Traits\LogTrait::class,
                [
                    "setLogger"             => $container->lazyGet("app.log"),
                    "setMessageCollector"   => $container->lazyGet("core.debugbar.message"),
                ]
            )
            ->setter(
                \Fratily\Framework\Traits\EventTrait::class,
                "setEventManager",
                $container->lazyGet("app.eventManager")
            )
            ->setter(
                \Fratily\Framework\Traits\TimelineTrait::class,
                "setTimeCollector",
                $container->lazyGet("core.debugbar.timeline")
            )
            ->setter(
                \Fratily\Framework\Traits\DumpTrait::class,
                "setVarCollector",
                $container->lazyGet("core.debugbar.dump")
            )
        ;
    }

    /**
     * タイプ指定でのインジェクション用の値を登録
     *
     * @param   Container   $container
     *
     * @return  void
     */
    public function defineTypes(Container $container){
        $container->types([
            Container::class                => $container,
            RouteCollector::class           => $container->lazyGet("app.routes"),
            EmitterInterface::class         => $container->lazyGet("app.response.emitter"),
            EventManagerInterface::class    => $container->lazyGet("app.eventManager"),
            DebugBar::class                 => $container->lazyGet("core.debugbar"),
            Twig_Environment::class         => $container->lazyGet("app.twig"),
            Environment::class              => $container->lazyGet("app.twig"),
            ResponseFactoryInterface::class => $container->lazyGet("app.factory.response"),
            ContainerInterface::class       => $container,
            CacheItemPoolInterface::class   => $container->lazyGet("app.cache"),
            CacheInterface::class           => $container->lazyGet("app.simplecache"),
            LoggerInterface::class          => $container->lazyGet("app.log"),
        ]);
    }

    /**
     *
     *
     * @param   Container   $container
     *
     * @return  void
     */
    public function defineControllerAndAction(Container $container){
        $container
            ->param(
                \Fratily\Framework\Controller\Controller::class,
                "debug",
                $container->lazyValue("app.debug")
            )
            ->set(
                "core.controller.httperror",
                $container->lazyNew(
                    \Fratily\Framework\Controller\HttpErrorController::class
                )
            )
            ->value(
                "core.action.badRequest",
                [$container->lazyGet("core.controller.httperror"), "badRequest"]
            )
            ->value(
                "core.action.forbidden",
                [$container->lazyGet("core.controller.httperror"), "forbidden"]
            )
            ->value(
                "core.action.notFound",
                [$container->lazyGet("core.controller.httperror"), "notFound"]
            )
            ->value(
                "core.action.methodNotAllowed",
                [$container->lazyGet("core.controller.httperror"), "methodNotAllowed"]
            )
            ->value(
                "core.action.internalServerError",
                [$container->lazyGet("core.controller.httperror"), "internalServerError"]
            )
            ->value(
                "core.action.notImplemented",
                [$container->lazyGet("core.controller.httperror"), "notImplemented"]
            )
            ->value(
                "core.action.serviceUnavailable",
                [$container->lazyGet("core.controller.httperror"), "serviceUnavailable"]
            )
        ;
    }

    /**
     *
     *
     * @param   Container   $container
     *
     * @return  void
     */
    private function defineMiddleware(Container $container){
        $container
            ->set("core.middleware.action", $container->lazyNew(
                \Fratily\Framework\Middleware\ActionMiddleware::class,
                [
                    "action"    => $container->lazyValue("core.action.internalServerError"),
                    "params"    => [
                        "msg"   => "Action is undefined.",
                    ],
                ]
            ))
            ->set("core.middleware.debug", $container->lazyNew(
                \Fratily\Framework\Middleware\DebugMiddleware::class,
                [
                    "debug" => $container->lazyValue("app.debug"),
                ]
            ))
            ->set("core.middleware.error", $container->lazyNew(
                \Fratily\Framework\Middleware\ErrorMiddleware::class,
                [
                    "twig"  => $container->lazyGet("core.twig"),
                    "debug" => $container->lazyValue("app.debug"),
                ]
            ))
        ;
    }
}