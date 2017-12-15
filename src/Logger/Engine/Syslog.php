<?php
/**
 * FratilyPHP
 * 
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Logger\Engine;

use Fratily\Configer\InstanceConfigTrait;
use Fratily\Configer\ConfigData;
use Fratily\Logger\Logger;
use Psr\Log\LogLevel;

/**
 * Logging class to output to Syslog.
 */
class Syslog extends BaseLog{
    
    use InstanceConfigTrait;
    
    const LEVEL_MAP = [
        LogLevel::EMERGENCY  => LOG_EMERG,
        LogLevel::ALERT      => LOG_ALERT,
        LogLevel::CRITICAL   => LOG_CRIT,
        LogLevel::ERROR      => LOG_ERR,
        LogLevel::WARNING    => LOG_WARNING,
        LogLevel::NOTICE     => LOG_NOTICE,
        LogLevel::INFO       => LOG_INFO,
        LogLevel::DEBUG      => LOG_DEBUG
    ];
    
    /**
     * Constructor
     * 
     * @param   mixed   $config
     *      Confguration list.
     * 
     * @return  void
     */
    public function __construct(array $config = []){
        foreach($config as $key => $val){
            $this->setConfig($key, $val);
        }
    }
    
    /**
     * Destruct the class.
     * 
     * @return  void
     */
    public function __destruct(){
        closelog();
    }
    
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []){
        $open = openlog(
            $this->getConfig("prefix", ""),
            $this->getConfig("option", LOG_ODELAY),
            $this->getConfig("facility", LOG_USER)
        );

        syslog(self::LEVEL_MAP[$level], sprintf("%s: %s",
            ucfirst(Logger::PSR2STR[$level]), static::format($message, $context)
        ));
        
        closelog();
    }
    
    protected function initConfigData(ConfigData $data): ConfigData{
        return $data->withValue(
            "prefix", "",
            function($v){
                return is_string($v);
            }
        )->withValue(
            "option", LOG_ODELAY
        )->withValue(
            "facility", LOG_USER
        );
    }
}