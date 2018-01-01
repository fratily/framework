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
namespace Fratily\Session\Engine;

use Fratily\Core\InstanceConfigTrait;
use Fratily\Log\Log;
use Fratily\Utility\Text;
use PDO;
use PDOException;

/**
 * 開発中：このハンドラ使うべからず
 */
class DatabaseSessionHandler implements \SessionHandlerInterface{

    use InstanceConfigTrait;

    const CREATE    = "CREATE TABLE IF NOT EXISTS {table} (name TEXT NOT NULL, id TEXT NOT NULL, modify INTEGER NOT NULL, data TEXT NOT NULL, primary key(name, id));";
    const SELECT    = "SELECT data FROM {table} WHERE name = :name AND id = :id;";
    const COUNT     = "SELECT count(*) FROM {table} WHERE name = :name AND id = :id;";
    const INSERT    = "INSERT INTO {table} VALUES (:name, :id, :modify, :data);";
    const UPDATE    = "UPDATE {table} SET modify = :modify, data = :data WHERE name = :name AND id = :id;";
    const DELETE    = "DELETE FROM {table} WHERE name = :name AND id = :id;";
    const GC        = "DELETE FROM {table} WHERE name = :name AND modify < :time;";

    private $dsn;
    private $user;
    private $passwd;
    private $options;
    
    /**
     * PDOコネクション
     * 
     * @var \PDO
     */
    private $pdo;
    
    /**
     * セッション名
     * 
     * @var string
     */
    private $name;
    
    /**
     * Constructor
     * 
     * @param   string  $dsn
     *      Data source name
     * @param   string  $user   [optional]
     *      Database user name
     * @param   string  $passwd [optional]
     *      Database user password
     * @param   mixed[] $options    [optional]
     *      PDO options
     */
    public function __construct(string $dsn, string $user = null, string $passwd = null, array $options = null){
        $this->dsn      = $dsn;
        $this->user     = $user;
        $this->passwd   = $passwd;
        $this->options  = $options;
    }

    /**
     * セッション開始
     *
     * @param   string  $path
     *      セッションの保存先パス
     * @param   string  $name
     *      セッション名
     *
     * return   bool
     */
    public function open($path, $name){
        $this->name = $name;
        
        try{
            $this->pdo  = new \PDO(
                $this->dsn, $this->user, $this->passwd,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION] + $this->options
            );
            
            //ここまで
            
            $stmt   = $this->pdo->prepare(
                Text::format(self::CREATE, ["table" => $this->getConfig("table", "session")])
            );

            $result = $stmt->execute();
        }catch(PDOException $e){
            Log::critical("Session open error (" . $e->getMessage() . ").");

            return false;
        }

        if($result){
            return true;
        }

        Log::critical("Failed to generate session database.");
        return false;
    }

    /**
     * Close the session.
     *
     * @return  bool
     */
    public function close(){
        try{
            $this->pdo->commit();
            
            $this->pdo  = null;
        }catch(PDOException $e){
            $this->pdo->rollBack();
            return false;
        }

        return true;
    }

    /**
     * Read session data.
     *
     * @param   string  $id
     *      The session ID.
     *
     * @return  string
     */
    public function read($id){
        try{
            $stmt   = $this->pdo->prepare(
                Text::format(self::SELECT, ["table" => $this->getConfig("table", "session")])
            );

            $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
            $stmt->bindValue(":id", $id, PDO::PARAM_STR);

            $result = $stmt->execute();
        }catch(PDOException $e){
            Log::critical("Session read error (" . $e->getMessage() . ").");

            return "";
        }

        if($result){
            if(($data = $stmt->fetchColumn()) !== false){
                return $data;
            }
        }
        
        return "";
    }

    /**
     * Write session data.
     *
     * @param   string  $id
     *      The session ID.
     * @param   string  $data
     *      The session data.
     *
     * @return  bool
     */
    public function write($id, $data){
        try{
            $stmt   = $this->pdo->prepare(
                Text::format(self::COUNT, ["table" => $this->getConfig("table", "session")])
            );

            $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
            $stmt->bindValue(":id", $id, PDO::PARAM_STR);

            if($stmt->execute() && ($cnt = $stmt->fetchColumn()) !== false){
                return ((int)$cnt === 0)
                    ? $this->insert($id, $data)
                    : $this->update($id, $data);
            }
        }catch(PDOException $e){
            Log::critical("Session write error (" . $e->getMessage() . " " . $e->getLine() .  ").");
            return false;
        }

        Log::critical("Failed to write session database.");
        return false;
    }

    private function insert($id, $data){
        $stmt   = $this->pdo->prepare(
            Text::format(self::INSERT, ["table" => $this->getConfig("table", "session")])
        );

        $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_STR);
        $stmt->bindValue(":modify", time(), PDO::PARAM_INT);
        $stmt->bindValue(":data", $data, PDO::PARAM_STR);

        if(!$stmt->execute()){
            Log::critical("Failed to register session data.");
            return false;
        }

        return true;
    }

    private function update($id, $data){
        $stmt   = $this->pdo->prepare(
            Text::format(self::UPDATE, ["table" => $this->getConfig("table", "session")])
        );

        $stmt->bindValue(":modify", time(), PDO::PARAM_INT);
        $stmt->bindValue(":data", $data, PDO::PARAM_STR);
        $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_STR);

        if(!$stmt->execute()){
            Log::critical("Failed to update session data.");
            return false;
        }

        return true;
    }

    /**
     * Destroy a session.
     *
     * @param   string $id
     *      The session ID being destroyed.
     *
     * @return  bool
     */
    public function destroy($id){
        try{
            $stmt   = $this->pdo->prepare(
                Text::format(self::DELETE, ["table" => $this->getConfig("table", "session")])
            );

            $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
            $stmt->bindValue(":id", $id, PDO::PARAM_STR);

            $result = $stmt->execute();
        }catch(PDOException $e){
            Log::critical("Session destroy error (" . $e->getMessage() . ").");

            return false;
        }

        if($result){
            return true;
        }

        Log::critical("Failed to destroy session database.");
        return false;
    }

    /**
     * Cleanup old sessions.
     *
     * @param   int $maxlifetime
     *
     * @return  bool
     */
    public function gc($maxlifetime){
        try{
            $stmt   = $this->pdo->prepare(
                Text::format(self::GC, ["table" => $this->getConfig("table", "session")])
            );

            $stmt->bindValue(":name", $this->name, PDO::PARAM_STR);
            $stmt->bindValue(":time", time() - $maxlifetime, PDO::PARAM_INT);

            $result = $stmt->execute();
        }catch(PDOException $e){
            Log::critical("Session garbage collection error (" . $e->getMessage() . ").");

            return false;
        }

        if($result){
            return true;
        }

        Log::critical("Failed to garbage collection session database.");
        return false;
    }
}