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
namespace Fratily\Framework\Debug\Panel;

use Fratily\DebugBar\Panel\AbstractPanel;
use Fratily\DebugBar\Block\TableBlock;
use Fratily\DebugBar\Block\MetricsBlock;
use Psr\Log\LogLevel;

class MessagePanel extends AbstractPanel{

    const LEVEL = [
        LogLevel::EMERGENCY => "EMERGENCY",
        LogLevel::ALERT     => "ALERT",
        LogLevel::CRITICAL  => "CRITICAL",
        LogLevel::ERROR     => "ERROR",
        LogLevel::WARNING   => "WARNING",
        LogLevel::NOTICE    => "NOTICE",
        LogLevel::INFO      => "INFO",
        LogLevel::DEBUG     => "DEBUG",
    ];

    /**
     * @var int[]
     */
    private $count  = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 0,
        LogLevel::CRITICAL  => 0,
        LogLevel::ERROR     => 0,
        LogLevel::WARNING   => 0,
        LogLevel::NOTICE    => 0,
        LogLevel::INFO      => 0,
        LogLevel::DEBUG     => 0,
    ];

    /**
     * @var TableBlock
     */
    private $tabel;

    /**
     * @var MetricsBlock
     */
    private $metrics;

    /**
     * Constructor
     *
     * @param   string  $name
     */
    public function __construct(string $name){
        $this->metrics  = new MetricsBlock();
        $this->table    = new TableBlock(["Level", "Message"]);

        parent::__construct($name, [$this->metrics, $this->tabel]);
    }

    /**
     * メッセージを追加する
     *
     * @param   string  $message
     * @param   mixed   $level
     *
     * @return  $this
     */
    public function addMessage(string $message, $level = LogLevel::DEBUG){
        if(!array_key_exists($level, self::LEVEL)){
            return;
        }

        $this->tabel->addRow(self::LEVEL[$level], $message);

        $this->count[$level]++;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeGetIterator(){
        foreach(self::LEVEL as $level => $name){
            $this->metrics->addTextMetric($name, $this->count[$level]);
        }
    }
}