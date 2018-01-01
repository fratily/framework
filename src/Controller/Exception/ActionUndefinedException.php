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
namespace Fratily\Controller\Exception;

use Fratily\Exception\MethodUndefinedException;

/**
 *
 */
class ActionUndefinedException extends MethodUndefinedException implements ControllerException{

    const MSG   = "Call to undefined action {class.name}::{method.name}().";
}