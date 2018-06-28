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

use Fratily\Framework\Traits\DebugTrait;
use Fratily\Framework\Traits\LogTrait;
use Fratily\Framework\Traits\EventTrait;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;

/**
 *
 */
class TraitConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container
            ->setter(
                DebugTrait::class,
                "setDebug",
                $container->lazyValue("app.debug")
            )
            ->setter(
                LogTrait::class,
                "setLogger",
                $container->lazyGet("app.log")
            )
            ->setter(
                EventTrait::class,
                "setEventManager",
                $container->lazyGet("app.eventManager")
            )
        ;
    }
}