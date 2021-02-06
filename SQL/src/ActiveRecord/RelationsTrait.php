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
trait RelationsTrait
{

    private static $with = [];
    private static $callable = [];
    
    /**  
    * Жадная загрузка
    *    
    * @param string|array $args
    * 
    * @return array
    */ 
    public function with()
    { 
        $with = func_get_args();
        
        if (isset($with[0]) && is_array($with[0])) {
            $with = $with[0];
        }
        
        if (!empty($with)) {
            foreach ($with as $name => $value) {
                if (is_int($name) && false !== strpos($value, '.')) {
                    $nesting = explode('.', $value);
                    foreach ($nesting as $each) {
                        self::$with[strtolower($each)] = true;
                    }
                } elseif (is_int($name)) {
                    self::$with[strtolower($value)] = true;
                } else {
                    self::$with[strtolower($name)] = $value;
                }
            }
        }
     
        return $this;
    }
    
    /**  
    * Получение связанной модели "один-к-одному"
    *     
    * @param string $relation
    * @param mix $link
    * @param string $columns
    * 
    * @return object
    */ 
    protected function hasOne($relation, $link = null, $columns = null)
    {        
        $link = $this->prepareLink($link, static::class);
        $model = $relation::create();        
        $this->prepareHas($relation, $model, $link, $columns);
        $this->dbcommand->limit(1);
        $row = $this->dbcommand->queryRow();
        $this->dbcommand->reset();
        return $this->createOne($row, $model);
    }

    /**  
    * Получение связанных моделей в виде массива
    *    
    * @param string $relation
    * @param mix $link
    * @param string $columns
    * 
    * @return array
    */ 
    protected function hasMany($relation, $link = null, $columns = null)
    {
        $model = $relation::create();
        $name  = strtolower($this->getBaseName($relation));
        $link = $this->prepareLink($link, static::class);
        $key  = key($link);
     
        if (isset(self::$with[$name])) {
            $rows = $this->queryWith($name, $relation, $model, $key, $columns);
        } else {    
            $this->prepareHas($relation, $model, $link, $columns);
            $rows = $this->dbcommand->queryAll();
        }
        
        $this->dbcommand->reset();
        
        if (empty($rows)) {
            return null; 
        }
        
        return $this->createListModels($rows, $model);
    }

    /**  
    * Получение связанных моделей "через одну"
    *    
    * @param string $relation
    * @param string $mediator
    * @param mix $link
    * @param string $columns
    * 
    * @return array
    */ 
    protected function hasManyThrough($relation, $mediator, $target = null, $link = null, $columns = null)
    {
        $interim  = $this->convertTable($mediator);
        $forthKey = $this->convertName($mediator);
        $backKey  = $this->convertName(static::class);
        $field = (null == $target) ? $backKey .'_id' : $target; 
        $link  = $this->prepareLink($link, $mediator, $field);
        $key   = key($link);
        $ids   = $this->queryToMediator($interim, $this->primary, array_shift($link));
        $model = $relation::create(); 
        $rows  = $this->queryMany($ids, $relation, $key, $columns);
        return $this->createListModels($rows, $model);
    }
   
    /**  
    * Получение обратной связи 
    *     
    * @param string $relation
    * @param mix $link
    * @param string $columns
    * 
    * @return object
    */ 
    protected function belongsTo($relation, $link = null, $columns = null)
    {
        $link = $this->prepareLink($link, $relation);
        $link = array_flip($link);
        $name = strtolower($this->getBaseName($relation));    
        $key  = key($link);
        
        if (isset(self::$with[$name])) {
            $model = $relation::create();        
         
            if (!isset(self::$withStorage[$name]['ids'])) {
                $this->setIdsForWith($name, $model);
            }
            
            if (!isset(self::$withStorage[$name]['rel'])) {
                self::$withStorage[$name]['rel'] = $this->queryMany(self::$withStorage[$name]['ids'], 
                                                        $relation, 
                                                        $model, 
                                                        $key, 
                                                        $columns);
            }
            
            $row = $this->getDataInOne($name, $link[$key]);
            return $this->createOne($row, $model);
        }
        
        return $this->hasOne($relation, $link, $columns);
    }
    
    /**  
    * Получение связи многие-ко-многим
    *     
    * @param string $relation
    * @param string $interim
    * @param mix $link
    * @param string $columns
    *  
    * @return object
    */ 
    protected function belongsToMany($relation, $interim = null, $link = null, $columns = null)
    {
        $forthKey  = $this->convertName($relation);
        $backKey   = $this->convertName(static::class);
     
        if (null === $interim) {
            $interim = [$forthKey, $backKey];
            sort($interim);
            $interim = implode('_', $interim);
        }
     
        $forthKey .= '_id';
        $backKey  .= '_id';
        $ids = $this->queryToMediator($interim, $forthKey, $backKey);
        $model = $relation::create(); 
        $rows  = $this->queryMany($ids, $relation, $model, null, $columns);
        return $this->createListModels($rows, $model);   
    }

    /**  
    * Подготовка ключей для связей
    *     
    * @param mix $link
    * @param string $relation
    * @param string $field
    *
    * @return array
    */ 
    private function prepareLink($link, $relation, $field = null)
    {
        $field = (null === $field) ? $this->primary : $field;
     
        if (null === $link) {
            $name = $this->convertName($relation);
            $link = [$name .'_id' => $field];
        } elseif (is_string($link)) {
            $link = [$link => $field];
        }
      
        return $link;
    } 
    
    /**  
    * Подготовка результата
    *     
    * @param array $row
    * @param object $model
    * 
    * @return object
    */ 
    private function createOne($row, $model) 
    {        
        if (empty($row)) {
            return null;
        }
        
        $attribute = $model->convertTypeForPHP($row); 
        $model->fill($attribute);
        return $model->selectResult($model);
    }
    
    /**  
    * Получение и установка массива первичных ключей для запроса IN()
    *    
    * @param string $name
    * @param object $model
    * 
    * @return array
    */ 
    private function setIdsForWith($name, $model)
    { 
        $limit = defined('static::RELATIONS') ? static::RELATIONS : 50;
        $rows = $model->command($model->primary)
                      ->limit($limit)
                      ->queryAll();
        $model->command()->reset();
        
        if (empty($rows)) {
            return null;
        }
        
        foreach ($rows as $values) {
            self::$withStorage[$name]['ids'][$values[$model->primary]] = $values[$model->primary];        
        }
        
        return self::$withStorage[$name]['ids'];
    }
 
    /**  
    * 
    *    
    * @param string $relation
    * @param string $mediator
    * @param mix $link
    * @param string $columns
    * 
    * @return array
    */ 
    private function queryWith($name, $relation, $model, $key, $columns)
    { 
        if (empty(self::$withStorage[$name])) {
          
            if (empty($this->ids)) {
                $this->ids = $this->setIdsForWith($name, $model);
            }
            
            self::$withStorage[$name] = $this->queryMany($this->ids, 
                                                         $relation, 
                                                         $model, 
                                                         $key, 
                                                         $columns);
            
        }
     
        return $this->getDataIn($name, $key);
    }
    
    /**  
    * Подготовка запроса для связей
    *     
    * @param string $relation
    * @param object $model    
    * @param mix $link
    * @param string $columns
    * 
    * @return array
    */ 
    private function prepareHas($relation, $model, $link, $columns)
    {           
        $columns = (null !== $columns) ? $model->primary .', '. $columns : null;
        $table = $this->convertTable($this->getBaseName($relation));
        $this->dbcommand->select($columns)
                      ->from($table);
                      
        $i = 0;
        foreach ($link as $k => $v) {
            if ($i++ == 0) {
                $this->dbcommand->where($k .' = :'. $k, [':'. $k => $this->attributes[$v]]);
            } else {
                $this->dbcommand->andWhere($k .' = :'. $k, [':'. $k => $this->attributes[$v]]);
            }
        } 
    }
    
    /**  
    * Запрос к промежуточной таблице.
    *     
    * @param string $interim
    * @param string $forthKey
    * @param string $backKey
    * 
    * @return array
    */ 
    private function queryToMediator($interim, $forthKey, $backKey)
    {
        $this->dbcommand
            ->select($forthKey)
            ->from($interim)
            ->where($backKey .' = :'. $backKey, [ 
                ':'. $backKey => $this->{$this->primary}
                ]
            );
                      
       
        $ids = [];
        foreach ($this->dbcommand->queryColumn() as $id) {
            $ids[] = $id;
        }
        
        $this->dbcommand->reset();
        return $ids;
    }
    
    /**
    * Запрос на выборку "многих".
    *     
    * @param array $ids 
    * @param string $relation
    * @param string $key
    * @param string $columns
    * 
    * @return array
    */ 
    private function queryMany($ids, $relation, $model, $key = null, $columns = null)
    {      
        if (empty($ids)) {
            return null;
        }
        
        $forthTable = $this->convertTable($relation);
        $key     = (null !== $key) ? $key : $model->primary;       
        $columns = (null !== $columns) ? $model->primary .', '. $columns : null;      
        $this->dbcommand
            ->select($columns)
            ->from($forthTable)
            ->where(['in', $key, $ids]); 
//     
        $name = strtolower($this->getBaseName($relation));
        $with = self::$with[$name];
        
        if (is_callable($with)) {
            call_user_func($with, $this->dbcommand);
        }
        
        $rows = $this->dbcommand->queryAll();
        $rowsOut = [];

        foreach ($rows as $row) {
            $rowsOut[$row[$model->primary]] = $row;
        }
        
        $this->dbcommand->reset();
        return $rowsOut;
    }

    
    /**  
    * Эмуляция запроса IN (...)
    *    
    * @param string $name  
    * @param string $key
    * 
    * @return array
    */ 
    private function getDataInOne($name, $key)
    { 
        $part = [];
        foreach (self::$withStorage[$name]['rel'] as $id => $relation) {
         
            if ($id == $this->attributes[$key]) {
               return $relation;
            }
        }
        
        return $part;
    } 
    
    /**  
    * Эмуляция запроса IN (...)
    *    
    * @param string $name  
    * @param string $key
    * 
    * @return array
    */ 
    private function getDataIn($name, $key)
    {
        $part = [];        
        foreach (self::$withStorage[$name] as $num => $relation) {
         
            if ($relation[$key] == $this->attributes[$this->primary]) {
                $part[] = $relation;
                unset(self::$withStorage[$name][$num]);
            }
        }
        
        return $part;
    }    
}
