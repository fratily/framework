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

use Fratily\DebugBar\Collector\TimeCollector;

/**
 *
 */
trait TimelineTrait{

    /**
     * @var TimeCollector|null
     */
    private $timeCollector;

    /**
     *
     *
     * @param   TimeCollector   $timeCollector
     *
     * @return  void
     */
    public function setTimeCollector(TimeCollector $timeCollector){
        $this->timeCollector = $timeCollector;
    }

    public function startTimeline(string $name){
        if($this->timeCollector !== null){
            $this->timeCollector->start($name);
        }
    }

    public function endTimeline(string $name){
        if($this->timeCollector !== null){
            $this->timeCollector->end($name);
        }
    }
}