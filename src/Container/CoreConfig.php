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

/**
 *
 */
class CoreConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
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
    }
}