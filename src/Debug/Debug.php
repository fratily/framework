<?php
/**
 * FratilyPHP Debug
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
namespace Fratily\Framework\Debug;

use Fratily\EventManager\EventManagerInterface;
use Fratily\DebugBar\DebugBar;

class Debug{

    /**
     * @var DebugBar|null
     */
    private static $debugbar;

    private static $log;

    private static $dump;


    public static function getDebugBar(){
        if(self::$debugbar === null){
            self::$debugbar = new DebugBar;
        }
    }

    public static function attachEventListener(EventManagerInterface $manager){
        $manager->attach("log", [self::class, ""]);
    }
}