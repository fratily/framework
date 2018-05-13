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
use Fratily\Container\{
    Container,
    ContainerConfig
};

/**
 *
 */
class AppConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container->value("app.debug", false);

        $container->set("app.routes", $container->lazyNew(RouteCollector::class));

        $container->set("app.application", $container->lazyNew(
            Application::class,
            [
                "routes"    => $container->lazyGet("app.routes"),
                "debug"     => $container->lazyValue("app.debug")
            ]
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function modify(Container $container){
        $app    = $container->get("app.application");

        // ミドルウェアを追加する
    }
}