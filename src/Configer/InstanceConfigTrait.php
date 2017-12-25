<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Configer;

/**
 * 動的なクラスの設定を保持するためのトレイト
 */
trait InstanceConfigTrait{

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
        return $this->getConfigData()->get($key) ?? $default;
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
        $data   = $this->getConfigData();
        
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
    public function getConfigData(): ConfigData{
        static $id;
        
        if($id === null){
            do{
                $id = "config_" . bin2hex(random_bytes(2));
            }while(Configure::has("__InstanceConfigTrait.{$id}"));
            
            Configure::set("__InstanceConfigTrait.{$id}", $this->initConfigData(new ConfigData()));
        }
        
        return Configure::get("__InstanceConfigTrait.{$id}");
    }
    
    /**
     * コンフィグデータの設定を初期化する
     * 
     * @param   ConfigData  $data
     * 
     * @return  ConfigData
     */
    protected function initConfigData(ConfigData $data): ConfigData{
        return $data;
    }
}