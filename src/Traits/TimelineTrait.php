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

use Fratily\DebugBar\Panel\TimelinePanel;

/**
 *
 */
trait TimelineTrait{

    /**
     * @var TimelinePanel
     */
    private $timelinePanel;

    /**
     *
     *
     * @param   TimelinePanel   $panel
     *
     * @return  void
     */
    public function setTimelinePanel(TimelinePanel $panel){
        $this->timelinePanel    = $panel;
    }

    public function startTimeline(string $name){
        if($this->timelinePanel !== null){
            $this->timelinePanel->start($name);
        }
    }

    public function endTimeline(string $name){
        if($this->timelinePanel !== null){
            $this->timelinePanel->end($name);
        }
    }
}