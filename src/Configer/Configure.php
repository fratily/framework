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
 * @todo    Hashクラスに共通部分をまとめる(たぶん無理)
 */
class Configure{
    
    const ALLOW_OVERWRITE   = 1;
    
    private static $config = [
        1   => []
    ];
    
    /**
     * ノードから指定したキーに一致する子ノードをすべて取得する
     * 
     * @param   mixed[] $node
     * @param   string  $key
     * 
     * @return  array[]|null
     */
    private static function getChildren(array $node, string $key){
        if(!isset($node[1]) || !is_array($node[1])){
            return null;
        }
        
        if($key === "*"){
            if(empty($node[1])){
                return null;
            }

            return array_keys($node[1]);
        }else{
            if(!isset($node[1][$key])){
                return null;
            }
            
            return [$key];
        }
    }
    
    /**
     * コンフィグデータを返す
     * 
     * @param   string  $key
     * 
     * @return  mixed|null
     */
    public static function get(string $key){
        $nodes  = [self::$config];
        $keys   = explode(".", $key);
        
        foreach($keys as $key){
            $newNodes   = [];
            foreach($nodes as $node){
                $children   = self::getChildren($node, $key);
                
                if($children !== null){
                    foreach($children as $child){
                        $newNodes[] = $node[1][$child];
                    }
                }
            }
            
            $nodes  = $newNodes;
        }
        
        if(empty($nodes)){
            return null;
        }else if(count($nodes) === 1){
            return array_pop($nodes)[0] ?? null;
        }else{
            $return = [];
            foreach($nodes as $node){
                if(array_key_exists(0, $node)){
                    $return[]   = $node[0];
                }
            }
            return $return;
        }
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
        
        return array_key_exists(0, $node);
    }
    
    /**
     * コンフィグデータを設定する
     * 
     * @param   string  $key
     * @param   mixed   $val
     * @param   int $option
     * 
     * @throws  Exception\CanNotOverwriteExecption
     * 
     * @return  void
     */
    public static function set(string $key, $val, int $option = null){
        $node   = &self::$config;
        $_key   = $key;
        
        foreach(explode(".", $key) as $key){
            if(!isset($node[1][$key])){
                $node[1][$key]  = [1 => []];
            }
            
            $node = &$node[1][$key];
        }
        
        if(array_key_exists(0, $node) && array_key_exists(2, $node)){
            if(!($node[2] & self::ALLOW_OVERWRITE)){
                throw new Exception\CanNotOverwriteException($_key);
            }
        }
        
        $node[0]    = $val;
        $node[2]    = $option ?? (array_key_exists(2, $node) ? $node[2] : 0);
    }
}