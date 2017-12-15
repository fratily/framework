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
use Fratily\Exception\RuntimeException;

/**
 * セッション機能が無効な場合にスローされる
 */
class SessionDisabledException extends Exception implements SessionException, RuntimeException{

    const MSG   = "Session is disabled in this PHP.";
}