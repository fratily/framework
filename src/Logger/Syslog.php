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

use Psr\Log\LogLevel;

/**
 * Logging class to output to Syslog.
 */
class Syslog extends BaseLog{

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
     * @var static|null
     */
    private static $instance;

    /**
     * @var string
     */
    private $prefix;

    private $option;

    private $facility;

    /**
     * Constructor
     *
     * @param   mixed   $config
     *      Confguration list.
     *
     * @return  void
     */
    public function __construct(
        string $prefix = "",
        $option = LOG_ODELAY,
        $facility = LOG_USER
    ){
        $this->prefix   = $prefix;
        $this->option   = $option;
        $this->facility = $facility;

        openlog($this->prefix, $this->option, $this->facility);

        self::$instance = $this;
    }

    /**
     * Destructor
     */
    public function __destruct(){
        self::$instance = null;

        closelog();
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []){
        syslog(self::LEVEL_MAP[$level], static::format($message, $context));
    }
}