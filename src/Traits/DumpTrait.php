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

use Fratily\DebugBar\Collector\VarCollector;

/**
 *
 */
trait DumpTrait{

    /**
     * @var VarCollector|null
     */
    private $varCollector;

    /**
     *
     *
     * @param   VarCollector    $varCollector
     *
     * @return  void
     */
    public function setVarCollector(VarCollector $varCollector){
        $this->varCollector = $varCollector;
    }

    /**
     *
     * @param   mixed   $val
     *
     * @return  void
     */
    public function dump($val){
        if($this->varCollector !== null){
            $trace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            $this->varCollector->dump(
                $val,
                $trace[0]["file"] ?? "unknown",
                $trace[0]["line"] ?? 0
            );
        }
    }
}