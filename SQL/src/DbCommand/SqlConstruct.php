<?php

namespace Abcsoft\SQL\DbCommand;

use Abcsoft\SQL\DbCommand\CommonTrait;


/** 
 * Конструктор запросов Mysql
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class SqlConstruct
{
    use CommonTrait;    

    /**
    * Конструктор
    *
    * @param string config
    */     
    public function __construct($command)
    {
        $this->command  = $command;
        $this->driver   = $command->driver;
        $this->prefix   = $this->driver->prefix;
        $this->rescuer  = $this->driver->rescuer;
    }    
  
    /**
    * Неопределенный метод
    * 
    * @return object
    */     
    public function __call($method, $param)
    {
        throw new \BadMethodCallRxception($this->component 
        . sprintf(ABC_SQL_NO_METHOD, $method, $method));
    } 
 
    /**
    * Метод оператора SELECT
    *
    * @param mixed $columns
    * @param array $options 
    *
    * @return object
    */     
    public function select($columns = null, $options = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 0)) {
            return false;
        }
        
        $this->checkDuble('select');
        $this->checkDuble('select distinct');
        $columns = $this->normaliseColumns($columns);
        $this->sql['select'] = $options .' '. $columns;
        return $this->command;
    }
    
    /**
    * Добавляет параметры к SELECT
    *
    * @param mixed $columns 
    *
    * @return object
    */     
    public function addSelect($columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 0)) {
            return false;
        }
        
        $this->checkSequence('select'); 
        $columns = $this->normaliseColumns($columns);
        $this->sql['select'] .= ', '. $columns;
        return $this->command;
    }
  
    /**
    * Метод оператора SELECT DISTINCT
    *
    * @param mixed $columns
    * @param array $options 
    *
    * @return object
    */     
    public function selectDistinct($columns = null, $options = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 0)) {
            return false;
        }
        
        $this->checkDuble('select');
        $this->checkDuble('select distinct');        
        $options = 'DISTINCT'. $options;
        $this->select($columns, $options);
        return $this->command;
    }
    
    /**
    * Метод оператора FROM
    *
    * @param string|array $tables 
    *
    * @return object
    */     
    public function from($tables = null)
    { 
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'delete');
        $this->checkDuble('from');
        
        if (is_array($tables)) {
            $this->sql['from'] = $this->normaliseFrom($tables);
        } else {
            $this->sql['from'] = $this->normaliseFrom([$tables]);
        }
        
        return $this->command;
    }

    /**
    * JOIN
    *
    * @param string $type
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */  
    public function join($type = null, $table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $type = strtolower($type);
        $this->joinInternal($type, $table, $on);
        return $this->command;
    }      
    
    /**
    * INNER JOIN
    *
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */  
    public function innerJoin($table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $this->joinInternal('inner join', $table, $on);
        return $this->command;
    }  
    
    /**
    * LEFT JOIN
    *
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */ 
    public function leftJoin($table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $this->joinInternal('left join', $table, $on);
        return $this->command;
    }    
    
    /**
    * RIGHT JOIN
    *
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */ 
    public function rightJoin($table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $this->joinInternal('right join', $table, $on);  
        return $this->command;
    }
    
    /**
    * CROSS JOIN
    *
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */ 
    public function crossJoin($table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $this->joinInternal('cross join', $table, $on);
        return $this->command;
    }    
    
    /**
    * NATURAL JOIN
    *
    * @param string $table
    * @param mixed $on 
    *
    * @return object
    */ 
    public function naturalJoin($table = null, $on = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'update');
        $this->joinInternal('natural join', $table, $on);
        return $this->command;
    }
    
    /**
    * Метод оператора WHERE
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */     
    public function where($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkDuble('where');
        
        if (!empty($params) && is_array($params)) {
         
            foreach ($params as $name => $value) {
                
                if (is_object($value)) {
                    $this->params[$name] = $this->createExpressions($value);
                } else {
                    $this->params[$name] = $this->rescuer->escape($value);                
                }
            }
        }         
       
        if (!empty($conditions)) {
            $this->sql['where'] = $this->conditionsInternal($conditions);
        } else {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);        
        } 
        
        return $this->command;
    }

    /**
    * Добавляет условие в существующую часть запроса WHERE с оператором AND
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */ 
    public function andWhere($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
     
        if(!in_array('where', $this->getСlauses())){
            return $this->where($conditions, $params);
        } else {
            $this->createConditions('where', $conditions, $params, 'and');        
        }
     
        return $this->command;
    }
    
    /**
    * Добавляет условие в существующую часть запроса WHERE с оператором OR
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */ 
    public function orWhere($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }

        $this->checkSequence('where');
        $this->createConditions('where', $conditions, $params, 'or');
        return $this->command;
    }

    /**
    * Метод оператора GROUP BY
    *
    * @param array $columns 
    *
    * @return object
    */  
    public function group($columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'from');
        $this->checkDuble('group by');
        $this->sql['group by'] = $this->prepareGroupOrder($columns);
        return $this->command;
    }
    
    /**
    * Добавляет параметры в оператор GROUP BY
    *
    * @param mixed $columns 
    *
    * @return object
    */      
    public function addGroup($columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('group by');
        $group = $this->sql['group by'];
        unset($this->sql['group by']);
        $this->group($columns);
        $this->sql['group by'] = $group .', '. $this->sql['group by'];
        return $this->command;
    }
    

    /**
    * Метод оператора HAVING
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */     
    public function having($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkDuble('having');
     
        if (!empty($params) && is_array($params)) {
         
            foreach ($params as $name => $value) {
                
                if (is_object($value)) {
                    $this->params[$name] = $this->createExpressions($value);
                } else {
                    $this->params[$name] = $this->rescuer->escape($value);                
                }
            }
        }         
       
        if (!empty($conditions)) {
            $this->sql['having'] = $this->conditionsInternal($conditions);
        } else {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);        
        }
        
        return $this->command;
    }
    
    /**
    * Добавляет условие в существующую часть запроса HAVING с оператором AND
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */ 
    public function andHaving($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('having');
        $this->createConditions('having', $conditions, $params, 'and');
        return $this->command;
    }
    
    /**
    * Добавляет условие в существующую часть запроса HAVING с оператором OR
    *
    * @param mixed $conditions
    * @param array $params 
    *
    * @return object
    */ 
    public function orHaving($conditions = null, $params = [])
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('having');
        $this->createConditions('having', $conditions, $params, 'or');
        return $this->command;
    }
    
    /**
    * Метод оператора ORDER BY
    *
    * @param mixed $columns 
    *
    * @return object
    */      
    public function order($columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'from');
        $this->checkDuble('order by');
        $this->sql['order by'] = $this->prepareGroupOrder($columns);
        return $this->command;
    }

    /**
    * Добавляет параметры в оператор ORDER
    *
    * @param mixed $columns 
    *
    * @return object
    */      
    public function addOrder($columns = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'from');
        $order = $this->sql['order by'];
        unset($this->sql['order by']);
        $this->order($columns);
        $this->sql['order by'] = $order .', '. $this->sql['order by'];
        return $this->command;
    }

    /**
    * Метод оператора LIMIT
    *
    * @param int $limit
    * @param int $offset 
    *
    * @return object
    */    
    public function limit($limit, $offset = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('select', 'from', 'update', 'insert', 'delete');
        $this->sql['limit'] = (int)$limit;
        
        if (!empty($offset)) {
            $this->sql['offset'] = (int)$offset;
        } 
        
        return $this->command;
    }
    
    /**
    * Метод оператора OFFSET
    *
    * @param int $offset 
    *
    * @return object
    */     
    public function offset($offset = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        $this->checkSequence('limit');
        $this->sql['offset'] = (int)$offset;
        return $this->command;
    } 
    
    /**
    * Метод оператора UNION
    *
    * @param string|array $sql 
    *
    * @return object
    */  
    public function union($sql = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 1)) {
            return false;
        }
        
        if (is_array($sql)) {
         
            foreach ($sql as $sql) {
                $this->addUnion($sql);           
            }
            
        } else {
            $this->addUnion($sql); 
        }
        
        return $this->command;
    }

    /**
    * Метод оператора INSERT INTO
    *
    * @param string $table
    * @param array $columns 
    *
    * @return object
    */  
    public function insert($table = null, $columns = [])
    { 
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        $this->checkDuble('insert into');
        $table = $this->rescuer->wrapTable($table);
        $this->sql['insert into'] = $table;
        $fields = $this->rescuer->wrapFields(array_keys($columns));
        $this->sql['insert into'] .= "\n    (". implode(', ', $fields) .")";
        $this->values([array_values($columns)]);
        return $this->command;
    }
    
    /**
    * Множественный INSERT
    *
    * @param string $table
    * @param array $columns
    * @param array $values 
    *
    * @return object
    */  
    public function batchInsert($table = null, $columns = null, $values = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 3)) {
            return false;
        }

        $this->checkDuble('insert into');
        $this->sql['insert into'] = $this->rescuer->wrapTable($table);
        $columns = $this->rescuer->wrapFields($columns);
        $this->sql['insert into'] .= "\n    (". implode(', ', $columns) .")";
        $this->values($values);
        return $this->command;
    }
 
    /**
    * Метод оператора UPDATE
    *
    * @param string $table
    * @param array $columns
    * @param array $conditions
    * @param array $params 
    *
    * @return object
    */  
    public function update($table = null, $columns = null, $conditions = null, $params = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 4)) {
            return false;
        }
        
        $this->checkDuble('update');
        $this->sql['update'] = $this->rescuer->wrapTable($table);
        
        $this->set($columns);
        
        if (!empty($conditions)) {        
            $this->where($conditions, $params);
        }
        
        return $this->command;
    }

    /**
    * Метод оператора DELETE
    *
    * @param string $table
    * @param array $conditions
    * @param array $params 
    *
    * @return object
    */  
    public function delete($table = null, $conditions = null, $params = null)
    {
        if (!$this->check(__METHOD__, func_num_args(), 2)) {
            return false;
        }
        
        $this->checkDuble('delete from');        
        $this->sql['delete from'] = $this->rescuer->wrapTable($table);
      
        if (!empty($conditions)) {       
            $this->where($conditions, $params);
        }
        
        return $this->command;
    }

    /**
    * Очищает объект для построения нового запроса
    *
    * @return void
    */       
    public function reset()
    {
        $this->sql = [];
        $this->params = [];
        $this->query = null;
        $this->disable = false;        
    } 
    
    /**
    * Формирует выражения из объекта класса Expression
    *
    * @param object $object
    *
    * @return string
    */ 
    public function createExpressions($object)
    {
        return (new Expression())->createExpression($object, $this->rescuer);
    } 
    
    /**
    * создает условие для WHERE, HAVING и ON
    *
    * @param string $command
    * @param array $conditions
    * @param array  $params
    * @param string $operator
    */ 
    protected function createConditions($command, $conditions, $params = null, $operator = null)
    {
        if (!empty($params) && is_array($params)) {
         
            foreach ($params as $name => $value) {
                $this->params[$name] = $this->rescuer->escape($value);
            }
        } 
     
        if (!empty($conditions)) {
            $this->sql[$command] .= ' '. strtoupper($operator) .' ';
            $this->sql[$command] .= $this->conditionsInternal($conditions, $this->sql[$command], $operator);
        } else {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);        
        }
    }
    
    /**
    * Генерация условий
    *
    * @param array $condition
    */  
    protected function conditionsInternal($conditions)
    {        
        if (!is_array($conditions)) {
            return $this->replace($conditions);
        } elseif (empty($conditions)) {
            throw new \LogicException($this->component . ABC_SQL_NO_CONDITIONS);    
        }
     
        $operator = strtoupper(array_shift($conditions));
     
        if (count($conditions) < 2) {
            throw new \LogicException($this->component . ABC_SQL_COUNT_VALUES);
        }        
        
        if ($operator === 'AND' || $operator === 'OR') { 
            return $this->conditionsAnd($conditions, $operator);   
        } elseif ($operator === 'IN' || $operator === 'NOT IN') { 
            return $this->conditionsIn($conditions, $operator);  
        } elseif ($operator === 'LIKE' || $operator === 'NOT LIKE' || $operator === 'OR LIKE' || $operator === 'OR NOT LIKE') {   
            return $this->conditionsLike($conditions, $operator); 
        } elseif (in_array($operator, $this->operators)) {   
            return $this->conditionsOther($conditions, $operator); 
        }
        
        throw new \LogicException($this->component . ABC_SQL_INVALID_OPERATOR);
    }
   
    /**
    * Генерация условий с операторами группы AND
    *
    * @param array $condition
    * @param string $operator
    */  
    protected function conditionsAnd($conditions, $operator)
    { 
        foreach ($conditions as &$parts) {
            
            if (is_array($parts)) {
                $parts = '('. $this->conditionsInternal($parts) .')';
            }
        }
     
        return $this->replace(implode(' '. $operator .' ', $conditions));
    }
    
    /**
    * Генерация условий с операторами группы IN 
    *
    * @param array $conditions
    * @param string $operator
    *
    * $return string
    */   
    protected function conditionsIn($conditions, $operator)
    { 
        if (!isset($conditions[0], $conditions[1])) {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);
        }
        
        if (is_array($conditions[1])) {
            $field = $this->rescuer->wrapFields($conditions[0]);
            $values = $conditions[1];
            return $this->replace($field .' '. $operator .' ('. implode(', ', $this->rescuer->escape($values)) .')');
        } else {
            throw new \LogicException($this->component . ABC_SQL_INVALID_VALUES);
        }
    }
    
    /**
    * Генерация условий с операторами группы LIKE
    *
    * @param array $conditions
    * @param string $operator
    *
    * $return string
    */  
    protected function conditionsLike($conditions, $operator)
    { 
        if (count($conditions) < 2) {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);
        }        
        
        if( $operator === 'LIKE' || $operator === 'NOT LIKE') {
            $andor = ' AND ';
        } else {
            $andor = ' OR ';
            $operator = ($operator === 'OR LIKE') ? 'LIKE' : 'NOT LIKE';
        }
        
        $expressions = [];            
        $field = $this->rescuer->wrapFields($conditions[0]);
     
        if (is_array($conditions[1])) {
         
            foreach ($conditions[1] as $value) {
                $expressions[] = $field .' '. $operator .' '. $this->rescuer->escape($value);
            }
            
        } elseif (is_string($conditions[1])) {
            $expressions[] =  $field .' '. $operator .' '. $this->rescuer->escape($conditions[1]);
        } else {
            throw new \LogicException($this->component . ABC_SQL_INVALID_VALUES);            
        }
     
        return $this->replace(implode($andor, $expressions));  
    }
   
    /**
    * Генерация условий с другими операторами
    *
    * @param array $conditions
    * @param string $operator
    *
    * $return string
    */  
    protected function conditionsOther($condition, $operator)
    {
        $field = $this->rescuer->wrapFields($condition[0]);
        $value = $condition[1];
        return $this->replace($field .' '. $operator .' '. $value);
    }
    
    /**
    * Замена плэйсхолдеров значениями
    *
    * @param string|object $subject
    *
    * @return string
    */      
    protected function replace($subject)
    {
        if (is_object($subject)) {
            return $this->createExpressions($subject);
        }
     
        foreach ($this->params as &$value) {
         
            if (is_object($value)) {
                $value = $this->createExpressions($value);
            }
        }
     
        return str_replace(array_keys($this->params), array_values($this->params), $subject);
    }
  
    /**
    * Подготавливает простые колонки
    *
    * @param array $columns
    *
    * @return string|array
    */     
    protected function normaliseSimple($columns)
    {
     
        if (is_string($columns)) {
            $exp = preg_split('~\s*,\s*~', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            $columns = $this->rescuer->wrapFields($exp);
        } else {
            throw new \InvalidArgumentException($this->component . ABC_SQL_NO_STRING);
        }    
     
        return  implode(', ', $columns);
    }      
    
    /**
    * Подготавливает колонки
    *
    * @param array $params
    *
    * @return string|array
    */     
    protected function normaliseColumns($params)
    {
        if (empty($params)) {
            $columns[] = '*';
        } 
        
        if (is_string($params)) {
            $exp = preg_split('~\s*,\s*~', trim($params), -1, PREG_SPLIT_NO_EMPTY);
            $columns = $this->rescuer->wrapFields($exp);
        } 
        
        if (is_array($params)) {
         
            foreach ($params as $key => $param) {
             
                if (is_object($param)) {
                    $expression = $this->createExpressions($param);
                    $columns[] = $this->rescuer->addAliasToExpression($expression, $key);
                } else {
                    $columns[] = $this->rescuer->addAliasToField($param, $key);
                }
            }           
        } 
        
        if (is_object($params)) {
            $expression = $this->createExpressions($params);
            $columns[] = $this->rescuer->addAliasToExpression($expression);
        } 
     
        return  implode(', ', $columns);
    }  
    
    /**
    * Подготавливает таблицы для FROM
    *
    * @param string|array $tables
    *
    * @return string
    */     
    protected function normaliseFrom($tables)
    {
        $from = '';
        foreach ($tables as $key => $table) {
         
            if (is_string($table) && false === strpos($table, '(')) {
                $table = $this->rescuer->addAliasToTable($table, $key);
            } elseif (is_object($table)) {
                $class = get_class($this->driver);
                
                if ($table instanceof $class) {
                    $table = '('. $table->createQuery() .') ';
                    $table = $this->rescuer->addAliasToTable($table, $key);
                } elseif($table instanceof DbCommand::$class) {
                    $table = $this->rescuer->addAliasToExpression($table, $key);
                } else {
                    throw new \InvalidArgumentException($this->component . ABC_OTHER_OBJECT);
                }
            }
            
            $from .= $table .', ';
        }
        
        return rtrim($from, ', ');
    }  

    /**
    * Эмуляция JOIN
    *
    * @param string $type
    * @param string $table
    * @param mixed $on
    *
    */ 
    protected function joinInternal($type, $table, $on)
    {
        if (!is_string($type)) {
            throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);
        }
     
        $conditions = '';
       
        if (!empty($on)) {
         
            if (is_string($on) && false === strpos($table, '(')) {
             
                if (preg_match('~^(.*?)(?i:\s+as|)\s+([^ ]+)$~', $table, $matches)) {
                    $table = $this->rescuer->wrapTable($matches[1]) .' '. $this->rescuer->wrapFields($matches[2]);
                }
             
                $conditions = $this->rescuer->wrapOn($on); 
                
            } elseif (is_array($on)) {               
                $conditions = ' ON ('. $this->conditionsInternal($on) .')';
            } elseif (is_object($on)) {
                $conditions = ' ON ('. $this->createExpressions($on) .')';
            } else {
                throw new \LogicException($this->component . ABC_SQL_INVALID_VALUES); 
            }
        } else {
            $table = $this->rescuer->wrapTable($table);
        }
        
        $this->sql[$type][] = $this->replace(' '. $table . $conditions);
    }
    
    /**
    * Метод оператора VALUES
    *
    * @param array $params
    */  
    protected function values($params)
    {
        $group = '';        
     
        foreach ($params as $values) {
            
            foreach ($values as $name => $value) {
                $values[$name] = '';
                
                if (is_object($value)) {
                    $values[$name] .= $this->createExpressions($value);
                } else {
                    $values[$name] = $this->rescuer->escape($value);               
                } 
            }
            
            $group .= "\n    (". implode(', ', $values) ."),"; 
        }
        
        $this->sql['values'] = trim($group, ',');
    }
    
    /**
    * Обработка выражений ORDER BY и GROUP BY
    *
    * @param mixed $values
    *
    * @return string
    */      
    protected function prepareGroupOrder($values)
    {
        if (is_string($values) && false !== strpos($values,'(')) {
            return $values;
        } elseif(is_string($values)) {  
            $exp = preg_split('~\s*,\s*~', trim($values), -1, PREG_SPLIT_NO_EMPTY);
            return $this->normaliseGroup($exp);  
        } elseif (is_array($values)) {
            return $this->normaliseGroup($values); 
        } elseif (is_object($values)) {
            return $this->createExpressions($values);
        } 
        
        throw new \LogicException($this->component . ABC_SQL_INVALID_CONDITIONS);        
    }
    
    /**
    * Нормализация выражений ORDER BY и GROUP BY
    *
    * @param mixed $values
    *
    * @return string
    */      
    protected function normaliseGroup($values)
    {
        foreach ($values as $direction => $column) {
         
            if (is_object($column)) {
                $columns[] = $this->createExpressions($column);
            } elseif (is_string($direction)) {
                $columns[] = $this->rescuer->wrapFields($column) .' '. strtoupper($direction);
            } elseif (false === strpos($column,'(') && !is_string($direction)) {
             
                if (preg_match('~^(.*?)\s+(asc|desc)$~i', $column, $matches)) {
                    $columns[] = $this->rescuer->wrapFields($matches[1]) .' '. strtoupper($matches[2]);
                } else {
                    $columns[] = $this->rescuer->wrapFields($column);
                }
            }
        }
       
        return implode(', ', $columns);
    }

    /**
    * Добавляет часть запроса в UNION
    *
    * @param array $sql
    */  
    protected function addUnion($sql)
    {
        if (isset($this->sql['union']) && is_string($this->sql['union'])) {
            $this->sql['union'] = ["\n    ". $this->sql['union']];
        }
        
        $this->sql['union'][] = "\n    ". $sql;
    } 
    
    /**
    * Метод оператора SET
    *
    * @param array $params
    */  
    protected function set($params)
    {
        $this->isDisable();
        $this->checkSequence('insert into', 'update');
        $set = [];
        
        foreach ($params as $name => $value) {
            $set[$name] = $this->rescuer->wrapFields($name);
           
            if (is_object($value)) { 
                $set[$name] .= ' = '. $this->createExpressions($value);
            } else {
                $set[$name] .= ' = '. $this->rescuer->escape($value);               
            }
        }
        
        $this->sql['set'] = implode(",\n    ", $set); 
    }
    
    /**
    * Возвращает текст запроса
    *
    * @return string
    */       
    public function getSql()
    {
        return $this->createQuery();
    }     
}

