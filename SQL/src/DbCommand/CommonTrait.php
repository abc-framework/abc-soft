<?php

namespace Abcsoft\SQL\DbCommand; 


/** 
 * Конструктор запросов Mysql
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
trait CommonTrait
{
    public $db;
    public $construct;
    public $rescuer;
    public $prefix;
    public $query;
    public $disable = false;

    protected $component = ' Component DbCommand: '; 
    protected $command;   
    protected $stmt;
    protected $driver;
    protected $scalar;
    protected $count;
    protected $rowCnt;
    protected $lastId;
    protected $execute = false;    
    protected $operators = ['=', '!=', '>', '<', '>=', '<=', '<>', '<=>', '!<', '!>'];
    protected $params = [];
    protected $sql    = []; 
    
    
    /**
    * Формирует запрос
    *
    * @return string
    */       
    protected function createQuery()
    {
        if (empty($this->query)) {
            $this->sql = array_change_key_case($this->sql, CASE_UPPER);
           
            foreach ($this->sql as $operand => $value) {
                
                if (is_array($value)) {
                 
                    foreach ($value as $v) {
                        $this->query .= "\n    ". $operand .' '. $v .' '; 
                    }
                    
                } else {
                    $this->query .= "\n    ". $operand .' '. $value .' ';
                }
            }
        }
     
        return ltrim($this->query, "\n ");
    } 
    
    /**
    * Метод проверки
    *
    * @param string $method
    * @param int $argsCnt
    * @param int $min
    */    
    protected function check($method, $argsCnt = 0, $min = 0)
    {  
        if (true === $this->isDisable()) {
            return false;
        }
     
        if ($argsCnt < $min) {
            throw new  \LogicException($this->component . sprintf(ABC_SQL_NOT_ARGUMENTS, basename($method))); 
        }
        
        return true;
    }
    
    /**
    * Проверка на блокировку
    *
    * @return void
    */       
    protected function isDisable()
    {
        if (true === $this->disable) {
            throw new  \LogicException($this->component . ABC_SQL_DISABLE);
        } 
    }

    /**
    * Метод проверки повтора оператора
    *
    * @param array $operand
    */      
    protected function checkDuble($operand)
    {
        if (isset($this->sql[strtolower($operand)])) {
            throw new  \LogicException($this->component . sprintf(ABC_SQL_DUBLE, $operand, $operand));
        }
    }
  
    /**
    * Метод проверки последовательности операторов
    *
    */    
    protected function checkSequence()
    {
        if (empty($this->sql)) {
            return true;
        }
        
        $operands = func_get_args();
        $clauses = $this->getСlauses();
     
        foreach ($operands as $operand) {
            $exp = preg_split('~\s+~', $operand, -1, PREG_SPLIT_NO_EMPTY);
            if (in_array($exp[0], $clauses)) {
                return true;
            }
        }
        
        throw new  \LogicException($this->component . ABC_SQL_SEQUENCE);
    }
    
    /**
    * Метод 
    *
    */    
    protected function getСlauses()
    {
        $check = array_change_key_case($this->sql, CASE_LOWER);
        $keys = array_keys($check);
        
        foreach ($keys as &$key) {
            $key = preg_replace('~(.+?)\s+.*~', '$1', $key);
        }
     
        return $keys;
    }    
    
}

