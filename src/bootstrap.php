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

if(!defined("DS")){
    /**
     * Alias of DIRECTORY_SEPARATOR.
     */
    define("DS", DIRECTORY_SEPARATOR);
}

if(!defined("FRATILY_FW_ROOT")){
    define("FRATILY_FW_ROOT", realpath(__DIR__ . "/.."));
}

if(!function_exists("getComposerClassLoader")){
    /**
     * composerのクラスローダーを取得する
     *
     * @return  \Composer\Autoload\ClassLoader|false
     */
    function getComposerClassLoader(){
        static $loader;

        if($loader === null){
            $loader = false;

            foreach(get_declared_classes() as $class){
                if(substr($class, 0, 24) === "ComposerAutoloaderInited"
                    && method_exists($class, "getLoader")
                ){
                    $tmp    = $class::getLoader();

                    if($tmp instanceof \Composer\Autoload\ClassLoader){
                        $loader = $tmp;
                    }
                }
            }
        }

        return $loader;
    }
}