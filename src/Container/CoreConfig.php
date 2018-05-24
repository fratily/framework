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

use Fratily\Router\RouteCollector;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;
use Fratily\Http\Message\Response\EmitterInterface;
use Twig\Environment;
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Log\LoggerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class CoreConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        // Constructor Injection by type name
        $container->type(Container::class, $container);
        $container->type(ContainerInterface::class, $container);
        $container->type(RouteCollector::class, $container->lazyGet("app.routes"));
        $container->type(ResponseFactoryInterface::class, $container->lazyGet("app.factory.response"));
        $container->type(EmitterInterface::class, $container->lazyGet("app.response.emitter"));
        $container->type(CacheItemPoolInterface::class, $container->lazyGet("app.cache"));
        $container->type(CacheInterface::class, $container->lazyGet("app.simplecache"));
        $container->type(LoggerInterface::class, $container->lazyGet("app.log"));
        $container->type(Environment::class, $container->lazyGet("app.twig"));

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

        // Controller
        $container->set("core.controller.httperror", $container->lazyNew(
            \Fratily\Framework\Controller\HttpErrorController::class
        ));

        // Action
        $container->value("core.action.badRequest", [
            $container->lazyGet("core.controller.httperror"), "badRequest"
        ]);
        $container->value("core.action.forbidden", [
            $container->lazyGet("core.controller.httperror"), "forbidden"
        ]);
        $container->value("core.action.notFound", [
            $container->lazyGet("core.controller.httperror"), "notFound"
        ]);
        $container->value("core.action.methodNotAllowed", [
            $container->lazyGet("core.controller.httperror"), "methodNotAllowed"
        ]);
        $container->value("core.action.internalServerError", [
            $container->lazyGet("core.controller.httperror"), "internalServerError"
        ]);
        $container->value("core.action.notImplemented", [
            $container->lazyGet("core.controller.httperror"), "notImplemented"
        ]);
        $container->value("core.action.serviceUnavailable", [
            $container->lazyGet("core.controller.httperror"), "serviceUnavailable"
        ]);

        // Middleware
        $container->set("core.middleware.action", $container->lazyNew(
            \Fratily\Framework\Middleware\ActionMiddleware::class,
            [
                "action"    => $container->lazyValue("core.action.internalServerError"),
                "params"    => [
                    "msg"   => "Action is undefined.",
                ],
            ]
        ));
        $container->set("core.middleware.debug", $container->lazyNew(
            \Fratily\Framework\Middleware\DebugMiddleware::class,
            [
                "twig"  => $container->lazyGet("core.twig"),
            ]
        ));
    }
}