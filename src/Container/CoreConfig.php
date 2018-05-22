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
                        FRATILY_FW_ROOT . "/recource/views"
                    ]
                )
            ]
        ));
    }
}