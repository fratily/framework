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

use Fratily\Framework\Controller\Controller;
use Fratily\Framework\Controller\HttpErrorController;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;

/**
 *
 */
class ActionConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container
            ->param(
                Controller::class,
                "debug",
                $container->lazyValue("app.debug")
            )
            ->set(
                "core.controller.httperror",
                $container->lazyNew(
                    HttpErrorController::class
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
}