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
use Psr\Container\ContainerInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 *
 */
class ApplicationFactory{

    const CONTAINER_CACHE_KEY       = "fratily.app.container";
    const DEBUG_CONTAINER_CACHE_KEY = "fratily.app.container.debug";

    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * Constructor
     *
     * @param   CacheItemPoolInterface $cache
     */
    public function __construct(CacheItemPoolInterface $cache){
        $this->cache    = $cache;
    }

    /**
     * アプリケーションインスタンスを生成する
     *
     * @param   mixed[] $containerConfig
     * @param   bool    $debug
     * @param   bool    $containerCache
     *
     * @return  Application
     */
    public function create(array $containerConfig = [], bool $debug = false, bool $containerCache = true){
        $cacheKey   = $debug ? self::DEBUG_CONTAINER_CACHE_KEY : self::CONTAINER_CACHE_KEY;
        $container  = null;
        $cacheItem  = $this->cache->getItem($cacheKey);

        if($containerCache && $cacheItem->isHit()){
            $container  = $cacheItem->get();
        }

        if(!($container instanceof ContainerInterface)){
            $containerConfig    = array_merge(
                [
                    new Container\AppConfig($this->cache, $debug),
                ],
                $containerConfig,
                [
                    new Container\CoreConfig($debug),
                ]
            );

            $container  = (new ContainerFactory())
                ->createWithConfig($containerConfig, true)
            ;

            $cacheItem->set($container);

            $this->cache->save($cacheItem);
        }

        $app    = $container->get("app");

        if(!($app instanceof Application)){
            throw new \LogicException;
        }

        return $app;
    }
}