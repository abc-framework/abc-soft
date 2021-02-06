<?php 

namespace Abcsoft\SQL\ActiveRecord; 
 
/** 
 * ActiveRecord 
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2018 
 * @license http://www.wtfpl.net/ 
 */   
class DTO extends \StdClass
{
    public function __construct($model)
    {
        foreach ($model->attributes as $name => $value) {
            $this->{$name} = $value;
        }
    }
    
    /**   
    * JSON
    *  
    * @return string
    */ 
    public function __toString()
    {
        return json_encode($this);
    }
    
    /**   
    * Массив
    *  
    * @return array
    */ 
    public function asArray()
    {
        return (array)$this;
    }    
}
