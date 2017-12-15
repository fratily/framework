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
namespace Fratily\Session;

use Fratily\Configer\InstanceConfigTrait;
use Fratily\Configer\ConfigData;

/**
 * このセッションクラスは、クッキーを用いてセッションが作成されることを前提としています
 */
class Session implements \Countable, \IteratorAggregate{

    use InstanceConfigTrait;
    
    /**
     * セッション機能が無効であることを示す
     */
    const DISABLED  = 0;
    
    /**
     * セッションが開始されていないことを示す
     */
    const NONE      = 1;
    
    /**
     * セッションが開始されていることを示す
     */
    const ACTIVE    = 2;

    /**
     * セッションが開始しているか
     *
     * @var bool
     */
    protected $started  = false;

    /**
     * Property get (access to session variable)
     *
     * @param   string  $key
     *
     * @return  mixed
     */
    public function __get($key){
        return $this->get($key, null);
    }

    /**
     * Property isset (access to session variable)
     *
     * @param   string  $key
     *
     * @return  bool
     */
    public function __isset($key){
        return $this->has($key);
    }

    /**
     * Property set (access to session variable)
     *
     * @param   string  $key
     * @param   mixed   $value
     *
     * @return  void
     */
    public function __set($key, $value){
        $this->set($key, $value);
    }

    /**
     * Property unset (access to session variable)
     *
     * @param   string  $key
     *
     * @return  void
     */
    public function __unset($key){
        $this->remove($key);
    }

    /**
     * セッションの実行状態を返す
     *
     * @return  int
     *      <dl>
     *          <dt><b>Session::DISABLED</b></dt>
     *              <dd>セッション機能が無効</dd>
     *          <dt><b>Session::NONE</b></dt>
     *              <dd>セッションが開始されていない</dd>
     *          <dt><b>Session::ACTIVE</b></dt>
     *              <dd>セッションが開始されている</dd>
     *      </dl>
     */
    public function status(){
        switch(session_status()){
            case PHP_SESSION_NONE:
                return self::NONE;
                
            case PHP_SESSION_ACTIVE:
                return self::ACTIVE;
                
            case PHP_SESSION_DISABLED:
            default:
                return self::DISABLED;
        }
    }

    /**
     * セッションを開始する
     *
     * @throws  Exception\SessionStartedException
     * @throws  Exception\SessionDisabledException
     * @throws  Exception\SessionOperationException
     *
     * @return  $this
     */
    public function start(){
        if(!$this->started){
            if($this->status() === self::ACTIVE){
                throw new Exception\SessionStartedException();
            }else if($this->status() === self::DISABLED){
                throw new Exception\SessionDisabledException();
            }

            $handler    = $this->getConfig("server.handler");
            
            if(is_object($handler)){
                session_set_save_handler($handler);
            }else if(is_array($handler)){
                session_set_save_handler(
                    $handler[0], $handler[1], $handler[2],
                    $handler[3], $handler[4], $handler[5]
                );
            }
            
            if(!session_start($this->options())){
                throw new Exception\SessionOperationException("Could not start the session {call.in}.");
            }
            
            $this->started  = true;
        }
        
        return $this;
    }

    /**
     * セッションデータの変更を保存してセッションを終了する
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  void
     */
    public function close(){
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }

        session_commit();
        
        $this->started  = false;
    }

    /**
     * セッションデータの変更を破棄してセッションを終了する
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  void
     */
    public function abort(){
        if(!$this->started){
            throw new NotStartedException();
        }

        session_abort();

        $this->started  = false;
    }
    
    /**
     * セッションを完全に破棄する
     *
     * @throws  Exception\SessionNotStartedException
     * @throws  Exception\SessionOperationException
     *
     * @return  $this
     */
    public function destroy(): Session{
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }else{
            $_SESSION   = [];

            if(ini_get("session.use_cookies")){
                $params = session_get_cookie_params();
                
                setcookie(session_name(), "", time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            session_destroy();
            
            $this->started  = false;

        }

        return $this;
    }

    /**
     * 現在のセッションIDを新しく生成した値に変更する
     *
     * @param   bool    $destroy
     *      関連付けられた古いセッションデータを削除するかどうか
     *
     * @throws  Exception\SessionNotStartedException
     * @throws  Exception\SessionOperationException
     *
     * @return  $this
     */
    public function regenerateID(bool $destroy = false): Session{
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }else if(!session_regenerate_id($destroy)){
            throw new Exception\SessionOperationException("Could not update the session id {call.in}.");
        }

        return $this;
    }

    

    /**
     * セッションデータをセッション開始時の値に戻す
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  $this
     */
    public function reset(): Session{
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }

        session_reset();

        return $this;
    }

    /**
     * セッションデータを空にする
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  $this
     */
    public function clear(): Session{
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }

        session_unset();

        return $this;
    }
    
    /**
     * セッションデータの値を取得する
     *
     * @param   string  $key
     *      セッションデータのキー
     * @param   mixed   $default
     *      データが存在しない場合に返される値
     *
     * @return  mixed
     */
    public function get(string $key, $default = null){
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    /**
     * セッションデータが存在するかどうか
     *
     * @param   string  $key
     *      セッションデータのキー
     *
     * @return  bool
     */
    public function has(string $key): bool{
        return array_key_exists($key, $_SESSION);
    }

    /**
     * セッションデータに値をセットする
     *
     * @param   string  $key
     *      セッションデータのキー
     * @param   mixed   $value
     *      セットする値
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  $this
     */
    public function set(string $key, $value){
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }

        $_SESSION[$key] = $value;

        return $this;
    }

    /**
     * セッションデータから値を削除する
     *
     * @param   string  $key
     *      セッションデータのキー
     *
     * @throws  Exception\SessionNotStartedException
     *
     * @return  $this
     */
    public function remove(string $key){
        if(!$this->started){
            throw new Exception\SessionNotStartedException();
        }

        if(array_key_exists($key, $_SESSION)){
            unset($_SESSION[$key]);
        }

        return $this;
    }

    /**
     * Countable
     */
    public function count(){
        return count($_SESSION);
    }
    
    /**
     * IteratorAggregate
     */
    public function getIterator(){
        return $_SESSION;
    }
    
    /**
     * セッション開始時のオプションを返す
     * 
     * @return  mixed[]
     */
    private function options(): array{
        return array_filter(
            array_merge(
                $this->optionsGC(),
                $this->optionsSid(),
                $this->optionsServer(),
                $this->optionsCookie()
            ),
            function($v){
                return $v !== null;
            }
        );
    }

    /**
     * ガーベージコレクションに関連するオプションを返す
     * 
     * @return mixed[]
     */
    private function optionsGC(): array{
        return [
            "gc_maxlifetime"    => $this->getConfig("gc.lifetime"),
            "gc_probability"    => $this->getConfig("gc.probability")[0] ?? null,
            "gc_divisor"        => $this->getConfig("gc.probability")[1] ?? null
        ];
    }

    /**
     * セッションIDに関連するオプションを返す
     * 
     * @return mixed[]
     */
    private function optionsSid(): array{
        return [
            "sid_length"                => $this->getConfig("sid.length"),
            "sid_bits_per_character"    => $this->getConfig("sid.bpc")
        ];
    }

    /**
     * サーバー内設定に関連するオプションを返す
     * 
     * @return mixed[]
     */
    private function optionsServer(): array{
        return [
            "save_path"         => $this->getConfig("server.savepath"),
            "use_strict_mode"   => $this->getConfig("server.strict")
        ];
    }

    /**
     * クッキーに関連するオプションを返す
     * 
     * @return mixed[]
     */
    private function optionsCookie(): array{
        return [
            "name"              => $this->getConfig("cookie.name"),
            "cookie_lifetime"   => $this->getConfig("cookie.lifetime"),
            "cookie_path"       => $this->getConfig("cookie.path"),
            "cookie_domain"     => $this->getConfig("cookie.domain"),
            "cookie_secure"     => $this->getConfig("cookie.secure"),
            "cookie_httponly"   => $this->getConfig("cookie.httponly")
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    protected function initConfigData(ConfigData $data): ConfigData{
        return $data->withValue(
            "gc.lifetime", null,
            function($v){
                return is_int($v) && -1 <= $v;
            }
        )->withValue(
            "gc.probability", null,
            function($v){
                return is_array($v) && is_int($v[0]??null) && is_int($v[1]??null) && 0 <= $v[0] && 1 <= $v[1];
            },
            function($v){
                if(is_string($v) && preg_match("`\A(0|[1-9][0-9]*)/[1-9][0-9]*\z`", $v)){
                    $v  = explode("/", $v);
                }
            }
        )->withValue(
            "sid.length", null,
            function($v){
                return (22 <= $v && $v <= 256);
            }
        )->withValue(
            "sid.bpc", null,
            function($v){
                return (4 <= $v && $v <= 6);
            }
        )->withValue(
            "server.savepath", null,
            function($v){
                return is_dir($v);
            },
            function($v){
                is_dir($v) || mkdir($v, 0777, true);
                return realpath($v);
            }
        )->withValue(
            "server.handler", null,
            function($v){
                return ($v instanceof \SessionHandlerInterface)
                    || ( is_callable($v[0] ?? null) && is_callable($v[1] ?? null)
                        && is_callable($v[2] ?? null) && is_callable($v[3] ?? null)
                        && is_callable($v[4] ?? null) && is_callable($v[5] ?? null)
                );
            }
        )->withValue(
            "server.strict", null,
            function($v){
                return $v === 0 || $v === 1;
            },
            function($v){
                return (bool)$v ? 1 :0;
            }
        )->withValue(
            "cookie.name", null,
            function($v){
                return is_string($v) && (bool)preg_match("`\A[a-zA-Z_][a-zA-Z0-9_]*\z`", $v);
            }
        )->withValue(
            "cookie.lifetime", null,
            function($v){
                return is_int($v) && 0 <= $v;
            }
        )->withValue(
            "cookie.path", null,
            function($v){
                return is_string($v);
            }
        )->withValue(
            "cookie.domain", null,
            function($v){
                return is_string($v);
            }
        )->withValue(
            "cookie.secure", null,
            function($v){
                return $v === 0 || $v === 1;
            },
            function($v){
                return (bool)$v ? 1 :0;
            }
        )->withValue(
            "cookie.httponly", null,
            function($v){
                return $v === 0 || $v === 1;
            },
            function($v){
                return (bool)$v ? 1 :0;
            }
        );
    }
}