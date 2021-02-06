<?php

namespace Abcsoft\SQL\Mysqli;


use ABC\ABC;
use ABC\Core\Exception\AbcError;

/** 
 * Класс Mysqli
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */  
class Mysqli extends \mysqli implements MysqlInterface
{
    public $prefix;
    public $test = false;
    public $sqlError;  
   
    protected $debugger;

    /**
    * Конструктор
    *
    * @param object $abc
    *
    */     
    public function __construct()
    {
        $config = \ABC::getConfig('mysqli');
     
        if (!empty($config)) {
            $this->newConnect($config);  
        } else {
            AbcError::invalidArgument(' Component Mysqli: '. ABC_WRONG_CONNECTION);
        }
    }
    
    /**
    * Коннектор
    *
    * @return void
    */     
    public function newConnect($config = [])
    {
        if (!$this->checkConfig($config)) {
            return false;
        }
     
        parent::__construct($config['host'], $config['user'], $config['pass'], $config['base']); 
        
        $this->debugger = !empty($config['debug']) ? $this->abc->newService('SqlDebug') : null;    
        $this->prefix = @$config['prefix']; 
        
        if ($this->connect_error) {
            AbcError::logic(' Component Mysqli: '. $this->connect_error); 
            return false;
        }
        
        $this->set_charset("utf8");
    }
    
    /**
    * Включает тестовый режим
    *
    * @return object
    */     
    public function test()
    {
       $this->test = true;
       return $this;
    }
    
    /**
    * Обертка для query()
    *
    * @param string $sql
    * @param int $resultMode
    *
    * @return object
    */     
    public function query($sql, $resultMode = null)
    {
        if (!empty($this->debugger)) {
         
            if (false === $this->checkEngine($sql)) {
                return false;
            }
        }

        $result = parent::query($sql, $resultMode);
        $this->sqlError = $this->error; 

        if (!empty($this->debugger)) {
            $trace = \ABC::getFromStorage('trace');
            $trace['sql_count'] += 1;
            \ABC::addToStorage('trace', $trace);
            
            $this->debugger->error = $this->sqlError;
            $this->debugger->trace = debug_backtrace();
            $this->debugger->db = $this;
            $this->debugger->component = 'Mysqli';
            $this->debugger->run($sql, $result);        
        } elseif (empty($this->debugger) && $this->test) {
            AbcError::badFunctionCallError('Component Mysqli: '. ABC_NO_SQL_DEBUGGER);
        }
        
        return $result;
    } 

    /**
    * Обертка для prepare()
    *
    * @param string $sql
    *    
    * @return void
    */     
    public function prepare($sql)
    {   
        if (!empty($this->debugger)) {
            return new Shaper($this, $sql);        
        }
        
        return parent::prepare($sql);
    }
    
    /**
    * Проверка транзакции.
    *
    * @return bool
    */ 
    public function inTransaction()
    {
        $point = md5(time());
        $this->rawQuery("SAVEPOINT `". $point ."`");
        return (bool)$this->rawQuery("RELEASE SAVEPOINT `". $point ."`");
    } 

    /**
    * Чистый запрос для дебаггера
    *
    * @param string $sql
    *    
    * @return void
    */     
    public function rawQuery($sql)
    {
        return parent::query($sql);
    }
    
    /**
    * Пытается получить имя таблицы и проверить её транзакционность.
    *
    * @param string $sql
    *    
    * @return bool
    */     
    public function checkEngine($sql)
    {    
        $sql = str_replace('`', '', trim($sql)) .' ';
        $sql = str_ireplace(['IGNORE', 'LOW_PRIORITY', 'DELAYED', 'INTO', 'FROM', 'QUICK'], ' ', $sql);
        preg_match('~^[INSERT|UPDATE|DELETE]+?[\s]+(.+?)[\s]+.*~i', $sql, $match);
      
        if (empty($match[1])) {
            return true;
        }
        
        $table  = preg_replace('~.*?\.~', '', $match[1]);
        $stmt   = $this->rawQuery("SHOW TABLES LIKE '". $table ."'");  
        $result = $stmt->fetch_row()[0];
        
        if (empty($result)) {
            AbcError::logic(' Component Mysqli: '. sprintf(ABC_SQL_NO_TABLE, $table, $table));
            return false;
        }
    
        $stmt  = $this->rawQuery("SELECT ENGINE 
                                     FROM INFORMATION_SCHEMA.TABLES
                                     WHERE TABLE_NAME =  ". $this->quote($table) 
                                  );
     
        $result = $stmt->fetch_row()[0];
        
        if ($result !== 'InnoDB') {
            AbcError::logic(' Component Mysqli: '. ABC_NO_SUPPORT);
            return false;
        }
        
        return true;
    }
    
    /**
    * Эскейпинг
    *
    * @param string $text
    *    
    * @return string
    */     
    protected function quote($text)
    {
        return "'". $this->real_escape_string($text) ."'";
    }  
    
    /**
    * Проверка корректности настроек
    *
    * @param string $config
    *    
    * @return bool
    */     
    protected function checkConfig($config = [])
    {
        extract($config);
        
        if (!isset($host, $user, $pass, $base)) {
            AbcError::invalidArgument(' Component Mysqli: '. ABC_WRONG_CONNECTION);
            return false;
        }
        
        return true;
    }  
}
