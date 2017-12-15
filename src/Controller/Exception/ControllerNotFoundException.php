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
namespace Fratily\Controller\Exception;

use Fratily\Exception\ClassUndefinedException;

/**
 *
 */
class ControllerNotFoundException extends ClassUndefinedException implements ControllerException{

    const MSG   = "Call to undefined controller {class.name} {call.in}.";
}