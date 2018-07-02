<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
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
use Twig_Environment;
use Twig\Environment;
use Interop\Http\Factory\ResponseFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 *
 */
class TypeConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container->types([
            Container::class                => $container,
            RouteCollector::class           => $container->lazyGet("app.routes"),
            EmitterInterface::class         => $container->lazyGet("app.response.emitter"),
            EventManagerInterface::class    => $container->lazyGet("app.eventManager"),
            Twig_Environment::class         => $container->lazyGet("app.twig"),
            Environment::class              => $container->lazyGet("app.twig"),
            ResponseFactoryInterface::class => $container->lazyGet("app.factory.response"),
            ContainerInterface::class       => $container,
            CacheItemPoolInterface::class   => $container->lazyGet("app.cache"),
            CacheInterface::class           => $container->lazyGet("app.simplecache"),
            ServerRequestInterface::class   => $container->lazyGet("app.request"),
            LoggerInterface::class          => $container->lazyGet("app.log"),
        ]);
    }
}