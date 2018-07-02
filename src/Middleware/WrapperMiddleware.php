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

use Fratily\Framework\Debug\Panel\PerformancePanel;
use Fratily\EventManager\EventManagerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;


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
     * @var PerformancePanel
     */
    private $performance;

    /**
     * Constructor
     *
     * @param   EventManagerInterface   $eventMng
     */
    public function __construct(EventManagerInterface $eventMng){
        $this->eventMng = $eventMng;
    }

    /**
     *
     *
     * @param   PerformancePanel    $timeline
     *
     * @return  void
     */
    public function setPerformancePanel(PerformancePanel $timeline){
        $this->performance  = $timeline;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $response   = $handler->handle($request);

        if($this->performance !== null){
            $this->performance->setEndTime(microtime(true));
        }

        return $response;
    }
}