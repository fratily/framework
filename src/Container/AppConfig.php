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

use Fratily\Framework\Application;
use Fratily\Router\RouteCollector;
use Fratily\Http\Factory\ServerRequestFactory;
use Fratily\Http\Factory\ResponseFactory;
use Fratily\Http\Message\Response\Emitter;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;
use Fratily\EventManager\EventManager;

/**
 *
 */
class AppConfig extends ContainerConfig{

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
    public function __construct(bool $debug){
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container
            ->set("app", $container->lazyNew(Application::class))
            ->set("app.routes", $container->lazyNew(RouteCollector::class))
            ->set("app.factory.request", $container->lazyNew(ServerRequestFactory::class))
            ->set("app.factory.response", $container->lazyNew(ResponseFactory::class))
            ->set("app.response.emitter", $container->lazyNew(Emitter::class))
            ->set("app.eventManager", $container->lazyNew(EventManager::class))
            ->value("app.debug", $this->debug)
        ;
    }
}