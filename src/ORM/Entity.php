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
namespace Fratily\ORM;


/**
 * 
 */
class Entity implements EntityInterface{
        
    /**
     * Table name.
     * 
     * @var string|null
     */
    protected static $tableName     = null;
    
    /**
     * Mapper name.
     * 
     * @var string|null
     */
    protected static $mapperName    = null;
    
    /**
     * Field config.
     * 
     * @var Field[]
     */
    private $fields = [];
    
    /**
     * Field data.
     * 
     * @var mixed[]
     */
    private $data   = [];
    
    public static function tableName(){
        return self::$tableName;
    }
    
    public static function mapperName(){
        return self::$mapperName;
    }
    
    public static function entityName(){
        return static::class;
    }
    
    /**
     * 
     * 
     * @return  Field[]
     */
    public static function fields(){
        return [
            Field::create("id", "int")->enablePrimary()->enableAutoincrement(),
            Field::create("created_at", "int")->enableRequired()->setValue(time()),
            Field::create("updated_at", "int")->enableRequired()->setValue(time()),
            Field::create("message", "text")->setDefaultValue("")
        ];
    }
    
    public static function relations(){
        
    }
    
    public function __construct(){
        
    }
    
    protected function initFields(){
        $fields = static::fields();
        
        foreach($fields as $field){
            
            if(!is_a($field, Field::class)){
                throw new \LogicException;
            }
            
            $this->fields[$field->column]   = $field;
            
            if(!array_key_exists($field->column, $this->data)){
                $this->data[$field->column] = $field->options["value"] ?? null;
            }
            if(isset($field->options["value"])){
                $this->data[$field->column] = $field->options["value"];
            }
        }
    }
    
}