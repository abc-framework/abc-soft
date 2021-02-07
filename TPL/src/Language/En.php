<?php

namespace Abcsoft\TPL\Language;

/** 
 * Класс En
 * 
 * NOTE: Requires PHP version 5.5 or later   
 * @author phpforum.su
 * @copyright © 2015
 * @license http://www.wtfpl.net/ 
 */   
class En
{
    
    public static function set() 
    {
        if(!defined("ABC_TPL")){
         
            define("ABC_TPL", true);
            
        define('ABC_TPL_NOT_FOUND',                ' Template file %s not found');
        define('ABC_TPL_BAD_METHOD',               ' Method %s is not supported by the current templating engine.');
        define('ABC_TPL_INVALID_BLOCK',            ' Block %s  does not exist or incorrect syntax');
        define('ABC_TPL_MODEL_NO_SAVE',            ' System failure. Updates are not accepted.');
        define('ABC_TPL_SELECT_NO_TEMPLATE',       ' Cannot select template if values ​​have already been passed to template.');  
    }
}
















