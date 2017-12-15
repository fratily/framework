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
namespace Fratily\Session\Engine;

use Fratily\Core\InstanceConfigTrait;
use Fratily\Log\Log;
use Fratily\Utility\Text;
use Fratily\Exception\FileExistsException;
use Fratily\Exception\DirectoryNotExistsException;
use SessionHandlerInterface;
use SplFileObject;

/**
 * 開発中：このハンドラ使うべからず
 */
class FileSessionHandler implements SessionHandlerInterface{

    use InstanceConfigTrait;

    protected $defaultConfig    = [
        "save"  => [
            "path"  => APP_TEMP . DS . "session"
        ],
        "file"  => [
            "prefix"    => "",
            "ext"       => ""
        ]
    ];

    /**
     * Session save path
     * 
     * @var string
     */
    private $savepath;
    
    /**
     * Session name.
     *
     * @var string
     */
    private $name;

    /**
     * File pointer.
     *
     * @var resource
     */
    private $fp;

    /**
     * Initialize session.
     *
     * @param   string  $path
     *      The path where to store/retrieve the session.
     * @param   string  $name
     *      The session name.
     *
     * return   bool
     */
    public function open($path, $name){
        $this->savepath = $path;
        $this->name     = $name;
        
        $dir    = $this->getConfig("save.path");
        $prefix = $this->getConfig("file.prefix");
        $ext    = $this->getConfig("file.ext");
        
        if(is_file($dir)){
            throw new FileExistsException($dir);
        }else if(!is_dir($path) && !mkdir($dir, 0777, true)){
            throw new DirectoryNotExistsException($dir);
        }
        
        $path   = realpath($dir) . DS . $prefix . 
        
        $this->fp   = fopen($name, $path);
        
        try{
            $this->pdo  = new PDO(
                $this->getConfig("dsn"),
                $this->getConfig("username"),
                $this->getConfig("passwd"),
                self::OPTION + $this->getConfig("options", [])
            );

            $this->pdo->beginTransaction();
            
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