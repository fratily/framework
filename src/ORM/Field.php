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


/**
 * 
 * 
 * 
 * @property-read   string  $column
 * @property-read   string  $type
 * @property-read   bool    $primary
 * @property-read   bool    $autoincrement
 * @property-read   bool    $ai
 * @property-read   bool    $required
 * @property-read   bool    $index
 * @property-read   mixed   $default
 * @property-read   mixed[] $options
 */
class Field{
    
    use StdClassTrait{
        __get as ___get;
    }
    
    const TYPE_LIST = [
        //  Integer
        "smallint"  => "smallint",
        "smlint"    => "smallint",
        "integer"   => "integer",
        "int"       => "integer",
        "bigint"    => "bigint",
        //  Decimal Types
        "decimal"   => "decimal",
        "float"     => "float",
        "double"    => "float",
        //  String Types
        "string"    => "string",
        "text"      => "text",
        "guid"      => "guid",
        //  Binary String Types
        "binary"    => "binary",
        "blob"      => "blob",
        //  Boolean
        "boolean"   => "boolean",
        "bool"      => "boolean",
        //  Date and Time Types
        "date"          => "date",
        "datetime"      => "datetime",
        "datetimetz"    => "datetimetz",
        "time"          => "time",
        //  Array Types
        "array"         => "array",
        "simpleArray"   => "simple_array",
        "jsonArray"     => "json_array",
        //  Object Types
        "object"    => "object"
    ];
    
    private $column;
    
    private $type;
    
    private $primary        = false;
    
    private $autoincrement  = false;
    
    private $required       = false;
    
    private $index          = false;
    
    private $default        = null;
    
    private $options        = [];
    
    public static function create(string $column, string $type){
        return new static($column, $type);
    }
    
    public function __construct(string $column, string $type){
        if(!(bool)preg_match("/\A[a-z_][a-z0-9_]\z/", $column)){
            throw new \LogicException;
        }else if(!isset(self::TYPE_LIST[$type])){
            throw new \LogicException;
        }
        
        $this->column   = $column;
        $this->type     = self::TYPE_LIST[$type];
    }
    
    public function __get(string $key){
        switch($key){
            case "column":
            case "type":
            case "primary":
            case "autoincrement":
            case "required":
            case "index":
            case "default":
            case "options":
                return $this->$key;
            case "ai":
                return $this->autoincrement;
        }
        
        return $this->___get($key);
    }
    
    public function setPrimary(bool $enable = true){
        $this->primary  = $enable;
        
        return $this;
    }
    
    public function setAutoincrement(bool $enable = true){
        $this->autoincrement    = $enable;
        
        return $this;
    }
    
    public function setRequired(bool $enable = true){
        $this->required = $enable;
        
        return $this;
    }
    
    public function setIndex(bool $enable = true){
        $this->index    = $enable;
        
        return $this;
    }
    
    public function setDefaultValue($value){
        $this->default  = $value;
        
        return $this;
    }
    
    public function setOption(string $key, $value){
        $this->options[$key]    = $value;
    }
}