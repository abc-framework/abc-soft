<?php

namespace Abcsoft\HTTP;


/** 
 * Трэйт Message
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2017
 * @license http://www.wtfpl.net/
 */   
trait MessageTrait
{
    protected static $validProtocol = [
                '1.0' => 1,
                '1.1' => 1,
                '2.0' => 1,
    ];
    
    protected static $special = [
                'CONTENT_TYPE'    => 1,
                'CONTENT_LENGTH'  => 1,
                'PHP_AUTH_USER'   => 1,
                'PHP_AUTH_PW'     => 1,
                'PHP_AUTH_DIGEST' => 1,
                'AUTH_TYPE'       => 1,
    ];   

    protected $protocolVersion;    
    protected $headers = [];
    protected $body;
    
    
    public function __set($name, $value) {}
    
    /**
    * Реакция на неподдерживаемые методы
    */ 
    public function __call($method, $params)
    {
        throw new \InvalidArgumentException(sprintf(ABC_HTTP_NO_METHOD, $method, $method));
    }

    /**
    * Возвращает версию протокола HTTP в виде строки
    */ 
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }
    
    /**
    * Возвращает новый объект с установленным HTTP протоколом
    *
    * @param string $version
    *
    * @return object
    */ 
    public function withProtocolVersion($version)
    {
        if (!isset(self::$validProtocol[$version])) {
            throw new \InvalidArgumentException(ABC_HTTP_INVALID_PROTOCOL);
        }
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }
    
    /**
    * Возвращает все заголовки
    *
    * @return array
    */ 
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
    * Проверяет наличие заголовка
    */ 
    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }
    
    /**
    * Возвращает значение заголовка по имени
    *
    * @param string $name
    *
    * @return string|array
    */ 
    public function getHeader($name)
    { 
        if (!isset($this->headers[$name])) {
            return [];
        }
        
        $value  = $this->headers[$name];
        $value  = is_array($value) ? $value : [$value];
        return array_shift($value);
    }
    
    /**
    * Получает строку значений заголовка, разделенными запятыми.
    *
    * @param string $name
    *
    * @return string
    */ 
    public function getHeaderLine($name)
    {
        $value = $this->getHeader($name);
     
        if (!is_array($value)) {
            $value = [$value];
        }
        return implode(', ', $value);
    }
    
    /**
    * Возвращает новый объект с новым или замененным заголовком.
    *
    * @param string $name
    * @param string $value
    *
    * @return object
    */ 
    public function withHeader($name, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        
        if (!$this->checkupArray($value)) {
            throw new \InvalidArgumentException(ABC_HTTP_VALUE_NO_STRING);
        }
        
        if ($this->validateName($name) && $this->validateValue($value)) {
            $clone = clone $this;
            $clone->headers[$name] = $value;
            return $clone; 
            
        } else {
            return false;
        }
    }
    
    /**
    * Возвращает новый объект с добавленными заголовками.
    *
    * @param string $name
    * @param string $value
    *
    * @return object
    */    
    public function withAddedHeader($name, $value)
    {
        $value = is_array($value) ? $value : [$value];    
     
        if (!$this->checkupArray($value)) {
            throw new \InvalidArgumentException(ABC_HTTP_VALUE_NO_STRING);
        }
        
        if ($this->validateName($name) && $this->validateValue($value)) {  
            $clone = clone $this;
            array_merge($clone->headers, [$name => $value]);
            return $clone;
        } else {
            return false;
        }
    }
    
    /**
    * Возвращает новый объект с удаленным заголовками.
    *
    * @param string $name
    *
    * @return object
    */   
    public function withOutHeader($name)
    {
        if (!$this->hasHeader($name, 'headers')) {
            throw new \InvalidArgumentException($name . ABC_HTTP_NO_HEADER);
        }
        
        $clone = clone $this;
        unset($clone->headers[$name]);
        return $clone;
    }
    
    /**
    * Возвращает тело сообщения
    *
    * @return object
    */ 
    public function getBody()
    {
        return $this->body;
    }
    
    /**
    * Возвращает новый объект с новым телом сообщения.
    *
    * @param object $body
    *
    * @return object
    */ 
    public function withBody($body)
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }
    
/*-----------------------------------------------------    
        Хэлперы
-------------------------------------------------------*/
    /**
    * Стартовая инициализация
    *
    * @param array $headers
    */ 
    protected function initialize($headers = [])
    {
        $this->storage = new Storage;
        $this->setHeaders($headers);
    }
    
    /**
    * Устанавливает заголовки
    *
    * @param array $headers
    */ 
    protected function setHeaders($headers = [])
    {
        $headers = $this->filterHeaders($headers);
        $this->headers = $headers;
    }
    
    /**
    * Устанавливает дефолтные заголовки
    * 
    * @param array $env
    */ 
    protected function setEnvHeaders($env)
    {
        $headers = [];
        foreach ($env as $key => $value) {
            $keyUpper = strtoupper($key);            
            
            if (isset(self::$special[$keyUpper]) || strpos($keyUpper, 'HTTP_') === 0) {
             
                if ($keyUpper !== 'HTTP_CONTENT_LENGTH') {
                    $headers[$key] = $value;
                }
            }
        }
        
        $this->setHeaders($headers);
    }  

    /**
    * Фильтрует и нормализует заголовки.
    *
    * @param array $header
    * 
    * @return array 
    */
    private function filterHeaders($headers)
    {
        $out = [];
        foreach ($headers as $header => $value) {
            if (!is_string($header)) {
                continue;
            }
         
            if (!is_array($value) && !is_string($value)) {
                continue;
            }
         
            if (!is_array($value)) {
                $value = [$value];
            }
         
            $out[$header] = $value;
        }
      
        return $out;
    }
    
    /**
    * Проверка значений массива на строковый тип
    *
    * @param array $array
    *
    * @return bool
    */
    protected function checkupArray($array)
    {
        return array_reduce($array, function ($carry, $item) {
            if (!is_string($item)) {
                return false;
            }
            return $carry;
        }, true);
    }
    
    /**
    * 
    *
    * @param mixed $name
    * 
    * @return bool
    */
    protected function validateName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new \InvalidArgumentException($name . ABC_HTTP_INVALID_HEADER_NAME);
        }
        
        return true;
    }
    
    /**
    * Assert that the provided header values are valid.
    *
    * @param array $values
    *
    * @return bool
    */
    protected function validateValue($values)
    {
        foreach ($values as $value) {
         
            if (!$this->isValidValue($value)) {
                throw new \InvalidArgumentException($value . ABC_HTTP_INVALID_HEADER_VALUE);
            }
        }
        
        return true;
    }
    
    /**
    * 
    *
    * @param array $value
    *
    * @return bool
    */
    protected function isValidValue($value)
    {
        $value = (string)$value;
     
        if (preg_match("#(?:(?:(?<!\r)\n)|(?:\r(?!\n))|(?:\r\n(?![ \t])))#", $value)) {
            return false;
        }
     
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($value[$i]);
         
            if (($ascii < 32 && !in_array($ascii, [9, 10, 13], true))
                || $ascii === 127
                || $ascii > 254
            ) {
                return false;
            }
        }
     
        return true;
    } 
}
