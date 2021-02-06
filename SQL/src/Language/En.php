<?php

namespace Abcsoft\SQL\Lang;

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
        if(!defined("ABC_SQL")){
         
            define("ABC_SQL", true);
            
            define('ABC_SQL_WRONG_CONNECTION',         ' wrong data connection in the configuration file ');
            define('ABC_SQL_NO_DEBUGGER',              ' SQL debugger is inactive. Set to true debug configuration. ');    
            define('ABC_SQL_INVALID_MYSQLI_TYPE',      ' Number of elements in type definition string doesn\'t match number of bind variables');
            define('ABC_SQL_NO_MYSQLI_TYPE',           ' Unknown type of the parameter ');
            define('ABC_SQL_ERROR',                    ' Query build error ');
            define('ABC_SQL_EMPTY_ARGUMENTS',          ' Too few arguments to method %s');  
            define('ABC_SQL_DISABLE',                  ' Blocked');        
            define('ABC_SQL_TRANSACTION_EXIST',        ' There is already an active transaction ');
            define('ABC_SQL_TRANSACTION_ERROR',        ' Transaction error: '); 
            define('ABC_SQL_NO_SUPPORT',               ' This type of table is not supported by the debugger '); 
            define('ABC_SQL_OTHER_OBJECT',             ' An inappropriate object is used '); 
            define('ABC_SQL_NO_METHOD',                ' Method %s is not supported by the Query builder ');
            define('ABC_SQL_ERROR_BINDVALUES',         ' The numbering of the array in the parameter of the bindValues() method must begin with 1 ');
            define('ABC_SQL_DBCOMAND_SERIALIZE',       ' You can not serialize a query builder object ');
            define('ABC_SQL_INVALID_CONDITIONS',       ' Conditions are set incorrectly'); 
            define('ABC_SQL_DUBLE',                    ' Methods usage error'); 
            define('ABC_SQL_SEQUENCE',                 ' Method sequence error');     
            /**
            * Acnive Record
            */ 
            define('ABC_SQL_FIND_SEQUENCE',            ' Method %s can not be used in the context of  ::%s()');
            define('ABC_SQL_INCORRECT_TYPE',           ' Type %s is not correct.');
            define('ABC_SQL_INVALID_TYPE',             ' Type %s is not supported. Use the type converter.');
            define('ABC_SQL_ATTRIBUTE_NOT_FOUND',      ' The %s attribute is not in the model.');
            define('ABC_SQL_JSON_ERROR',               ' JSON decoding error'); 
            define('ABC_SQL_NO_PRIMARY',               ' No primary key');
    }
}
















