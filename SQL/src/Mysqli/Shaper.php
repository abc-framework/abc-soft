<?php

namespace Abcsoft\SQL\Mysqli;

use ABC\Core\Exception\AbcError;

/** 
 * Класс Shaper
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */  
class Shaper extends \mysqli_stmt
{
    /**
    * @var ABC\Components\Mysqli\Mysqli
    */  
    protected $mysqli;

    protected $rawSql;    
    protected $debugTypes;
    protected $debugVars;
    
    /**
    * Конструктор
    *
    * @param object $mysqli
    * @param string $sql
    *    
    */     
    public function __construct($mysqli, $sql)
    {
        $this->mysqli = $mysqli;
        $this->rawSql = $sql;
        parent::__construct($mysqli, $sql);
    }
    
    /**
    * Подготавливает параметры для запроса.
    *
    * @param string $types
    * @param mixed &$vars
    *    
    * @return void
    */     
    public function bind_param($types, &...$vars)
    {    
        if (strlen($types) !== count($vars)) {
            AbcError::invalidArgument('Component Mysqli: '. ABC_INVALID_MYSQLI_TYPE);
        }
     
        $this->debugTypes = $types;
        $this->debugVars  = $vars;
        
    }
    
    /**
    * Выполняет запрос.
    *    
    * @return void
    */     
    public function execute()
    { 
        $types  = str_split($this->debugTypes);
        $params = ['types' => $types,
                   'vars'  => $this->debugVars
        ];
        
        $error = $this->mysqli->error;      
        $sql = $this->createSqlString($params);

        if ($this->mysqli->inTransaction()) {
            $this->mysqli->rawQuery("SAVEPOINT `sqldebug`");
            $this->mysqli->query($sql);            
            $this->mysqli->rawQuery("ROLLBACK TO SAVEPOINT `sqldebug`");
        } else {
            $this->mysqli->autocommit(false);  
            $this->mysqli->query($sql);
            $this->mysqli->rollback();
            $this->mysqli->autocommit(true);
        }
   
        if (empty($this->mysqli->error)) {       
            $this->mysqli->rawQuery($sql);
        } 
    }
    
    /**
    * Генерирует результирующий SQL.
    * 
    * @param array $params
    *
    * @return string
    */ 
    protected function createSqlString($params)
    {
        $sql = $this->rawSql;
     
        foreach ($params['types'] as $k => $type) {
            $value = $this->escape($params['vars'][$k], $type);
            $sql = preg_replace('#\?#', $value, $sql, 1);            
        }
        
        return $sql;
    }
    
    /**
    * Обрабатывает параметры для дебаггинга в зависимости от типа.
    *
    * @param string $param
    * @param string $type
    *    
    * @return string
    */     
    protected function escape($param, $type)
    {    
        switch ($type) {
            case 'i' :
                return (int)$param;
            
            case 'd' :
                return "'". (float)$param ."'";
            
            case 's' :
            case 'b' :
                return "'". $this->mysqli->real_escape_string($param) ."'";
            
            default :
                AbcError::invalidArgument('Component Mysqli: '. ABC_NO_MYSQLI_TYPE . $type);    
        }   
    }
}

