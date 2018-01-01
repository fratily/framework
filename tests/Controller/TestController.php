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
namespace FratilyTest\Controller;

/**
 *
 */
class TestController extends \Fratily\Controller\Controller{

    public function returnString(){
        return "message";
    }

    public function returnArray(){
        return ["message", [1,2,3]];
    }

    public function returnResponse($response){
        $response->getBody()->write("string");
        return $response;
    }

    public static function staticAction(){
        return "message";
    }

    private function privateAction(){
        return "message";
    }

    protected function protectedAction(){
        return "message";
    }
}