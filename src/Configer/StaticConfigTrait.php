<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Configer;

/**
 * 静的なクラスの設定を保持するためのトレイト
 */
trait StaticConfigTrait{

    /**
     * Get config.
     *
     * @param   string  $key
     * @param   mixed   $default
     *      Value to return if not found.
     *
     * @return  mixed
     */
    public function getConfig(string $key = null, $default = null){
        return static::getConfigData()->get($key) ?? $default;
    }
    
    /**
     * Set config.
     *
     * @param   string  $key
     * @param   mixed   $val
     *
     * @return  void
     */
    public function setConfig(string $key, $val = null): void{
        $data   = static::getConfigData();
        
        if(!$data->has($key)){
            throw new Exception\UndefinedKeyException($key);
        }

        if(!$data->set($key, $val)){
            throw new Exception\InvalidValueException($key);
        }
    }

    /**
     * コンフィグデータを返す
     *
     * @return  ConfigData
     */
    protected static function getConfigData(): ConfigData{
        static $id;
        
        if($id === null){
            do{
                $id = "config_" . bin2hex(random_bytes(2));
            }while(Configure::has("__StaticConfigTrait.{$id}"));
            
            Configure::set("__StaticConfigTrait.{$id}", satic::initConfigData(new ConfigData()));
        }
        
        return Configure::get("__StaticConfigTrait.{$id}");
    }
    
    /**
     * コンフィグデータの設定を初期化する
     * 
     * @param   ConfigData  $data
     * 
     * @return  ConfigData
     */
    protected static function initConfigData(ConfigData $data): ConfigData{
        return $data;
    }
}