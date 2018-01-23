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
namespace Fratily\Application;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * 
 */
class Logger{

    /**
     * Default logger
     */
    const LOGGER_LIST   = [
        "filelog"   => Logger\Filelog::class,
        "syslog"    => Logger\Syslog::class
    ];

    /**
     * レベルごとのロガーリスト
     *
     * @var LoggerInterface[][]
     */
    private static $loggerList = [
        LogLevel::EMERGENCY => [],
        LogLevel::ALERT     => [],
        LogLevel::CRITICAL  => [],
        LogLevel::ERROR     => [],
        LogLevel::WARNING   => [],
        LogLevel::NOTICE    => [],
        LogLevel::INFO      => [],
        LogLevel::DEBUG     => []
    ];

    /**
     * ロガーに対応するスコープリスト
     *
     * @var \SplObjectStorage
     */
    private static $scopeList;

    private static function init(){
        if(self::$scopeList === null){
            self::$scopeList    = new \SplObjectStorage();
        }
    }

    /**
     * ロガーを追加する
     *
     * @param   mixed|mixed[]   $levels
     *      対応するレベルリスト
     * @param   LoggerInterface|string|callable $logger
     *      ロガーインスタンスもしくは生成用クラス名、もしくはコールバック
     * @param   string[]|string|null|false  $scopes
     *      対応するスコープリスト
     *
     * @throws  InvalidArgumentException
     */
    public static function addLogger($levels, $logger, $scopes = null, array $options = []){
        self::init();

        $levels = self::validLevels($levels);
        $scopes = self::validScopes($scopes);

        if(is_string($logger)){
            if(isset(self::LOGGER_LIST[$logger])){
                $logger = self::LOGGER_LIST[$logger];
                $logger = new $logger($options);
            }else if(class_exists($logger)){
                $logger = new $logger($options);
            }else if(is_callable($logger)){
                $logger = $logger($options);
            }
        }
        
        if(!($logger instanceof LoggerInterface)){
            throw new \InvalidArgumentException(
                ""
            );
        }else if($levels === false){
            throw new \InvalidArgumentException(
                "Level setting is invalid {call.in}."
            );
        }else if($scopes !== null && $scopes !== false && !is_array($scopes)){
            throw new \InvalidArgumentException(
                "Scope setting is invalid {call.in}."
            );
        }

        if(!isset(self::$scopeList[$logger])){
            foreach($levels as $level){
                self::$loggerList[$level][] = $logger;
            }

            self::$scopeList[$logger]   = $scopes;
        }
    }

    protected static function validLevels($levels){
        $levels = is_array($levels) ? $levels : [$levels];
        $levels = array_filter(array_unique($levels), function($v){
            return array_key_exists($v, Logger\BaseLog::LV2STR);
        });

        if(empty($levels)){
            return false;
        }

        return $levels;
    }

    protected static function validScopes($scope){
        if(is_string($scope)){
            $scope  = explode(",", $scope);
        }

        if(is_array($scope)){
            $scope  = array_filter(array_unique($scope), "is_string");
            $scope  = empty($scope) ? null : array_flip($scope);
        }else if($scope !== false && $scope !== null){
            $scope  = null;
        }

        return $scope;
    }

    /**
     * ログを出力する
     *
     * @param   mixed   $level
     *      ログのレベル
     * @param   string  $message
     *      ログのメッセージ
     * @param array $context
     *      ログメッセージのコンテキスト
     *
     * @return  void
     */
    protected static function write(
        $level,
        string $message,
        array $context = [],
        string $scope = null
    ){
        if(array_key_exists($level, self::PSR2STR)){
            $method = self::PSR2STR[$level];

            foreach(self::$loggerList[$level] as $logger){
                $scopes = self::$scopeList[$logger] ?? null;

                if($scopes === null
                    || ($scopes === false && $scope === null)
                    || (is_array($scopes) && array_key_exists($scope, $scopes))
                ){
                    $logger->$method($message, $context);
                }
            }
        }
    }

    /**
     * System is unusable.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function emergency(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::EMERGENCY, $message, $context, $scope);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function alert(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::ALERT, $message, $context, $scope);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function critical(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::CRITICAL, $message, $context, $scope);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function error(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::ERROR, $message, $context, $scope);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function warning(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::WARNING, $message, $context, $scope);
    }

    /**
     * Normal but significant events.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function notice(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::NOTICE, $message, $context, $scope);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function info(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::INFO, $message, $context, $scope);
    }

    /**
     * Detailed debug information.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public static function debug(string $message, array $context = [], string $scope = null){
        self::write(LogLevel::DEBUG, $message, $context, $scope);
    }
}
