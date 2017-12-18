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
 * コンフィグデータの設定や値を保存するクラス
 */
class ConfigData{
    
    /**
     * 登録されたデータ
     * 
     * @var mixed[]
     */
    private $data   = [];
    
    /**
     * バリデーション用コールバック
     * 
     * @var callable[]
     */
    private $valid  = [];
    
    /**
     * 値修正用コールバック
     * 
     * @var callable[]
     */
    private $format = [];
    
    /**
     * 値を追加する
     * 
     * @param   string  $key
     *      コンフィグデータのアクセスキー
     * @param   mixed   $default
     *      コンフィグデータのデフォルト値
     * @param   callable    $valid
     *      バリデーション用コールバック
     * @param   callable    $fomat
     *      修正用コールバック
     * 
     * @return  ConfigData
     *      異なるインスタンスが返される
     */
    public function withValue(string $key, $default = null, callable $valid = null, callable $fomat = null): self{
        if(array_key_exists($key, $this->data)){
            throw new Exception\KeyExistsException($key);
        }
        
        $clone  = clone $this;
        
        $clone->data[$key]   = $default;
        $clone->valid[$key]  = $valid;
        $clone->format[$key]  = $fomat;
        
        return $clone;
    }
    
    /**
     * コンフィグデータを返す
     * 
     * @param   string  $key
     * 
     * @return  mixed
     */
    public function get(string $key){
        return $this->data[$key] ?? null;
    }
    
    /**
     * コンフィグデータが存在するか確認する
     * 
     * @param   string  $key
     * 
     * @return  bool
     */
    public function has(string $key){
        return array_key_exists($key, $this->data);
    }
    
    /**
     * コンフィグデータを追加する
     * 
     * @param   string  $key
     * @param   type    $val
     * 
     * @return  bool
     */
    public function set(string $key, $val){
        if(array_key_exists($key, $this->data)){
            $valid  = $this->valid[$key] ?? null;
            $format = $this->format[$key] ?? null;
            
            if($valid === null || (bool)$valid($val)){
                $this->data[$key]   = $format === null ? $val : $format($val);
                
                return true;
            }
        }
        
        return false;
    }
}