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

use Fratily\Framework\Application;
use Fratily\Router\RouteCollector;
use Fratily\Http\Factory\ResponseFactory;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;
use Fratily\Cache\SimpleCache;
use Psr\Cache\CacheItemPoolInterface;

/**
 *
 */
class AppConfig extends ContainerConfig{

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param   CacheItemPoolInterface  $cache
     * @param   bool    $debug
     */
    public function __construct(CacheItemPoolInterface $cache, bool $debug){
        $this->cache    = $cache;
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container->set("app", $container->lazyNew(
            Application::class,
            [
                "debug"     => $container->lazyValue("app.debug")
            ]
        ));

        $container->set("app.cache", $this->cache);
        $container->set("app.simplecache", $container->lazyNew(SimpleCache::class));
        $container->set("app.routes", $container->lazyNew(RouteCollector::class));
        $container->set("app.factory.response", $container->lazyNew(ResponseFactory::class));

        $container->value("app.debug", $this->debug);
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Container $container){
        $app    = $container->get("app");

        // ミドルウェアを追加する
    }
}