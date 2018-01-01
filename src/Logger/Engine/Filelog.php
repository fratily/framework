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
namespace Fratily\Logger\Engine;

use Fratily\Configer\InstanceConfigTrait;
use Fratily\Configer\ConfigData;
use Fratily\Configer\Configure;
use Psr\Log\LogLevel;

/**
 * Logging class to output to the specified file.
 */
class Filelog extends BaseLog{
    
    use InstanceConfigTrait;
    
    /**
     * Constructor
     * 
     * @param   mixed   $config
     *      Confguration list.
     * 
     * @return  void
     */
    public function __construct(array $config = []){
        foreach($config as $key => $val){
            $this->setConfig($key, $val);
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []){
        $path   = $this->getFilePath($level);
        
        if(is_string($path)){
            $output = static::createOutput($level, $message, $context);

            if($this->rotateFile($path)){
                //  TODO: パーミッション関連のエラー捕捉
                file_put_contents($path, $output, FILE_APPEND);
            }
        }
    }
    
    
    protected function getFilePath($level){
        if(($name = $this->getConfig("file.name")) === null){
            switch($level){
                case LogLevel::EMERGENCY:
                case LogLevel::ALERT:
                case LogLevel::CRITICAL:
                case LogLevel::ERROR:
                case LogLevel::WARNING:
                    $name   = "error";
                    break;
                case LogLevel::NOTICE:
                case LogLevel::INFO:
                case LogLevel::DEBUG:
                    $name   = "debug";
                    break;
                default:
                    $name   = "none";
            }
        }
        
        $dir    = $this->getConfig("file.dir");
        
        if(is_dir($dir) || mkdir($dir, 0775, true)){
            $dir    = realpath($dir);
        }else if(is_dir(Configure::get("app.dir.log"))){
            $dir    = realpath(Configure::get("app.dir.log"));
        }else{
            return null;
        }
        
        return $dir . DS . $name . "." . $this->getConfig("file.ext");
    }
    
    protected function rotateFile(string $path): bool{
        clearstatcache(true, $path);
        $rotate = $this->getConfig("rotate.count", 10);
        
        if(is_dir($path)){
            return false;
        }else if(!is_file($path) || (filesize($path) < $this->getConfig("rotate.size"))){
            return true;
        }else{
            if($rotate === 0){
                unlink($path);
            }else{
                rename($path, $path . "." . time());
            }

            if(($files = glob($path . ".*")) !== false){
                for($i = (count($files) - $rotate - 1); $i >= 0; --$i){
                    unlink($files[$i]);
                }
            }
        }
        
        return true;
    }
    
    protected function initConfigData(ConfigData $data): ConfigData{
        return $data->withValue(
            "file.dir", Configure::get("app.dir.log"),
            function($v){
                return is_string($v) && !is_file($v);
            }
        )->withValue(
            "file.name", null,
            function($v){
                return is_string($v) && (bool)preg_match("`\A[A-Za-z0-9_-]+\z`", $v);
            }
        )->withValue(
            "file.ext", "log",
            function($v){
                return is_string($v) && (bool)preg_match("`\A[A-Za-z0-9]+\z`", $v);
            }
        )->withValue(
            "rotate.size", 10485760,
            function($v){
                return $v === null || is_int($v) && 1024  <= $v;
            }
        )->withValue(
            "rotate.count", 10,
            function($v){
                return is_int($v) && 0 <= $v;
            }
        );
    }
}