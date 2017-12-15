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
namespace Fratily\Router;

/**
 * 
 */
interface RouterInterface{

    const FOUND                 = 0;
    const NOT_FOUND             = 1;
    const METHOD_NOT_ALLOWED    = 2;
    
    /**
     * ルーティングを行う
     *
     * @param   string  $method
     *      アクセスメソッド名
     * @param   string  $path
     *      アクセスURIパス
     *
     * @return  Reslut
     */
    public function search(string $method, string $path): Result;
}