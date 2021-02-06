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
class Model 
{ 
    use OverridingTrait; 
    use StaticTrait;
    use TransducerTrait;
    use RelationsTrait;
    
    private static $command;
    private static $withStorage = [];
    
    public $attributes = [];   
    protected $table;
    protected $primary = 'id';

    private $ids = [];
    private $allowed    = [];
    private $forbidden  = [];
    private $asArray  = false;
    private $asObject = false;
    private $find     = false;
    private $relations = [];
    private $casts  = [];
    private $ignore = [];    
 
    private function __construct(array $config, $DBCommand = null, $lang = 'Ru')
    { 
        $language = '\Abcsoft\SQL\Language\\'. $lang;
        $language::set();
        $this->dbcommand = $DBCommand ?? new Dbcommand($config);
        $this->fill([$this->primary => 0]);        
        $this->setTypes([$this->primary => 'integer']);
        $this->settings();
        $this->setTableName();        
    }
    
    /**   
    * __call 
    */ 
    public function __call($method, array $params)
    {
        call_user_func_array([$this->dbcommand, $method], $params);
        return $this;  
    } 
    
    /**   
    * __get 
    */ 
    public function __get($property)
    {
        $name = $this->camelize($this->getBaseName($property));
        $method = 'get'. $name;
      
        if (isset($this->relations[$name])) { 
            return $this->relations[$name];
        }
        
        if (method_exists($this, $method)) {
            $relations = $this->{$method}();
            $this->relations[$name] = $relations;
            return $relations;
        }
        
        $method .= 'Attr';
        
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        
        $name = lcfirst($name);
        
        if (!isset($this->attributes[$property])) {
            if (defined('static::EXCEPTION') && true === static::EXCEPTION) {
                throw new \InvalidArgumentException(sprintf(ABC_ATTRIBUTE_NOT_FOUND, $name, $name));
            }
            
            return null;
        }
        
        return $this->attributes[$property];  
    } 
    
    /**   
    * __set 
    */ 
    public function __set($name, $value)
    {
        $method = 'set'. $this->camelize($name) .'Attr';
        
        if (method_exists($this, $method)) {
            $value = $this->{$method}($value);
        }
        
        $this->attributes[$name] = $value;  
    }
    
    /**   
    * __isset 
    */ 
    public function __isset($name)
    {
        return (bool)$this->__get($name); 
    }    
    
    /**   
    * __toString 
    */ 
    public function __toString()
    {
        return json_encode($this->attributes);
    }
    
    /**   
    * В виде DTO 
    */  
    public function asDTO()
    {
        return new DTO($this);
    }
    
    /**   
    * Сохранение модели в БД. 
    * 
    * @param array $attributes
    *    
    * @return void
    */ 
    public function save(array $attributes = []) 
    {
        if (empty($attributes)) {
            $attributes = $this->prepareAttributes($attributes);        
        } else {  
            $attributes = $this->checkAttributes($attributes);
            $attributes = array_merge($attributes, [$this->primary => 0]); 
            $this->fill($attributes);            
        }
     
        $attributes = $this->convertTypeForDB($attributes);
        $id = $this->{$this->primary};
        
        if (empty($id)) {
            $this->saveModel($attributes); 
        } else {
            $this->updateModel($attributes);
        }
    }

    /**  
    * Удаление записи 
    *    
    * @return void
    */ 
    public function delete()
    {
        if (empty($this->{$this->primary})) {
           return false;
        }
        
        $numRows = $this->dbcommand->delete(
                $this->table, 
                $this->primary .'=:'. $this->primary, 
                [':'. $this->primary => $this->{$this->primary}]
                                  )->execute();           
        
        if ($numRows == 0) {
            if (defined('static::EXCEPTION') && true === static::EXCEPTION) {
                throw new \Exception(ABC_MODEL_NO_SAVE);
            }
        }
            
        $this->dbcommand->reset();
        $this->clear();
        return true;
    }  
    
    /**   
    * Возвращает одну готовую модель. 
    *    
    * @return object 
    */ 
    public function one()
    {
        $this->dbcommand->limit(1);
        $values = $this->dbcommand->queryRow();
        $this->dbcommand->reset();
        $values = $this->convertTypeForPHP($values);
        $this->fill($values); 
        $this->ids[] = $values[$this->primary];
        return $this->selectResult($this);
    }
    
    /**   
    * Возвращает семейство моделей.  
    * 
    * @return array 
    */ 
    public function all()
    {
        $rows = $this->dbcommand->queryAll();
        $this->dbcommand->reset();
        
        foreach ($rows as $values) {
            $this->ids[] = $values[$this->primary];        
        }
     
        return $this->createListModels($rows); 
    }
    
    /**   
    * Возвращает семейство моделей партиями.  
    *  
    * @param int $amount
    * 
    * @return array 
    */ 
    public function batch($amount = 1)
    {
        $cnt  = $this->dbcommand->count($this->primary);
        $part = ceil($cnt / $amount);
        $offset = 0;
        
        do {
            $rows = $this->dbcommand->getBatch($amount, $offset);
            $batch = $this->createListModels($rows);
            
            if (empty($batch)) {
                break;         
            }
            
            yield $batch;
            $offset += $amount;
        } while ($part--);        
    }
    
    /**   
    * Возвращает семейство моделей по одной.  
    *  
    * @param int $amount
    * 
    * @return array 
    */ 
    public function each($amount = 1)
    {  
        $this->dbcommand->limit($amount);
        $rows = $this->dbcommand->queryAll();
        $this->dbcommand->reset();
        $result = $this->createListModels($rows);
        
        foreach ($result as $model) {
            yield $model;
        }         
    }
    
    /**  
    * Выборка с помощью конструктора запросов
    *     
    * @param mixed $columns
    *
    * @return array  
    */ 
    public function command($columns = null)
    {
        $this->dbcommand->select($columns)->from($this->table);
        return $this;
    } 
    
    /**   
    * Устанавливает тип результата (объект). 
    *  
    * @param bool $value
    *     
    * @return object
    */ 
    public function asObject($value = true)
    { 
        $this->asObject = $value;
        return $this;
    }

    /**   
    * Устанавливает тип результата (массив). 
    *  
    * @param bool $value
    *    
    * @return object
    */ 
    public function asArray($value = true)
    {
        $this->asArray = $value;
        return $this;
    }
    
    /**  
    * Возвращает семейство моделей в виде массива.
    * 
    * @param array $rows 
    *      
    * @return object
    */  
    public function createListModels($rows, $model = null)
    {
        $model = (null === $model) ? $this : $model;
        $models = [];
        foreach ($rows as $values) {
            $clone = clone $model;        
            $values = $clone->convertTypeForPHP($values);
            $clone->fill($values);
            $models[$values[$this->primary]] = $this->selectResult($clone);
        }
        
        return $models;
    } 
    
    /**  
    * Переопределяемый метод для настройки.
    */ 
    protected function settings(){}
    
    
    /**   
    * Установка типов атрибутов
    *  
    * @param array $types
    *    
    * @return void
    */ 
    protected function setTypes($types)
    {
        $this->casts = array_merge($this->casts, $types);
    }
 
    /**  
    * Установка имени таблицы.
    *
    * @param string $table
    *
    * @return void
    */ 
    protected function setTableName($table = null)
    {
        if (empty($table) && empty($this->table)) {
            $this->table = $this->convertTable(static::class); 
        } elseif (!empty($table)) {
            $this->table = $table;
        }
    }

    /**  
    * Установка игнорируемых свойств
    *  
    * @param string|array $attributes
    *    
    * @return void
    */ 
    protected function setIgnored($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : [$attributes]; 
        $this->ignore = array_merge($this->ignore, $attributes);
    }   
    
    /**  
    * Установка разрешений к заполнению для атрибутов
    *  
    * @param string|array $allowed
    *    
    * @return void
    */ 
    protected function setAllow($allowed)
    {
        $allowed = is_array($allowed) ? $allowed : [$allowed];
        $this->allowed = array_merge($this->allowed, $allowed); 
    }
    
    /**  
    * Установка запрещений к заполнению для атрибутов
    *  
    * @param string|array $forbidden
    *    
    * @return void
    */ 
    protected function setForbid($forbidden)
    {
        $forbidden = is_array($forbidden) ? $forbidden : [$forbidden];
        $this->forbidden = array_merge($this->forbidden, $forbidden);
    }    

    /**  
    * Вставка записи. 
    * 
    * @param array $values
    *    
    * @return void
    */ 
    protected function saveModel($values)
    {
        $numRows = $this->dbcommand->insert($this->table, $values)->execute();            
        
        if ($numRows == 0) {
            if (defined('static::EXCEPTION') && true === static::EXCEPTION) {
                throw new \Exception(ABC_MODEL_NO_SAVE);
            }
        } 
        
        $lastId = $this->dbcommand->lastInsertId();
        $values[$this->primary] = $lastId;
        $values = $this->convertTypeForPHP($values);
        $this->fill($values);
    }
    
    /**  
    * Обновление записи 
    * 
    * @param array $values
    *    
    * @return bool 
    */ 
    protected function updateModel($values)
    {
        $id = $this->{$this->primary};
        
        if (empty($id)) {
           return false;
        }
        
        $numRows = $this->dbcommand->update(
                                $this->table, 
                                $values, 
                                $this->primary .'=:'. $this->primary, 
                                [':'. $this->primary => $id]
                                )->execute();
                            
        if ($numRows == 0) {
            if (defined('static::EXCEPTION') && true === static::EXCEPTION) {
                throw new \Exception(ABC_MODEL_NO_SAVE);
            }
        } 
     
        return $numRows;    
    } 

    /**  
    * Проверяет аттрибуты для массовой загрузки
    *  
    * @param array $attributes
    *    
    * @return array
    */ 
    protected function checkAttributes($attributes)
    {
        $this->checkAllowed($attributes);
        $this->checkForbidden($attributes);
        return $attributes; 
    } 
    
    /**  
    * Подготовка полей для записи
    * 
    * @param array $options
    *   
    * @return array
    */ 
    protected function prepareAttributes($options = [])
    {
        $attributes = array_merge($this->attributes, $options); 
        $attributes = array_diff_key($attributes, $this->ignore);        
        $this->fill($attributes); 
        return $attributes;
    } 
    
    /**  
    * Получение строки таблицы по ID
    *     
    * @param int $id
    * @param mixed $columns
    *
    * @return array
    */ 
    protected function fetchByIdOne($id, $columns)
    {
        $this->query([$this->primary => $id], $columns);
        $values = $this->dbcommand->queryRow();
        
        if (false === $values) {
            return false;
        }
        
        $this->dbcommand->reset();
        $values = $this->convertTypeForPHP($values);
        $this->fill($values);
        return $this->selectResult($this);
    } 

    /**  
    * Получение строк таблицы по списку ID
    *     
    * @param array $ids
    * @param mixed $columns
    *
    * @return array
    */ 
    protected function fetchById($ids, $columns)
    {
        $columns = is_array($columns) ? $this->primary .','. implode(',', $columns) : null;
        $rows = $this->dbcommand->select($columns)   
                              ->from($this->table)
                              ->where(['in', $this->primary, $ids])
                              ->queryAll();
        
        $this->dbcommand->reset();
        return $this->createListModels($rows);
    } 

    /**  
    * Получение одной строки таблицы по условию
    *     
    * @param array $conditions
    * @param mixed $columns
    * 
    * @return array
    */ 
    protected function fetchOne($conditions = [], $columns)
    {
        $this->query($conditions, $columns);
        $values = $this->dbcommand->queryRow();
        $this->dbcommand->reset();
        $values = $this->convertTypeForPHP($values);
        $this->fill($values);
        return $this->selectResult($this);         
    }    

    /** 
    * Получение всех строк таблицы по условию
    *     
    * @param array $conditions
    * @param mixed $columns
    * 
    * @return array
    */ 
    protected function fetchAll($conditions = [], $columns)
    {
        $this->query($conditions, $columns);
        $rows = $this->dbcommand->queryAll();
        $this->dbcommand->reset();
        return $this->createListModels($rows);          
    }

    /**  
    * Получение всех строк таблицы по условию в виде генератора
    *     
    * @param array $conditions
    * @param mixed $columns
    * 
    * @return array
    */ 
    protected function fetchEach($conditions = [], $columns)
    {
        $this->query($conditions, $columns);
        $rows = $this->dbcommand->queryAll();
        $this->dbcommand->reset();
        return $this->createEachModels($rows);          
    }    
    
    /**  
    * Получение всех строк таблицы по SQL выражению.
    *     
    * @param string $sql 
    * @param array $params
    * 
    * @return mixed
    */  
    protected function fetchBySql($sql, $params = [])
    {
        $rows = $this->dbcommand->createCommand($sql)   
                              ->bindValues($params)
                              ->queryAll(); 
     
        $this->dbcommand->reset();
        return $this->createListModels($rows);
    }

    /**  
    * Возвращает количество строк, удовлетворяющих условию. 
    *     
    * @param mixed $condition
    *  
    * @return array
    */ 
    protected function cnt($conditions = [])
    {
        $this->query($conditions, $this->dbcommand->expression('COUNT(id) AS cnt'));
        $cnt = $this->dbcommand->queryScalar();
        $this->dbcommand->reset();
        return (int)$cnt;
    }
    
    /**   
    * Возвращает количество строк, найденых произвольным запросом 
    *      
    * @param mixed $condition
    * @param array $params
    * 
    * @return array 
    */ 
    protected function cntBySql($sql, $params = [])
    {
        $cnt = $this->dbcommand->createCommand($sql) 
                             ->bindValues($params) 
                             ->count();
     
        $this->dbcommand->reset();
        return (int)$cnt;   
    } 
    
    /**  
    * Основной запрос
    *     
    * @param mixed $condition
    * @param mixed $columns
    * 
    * @return array
    */ 
    protected function query($conditions, $columns = null)
    {
        if (is_array($columns)) {
            $columns = $this->primary .','. implode(',', $columns);
        } 
     
        $this->dbcommand->select($columns)   
                      ->from($this->table);
        
        if (!empty($conditions)) {
            $conditions = $this->convertTypeForDB($conditions);
            
            foreach ($conditions as $field => $value) {
                $part[] = $field .' = :'. $field;
                $params[':'. $field ] = $value;
            }
            
            $conditions = implode("\n\tAND ", $part);
            $this->dbcommand->where($conditions, $params);
        }
    } 

    /**  
    * Проверяет свойства по списку разрешенных
    *  
    * @param array $columns
    *    
    * @return array
    */ 
    protected function checkAllowed($columns)
    { 
        if (defined('static::BULK_UPLOAD') && true === static::BULK_UPLOAD) {
            return true;
        }
     
        foreach ($columns as $name => $value) {
         
            if (in_array($name, $this->allowed)) {
                continue;
            } 
            
            throw new \InvalidArgumentException(sprintf(ABC_BANNED_ATTRIBUTE, $name, $name));
        }
    }
    
    /**  
    * Проверяет свойства по списку запрещенных
    *  
    * @param array $columns
    *    
    * @return array
    */ 
    protected function checkForbidden($columns)
    {
        foreach ($columns as $name => $value) {
         
            if (in_array($name, $this->forbidden)) {
                throw new \InvalidArgumentException(sprintf(ABC_BANNED_ATTRIBUTE, $name, $name));
            }
        }
    }

    /**  
    * Возвращает семейство моделей в виде генератора.
    * 
    * @param array $rows 
    *      
    * @return object
    */  
    protected function createEachModels($rows)
    {
        foreach ($rows as $values) {
            $clone = clone $this;        
            $values = $clone->convertTypeForPHP($values);
            $clone->fill($values);
            yield $this->selectResult($clone);
        }
    } 
 
    /**  
    * Выбирает форму результата
    */ 
    protected function selectResult($model) 
    {
        if (true === $this->asObject || (defined('static::AS_OBJECT') && true === static::AS_OBJECT)) {
            return $this->asDTO($model);
        }
        
        if (true === $this->asArray || (defined('static::AS_ARRAY') && true === static::AS_ARRAY)) {
            return $model->prepareAttributes();
        }
        
        return $model;        
    } 
    
    /**  
    * Заполняет объект свойствами
    *  
    * @param array $columns
    *    
    * @return void
    */ 
    protected function fill($columns = [])
    {
        if (empty($columns)) {
            return null;
        } 
         
        foreach ($columns as $name => $value) {
            $this->attributes[$name] = $value;
        }
    } 
    
    /**  
    * Нормализация basename
    *    
    * @param string $path
    * 
    * @return string
    */ 
    protected function getBaseName($path)
    {    
        return basename(str_replace('\\', '/', $path));
    
    }
    
    /**  
    * Очищает модель от данных
    */ 
    protected function clear()  
    {
        $this->primary = 0;
        $this->attributes = [];    
        $this->ids = [];
        $this->allowed    = [];
        $this->forbidden  = [];
        $this->asArray  = false;
        $this->asObject = false;
        $this->find     = false;
        $this->relations = [];
        $this->casts  = [];
        $this->ignore = []; 
    }
}
