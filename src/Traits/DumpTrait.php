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

use Fratily\DebugBar\Panel\DumpPanel;

/**
 *
 */
trait DumpTrait{

    /**
     * @var DumpPanel
     */
    private $dumpPanel;

    /**
     *
     *
     * @param   DumpPanel   $panel
     *
     * @return  void
     */
    public function setDumpPanel(DumpPanel $panel){
        $this->dumpPanel    = $panel;
    }

    /**
     *
     * @param   mixed   $val
     *
     * @return  void
     */
    public function dump($val){
        if($this->dumpPanel !== null){
            $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            $this->dumpPanel->dump(
                $val,
                $trace[0]["file"] ?? "unknown",
                $trace[0]["line"] ?? 0
            );
        }
    }
}