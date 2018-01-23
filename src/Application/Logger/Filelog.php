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
namespace Fratily\Application\Logger;

use Psr\Log\LogLevel;

/**
 * Logging class to output to the specified file.
 */
class Filelog extends BaseLog{
    
    private $dir;
    
    private $name;
    
    private $ext;
    
    private $rotate;
    
    private $count;
    
    /**
     * Constructor
     * 
     * @param   string  $dir
     * @param   string  $name
     * @param   string  $ext
     * @param   int $rotate
     * @param   int $count
     * 
     * @throws  \InvalidArgumentException
     */
    public function __construct(
        string $dir,
        string $name = null,
        string $ext = "log",
        int $rotate = 10485760,
        int $count = 10
    ){
        if(!is_dir($dir)){
            throw new \InvalidArgumentException();
        }else if($name !== null
            && !(bool)preg_match("/\A[0-9a-z-_.]+\z/i", $name)
        ){
            throw new \InvalidArgumentException();
        }else if(!(bool)preg_match("/\A[0-9a-z]*\z/i", $ext)){
            throw new \InvalidArgumentException();
        }else if($rotate < 1024){
            throw new \InvalidArgumentException();
        }else if($count < 0){
            throw new \InvalidArgumentException();
        }
        
        $this->dir      = realpath($dir);
        $this->name     = $name;
        $this->ext      = $ext;
        $this->rotate   = $rotate;
        $this->count    = $count;
    }
    
    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []){
        $path   = $this->getFilePath($level);
        
        if(is_string($path)){
            $output = static::createOutput($level, $message, $context);

            if($this->rotateFile($path)){
                file_put_contents($path, $output, FILE_APPEND);
            }
        }
    }
    
    /**
     * ログファイルのパスを取得する
     * 
     * @param   mixed   $level
     * 
     * @return  string
     */
    protected function getFilePath($level){
        if($this->name === null){
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
        }else{
            $name   = $this->name;
        }
        
        
        return $this->dir . DS . $name
            . (isset($this->ext) ? ".{$this->ext}" : "");
    }
    
    /**
     * ログファイルのローテーションを行う
     * 
     * @param   string  $path
     * 
     * @return  bool
     */
    protected function rotateFile(string $path): bool{
        clearstatcache(true, $path);
        
        if(is_dir($path)){
            return false;
        }else if(is_file($path) && ($this->rotate <= filesize($path))){
            if($this->rotate === 0){
                unlink($path);
            }else{
                rename($path, $path . "." . time());
            }

            if(($files = glob($path . ".*")) !== false){
                for($i = (count($files) - $this->rotate - 1); $i >= 0; --$i){
                    unlink($files[$i]);
                }
            }
        }
        
        return true;
    }
    
    
}