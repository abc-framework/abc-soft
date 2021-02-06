<?php

namespace Abcsoft\SQL\DbCommand;

/** 
 * Транзакции
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class Transaction
{

    protected $driver;
    protected $class;
    protected $config;
    protected $transLevel = 0;    
    /**
    * Конструктор
    *
    * @param object $abc
    * @param object $abc
    */  
    public function __construct($driver)
    {
        $this->driver = $driver;
        $this->debug  = \ABC::getConfig(strtolower(ABC_DBCOMMAND))['debug'];
    }
    
    /**
    * Стартует транзакцию
    *
    * @return void
    */     
    public function beginTransaction($exception = false)
    {
        $this->driver->db->newConnect();
        
        if (true === $exception && true === $this->debug) {
            $this->driver->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
        }
     
        if ($this->transLevel == 0) {
            $this->driver->beginTransaction();
        } else {
            $this->driver->db->exec('SAVEPOINT LEVEL'. $this->transLevel);
        }
     
        $this->transLevel++;
    } 
    
    /**
    * Фиксирует транзакцию
    *
    * @return void
    */     
    public function commit()
    {
        $this->transLevel--;
     
        if ($this->transLevel == 0) {
            $this->driver->commit();
            $this->restoreInstallation();
        } else {
            $this->driver->db->exec('RELEASE SAVEPOINT LEVEL'. $this->transLevel);
        }
    } 
    
    /**
    * Откат транзакции
    *
    * @return void
    */     
    public function rollback()
    {
        $this->transLevel--;
     
        if ($this->transLevel == 0) {
            $this->driver->rollback();
            $this->restoreInstallation();
        } else {
            $this->driver->db->exec('ROLLBACK TO SAVEPOINT LEVEL'. $this->transLevel);
        }
    }
 
    /**
    * Возврат установки дебаггера
    *
    * @return void
    */     
    protected function restoreInstallation()
    {
        if (true === $this->debug) {
            $this->driver->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING); 
        }
    }    
}