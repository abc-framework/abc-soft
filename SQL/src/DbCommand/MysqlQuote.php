<?php

namespace Abcsoft\SQL\DbCommand;

/** 
 * Кавычки для MYSQL
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class MysqlQuote
{
    public $prefix;
    public $newPrefix;

    protected $db;

    /**
    * @param object $db
    * @param string $prefix
    */  
    public function __construct($db, $prefix)
    {
        $this->db = $db;
        $this->prefix = $prefix;
    }

    
    /**
    * Экранирует имена таблиц и добавляет префиксы
    *
    * @param string $table
    * @param string $prefix
    *
    * @return string 
    */     
    public function wrapTable($table)
    {
        $base = ''; 
        $table = str_replace('{{%', $this->newPrefix, $table); 
        $table = str_replace(['`', '{{', '}}'], '', $table);            
        $p = explode('.', $table);
     
        if (count($p) > 1) {
            $base  = '`'. $p[0] .'`.';
            $table = $p[1];
        } else {
            $table = $p[0];
        }
       
        $exp = preg_split('~\s+~', trim($table), -1, PREG_SPLIT_NO_EMPTY);
        $alias = !empty($exp[1]) ? ' `'. $exp[1] .'`' : '';
     
        return $base .'`'. $this->prefix . $exp[0] .'`'. $alias;
    } 

    /**
    * Экранирует поля
    *
    * @param string|array $fields
    *
    * @return array
    */     
    public function wrapFields($fields)
    {
        if (!is_array($fields)) {
            return $this->quote($fields);
        }
        
        $quoteFields = [];
        foreach ($fields as $field) {
            $quoteFields[] = $this->quote($field);                
        }
     
        return $quoteFields;
    } 
    
    /**
    * Экранирует условие для ON
    *
    * @param string $on
    *
    * @return string
    */     
    public function wrapOn($on)
    {  
        if (false !== strpos($on, '(')) {
            return ' ON '. $on;
        }
     
        if (is_string($on)) {
            $exp = preg_split('~\s*=\s*~', trim($on), -1, PREG_SPLIT_NO_EMPTY);
            
            if (!empty($exp[1])) {
                return ' ON '. $this->quote($exp[0]) .' = '. $this->quote($exp[1]);
            }
            
            return ' '. $on;            
        }
    }
    
    /**
    * Экранирует значения согласно выбранному драйверу
    *
    * @param string|array $values
    *
    * @return array
    */     
    public function escape($values)
    {
        $result = [];
        switch (ABC_DBCOMMAND) {
         
            case 'PDO' :
            
                if (!is_array($values)) {
                 
                    if (false !== strpos($values, '(')){
                        return $values;
                    } 
                    
                    return $this->db->quote($values);
                }
                
                foreach ($values as $value) {
                 
                    if (false !== strpos($value, '(')){
                        $result[] = $value;
                    }
                    
                    $result[] = $this->db->quote($value);                
                }
                
            break;
            
            case 'Mysqli' :
             
                if (!is_array($values)) {
                 
                    if (false !== strpos($values, '(')){
                        return $values;
                    } 
                 
                    return "'". $this->db->escape_string($values) ."'";
                }
                
                foreach ($values as $value) {
                 
                    if (false !== strpos($value, '(')){
                        $result[] = $value;
                    }
                    
                    $result[] = "'". $this->db->escape_string($values) ."'";                
                }
                
            break;
        }
        
        return $result;
    }      
    
    /**
    * Заменяет псевдоэкранирование на косые кавычки
    *
    * @param string $sql
    *
    * @return string
    */     
    public function quoteFields($sql)
    {           
        $sql = str_replace('{{%', '{{'. $this->prefix . $this->newPrefix, $sql);
        return str_replace(['[[', ']]', '{{', '}}'], '`', $sql);                
    } 
    
    /**
    * Добавляет алиас к полю
    *
    * @param string $field
    * @param string $key
    *
    * @return string
    */  
    public function addAliasToField($field, $key = null)
    {
        $alias = $this->createAlias($field, $key);
        return $this->wrapFields($field) . $alias;
    } 
    
    /**
    * Добавляет алиас к таблице
    *
    * @param string $table
    * @param string $key
    *
    * @return string
    */  
    public function addAliasToTable($table, $key = null)
    { 
        $alias = $this->createAlias($table, $key);
        return $this->wrapTable($table) . $alias;
    } 
    
    /**
    * Добавляет алиас к выражению
    *
    * @param string $expression
    * @param string $key
    *
    * @return string
    */  
    public function addAliasToExpression($expression, $key = null)
    {
        return '('. $expression .') '. (!empty($key) ? ' AS `'. $key .'`' : '');
    } 
    
    /**
    * Формирует алиас
    *
    * @param string $string
    * @param string $key
    *
    * @return string
    */  
    protected function createAlias($string, $key = null)
    {   
        if (empty($key)) {
            return null;
        }
     
        if (is_numeric($key)) {
            $alias = null;
           
            if (false === strpos($string, '(')) {
                $exp = preg_split('~\s+~', trim($string), -1, PREG_SPLIT_NO_EMPTY);
              
                if (!empty($exp[2]) && strtoupper($exp[1]) == 'AS') {
                    $alias = ' AS '. $exp[2];
                } elseif (!empty($exp[1])) {
                    $alias = ' '. $exp[1];
                }
            }
            
            return ' '. $this->wrapFields($alias);
        } 
        
        return ' '. $this->wrapFields($key);
    }     
    
    /**
    * Экранирует поля
    *
    * @param string $field
    *
    * @return string
    */     
    protected function quote($field)
    {
        if (false !== strpos($field, '(')){
            return $field;
        } 
        
        $exp = preg_split('~\s+~', trim($field), -1, PREG_SPLIT_NO_EMPTY);  
     
        if (false !== strpos(strtoupper($field), ' AS ')) {
            $field = $exp[0] .'` AS `'. $exp[2];
        } elseif (false !== strpos($field, ' ')) {
            $field = $exp[0] .'` `'. $exp[1];
        }
     
        if (false !== ($pos = strrpos($field, '.'))) {
            $field = substr($field, 0, $pos) .'`.`'. substr($field, $pos + 1);
        }
        
        return '`'. $field .'`';                
    } 
}
