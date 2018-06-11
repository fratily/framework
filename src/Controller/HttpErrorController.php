<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Framework\Controller;

use Fratily\Http\Message\Status\BadRequest;
use Fratily\Http\Message\Status\Forbidden;
use Fratily\Http\Message\Status\NotFound;
use Fratily\Http\Message\Status\MethodNotAllowed;
use Fratily\Http\Message\Status\InternalServerError;
use Fratily\Http\Message\Status\NotImplemented;
use Fratily\Http\Message\Status\ServiceUnavailable;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 */
class HttpErrorController extends Controller{

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  BadRequest
     */
    public function badRequest(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new BadRequest($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  Forbidden
     */
    public function forbidden(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new Forbidden($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  NotFound
     */
    public function notFound(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new NotFound($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  MethodNotAllowed
     */
    public function methodNotAllowed(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new MethodNotAllowed($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  InternalServerError
     */
    public function internalServerError(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new InternalServerError($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  NotImplemented
     */
    public function notImplemented(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new NotImplemented($msg, $code, $prev);
    }

    /**
     *
     * @param   ServerRequestInterface  $_request
     * @param   string $msg
     * @param   int $code
     * @param   \Throwable  $prev
     *
     * @throws  ServiceUnavailable
     */
    public function serviceUnavailable(
        ServerRequestInterface $_request,
        string $msg = "",
        int $code = 0,
        \Throwable $prev = null
    ){
        throw new ServiceUnavailable($msg, $code, $prev);
    }
}