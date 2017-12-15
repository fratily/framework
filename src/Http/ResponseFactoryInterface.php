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
namespace Fratily\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * {@inheritdoc}
 */
interface ResponseFactoryInterface{
    
    /**
     * Create a new response.
     *
     * @param   int $code
     *      HTTP status code
     *
     * @return  ResponseInterface
     */
    public function createResponse(int $code = 200): ResponseInterface;
}