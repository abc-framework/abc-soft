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
trait OverridingTrait   
{ 
    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */ 
    public function queryRow($dataType = ABC_FETCH_ASSOC) 
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->queryRow($dataType);
    } 
    
    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */  
    public function queryAll($dataType = ABC_FETCH_ASSOC)
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->queryAll($dataType);
    } 
    
    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */ 
    public function queryColumn($num = 0)
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->queryColumn($num);
    }
    
    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */ 
    public function queryScalar()
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->queryScalar();
    } 

    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */ 
    public function queryObject($className = null, $ctorArgs = [])
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->queryObject ($className, $ctorArgs);
    }    
    
    /**  
    * Переопределенный метод сервиса DB_COMMAND
    */ 
    public function count($column = '*')
    {
        $this->checkSequence(__METHOD__);
        return $this->dbcommand->count($column);
    } 
    
    /**  
    * Проверка совместимости методов
    */ 
    public function checkSequence($method)
    {
        if ($this->find) {
            $method = '::'. basename($method) .'()';
            throw new \LogicException(sprintf(ABC_FIND_SEQUENCE, $method, 'find', $method, 'find'));
            return false;
        }
    } 
}
