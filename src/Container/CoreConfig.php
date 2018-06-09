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
        $this->defineTrait($container);
        $this->defineType($container);
        $this->defineController($container);
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
        if($this->debug){
            $container
                ->setter(
                    \Fratily\Framework\Traits\DumpTrait::class,
                    "setVarCollector",
                    $container->lazyGet("core.debugbar.dump")
                )
                ->setter(
                    \Fratily\Framework\Traits\TimelineTrait::class,
                    "setTimeCollector",
                    $container->lazyGet("core.debugbar.timeline")
                )
                ->setter(
                    \Fratily\Framework\Traits\LogTrait::class,
                    "setMessageCollector",
                    $container->lazyGet("core.debugbar.message")
                )
            ;
        }

        $container
            ->set("core.debugbar", $container->lazyNew(
                    \Fratily\DebugBar\DebugBar::class,
                    [
                        $container->lazyArray([
                            "message"   => $container->lazyGet("core.debugbar.message"),
                            "timeline"  => $container->lazyGet("core.debugbar.timeline"),
                            "dump"      => $container->lazyGet("core.debugbar.dump"),
                        ]),
                    ]
                )
            )
            ->set("core.debugbar.message", $container->lazyNew(
                    \Fratily\DebugBar\Collector\MessageCollector::class
                )
            )
            ->set("core.debugbar.timeline", $container->lazyNew(
                    \Fratily\DebugBar\Collector\TimeCollector::class
                )
            )
            ->set("core.debugbar.dump", $container->lazyNew(
                    \Fratily\DebugBar\Collector\VarCollector::class
                )
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
    private function defineTrait(Container $container){
        $container->setter(
            \Fratily\Framework\Traits\LogTrait::class,
            "setLogger",
            $container->lazyGet("app.log")
        );

        $container->setter(
            \Fratily\Framework\Traits\EventTrait::class,
            "setEventManager",
            $container->lazyGet("app.eventManager")
        );
    }

    /**
     *
     *
     * @param   Container   $container
     *
     * @return  void
     */
    public function defineType(Container $container){
        $container
            ->type(Container::class, $container)
            ->type(
                \Psr\Container\ContainerInterface::class,
                $container
            )
            ->type(
                \Fratily\Router\RouteCollector::class,
                $container->lazyGet("app.routes")
            )
            ->type(
                \Interop\Http\Factory\ResponseFactoryInterface::class,
                $container->lazyGet("app.factory.response")
            )
            ->type(
                \Fratily\Http\Message\Response\EmitterInterface::class,
                $container->lazyGet("app.response.emitter")
            )
            ->type(
                \Psr\Cache\CacheItemPoolInterface::class,
                $container->lazyGet("app.cache")
            )
            ->type(
                \Psr\SimpleCache\CacheInterface::class,
                $container->lazyGet("app.simplecache")
            )
            ->type(
                \Psr\Log\LoggerInterface::class,
                $container->lazyGet("app.log")
            )
            ->type(
                \Twig_Environment::class,
                $container->lazyGet("app.twig")
            )
            ->type(
                \Fratily\EventManager\EventManagerInterface::class,
                $container->lazyGet("app.eventManager")
            )
            ->type(
                \Fratily\DebugBar\DebugBar::class,
                $container->lazyGet("core.debugbar")
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
    public function defineController(Container $container){
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
                    "debug" => $container->lazyGet("app.debug"),
                ]
            ))
            ->set("core.middleware.error", $container->lazyNew(
                \Fratily\Framework\Middleware\ErrorMiddleware::class,
                [
                    "twig"  => $container->lazyGet("core.twig"),
                    "debug" => $container->lazyGet("app.debug"),
                ]
            ))
        ;
    }
}