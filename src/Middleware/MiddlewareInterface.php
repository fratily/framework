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
namespace Fratily\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 
 */
interface MiddlewareInterface{
    
    /**
     * ミドルウェアを実行する
     * 
     * @param   ServerRequestInterface  $request
     * @param   ResponseInterface   $response
     * @param   callable    $next
     *      Function for calling the next middleware.
     * 
     * @return  ResponseInterface
     */
    public function process(ServerRequestInterface $request, MiddlewareHandlerInterface $handler): ResponseInterface;
}