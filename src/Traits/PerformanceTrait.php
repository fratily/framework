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
namespace Fratily\Framework\Traits;

use Fratily\Framework\Debug\Panel\PerformancePanel;

/**
 *
 */
trait PerformanceTrait{

    /**
     * @var PerformancePanel
     */
    private $performancePanel;

    /**
     *
     *
     * @param   PerformancePanel    $panel
     *
     * @return  void
     */
    public function setPerformancePanel(PerformancePanel $panel){
        $this->performancePanel = $panel;
    }

    public function startTimeline(string $name){
        if($this->performancePanel !== null){
            $this->performancePanel->start($name);
        }
    }

    public function endTimeline(string $name){
        if($this->performancePanel !== null){
            $this->performancePanel->end($name);
        }
    }
}