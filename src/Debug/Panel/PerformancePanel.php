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
use Fratily\DebugBar\Block\TimelineBlock;
use Fratily\DebugBar\Block\MetricsBlock;

class PerformancePanel extends AbstractPanel{

    /**
     * @var TimelineBlock
     */
    private $timeline;

    /**
     * @var MetricsBlock
     */
    private $metrics;

    /**
     * @var float
     */
    private $start;

    /**
     * @var float
     */
    private $end;

    /**
     * @var float[]
     */
    private $lineStart  = [];

    /**
     * Constructor
     *
     * @param   string  $name
     * @param   float   $start
     */
    public function __construct(string $name, float $start){
        $this->timeline = new TimelineBlock();
        $this->metrics  = new MetricsBlock();
        $this->start    = $start;

        parent::__construct($name, [
            $this->metrics,
            $this->timeline,
        ]);
    }

    /**
     * タイムラインの終了時間を設定する
     *
     * マイクロ秒精度UNIXタイムスタンプで設定する
     *
     * @param   float   $time
     *
     * @return  void
     */
    public function setEndTime(float $time){
        if($time <= $this->start){
            throw new \LogicException;
        }

        $this->end  = $time;

        $this->timeline->setExecutionTime($this->end - $this->start);
    }

    /**
     * 指定名のタイムラインを開始する
     *
     * @param   string  $name
     *
     * @return  void
     */
    public function start(string $name){
        $this->lineStart[$name] = microtime(true);
    }

    /**
     * 指定名のタイムラインを終了する
     *
     * @param   string  $name
     *
     * @return  void
     */
    public function end(string $name){
        $time   = microtime(true);

        if(!array_key_exists($name, $this->lineStart)){
            throw new \LogicException;
        }

        $this->timeline->addLine(
            $name,
            $this->lineStart[$name] - $this->start,
            $time - $this->lineStart[$name]
        );
    }

    /**
     * タイムラインを追加する
     *
     * @param   string  $name
     *  タイムライン名
     * @param   float   $start
     *  タイムライン開始時間
     * @param   float   $end
     *  タイムライン終了時間
     *
     * @return  $this
     */
    public function addLine(string $name, float $start, float $end){
        $this->timeline->addLine(
            $name,
            $start - $this->start,
            $end - $start
        );

        return $this;
    }

    protected function beforeGetIterator(){
        if($this->end === null){
            $this->end  = microtime(true);
        }

        $this->metrics->addTextMetric(
            "Execution time",
            round(($this->end - $this->start) * 1000, 2),
            "ms"
        );
        $this->metrics->addTextMetric(
            "Peak memory usage",
            round(memory_get_peak_usage(true) / (1024 * 1024), 3),
            "MB"
        );
    }
}