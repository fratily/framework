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
namespace Fratily\Framework;

use Fratily\Container\ContainerFactory;

/**
 *
 */
class ApplicationFactory{

    /**
     * @var string[]
     */
    private $containerConfig;

    public function __construct(array $containerConfig = []){
        $this->containerConfig  = array_merge([
            Container\CoreConfig::class,
            Container\AppConfig::class,
        ], $containerConfig);
    }

    /**
     * アプリケーションインスタンスを生成する
     *
     * @return  Application
     */
    public function create(array $containerConfig = []){
        $containerConfig    = array_merge([
            Container\CoreConfig::class,
            Container\AppConfig::class,
        ], $containerConfig);

        $app    = (new ContainerFactory())
            ->createWithConfig($containerConfig,true)
            ->get("app.application")
        ;

        return $app;
    }
}