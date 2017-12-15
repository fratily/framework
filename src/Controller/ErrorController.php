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
namespace Fratily\Controller;

/**
 * 
 */
class ErrorController extends Controller implements ErrorControllerInterface{
    
    /**
     * {@inheritdoc}
     */
    public function status(int $code){
        if(method_exists($this, "http$code")){
            return $this->{"http$code"}();
        }
        
        $response   = $this->response->withStatus($code);
        $response->getBody()->write("");
        
        return $response;
    }
    
    /**
     * {@inheritdoc}
     */
    public function throwable(\Throwable $e){
        if(!\Fratily\Configer\Configure::get("app.debug")){
            return $this->status(500);
        }
        
        $tracese    = "";
        
        foreach($e->getTrace() as $t){
            $args   = array_map(function($v){
                return \Fratily\Debug\Dumper::dumpSimple($v);
            }, $t["args"]??[]);
            
            $tracese    .= PHP_EOL.($t["class"]??"").($t["type"]??"").$t["function"]."(".implode(", ", $args).")".PHP_EOL;
            $tracese    .= "\t".($t["file"]??"")." in ".($t["line"]??"");
        }
        
        $response   = $this->response->withStatus(500);
        $response->getBody()->write(
            get_class($e). " ({$e->getFile()} {$e->getLine()})<br>" . PHP_EOL
            . "{$e->getMessage()}<br>" . PHP_EOL
            . "<pre>" . $tracese . "</pre>"
        );
        
        return $response;
    }
}