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
 * Configure class.
 * 
 * @todo    abc.*.ghi, abc.{...} 等のアクセス方法にも対応する（検討）
 * @todo    Hashクラスに共通部分をまとめる
 */
class Configure{
    
    private static $config = [
        1   => []
    ];
    
    /**
     * コンフィグデータを取得する
     * 
     * @param   string  $key
     * 
     * @return  mixed|null
     */
    public static function get(string $key){
        $node   = self::$config;
        $keys   = explode(".", $key);
        $last   = array_pop($keys);
        
        foreach($keys as $key){
            if(!isset($node[1][$key])){
                return null;
            }
            
            $node = $node[1][$key];
        }
        
        if(isset($node[1][$last])){
            return $node[1][$last][0];
        }else if($last === "*"){
            $return = [];
            
            foreach($node[1] as $key => $child){
                $return[$key] = $child[0];
            }
            
            return $return;
        }
        
        return null;
    }
    
    /**
     * コンフィグデータが存在するか確認する
     * 
     * @param   string  $key
     * 
     * @return  bool
     */
    public static function has(string $key){
        $node   = self::$config;
        
        foreach(explode(".", $key) as $key){
            if(!isset($node[1][$key])){
                return false;
            }
            
            $node   = $node[1][$key];
        }
        
        return true;
    }
    
    /**
     * コンフィグデータを設定する
     * 
     * @param   string  $key
     * @param   mixed   $val
     * 
     * @return  void
     */
    public static function set(string $key, $val){
        $node   = &self::$config;
        
        foreach(explode(".", $key) as $key){
            if(!isset($node[1][$key])){
                $node[1][$key]  = [0 => null, 1 => []];
            }
            
            $node = &$node[1][$key];
        }
        
        $node[0]    = $val;
    }
    
    /**
     * 
     * 
     * @param   string  $key
     *      
     * 
     * @return  void
     */
//    public static function remove(string $key){
//        $node   = &self::$config;
//        $keys   = explode(".", $key);
//        $last   = array_pop($keys);
//        
//        foreach($keys as $key){
//            if(!isset($node[1][$key])){
//                return;
//            }
//            
//            $node = &$node[1][$key];
//        }
//
//        if(isset($node[1][$last])){
//            unset($node[1][$last]);
//        }else if($last === "*"){
//            $node[1]    = [];
//        }
//    }
}