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

use Fratily\EventManager\EventManagerInterface;

/**
 *
 */
trait EventTrait{

    /**
     * @var EventManagerInterface|null
     */
    private $eventManager;

    /**
     *
     *
     * @param   EventManagerInterface   $eventManager
     *
     * @return  void
     */
    public function setEventManager(EventManagerInterface $eventManager){
        $this->eventManager = $eventManager;
    }

    public function event($event, ...$args){
        if($this->eventManager !== null){
            $this->eventManager->trigger($event, $args);
        }
    }

}