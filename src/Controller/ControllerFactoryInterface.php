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
namespace Fratily\Controller;

/**
 *
 */
interface ControllerFactoryInterface{

    /**
     * コントローラーを返す
     *
     * @param   string  $controller
     *      コントローラー名
     *
     * @throws  EXception\ControllerNotFoundException
     *
     * @return  Controller
     */
    public function getController(string $controller): Controller;

    /**
     * エラーコントローラーを返す
     *
     * @return  ErrorControllerInterface
     */
    public function getErrorController(): ErrorControllerInterface;
}