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
namespace Fratily\Framework;

/**
 *
 */
abstract class Configure{

    const ALLOW_OVERWRITE   = 1;

    private static $config  = [
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
     * @throws  Exception\ConfigureException
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
                throw Exception\ConfigureException::overwrite($_key);
            }
        }

        $node[0]    = $val;
        $node[2]    = $option ?? (array_key_exists(2, $node) ? $node[2] : 0);
    }

    /**
     * イベントを取得する
     *
     * @param   string  $key
     *
     * @return  EventManagerInterface|null
     *
     * @throws  Exception\ConfigureException
     */
    public static function getEvent(string $key){
        $key    = "app.event.{$key}";
        if(self::has($key)){
            $event  = self::get($key);

            if(!is_callable($event)){
                throw Exception\ConfigureException::unexpectedValue(
                    $key, "callable"
                );
            }

            return $event;
        }

        return null;
    }

    /**
     * イベントを登録する
     *
     * @param   string  $key
     * @param   callable    $event
     *
     * @return  void
     */
    public static function setEvent(string $key, callable $event){
        $key    = "app.event.{$key}";

        self::set($key, $event);
    }

    /**
     * デバッグモードが有効かを返す
     *
     * 引数にboolを指定するとその値で上書きされる
     *
     * @param   bool    $debug
     *
     * @return  bool
     */
    public static function isDebug(bool $debug = null){
        $key    = "app.debug";

        if($debug !== null){
            self::set($key, $debug);
        }else{
            $debug  = self::get($key);

            if(!is_bool($debug)){
                throw Exception\ConfigureException::unexpectedValue($key, "bool");
            }
        }

        return $debug;
    }

    /**
     * ルートディレクトリのパスを返す
     *
     * @return  string
     *
     * @throws  Exception\ConfigureException
     */
    public static function getRootPath(){
        $key    = "app.path.root";

        if(self::has($key)){
            $path   = self::get($key);
        }else{
            $path   = isset($_SERVER["DOCUMENT_ROOT"])
                ? realpath($_SERVER["DOCUMENT_ROOT"] . "/..") : null;
        }

        if(!is_string($path)){
            throw Exception\ConfigureException::unexpectedValue($key, "string");
        }

        return $path;
    }

    /**
     * コンフィグディレクトリのパスを返す
     *
     * @return  string
     *
     * @throws  Exception\ConfigureException
     */
    public static function getConfigPath(){
        $key    = "app.path.config";

        if(self::has($key)){
            $path   = self::get($key);

            if(!is_string($path)){
                throw Exception\ConfigureException::unexpectedValue($key, "string");
            }
        }else{
            $path   = self::getRootPath() . DS . "var" . DS . "config";
        }

        return $path;
    }

    /**
     * キャッシュディレクトリのパスを返す
     *
     * @return  string
     *
     * @throws  Exception\ConfigureException
     */
    public static function getCachePath(){
        $key    = "app.path.cache";

        if(self::has($key)){
            $path   = self::get($key);

            if(!is_string($path)){
                throw Exception\ConfigureException::unexpectedValue($key, "string");
            }
        }else{
            $path   = self::getRootPath() . DS . "var" . DS . "cache";
        }

        return $path;
    }

    /**
     * ログディレクトリのパスを返す
     *
     * @return  string
     *
     * @throws  Exception\ConfigureException
     */
    public static function getLogPath(){
        $key    = "app.path.log";

        if(self::has($key)){
            $path   = self::get($key);

            if(!is_string($path)){
                throw Exception\ConfigureException::unexpectedValue($key, "string");
            }
        }else{
            $path   = self::getRootPath() . DS . "var" . DS . "logs";
        }

        return $path;
    }

    /**
     * テンポラリディレクトリのパスを返す
     *
     * @return  string
     *
     * @throws  Exception\ConfigureException
     */
    public static function getTempPath(){
        $key    = "app.path.temp";

        if(self::has($key)){
            $path   = self::get($key);

            if(!is_string($path)){
                throw Exception\ConfigureException::unexpectedValue($key, "string");
            }
        }else{
            $path   = sys_get_temp_dir();
        }

        return $path;
    }

    /**
     * コントローラークラスのネームスペースを取得する
     *
     * @return  string
     */
    public static function getControllerNamespace(){
        $key    = "app.controller.namespace";

        return self::get($key) ?? "App\\Controller\\";
    }

    /**
     * コントローラークラスのネームスペースを登録する
     *
     * @param   string  $ns
     */
    public static function setControllerNamespace(string $ns){
        $key    = "app.controller.namespace";

        self::set($key, $ns);
    }

    /**
     * エラーコントローラーのクラス名を取得する
     */
    public static function getErrorController(){
        $key    = "app.controller.error";

        return self::get($key) ?? Controller\ErrorController::class;
    }

    public static function setErrorController(string $controller){
        $key    = "app.controller.error";

        if(!class_exists($controller)){
            throw new \InvalidArgumentException();
        }

        $ref    = new \ReflectionClass($controller);

        if(!$ref->implementsInterface(Controller\ErrorControllerInterface::class)){
            throw new \InvalidArgumentException();
        }

        self::set($key, $controller);
    }
}