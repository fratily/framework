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
namespace Fratily\Framework\Controller;

/**
 *
 */
class HttpStatusController extends Controller{

    public function notFound(){
        $response   = $this->response(404);
        $body       = $this->render("error/404.twig");

        return $response->getBody()->write($body);
    }
}