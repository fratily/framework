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
namespace Fratily\Framework\Middleware;

use Fratily\DebugBar\Panel\TimelinePanel;
use Fratily\EventManager\EventManagerInterface;



/**
 *
 */
class WrapperMiddleware implements MiddlewareInterface{

    use \Fratily\Framework\Traits\DebugTrait;

    /**
     * @var EventManagerInterface
     */
    private $eventMng;

    /**
     * @var TimelinePanel
     */
    private $timeline;

    /**
     * Constructor
     *
     * @param   EventManagerInterface   $eventMng
     */
    public function __construct(EventManagerInterface $eventMng){
        $this->eventMng = $eventMng;
    }

    /**
     * タイムラインパネルをセットする
     *
     * @param   TimelinePanel   $timeline
     *
     * @return  void
     */
    public function setTimelinePanel(TimelinePanel $timeline){
        $this->timeline = $timeline;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $response   = $handler->handle($request);

        if($this->timeline !== null){
            $this->timeline->setEndTime(microtime(true));
        }

        return $response;
    }
}