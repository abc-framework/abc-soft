<?php

namespace Abcsoft\SQL\DbCommand;

use ABC\Core\Exception\AbcError;

/** 
 * Выражения
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/ 
 */  
class Expression
{

    protected $component = ' Component DbCommand: ';
    protected $expression;
    protected $params = [];

    /**
    * 
    *
    */     
    public function __construct($extression = null)
    {
        $this->extression = $extression;
     
        if (!empty($params) && is_array($params)) {
            foreach ($params as $name => $value) {
                $this->params[$name] = $value;
            }
        }
    }  
    
    /**
    * 
    *
    */     
    public function createExpression($object, $rescuer)
    {
        if ($object instanceof self) {
            $expressions = '';
            $params = $object->getParams();
            $expression = $object->getExpression();
           
            if (!empty($params)) {
             
                foreach ($params as $p => $v) {
                    
                    if (is_object($v)) {
                        $expressions .= str_replace($p, '('. $v .')', $expression);
                    } else {
                        $expressions .= str_replace($p, $this->rescuer->escape($v), $expression);                    
                    }
                }
            } 
         
            return $expression;
            
        } else {
            return '('. (string)$object .')';
        }
    } 
    
    /**
    * 
    *
    */     
    public function getParams()
    { 
        return $this->params;
    }
    
    /**
    * 
    *
    */     
    public function getExpression()
    {
        return $this->extression;
    } 
}
