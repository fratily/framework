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

use Fratily\Framework\Middleware\ActionMiddleware;
use Fratily\Framework\Middleware\ErrorMiddleware;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;

/**
 *
 */
class MiddlewareConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container
            ->set("core.middleware.action", $container->lazyNew(
                ActionMiddleware::class,
                [
                    "action"    => $container->lazyValue("core.action.internalServerError"),
                    "params"    => [
                        "msg"   => "Action is undefined.",
                    ],
                ]
            ))
            ->set("core.middleware.error", $container->lazyNew(
                ErrorMiddleware::class,
                [
                    "twig"  => $container->lazyGet("core.twig"),
                    "debug" => $container->lazyValue("app.debug"),
                ]
            ))
        ;
    }
}