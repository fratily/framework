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
use Fratily\Http\Factory\ResponseFactory;
use Fratily\Container\{
    Container,
    ContainerConfig
};
use Psr\Container\ContainerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class CoreConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container->type(ContainerInterface::class, $container);
        $container->type(RouteCollector::class, $container->lazyNew(RouteCollector::class));
        $container->type(ResponseFactoryInterface::class, $container->lazyNew(ResponseFactory::class));

        // Twig
        $container->set("core.twig", $container->lazyNew(
            \Twig\Environment::class,
            [
                $container->lazyNew(
                    \Twig\Loader\FilesystemLoader::class,
                    [
                        __DIR__ . "/../../recource/views"
                    ]
                )
            ]
        ));
    }
}