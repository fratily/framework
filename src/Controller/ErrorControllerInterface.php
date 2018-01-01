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
interface ErrorControllerInterface{
    
    /**
     * HTTPステータスコード別の処理
     * 
     * @param   int $code
     * 
     * @return  Psr\Http\Message\ResponseInterface|string
     */
    public function status(int $code);
    
    /**
     * 例外およびエラーをキャッチした場合の処理
     * 
     * @param   \Throwable  $e
     * 
     * @return  Psr\Http\Message\ResponseInterface|string
     */
    public function throwable(\Throwable $e);
}