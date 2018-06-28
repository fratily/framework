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

/**
 *
 */
trait DebugTrait{

    /**
     * @var bool
     */
    private $debug;

    /**
     * デバッグモードが有効か確認する
     *
     * @return  bool
     */
    public function isDebug(){
        return $this->debug ?? false;
    }

    /**
     * デバッグモードが有効か設定する
     *
     * @param   bool    $debug
     *
     * @return  void
     */
    public function setDebug(bool $debug){
        $this->debug    = $debug;
    }
}