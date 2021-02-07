<?php

namespace Abcsoft\TPL\TplNative;

/** 
 * Класс BaseView
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class TplNative
{    
    protected $config;
    protected $tplName;
    protected $tplDir;
    protected $template;
    protected $data = [];
    
    /**
    * @param object $container
    */ 
    public function __construct(array $config)
    {
        $this->config = $config; 
    }
    
    /**
    * Устанавливает шаблон
    *
    * @param string|array $data
    * @param mix $value
    *
    * @return void
    */     
    public function selectTpl($tplName)
    {
        $parts = pathinfo($tplName); 
        $this->tplDir   = str_replace('\\', DIRECTORY_SEPARATOR, $this->config['template']['dir_template']);
        $tplName        = str_replace('\\', DIRECTORY_SEPARATOR, $parts['dirname'] . DIRECTORY_SEPARATOR . $parts['filename']);
        $this->template = $this->tplDir . $tplName .'.'. $this->config['template']['ext'];
        return $this;
    } 
    
    /**
    * Передает переменные в шаблон
    *
    * @param string|array $data
    * @param mix $value
    *
    * @return $this
    */     
    public function assign($data, $value = null)
    {
        if (is_array($data)) {
            $this->data = array_merge($data, $data);
        } else {
            $this->data[$data] = $value;
        }
        
        return $this;
    } 
    
    /**
    * Передает переменные в шаблон для вывода в поток 
    *
    * @param string|array $data
    * @param mix $value
    *
    * @return void
    */     
    public function assignHtml($data, $value = null)
    {
        if (is_array($data)) {
            $this->data = array_merge($this->data, htmlChars($data));
        } else {
            $this->data[$data] = htmlChars($value);
        }
     
        return $this;
    }
    
    /**
    * Наследование шаблона 
    *
    * @param string $block
    * @param string $layout
    *
    * @return void
    */     
    public function extendsTpl($block, $layout = 'index')
    {
        $template = $this->execute($this->template);
        $this->assign($block, $template);
        $layout = $layout ?? $this->config['template']['layout'];
        $parts = pathinfo($layout);
        $this->html = $this->execute($this->tplDir . $parts['dirname'] . DIRECTORY_SEPARATOR 
                    . $parts['filename'] .'.'. $this->config['template']['ext']);
        return $this;
    }  
    
    /**
    * Возвращает заполненный шаблон
    *
    * @return string
    */     
    public function parseTpl()
    {
        if (!empty($this->html)) {
            return $this->execute($this->template);
        }
        
        return $this->html;
    }  
    
    /**
    * Возвращает контент
    * 
    * @return string
    */
    public function display()
    {        
        echo $this->html;
    }  
    
    
    /**
    * Returns the content
    * 
    * @return string
    */
    public function getContent()
    {        
        return $this->html;
    } 
 
    /**
    * Разбор шаблона
    *
    * @param string $template
    *
    * @return string
    */     
    protected function execute($template)
    {
        ob_start();
        extract($this->data);
        include_once $template;        
        return ob_get_clean();
    }     
    
    /**
    * Ошибка вызова метода
    *
    * @param string $method
    * @param mix $param
    *
    * @return void
    */     
    public function __call($method, $param)
    {
        $methods = explode('::', $method);
        $method  = array_pop($methods);
        throw new \BadMethodCallException('Native Template: '. sprintf(ABC_TPL_BAD_METHOD, $method, $method);
    } 
}
