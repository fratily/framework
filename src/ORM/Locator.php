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
namespace Fratily\ORM;

use Doctrine\DBAL\Connection;

/**
 * 
 */
class Locator{
        
    /**
     * Default connection name.
     */
    const DEFAULT_CONNECTION    = "default";
    
    /**
     *
     * @var Connection[]
     */
    private static $connection  = [];
    
    /**
     *
     * @var Mapper[][]
     */
    private static $mapper  = [];
    
    /**
     * @todo    implement
     */
    public static function createConnection(){
        throw new \LogicException;
    }
    
    /**
     * Return registerd connection.
     * 
     * @param   string  $connectionName
     *      Connection name.
     * 
     * @return  Connection
     */
    public static function getConnection(string $connectionName = self::DEFAULT_CONNECTION): Connection{
        if(!isset(self::$connection[$connectionName])){
            throw new \LogicException;
        }
        
        return self::$connection[$connectionName];
    }
    
    /**
     * Add new db connection.
     * 
     * @param   Connection  $connection
     *      Connection instance.
     * @param   string  $connectionName
     *      Connection name.
     * 
     * @return  void
     */
    public static function addConnection(Connection $connection, string $connectionName = self::DEFAULT_CONNECTION){
        if(isset(self::$connection[$connectionName])){
            throw new \LogicException;
        }
        
        self::$connection[$connectionName]    = $connection;
    }
    
    /**
     * Remove registerd db connection and relational mapper.
     * 
     * @param   string  $connectionName
     *      Connection name.
     * 
     * @return  void
     */
    public static function removeConnection(string $connectionName = self::DEFAULT_CONNECTION){
        if(isset(self::$connection[$connectionName])){
            foreach(self::$mapper as &$mapperList){
                if(array_key_exists($connectionName, $mapperList)){
                    unset($mapperList[$connectionName]);
                }
            }
            
            unset(self::$connection[$connectionName]);
        }
    }
    
    /**
     * Remove registerd all connection and cached mapper.
     */
    public static function clearConnection(){
        self::$mapper       = [];
        self::$connection   = [];
    }
    
    /**
     * Return mapper instance.
     * 
     * @param   string  $entityName
     *      Entity name.
     * @param   string  $connectionName
     *      Connection name for use.
     * 
     * @return  MapperInterface
     */
    public static function mapper(string $entityName = null, string $connectionName = self::DEFAULT_CONNECTION): MapperInterface{
        if(!is_subclass_of($entityName, EntityInterface::class)){
            throw new \LogicException;
        }else if(!isset(self::$connection[$connectionName])){
            throw new \LogicException;
        }
        
        if(!isset(self::$mapper[$entityName][$connectionName])){
            if(!isset(self::$mapper[$entityName])){
                self::$mapper[$entityName]  = [];
            }
            
            $mapperName = $entityName::getMapperName() ?? Mapper::class;
            
            if(!is_subclass_of($mapperName, MapperInterface::class)){
                throw new \LogicException;
            }
            
            self::$mapper[$entityName][$connectionName] = new $mapperName($entityName, self::$connection[$connectionName]);
        }

        return self::$mapper[$entityName][$connectionName];
    }
}