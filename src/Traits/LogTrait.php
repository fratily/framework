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
namespace Fratily\Framework\Traits;

use Fratily\DebugBar\Collector\MessageCollector;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 *
 */
trait LogTrait{

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var MessageCollector|null
     */
    private $messageCollector;

    /**
     *
     *
     * @param   LoggerInterface $logger
     *
     * @return  void
     */
    public function setLogger(LoggerInterface $logger){
        $this->logger   = $logger;
    }

    /**
     *
     *
     * @param   MessageCollector    $messageCollector
     *
     * @return  void
     */
    public function setMessageCollector(MessageCollector $messageCollector){
        $this->messageCollector  = $messageCollector;
    }

    /**
     * System is unusable.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public function emergency(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->emergency($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::EMERGENCY);
        }
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
    public function alert(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->alert($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::ALERT);
        }
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
    public function critical(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->critical($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::CRITICAL);
        }
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
    public function error(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->error($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::ERROR);
        }
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
    public function warning(string $message, array $context = []){
        if($this->logger !== null){
            $this->warning($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::WARNING);
        }
    }

    /**
     * Normal but significant events.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public function notice(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->notice($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::NOTICE);
        }
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
    public function info(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->info($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::INFO);
        }
    }

    /**
     * Detailed debug information.
     *
     * @param   string  $message
     * @param   mixed[] $context
     *
     * @return  void
     */
    public function debug(string $message, array $context = []){
        if($this->logger !== null){
            $this->logger->debug($message, $context);
        }

        if($this->messageCollector !== null){
            $this->messageCollector->addMessage($message, LogLevel::DEBUG);
        }
    }
}