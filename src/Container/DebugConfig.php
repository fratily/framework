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

use Fratily\Framework\Traits\LogTrait;
use Fratily\Framework\Traits\DumpTrait;
use Fratily\Framework\Traits\PerformanceTrait;
use Fratily\Framework\Middleware\DebugMiddleware;
use Fratily\Framework\Debug\Panel\MessagePanel;
use Fratily\Framework\Debug\Panel\PerformancePanel;
use Fratily\Framework\Middleware\WrapperMiddleware;
use Fratily\DebugBar\DebugBar;
use Fratily\DebugBar\Panel\PHPInfoPanel;
use Fratily\DebugBar\Panel\DumpPanel;
use Fratily\Container\Container;
use Fratily\Container\ContainerConfig;

/**
 *
 */
class DebugConfig extends ContainerConfig{

    /**
     * @var float
     */
    private $startedAt;

    public function __construct(float $startedAt){
        $this->startedAt    = $startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function define(Container $container){
        $container
            ->set("core.middleware.debug", $container->lazyNew(
                DebugMiddleware::class,
                [
                    $container->lazyGet("core.debugbar"),
                ]
            ))
            ->set("core.debugbar", $container->lazyNew(
                DebugBar::class,
                [
                    $container->lazyArray([
                        $container->lazyGet("core.debugbar.phpinfo"),
                        $container->lazyGet("core.debugbar.performance"),
                        $container->lazyGet("core.debugbar.message"),
                        $container->lazyGet("core.debugbar.dump"),
                    ]),
                ]
            ))
            ->set("core.debugbar.phpinfo", $container->lazyNew(
                PHPInfoPanel::class,
                [
                    "name"  => "PHP"
                ]
            ))
            ->set("core.debugbar.message", $container->lazyNew(
                MessagePanel::class,
                [
                    "name"  => "Log"
                ]
            ))
            ->set("core.debugbar.performance", $container->lazyNew(
                PerformancePanel::class,
                [
                    "name"  => "Performance",
                    "start" => $this->startedAt,
                ]
            ))
            ->set("core.debugbar.dump", $container->lazyNew(
                DumpPanel::class,
                [
                    "name"  => "Dump"
                ]
            ))
            ->setter(
                DumpTrait::class,
                "setDumpPanel",
                $container->lazyGet("core.debugbar.dump")
            )
            ->setter(
                LogTrait::class,
                "setMessagePanel",
                $container->lazyGet("core.debugbar.message")
            )
            ->setter(
                PerformanceTrait::class,
                "setPerformancePanel",
                $container->lazyGet("core.debugbar.performance")
            )
            ->setter(
                WrapperMiddleware::class,
                "setPerformancePanel",
                $container->lazyGet("core.debugbar.performance")
            )
        ;
    }
}