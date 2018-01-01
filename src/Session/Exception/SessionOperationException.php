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
namespace Fratily\Session\Exception;

use Fratily\Core\Exception;
use Fratily\Exception\RuntimeException;

/**
 * セッションの操作に失敗した場合にスローされる例外
 */
class SessionOperationException extends Exception implements SessionException, RuntimeException{
    
    const MSG   = "Faild to session operation {call.in}";
}