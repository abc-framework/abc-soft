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
trait TransducerTrait
{
    private $validTypes  = [  
                'integer'   => true,
                'string'    => true,
                'double'    => true,
                'boolean'   => true,
                'array'     => true,
                'object'    => true,
    ]; 
    
    
    /**  
    * Конвертация имени таблицы.
    *
    * @param string $table
    * 
    * @return void
    */ 
    protected function convertTable($table, $plural = true)
    {
        $inflector = \ABC::sharedService(\ABC::INFLECTOR);
        $table = $this->convertName(basename($table));
        $part = explode('_', $table); 
        $last = array_pop($part);
        $last = $inflector->pluralizeEn($last);
        array_push($part, $last);
        return implode('_', $part);
    }  

    /**  
    * Конвертация имени таблицы или поля.
    *
    * @param string $name
    * 
    * @return void
    */ 
    protected function convertName($name)
    {
        $inflector = \ABC::sharedService(\ABC::INFLECTOR);
        $name = basename(str_replace('\\', '/', $name));
        return $inflector->underscore($name);
    } 
    
    /** 
    * Перевод нижней_змеиной_нотации в ВерблюжьюНотацию
    *    
    * @param string $string
    *    
    * @return string 
    */ 
    protected function camelize($string)
    {
        $words = explode('_', $string);
        $words = array_map(function ($m) {
            return ucfirst($m);
        }, $words);
     
        return implode($words);
    }

    /**  
    * Конвертация для чтения
    * 
    * @param array $values
    *      
    * @return array
    */     
    protected function convertTypeForPHP($values)
    {
        $this->checkCasts();
     
        foreach ($this->casts as $property => $type) {
         
            if (!isset($values[$property])) {
                continue;
            }
            
            switch ($type) {
             
                case 'integer' :
                    $values[$property] = (int)$values[$property];
                    break;
                    
                case 'boolean':
                    $values[$property] = (bool)$values[$property];
                    break;
             
                case 'string' :
                    $values[$property] = (string)$values[$property];
                    break;    
                
                case 'double' :
                    $values[$property] = (float)$values[$property];
                    break;   
             
                case 'array' :
                    $values[$property] = json_decode($values[$property], true);
                 
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \InvalidArgumentException(ABC_JSON_ERROR);
                    }
                    
                    break;
                
                case 'object' :
                    $values[$property] = json_decode($values[$property]);
                 
                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \InvalidArgumentException(ABC_JSON_ERROR);
                    }
                    
                    break;
            } 
        }
     
        return $values;
    } 

    /**  
    * Конвертация для сохранения 
    * 
    * @param array $values
    *      
    * @return array
    */     
    protected function convertTypeForDB($values)
    {
        $this->checkCasts();
     
        foreach ($this->casts as $property => $type) {
         
            if (!isset($values[$property])) {
                continue;
            }
            
            switch ($type) {
             
                case 'integer' :
                    $values[$property] = (int)$values[$property];
                    break;
                    
                case 'boolean':
                    $values[$property] = (bool)$values[$property];
                    $values[$property] = (int)$values[$property];
                    break;
                    
                case 'string' :
                case 'double' :
                    $values[$property] = (string)$values[$property];
                    break;
                    
                case 'array' :
                case 'object' :
                    $values[$property] = json_encode($values[$property]);
                    
                    if (false === $values[$property]) {
                        throw new \InvalidArgumentException(ABC_JSON_ERROR);
                    }
                    
                    break;
            } 
        }
     
        return $values;
    } 
 
    /**  
    * Проверка валидности типов.
    *    
    * @return bool
    */ 
    protected function checkCasts()
    { 
        if (empty($this->casts)) {
            return true;        
        }
        
        foreach ($this->casts as $type) {
         
            if (empty($this->validTypes[$type])) {
                throw new \logicException(sprintf(ABC_INCORRECT_TYPE, $type, $type));
            }
        }
        
        return true;
    } 
}
