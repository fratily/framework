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
namespace Fratily\Tests\Framework;

use Fratily\Framework\Application;
use Fratily\Framework\ApplicationFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

class ApplicationFactoryTest extends \PHPUnit\Framework\TestCase{

    public function testStandard(){
        $cacheItem  = $this->createMock(CacheItemInterface::class);
        $cachePool  = $this->createMock(CacheItemPoolInterface::class);

        $cachePool->method("getItem")->willReturn($cacheItem);
        $cachePool->method("save")->willReturn(true);

        $cacheItem->method("isHit")->willReturn(false);

        $factory    = new ApplicationFactory($cachePool);

        $this->assertInstanceOf(Application::class, $factory->create());
    }
}