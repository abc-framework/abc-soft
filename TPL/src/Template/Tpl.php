<?php 

namespace Abcsoft\TPL\Template;

use Abcsoft\TPL\Template\Processor;


/** 
 * Класс Template 
 * Шаблонизатор
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */ 
   
class Tpl extends Processor
{  
    protected $functions   = ['createUri', 'createLink', 'activeLink'];

    /**
    * Constructor.
    *
    * @param string $tplDir      Path to templates directory
    */
    public function __construct(array $config)
    {
        $this->config = $config; 
        $language = '\Abcsoft\TPL\Language\\'. $config['Language'];
        $language::set();
        $this->tplDir = str_replace('\\', DIRECTORY_SEPARATOR, $this->config['dir_template']);
        $this->layout = $this->config['layout'];
        $this->tplExt = $this->config['ext'];
        $this->tplPhp = $this->config['php'];
        $functions = $this->config['functions'];
        $this->functions = array_merge($this->functions, $functions);
    }
} 
