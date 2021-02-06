<?php

namespace Abcsoft\SQL\DbCommand;

use Abcsoft\SQL\DbCommand\CommonTrait;
use Abcsoft\SQL\Pdo\PDO;

/** 
 * Конструктор для PDO
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su 
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class PdoDriver
{
    use CommonTrait;

    /**
    * Конструктор
    *
    */     
    public function __construct(DbCommand $command, array $config)
    {
        $dbType   = $config['db_command']['db_type'];        
        $rescuer  = __NAMESPACE__ .'\\'. $dbType . 'Quote';
        $this->db = new PDO($config);         
        $this->prefix  = $this->db->prefix;    
        $this->command = $command;
        
        $this->rescuer = new $rescuer($this->db, $this->prefix);
        $this->defineConstants();
    }
 
    /**
    * Смена СУБД 
    */  
    public function setDb($config)
    { 
        $this->db = new PDO($config); 
        $this->db->newConnect($config);
    }
    
    /**
    * Получает текущий префикс
    *
    * @param array $params
    */     
    public function getPrefix()
    {
        return $this->rescuer->prefix;
    }
    
    /**
    * Устанавливает префикс
    *
    * @param array $params
    */     
    public function setPrefix($prefix)
    {
        $this->rescuer->newPrefix = $prefix;
    }

    /**
    * Удаляет префиксы
    *
    */     
    public function unsetPrefix($prefix = null)
    {
        if(null === $prefix){
            $this->rescuer->prefix = null;
            $this->rescuer->newPrefix = null;
        } else {
            $this->rescuer->newPrefix = str_replace($prefix, '', $this->rescuer->newPrefix);
        }
    } 

    /**
    * Общий запрос
    *
    * @param string $sql
    *
    * @return object
    */     
    public function createCommand($sql)
    {
        $this->disable = true;
        $this->query = $sql; 
        $this->query = $this->rescuer->quoteFields($this->query);
        return $this->command;
    }
    
    /**
    * Создает таблицу БД
    *
    * @param string $table
    * @param array $conditions
    * @param array $keys
    * @param string $options
    * @param boolean $exists
    *
    * @return object
    */  
    public function createTable($table = null, $columns = null, $keys = null, $options = null, $exists = false)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        $fields = [];
        foreach ($columns as $name => $type) {
            $fields[] = "\t". $this->rescuer->wrapFields($name) .' '. $type;
        }
        foreach ($keys as $field => $type) {
            $fields[] = "\t". $type .' ('. $this->rescuer->wrapFields($field) .')';
        }
        
        $this->sql['create table '. ($exists ? 'if not exists ' : '')] = $this->rescuer->wrapTable($table)
                                   ." (\n". implode(",\n", $fields) . "\n)"
                                   ."\n". $options;
        $this->query = $this->createQuery();
        $this->executeInternal();
        
        return $this->command;
    }
    
    /**
    * Переименовывает таблицу БД
    *
    * @param string $table
    * @param string $newname 
    *
    * @return object
    */  
    public function renameTable($table = null, $newname = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        $this->sql['rename table'] = $this->rescuer->wrapTable($table) .' TO '. $this->rescuer->wrapTable($newname);
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
    
    /**
    * Очищает таблицу БД
    *
    * @param string $table 
    *
    * @return object
    */  
    public function truncateTable($table = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->sql['truncate'] = $this->rescuer->wrapTable($table);
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }

    /**
    * Удаляет таблицу БД
    *
    * @param string $table 
    *
    * @return object
    */  
    public function dropTable($table = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->sql['drop table'] = $this->rescuer->wrapTable($table);
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }

    /**
    * Создает таблицу БД
    *
    * @param string $table
    * @param array $columns
    * @param string $after 
    *
    * @return object
    */  
    public function addColumns($table = null, $columns = null, $after = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        $fields = [];
        foreach ($columns as $name => $type) {
            $fields[] = "\t" . $this->rescuer->wrapFields($name) .' '. $type 
                      . ($after ? ' AFTER '. $this->rescuer->wrapFields($after) : ' FIRST');
            $after = $name;
        }
     
        $this->sql['alter table'] = $this->rescuer->wrapTable($table)
                                   ." \nADD ". implode(",\nADD ", $fields) . "\n";
                                  
        $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
    
    /**
    * Переименовывает колонку
    *
    * @param string $table
    * @param string $name
    * @param string $newname 
    *
    * @return object
    */  
    public function renameColumn($table = null, $name = null, $newname = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 3)) {
            return false;
        }
        
        $this->sql['alter table'] = $this->rescuer->wrapTable($table)
                                  .' RENAME COLUMN '. $this->rescuer->wrapFields($name)
                                  .' TO '. $this->rescuer->wrapFields($newname);
        $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
    
    /**
    * Меняет тип колонки
    *
    * @param string $table
    * @param string $name
    * @param string $type 
    *
    * @return object
    */  
    public function afterColumn($table = null, $name = null, $type = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 3)) {
            return false;
        }
        
        $this->sql['alter table'] = $this->rescuer->wrapTable($table)
                                  .' CHANGE '. $this->rescuer->wrapFields($name)
                                  .' '. $this->rescuer->wrapFields($name)
                                  .' '. $type;
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
 
    /**
    * Удаляет колонки
    *
    * @param string $table
    * @param array $columns 
    *
    * @return object
    */  
    public function dropColumns($table = null, $columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        
        $fields = [];
        foreach ($columns as $field) {
            $fields[] = "\t DROP ". $this->rescuer->wrapFields($field);
        }
     
        $this->sql['alter table'] = $this->rescuer->wrapTable($table)
                                   ." \n". implode(",\n", $fields) . "\n";
                                  
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }    

    /**
    * Добавляет первичный ключ
    *
    * @param string $table
    * @param string|array $columns 
    *
    * @return object
    */       
    public function addPrimaryKey($table = null, $columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
     
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        
        $fields = [];
        foreach ($columns as $i => $field) {
            $fields[$i] = $this->rescuer->wrapFields($field);
        }
     
        $this->sql['alter table'] = $this->rescuer->wrapTable($table) .' ADD PRIMARY KEY '
                                  .' ('. implode(', ', $fields) . ')';
                                  
     
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }    

    /**
    * Удаляет первичный ключ
    *
    * @param string $table 
    *
    * @return object
    */    
    public function dropPrimaryKey($table = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
     
        $this->sql['alter table'] = $this->rescuer->wrapTable($table).' DROP PRIMARY KEY ';
         $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
    
    /**
    * Добавляет уникальный индекс
    *
    * @param string $table
    * @param string|array $columns 
    * @param string$name 
    *
    * @return object
    */ 
    public function createUniqueIndex($table = null, $columns = null, $name = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        return $this->createIndex($table, $columns, $name, true);
    } 
    
    /**
    * Добавляет индекс
    *
    * @param string $table
    * @param string|array $columns 
    * @param string $name
    * @param boolean $unique 
    *
    * @return object
    */ 
    public function createIndex($table = null, $columns = null, $name = null, $unique = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s*/', $columns, -1, PREG_SPLIT_NO_EMPTY);
        }
        
        $name = $name ?? $columns[0];
        $this->sql['create '. ($unique ? 'unique ' : '')  .'index']
                                       = $this->rescuer->wrapFields($name) .' ON '
                                       . $this->rescuer->wrapTable($table)
                                       .' (' . implode(', ', $this->rescuer->wrapFields($columns)) . ')';
      
        $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }
    
    /**
    * Удаляет индекс
    *
    * @param string $table
    * @param string $name 
    *
    * @return object
    */
    public function dropIndex($table = null, $name = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
     
        $this->sql['drop index'] = $this->rescuer->wrapFields($name).' ON '
                                 . $this->rescuer->wrapTable($table);
        $this->query = $this->createQuery();
        $this->executeInternal();
        return $this->command;
    }    
    
    
    /**
    * Обертка PDO::execute()
    *
    * @return int
    */     
    public function execute()
    { 
        $sql = $this->getSql();
        $stmt = $this->db->prepare($sql);
     
        $params = $this->command->getParams();        
     
        if (!empty($params)) {
            $this->bindValuesInternal($stmt, $params);
        }
     
        try{
            $stmt->execute();
        } catch(\Excepyion $e) {
            throw new \RuntomeException(ABC_IVALID_QUERY);
        }
        
        $this->rowCnt = $stmt->rowCount();
        $this->lastId = $this->db->lastInsertId();
        $this->reset();
        return $this->rowCnt;
    }
    
    /**
    * Возвращает набор строк. каждая строка - это ассоциативный массив с именами столбцов и значений.
    * если выборка ничего не вернёт, то будет получен пустой массив.
    *
    * @param int $style
    *
    * @return array
    */     
    public function queryAll($style = \PDO::FETCH_ASSOC)
    {
        $this->executeInternal();         
        return $this->stmt->fetchAll($style);
    }  
    
    /**
    * Вернёт одну строку 
    * false, если ничего не будет выбрано
    *
    * @param int $style
    *
    * @return mixed
    */     
    public function queryRow($style = \PDO::FETCH_ASSOC)
    { 
        $this->executeInternal(); 
        while ($row = $this->stmt->fetch($style)) {
            yield $row;
        } 
        
    }
    
    /**
    * Вернёт один столбец 
    * пустой массив, при отсутствии результата
    *
    * @param int $num
    *
    * @return mixed
    */     
    public function queryColumn($num = 0)
    {
        $this->query = $this->getSql();
        $this->executeInternal();
     
        while ($column = $this->stmt->fetchColumn($num)) {
            yield $column;
        }        
    }
    
    /**
    * Вернёт скалярное значение
    * или false, при отсутствии результата
    *
    * @return mixed
    */     
    public function queryScalar()
    {
        if (empty($this->scalar)) {
            $this->query = $this->getSql();
            $this->executeInternal();
            $this->scalar = $this->stmt->fetchColumn();
            $this->stmt->closeCursor();
        }
     
        return $this->scalar;
    }
    
    /**
    * Вернёт результат в виде объекта
    *
    * @param string $className
    * @param array $ctorArgs
    *
    * @return mixed
    */     
    public function queryObject($className = null, $ctorArgs = [])
    {        
        $this->query = $this->getSql();
        $this->executeInternal();    
        return $this->stmt->fetchObject($className, $ctorArgs);
    }

// !!!!!     NO DOCUMENTATION   !!!!!
    /**   
    * Возвращает результат партиями.  
    *  
    * @param int $amount
    * @param int $style
    * 
    * @return array 
    */ 
    public function batch($amount = 1, $style = \PDO::FETCH_ASSOC)
    {
        $cnt  = $this->count();
        $part = ceil($cnt / $amount);
        $offset = 0;
        
        do {
            $batch = $this->getBatch($amount, $offset, $style);
         
            if (empty($batch)) {
                break;         
            }
                
            yield $batch;
            $offset += $amount;
        } while ($part--);        
    }
     
    /**
    * Вернёт количество строк текущего запроса
    *
    * @param string $field
    *
    * @return mixed
    */     
    public function count($field = '*')
    {
        $sql = $this->getSql();
        $sql = preg_replace('~^SELECT(.+?)FROM~is', 
                                    'SELECT COUNT('. $field .') FROM', 
                                    $sql); 
     
        if (empty($this->count)) {
            $stmt = $this->executeCount($sql);
            $this->count = $stmt->fetchColumn();
            $stmt->closeCursor();
        }
     
        return $this->count;
    }
    
    /**
    * Старт транзакции
    *
    * @return void
    */     
    public function beginTransaction()
    {
        $this->db->beginTransaction();
    }
    
    /**
    * COMMIT
    *
    * @return void
    */     
    public function commit()
    {
        $this->db->commit();
    }
    
    /**
    * ROLLBACK
    *
    * @return void
    */     
    public function rollback()
    {
        $this->db->rollback();
    }
    
    /**
    * Возвращает текущий текст SQL 
    *
    * @return string
    */     
    public function getSql()
    {
        if (empty($this->query) && !empty($this->construct)) {
            return $this->construct->getSql();
        }
        $query = $this->query;
        $this->query = null;
        return $query; 
    } 
    
    /**
    * Очищает объект для построения нового запроса
    *
    * @return void
    */       
    public function reset()
    {
        if (!empty($this->construct)) {
            $this->construct->reset(); 
        }
     
        $this->sql = $this->query  = null;       
        $this->disable = false;
        $this->close();      
     
        if (!empty($this->stmt)) { 
            $this->execute = false;
            $this->stmt = null;
        }
    }
    
    /**
    * ID последней вставленной строки
    *  
    * @param string $table
    * @param array $columns
    * @param array $values
    */  
    public function rowCount()
    { 
        return $this->rowCnt; 
    }
    
    /**
    * ID последней вставленной строки
    *  
    * @param string $table
    * @param array $columns
    * @param array $values
    */  
    public function lastInsertId()
    { 
        return $this->lastId; 
    }
    
    /**
    * Тестирует запрос
    *
    * @return object
    */     
    public function test()
    {
        $this->db->test();
        return $this->command;
    }
    
    /**
    * Освобождает ресурсы, выделенные для выполнения текущего запроса
    * 
    * @return void
    */
    public function close()
    {
        if (!empty($this->stmt)) {
            $this->stmt->closeCursor();
        }
    }
    
    /**
    * Установка констант
    *
    * @return void
    */     
    public function defineConstants()
    {
        defined('ABC_DBCOMMAND') or define('ABC_DBCOMMAND', 'PDO');
        defined('ABC_PARAM_INT') or define('ABC_PARAM_INT', \PDO::PARAM_INT);
        defined('ABC_PARAM_BOOL') or define('ABC_PARAM_BOOL', \PDO::PARAM_BOOL);
        defined('ABC_PARAM_NULL') or define('ABC_PARAM_NULL', \PDO::PARAM_NULL);
        defined('ABC_PARAM_STR') or define('ABC_PARAM_STR', \PDO::PARAM_STR);    
        defined('ABC_PARAM_LOB') or define('ABC_PARAM_LOB', \PDO::PARAM_LOB);
        defined('ABC_PARAM_INPUT_OUTPUT') or define('ABC_PARAM_INPUT_OUTPUT', \PDO::PARAM_INPUT_OUTPUT);  
        defined('ABC_FETCH_ASSOC') or define('ABC_FETCH_ASSOC', \PDO::FETCH_ASSOC);
        defined('ABC_FETCH_NUM') or define('ABC_FETCH_NUM', \PDO::FETCH_NUM);
    }
    
     /**   
    *  
    *  
    * @param int $amount
    * @param int $style
    * 
    * @return array 
    */ 
    protected function getBatch($amount, $offset, $style = \PDO::FETCH_ASSOC)
    {
        $this->construct->query = null;
        $this->construct->limit($amount);
        $this->construct->offset($offset);
        $batch = $this->queryAll($style);
        $this->execute = false;
        $this->disable = false; 
        $this->close();
        return $batch;
    }  
    
    /**
    * Выполняет внутренние SELECT-запросы
    *
    * @return void
    */     
    protected function executeInternal()
    {       
        if (false === $this->execute) {
         
            $sql = $this->getSql(); 
            $this->stmt = $this->db->prepare($sql);
            $values = $this->command->getParams();        
            
            if (!empty($values)) {
                $this->bindValuesInternal($this->stmt, $values);
            } 
         
            $this->stmt->execute();
        }
     
//        $this->disable = true; 
    }
    
    /**
    * Выполняет запрос для count()
    *
    * @param string $sql
    *
    * @return object
    */     
    protected function executeCount($sql)
    {
        $stmt = $this->db->prepare($sql);
        
        $values = $this->command->getParams();        
        
        if (!empty($values)) {
            $this->bindValuesInternal($stmt, $values);
        } 
        
        $stmt->execute();
        return $stmt;
    } 

    /**
    * Обертка PDO::bindValue() для массива
    *
    * @param object $stmt
    * @param array $params
    *
    * @return void
    */     
    protected function bindValuesInternal($stmt, $params)
    {
        foreach ($params as $name => $param) {
         
            if (is_array($param)) {
                $value = $param['value'];
                $type  = $param['type'];
            } else {
                $value = $param;
                $type  = \PDO::PARAM_STR;
            }
            
            if (is_object($value)) {
                $value = (new Expression())->createExpression($value, $this->rescuer);
                $type  = null; 
            }
            
            $stmt->bindValue($name, $value, $type);
        }
    }    

}