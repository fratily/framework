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
namespace Fratily\Framework\ContainerConfig;

use Fratily\Container\{
    Container,
    ContainerConfig
};
use \Twig\{
    Environment,
    Loader\FilesystemLoader
};

/**
 *
 */
class CoreConfig extends ContainerConfig{

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container->set(
            "core.twig",
            $container->lazyNew(Environment::class, [
                $container->lazyNew(FilesystemLoader::class, [
                    $container->lazyValue("core.twig.views")
                ]),
            ]
        ));

        $container->value("core.twig.views", FRATILY_FW_ROOT . DS . "resource" . DS .  "views");
    }
}