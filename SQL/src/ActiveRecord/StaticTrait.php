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
trait StaticTrait
{

    /**  
    * Создание новой модели.
    * 
    * @param array $attributes
    *      
    * @return object
    */     
    public static function create(array $attributes = [])
    {
        $model = new static;
        
        if (!empty($attributes)) {
            $model->save($attributes);
        }
        
        return $model;
    }    

    /**  
    * Возвращает  SQL конструктор 
    * 
    * @param array $columns
    *       
    * @return array|object
    */         
    public static function find($columns = null)
    {
        $model = new static; 
        $model->find = true;
        return $model->command($columns);  
    } 
 
    /**  
    * Возвращает заполненный(ые) объект(ы) модели по первичному ключу(ам).
    * 
    * @param int $ids
    * @param array $columns
    *      
    * @return array|object
    */       
    public static function findById($ids, $columns = null)
    {
        $model = new static;
        
        if (is_array($ids)) {
            return $model->fetchById($ids, $columns);
        }
        
        return $model->fetchByIdOne($ids, $columns);  
    } 
    
    /**  
    * Возвращает заполненный объект модели, удовлетворяющий условию.
    * 
    * @param string|array $condition
    * @param array $columns
    *      
    * @return array|object
    */      
    public static function findOne($conditions = null, $columns = null)
    {
        $model = new static;
        return $model->fetchOne($conditions, $columns);  
    }    
    
    /**  
    * Возвращает заполненные объекты модели, удовлетворяющие критериям в виде массива,
    * нумерованного первичными ключами.
    * 
    * @param array $condition
    * @param array $columns
    *      
    * @return array
    */      
    public static function findAll($condition = null, $columns = null)
    {
        $model = new static;
        return $model->fetchAll($condition, $columns);  
    }
    
    /**  
    * Возвращает заполненные объекты модели, удовлетворяющие критериям, в виде генератора.
    * 
    * @param array $condition
    * @param array $columns
    *      
    * @return object
    */      
    public static function findEach($condition = null, $columns = null)
    {
        $model = new static;
        return $model->fetchEach($condition, $columns);  
    }    

    /**  
    * Возвращает заполненные объекты модели по SQL выражению.
    * 
    * @param string $sql
    * @param array $params 
    *       
    * @return object
    */       
    public static function findBySql($sql, $params = [])
    {
        $model = new static;
        return $model->fetchBySql($sql, $params);
    }
     
    /**  
    * Проверяет, есть ли хоть одна строка, удовлетворяющая условию.
    * 
    * @param string|array $condition
    * @param array $params 
    *      
    * @return bool
    */       
    public static function exists($condition, $params = [])
    {
        $model = new static;
        return ($model->cnt($condition, $params) > 0);
    }
    
    /**  
    * Возвращает количество строк, удовлетворяющих условию.
    * 
    * @param string|array $condition 
    *      
    * @return int
    */       
    public static function countAll($condition = null)
    {
        $model = new static; 
        return $model->cnt($condition);
    }    
    
    /**  
    * Возвращает количество строк по SQL выражению.
    * 
    * @param string $sql
    * @param array $params 
    *      
    * @return int 
    */       
    public static function countBySql($sql, $params = [])
    {
        $model = new static;
        return $model->cntBySql($sql, $params);
    } 
    
    /**  
    * Возвращает конструктор запросов.
    *     
    * @return object
    */       
    public static function DB()
    {
        $model = new static;
        return $model->dbcommand;
    }  
    
}
