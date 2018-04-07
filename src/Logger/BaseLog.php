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
namespace Fratily\Framework\Logger;

use Fratily\Utility\Hash;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Base logging class.
 */
abstract class BaseLog implements LoggerInterface{

    const LV2STR    = [
        LogLevel::EMERGENCY  => "emergency",
        LogLevel::ALERT      => "alert",
        LogLevel::CRITICAL   => "critical",
        LogLevel::ERROR      => "error",
        LogLevel::WARNING    => "warning",
        LogLevel::NOTICE     => "notice",
        LogLevel::INFO       => "info",
        LogLevel::DEBUG      => "debug"
    ];

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []){
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []){
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []){
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []){
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []){
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []){
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []){
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []){
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Embed context in message.
     *
     * @param   string  $message
     *
     * @return  string
     */
    public static function format($message, array $context = []): string{
        static $pattern = "/\{([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)\}/u";

        if(!(bool)preg_match_all(
            $pattern, $message, $matches, PREG_PATTERN_ORDER
        )){
            return $message;
        }

        $matches    = array_unique($matches[1]);
        $search     = [];
        $replace    = [];

        foreach($matches as $match){
            $data       = Hash::get($context, $match);
            $search[]   = "{{$match}}";

            if(is_scalar($data) || (is_object($data) && method_exists($data, "__toString"))){
                $replace[]  = (string)$data;
            }else{
                $replace[]  = ":unknown:";
            }
        }

        return str_replace($search, $replace, $message);
    }

    /**
     *
     * @param type $level
     * @param $message
     * @param array $context
     */
    public static function createOutput($level, $message, array $context): string{
        return sprintf("[%s] %s: %s" . PHP_EOL,
            date("Y-m-d H:i:s"),
            ucfirst(self::LV2STR[$level] ?? "none"),
            static::format($message, $context)
        );
    }
}