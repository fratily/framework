<?php
/**
 * FratilyPHP
 * 
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Session\Exception;

use Fratily\Core\Exception;
use Fratily\Exception\LogicException;

/**
 * セッションが開始されていない場合にスローされる例外
 */
class SessionNotStartedException extends Exception implements SessionException, LogicException{

    const MSG   = "Session not started {call.in}";
}