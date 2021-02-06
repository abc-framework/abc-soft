<?php

namespace Abcsoft\HTTP;

/** 
 * Класс Uri
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/
 */   
abstract class RequestAddition
{
    /**
    * Возвращает параметр server ($_SERVER) по ключу.
    *
    * @param string $key 
    * @param string|array $default 
    *
    * @return string|array
    */
    public function getServerParam($key, $default = null)
    {
        $serverParams = $this->getServerParams();
        return isset($serverParams[$key]) ? $serverParams[$key] : $default;
    }
    
    /**
    * Возвращает параметр query string ($_GET) по ключу.
    *
    * @param string $key 
    * @param string|array $default 
    *
    * @return string|array
    */
    public function getQueryParam($key, $default = null)
    {
        $getParams = $this->getQueryParams();
        return isset($getParams[$key]) ? $getParams[$key] : $default;
    }
    
    
    /**
    * Возвращает параметр cookie ($_COOKIE) по ключу.
    *
    * @param string $key 
    * @param string|array $default 
    *
    * @return string|array
    */
    public function getCookieParam($key, $default = null)
    {
        $cookieParams = $this->getCookieParams();
        return isset($cookieParams[$key]) ? $cookieParams[$key] : $default;
    }
    
    /**
    * Возвращает объект, в котором добавлен указанный атрибут.
    *
    * @param string $name
    * @param mixed $value 
    *
    * @return object
    */
    public function withAttributes(array $attributes)
    {
        $clone = clone $this;
        $clone->attributes = $attributes;
        return $clone;
    }
}

